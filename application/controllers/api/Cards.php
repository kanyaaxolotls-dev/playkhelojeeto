<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cards extends CI_Controller
{
    // =========================================================
    // CONFIG
    // =========================================================

     const GAME_ID_DEFAULT          = 11;

     const ADMIN_COMMISSION_RATE    = 20;
     const DEALER_COMMISSION_RATE   = 2;
     const DISTRIBUTOR_RATE         = 0.5;

     const STRAIGHT_MULTIPLIER      = 12;
     const SUIT_MULTIPLIER          = 3.6;
     const GROUP_MULTIPLIER         = 1.8;
     const SPLIT_MULTIPLIER         = 1.8;

    // =========================================================
    // ALL CARDS
    // =========================================================

    private $allCards = [

        'RHA','RH2','RH3','RH4','RH5','RH6','RH7','RH8','RH9','RH10','RHJ','RHQ','RHK',

        'BSA','BS2','BS3','BS4','BS5','BS6','BS7','BS8','BS9','BS10','BSJ','BSQ','BSK',

        'BCA','BC2','BC3','BC4','BC5','BC6','BC7','BC8','BC9','BC10','BCJ','BCQ','BCK',

        'BDA','BD2','BD3','BD4','BD5','BD6','BD7','BD8','BD9','BD10','BDJ','BDQ','BDK'
    ];

    // =========================================================
    // SUITS
    // =========================================================

    private $heart = [
        'RHA','RH2','RH3','RH4','RH5','RH6',
        'RH7','RH8','RH9','RH10','RHJ','RHQ','RHK'
    ];

    private $spade = [
        'BSA','BS2','BS3','BS4','BS5','BS6',
        'BS7','BS8','BS9','BS10','BSJ','BSQ','BSK'
    ];

    private $club = [
        'BCA','BC2','BC3','BC4','BC5','BC6',
        'BC7','BC8','BC9','BC10','BCJ','BCQ','BCK'
    ];

    private $diamond = [
        'BDA','BD2','BD3','BD4','BD5','BD6',
        'BD7','BD8','BD9','BD10','BDJ','BDQ','BDK'
    ];

    // =========================================================
    // GROUPS
    // =========================================================

    private $groupA6 = [

        'RHA','RH2','RH3','RH4','RH5','RH6',

        'BSA','BS2','BS3','BS4','BS5','BS6',

        'BCA','BC2','BC3','BC4','BC5','BC6',

        'BDA','BD2','BD3','BD4','BD5','BD6'
    ];

    private $group8K = [

        'RH8','RH9','RH10','RHJ','RHQ','RHK',

        'BS8','BS9','BS10','BSJ','BSQ','BSK',

        'BC8','BC9','BC10','BCJ','BCQ','BCK',

        'BD8','BD9','BD10','BDJ','BDQ','BDK'
    ];

    // =========================================================
    // VALID BET TYPES
    // =========================================================

    private $validBetTypes = [
        'straight',
        'suit',
        'group',
        'split'
    ];

    // =========================================================
    // JSON RESPONSE
    // =========================================================

    private function json($data)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }

    // =========================================================
    // GAME ID
    // =========================================================

    private function get_game_id()
    {
        $gameId =
            $this->input->post('gameid')
            ?? $this->input->post('game_id');

        return $gameId
            ? intval($gameId)
            : self::GAME_ID_DEFAULT;
    }

    // =========================================================
    // ANDAR BAHAR MAP
    // =========================================================

    private function get_andar_bahar_map($period_id)
    {
        $cards = $this->allCards;

       // srand(crc32($period_id));
  mt_srand(crc32($period_id));
        shuffle($cards);

        // Prepare cards with their sides
        $cardsWithSide = [];
        for ($i = 0; $i < 26; $i++) {
            $cardsWithSide[$cards[$i]] = 'ANDAR';
        }
        for ($i = 26; $i < 52; $i++) {
            $cardsWithSide[$cards[$i]] = 'BAHAR';
        }

        return [
            'andar' => array_slice($cards, 0, 26),
            'bahar' => array_slice($cards, 26),
            'cards_with_side' => $cardsWithSide
        ];
    }

    // =========================================================
    // CRON
    // =========================================================

    public function cron()
    {
        $this->db->trans_begin();

        try {

            $gameId = $this->get_game_id();

            $game = $this->db
                ->query(
                    "SELECT * FROM tbl_games WHERE id=? FOR UPDATE",
                    [$gameId]
                )
                ->row();

            if (!$game) {

                throw new Exception('Game Not Found');
            }

            $period_id  = $game->period_id;
            $manual_set = intval($game->manual_set);
            $win_card   = trim($game->win_number);

            $already = $this->db->get_where(
                'tbl_cards_results',
                [
                    'period_id' => $period_id,
                    'game_id'   => $gameId
                ]
            )->row();

            if ($already) {

                $this->db->trans_commit();

                return $this->json([
                    'status' => 'error',
                    'message' => 'Result Already Generated'
                ]);
            }

            // =====================================================
            // FETCH BETS
            // =====================================================

            $bets = $this->db->get_where(
                'tbl_cards_bet',
                [
                    'period_id' => $period_id,
                    'game_id'   => $gameId,
                    'status'    => 'pending'
                ]
            )->result();

            // =====================================================
            // TOTALS
            // =====================================================

            $totalBetAmount = 0;
            $dealerCommission = 0;
            $distributorCommission = 0;

            foreach ($bets as $bet) {

                $totalBetAmount += floatval($bet->amount);

                $dealerCommission += floatval($bet->dealer_commission);

                $distributorCommission += floatval($bet->distributor_commission);
            }

            $adminCommission = round(
                ($totalBetAmount * self::ADMIN_COMMISSION_RATE) / 100,
                2
            );

            $remainingProfit =
                $totalBetAmount
                - $dealerCommission
                - $distributorCommission
                - $adminCommission;

            if ($remainingProfit < 0) {
                $remainingProfit = 0;
            }

            // =====================================================
            // EXPOSURE
            // =====================================================
            $map = $this->get_andar_bahar_map($period_id);

            $exposures = [];

            foreach ($this->allCards as $card) {

                $exposures[$card] = 0;
            }

            foreach ($bets as $bet) {

                foreach ($this->allCards as $card) {

                    $exposures[$card] += $this->calculate_payout(
                        $bet->bet_type,
                        $bet->bet,
                        $card,
                        $bet->amount,
                        $map
                    );
                }
            }
            
            // =====================================================
            // WIN CARD (LUCKEY36 STYLE)
            // =====================================================

            if ($manual_set == 1 && !empty($win_card)) {

                $selectedCard = strtoupper($win_card);
                $selectedSide = $map['cards_with_side'][$selectedCard] ?? 'UNKNOWN';

            } else {

                // Find safest cards (payout <= remainingProfit, with highest payout)
                $safestCards = [];
                $maxEligiblePayout = null;
                
                foreach ($exposures as $card => $payout) {
                    if ($payout <= $remainingProfit) {
                        if ($maxEligiblePayout === null || $payout > $maxEligiblePayout) {
                            $maxEligiblePayout = $payout;
                            $safestCards = [$card];
                        } elseif ($payout == $maxEligiblePayout) {
                            $safestCards[] = $card;
                        }
                    }
                }
                
                if (!empty($safestCards)) {
                    $selectedCard = $safestCards[array_rand($safestCards)];
                } else {
                    // No safe card - find card with payout closest to remainingProfit
                    $closestCards = [];
                    $closestDistance = null;
                    
                    foreach ($exposures as $card => $payout) {
                        $distance = abs($payout - $remainingProfit);
                        
                        if ($closestDistance === null || $distance < $closestDistance) {
                            $closestDistance = $distance;
                            $closestCards = [$card];
                        } elseif ($distance == $closestDistance) {
                            $closestCards[] = $card;
                        }
                    }
                    
                    $selectedCard = $closestCards[array_rand($closestCards)];
                }
                
                $selectedSide = $map['cards_with_side'][$selectedCard] ?? 'UNKNOWN';
            }

            // =====================================================
            // SAVE RESULT (with side)
            // =====================================================

            $this->db->insert(
                'tbl_cards_results',
                [
                    'period_id' => $period_id,
                    'game_id'   => $gameId,
                    'win_card'  => $selectedCard,
                    'win_side'  => $selectedSide,
                    'created_at'=> date('Y-m-d H:i:s')
                ]
            );

            // =====================================================
            // PROCESS BETS
            // =====================================================


            foreach ($bets as $bet) {

                $win_amount = $this->calculate_payout(
                    $bet->bet_type,
                    $bet->bet,
                    $selectedCard,
                    $bet->amount,
                    $map
                );

                $status = 'lost';

                if ($win_amount > 0) {

                    $status = 'won';

                    $this->db->set(
                        'winning_wallet',
                        'winning_wallet + '.$win_amount,
                        FALSE
                    );

                    $this->db->where('id', $bet->userid);

                    $this->db->update('tbl_users');
                }

                $this->db->where('id', $bet->id);

                $this->db->update(
                    'tbl_cards_bet',
                    [
                        'status'      => $status,
                        'win_amount'  => $win_amount,
                        'result_card' => $selectedCard
                    ]
                );
            }

            // =====================================================
            // UPDATE GAME
            // =====================================================

            $this->db->where('id', $gameId);

            $this->db->update(
                'tbl_games',
                [
                    'period_id' => $period_id + 1,
                    'manual_set'=> 0,
                    'win_number'=> $selectedCard
                ]
            );

            $this->db->trans_commit();

            return $this->json([
                'status' => 'success',
                'card'   => $selectedCard,
                'side'   => $selectedSide
            ]);

        } catch (Exception $e) {

            $this->db->trans_rollback();

            log_message('error', $e->getMessage());

            return $this->json([
                'status' => 'error',
                'message'=> $e->getMessage()
            ]);
        }
    }

    // =========================================================
    // PAYOUT
    // =========================================================

    private function calculate_payout(
        $bet_type,
        $bet_value,
        $winning_card,
        $amount,
        $map = []
    )
    {
        $amount = floatval($amount);

        $bet_type = strtolower(trim($bet_type));

        $bet_value = strtoupper(trim($bet_value));

        $winning_card = strtoupper(trim($winning_card));

        // =====================================================
        // STRAIGHT
        // =====================================================

        if ($bet_type == 'straight') {

            $parts = explode('|', $bet_value);

            $card = strtoupper($parts[0] ?? '');

            $side = strtoupper($parts[1] ?? '');

            $isCard = ($card == $winning_card);

            $isSide =
                ($side == 'ANDAR'
                    && in_array($winning_card, $map['andar'] ?? []))
                ||
                ($side == 'BAHAR'
                    && in_array($winning_card, $map['bahar'] ?? []));

            if ($isCard && $isSide) {

                return round(
                    $amount * self::STRAIGHT_MULTIPLIER,
                    2
                );
            }

            return 0;
        }

        // =====================================================
        // SUIT
        // =====================================================

        if ($bet_type == 'suit') {

            if (
                $bet_value == 'HEART'
                && in_array($winning_card, $this->heart)
            ) {

                return round(
                    $amount * self::SUIT_MULTIPLIER,
                    2
                );
            }

            if (
                $bet_value == 'SPADE'
                && in_array($winning_card, $this->spade)
            ) {

                return round(
                    $amount * self::SUIT_MULTIPLIER,
                    2
                );
            }

            if (
                $bet_value == 'CLUB'
                && in_array($winning_card, $this->club)
            ) {

                return round(
                    $amount * self::SUIT_MULTIPLIER,
                    2
                );
            }

            if (
                $bet_value == 'DIAMOND'
                && in_array($winning_card, $this->diamond)
            ) {

                return round(
                    $amount * self::SUIT_MULTIPLIER,
                    2
                );
            }

            return 0;
        }

        // =====================================================
        // GROUP
        // =====================================================

        if ($bet_type == 'group') {

            if (
                $bet_value == 'A-6'
                && in_array($winning_card, $this->groupA6)
            ) {

                return round(
                    $amount * self::GROUP_MULTIPLIER,
                    2
                );
            }

            if (
                $bet_value == '8-K'
                && in_array($winning_card, $this->group8K)
            ) {

                return round(
                    $amount * self::GROUP_MULTIPLIER,
                    2
                );
            }

            return 0;
        }

        // =====================================================
        // SPLIT
        // =====================================================

        if ($bet_type == 'split') {

            $splitAmount = $amount / 2;

            if ($bet_value == 'RED') {

                if (
                    in_array($winning_card, $this->heart)
                    ||
                    in_array($winning_card, $this->diamond)
                ) {

                    return round(
                        $splitAmount * self::SPLIT_MULTIPLIER,
                        2
                    );
                }
            }

            if ($bet_value == 'BLACK') {

                if (
                    in_array($winning_card, $this->spade)
                    ||
                    in_array($winning_card, $this->club)
                ) {

                    return round(
                        $splitAmount * self::SPLIT_MULTIPLIER,
                        2
                    );
                }
            }

            return 0;
        }

        return 0;
    }

    // =========================================================
    // PLACE BET
    // =========================================================

    public function place_bet()
    {
        $this->db->trans_begin();

        try {

            $userid = intval($this->input->post('userid'));

            $amount = floatval($this->input->post('amount'));

            $bet_type =
                strtolower(trim($this->input->post('bet_type')));

            $bet_value =
                strtoupper(trim($this->input->post('bet_value')));

            $gameId = $this->get_game_id();

            if (
                !$userid
                || !$amount
                || !$bet_type
                || !$bet_value
            ) {

                throw new Exception('Invalid Input');
            }

            if (!in_array($bet_type, $this->validBetTypes)) {

                throw new Exception('Invalid Bet Type');
            }

            $game = $this->db->get_where(
                'tbl_games',
                ['id' => $gameId]
            )->row();

            if (!$game) {

                throw new Exception('Game Not Found');
            }

            $period_id = $game->period_id;

            // =====================================================
            // LOCK USER
            // =====================================================

            $user = $this->db
                ->query(
                    "SELECT * FROM tbl_users WHERE id=? FOR UPDATE",
                    [$userid]
                )
                ->row();

            if (!$user) {

                throw new Exception('Invalid User');
            }

            if ($user->wallet < $amount) {

                throw new Exception('Insufficient Balance');
            }

            // =====================================================
            // COMMISSION
            // =====================================================

            $dealer_commission = round(
                ($amount * self::DEALER_COMMISSION_RATE) / 100,
                2
            );

            $distributor_commission = round(
                ($amount * self::DISTRIBUTOR_RATE) / 100,
                2
            );

            $admin_commission = round(
                ($amount * self::ADMIN_COMMISSION_RATE) / 100,
                2
            );

            // =====================================================
            // INSERT BET
            // =====================================================

            $insertData = [

                'userid'                   => $userid,

                'bet'                      => $bet_value,

                'bet_type'                 => $bet_type,

                'period_id'                => $period_id,

                'game_id'                  => $gameId,

                'amount'                   => $amount,

                'dealer_commission'        => $dealer_commission,

                'distributor_commission'   => $distributor_commission,

                'admin_commission'         => $admin_commission,

                'user_amount'              => $user->wallet,

                'date'                     => date('Y-m-d H:i:s'),

                'status'                   => 'pending'
            ];

            $this->db->insert(
                'tbl_cards_bet',
                $insertData
            );

            // =====================================================
            // WALLET DEDUCT
            // =====================================================

            $this->db->set(
                'wallet',
                'wallet - '.$amount,
                FALSE
            );

            $this->db->where('id', $userid);

            $this->db->update('tbl_users');

            // =====================================================
            // TRANSACTION
            // =====================================================

            $this->db->insert(
                'tbl_transactions',
                [
                    'userid' => $userid,
                    'amount' => $amount,
                    'status' => 'debit',
                    'type'   => 'Cards Bet'
                ]
            );

            $this->db->trans_commit();

            return $this->json([
                'status' => 'success',
                'message'=> 'Bet Placed Successfully'
            ]);

        } catch (Exception $e) {

            $this->db->trans_rollback();

            return $this->json([
                'status' => 'error',
                'message'=> $e->getMessage()
            ]);
        }
    }

    // ======================================================
    // SHOW RESULT (with side)
    // ======================================================

    public function show_result()
    {
        $gameId = $this->get_game_id();

        $period_id = $this->db_model->select(
            'period_id',
            'tbl_games',
            ['id' => $gameId]
        );

        $result = $this->db_model->count_all(
            'tbl_cards_results',
            [
                'period_id' => $period_id,
                'game_id'   => $gameId
            ]
        );

        $result2 = $this->db_model->select(
            'win_number',
            'tbl_games',
            ['id' => $gameId]
        );

        $limit = 10;

        $this->db->select('win_card, win_side');
        $this->db->from('tbl_cards_results');
        $this->db->where('game_id', $gameId);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);

        $lstresult = $this->db->get()->result();

        if ($result > 0) {

            $currentResult = $this->db->get_where(
                'tbl_cards_results',
                [
                    'period_id' => $period_id,
                    'game_id'   => $gameId
                ]
            )->row();

            $response = [
                'status'   => 'success',
                'card'     => $currentResult->win_card,
                'side'     => $currentResult->win_side,
                'last_res' => $lstresult
            ];

        } elseif ($result2 != null) {

            // Get side for manual win_number
            $map = $this->get_andar_bahar_map($period_id);
            $side = $map['cards_with_side'][$result2] ?? 'UNKNOWN';

            $response = [
                'status'   => 'success',
                'card'     => $result2,
                'side'     => $side,
                'last_res' => $lstresult
            ];

        } else {

            $response = [
                'status'  => 'error',
                'message' => 'Something Went Wrong'
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // RESULTS
    // ======================================================

    public function results()
    {
        $gameId = $this->get_game_id();

        $period_id = $this->db_model->select(
            'period_id',
            'tbl_games',
            ['id' => $gameId]
        );

        $limit = 10;

        $this->db->select('win_card, win_side');
        $this->db->from('tbl_cards_results');
        $this->db->where('game_id', $gameId);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);

        $query = $this->db->get();

        $result = $query->num_rows() > 0
            ? $query->result()
            : [];

        $response = [
            'status'  => 'success',
            'results' => $result
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // PERIOD ID
    // ======================================================

    public function period_id()
    {
        $gameId = $this->get_game_id();

        $period_id = $this->db_model->select(
            'period_id',
            'tbl_games',
            ['id' => $gameId]
        );

        $response = [
            'status'    => 'success',
            'game_id'   => $gameId,
            'period_id' => $period_id
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // TIMER
    // ======================================================

    public function timer()
    {
        $gameId = $this->get_game_id();

        $period_id = $this->db_model->select(
            'period_id',
            'tbl_games',
            ['id' => $gameId]
        );

        $currentTime = time();

        $remaining = 59 - ($currentTime % 60);

        $response = [
            'status'      => 'success',
            'game_id'     => $gameId,
            'period_id'   => $period_id,
            'remaining_s' => $remaining
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // WALLET BALANCE
    // ======================================================

    public function wallet_balance()
    {
        $userid = $this->input->post('userid');

        if (!$userid) {

            $response = [
                'status'  => 'error',
                'message' => 'User ID Required'
            ];

        } else {

            $user = $this->db->get_where('tbl_users', [
                'id' => $userid
            ])->row();

            if (!$user) {

                $response = [
                    'status'  => 'error',
                    'message' => 'Invalid User'
                ];

            } else {

                $pending_amount = floatval(
                    $this->db_model->sum(
                        'amount',
                        'tbl_cards_bet',
                        [
                            'userid' => $userid,
                            'status' => 'pending'
                        ]
                    )
                );

                $response = [
                    'status' => 'success',
                    'wallet' => floatval($user->wallet),
                    'winning_wallet' => floatval($user->winning_wallet),
                    'pending_bets' => $pending_amount
                ];
            }
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // MY BETS
    // ======================================================

    public function my_bets()
    {
        $userid = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        $check_user = $this->db_model->count_all(
            'tbl_users',
            ['id' => $userid]
        );

        if ($check_user <= 0) {

            $response = [
                'status'  => 'error',
                'message' => 'Invalid User'
            ];

        } else {

            $this->db->select(
                'bet_type,
                 SUM(amount) as total_amount,
                 COUNT(*) as bet_count'
            );

            $this->db->from('tbl_cards_bet');
            $this->db->where('userid', $userid);
            $this->db->where('period_id', $period_id);
            $this->db->where('status', 'pending');
            $this->db->group_by('bet_type');

            $bets = $this->db->get()->result();

            $betAmounts = [];
            $total_bet = 0;

            foreach ($bets as $bet) {
                $betAmounts[$bet->bet_type] = [
                    'amount' => $bet->total_amount,
                    'count'  => $bet->bet_count
                ];
                $total_bet += $bet->total_amount;
            }

            $response = [
                'status'    => 'success',
                'my_bets'   => $betAmounts,
                'total_bet' => $total_bet
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // TAKE WINNING
    // ======================================================

    public function take()
    {
        $userid = $this->input->post('userid');

        if (!$userid) {
            $response = [
                'status'  => 'error',
                'message' => 'User ID Required'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $user = $this->db->get_where('tbl_users', ['id' => $userid])->row();

        if (!$user) {
            $response = [
                'status'  => 'error',
                'message' => 'Invalid User'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        if ($user->winning_wallet > 0) {
            $new_wallet = $user->wallet + $user->winning_wallet;

            $this->db->where('id', $userid);
            $this->db->update('tbl_users', [
                'wallet' => $new_wallet,
                'winning_wallet' => 0
            ]);

            $this->db->insert('tbl_transactions', [
                'userid' => $userid,
                'amount' => $user->winning_wallet,
                'status' => 'credit',
                'type'   => 'Winning Transfer'
            ]);

            $response = [
                'status'  => 'success',
                'message' => 'Winning Added Successfully',
                'wallet'  => $new_wallet
            ];

        } else {
            $response = [
                'status'  => 'error',
                'message' => 'Winning Wallet Empty'
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // TOTAL BETS
    // ======================================================

    public function total_bets()
    {
        $userid = $this->input->post('userid');
        $check_user = $this->db_model->count_all('tbl_users', ['id' => $userid]);
        $today_date = date('Y-m-d');

        if ($check_user <= 0) {
            $response = [
                'status'  => 'error',
                'message' => 'Invalid Userid'
            ];
        } else {
            $result = $this->db
                ->select('SUM(amount) as total_amount, COUNT(*) as total_count')
                ->where("DATE(date) =", $today_date)
                ->where('userid', $userid)
                ->where('game_id', $this->get_game_id())
                ->get('tbl_cards_bet')
                ->row();

            $total_amount = ($result) ? $result->total_amount + 0 : 0;
            $total_count = ($result) ? $result->total_count : 0;

            $response = [
                'status' => 'success',
                'total_bet_amount' => $total_amount,
                'total_bet_count' => $total_count
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // WIN BETS
    // ======================================================

    public function win_bets()
    {
        $userid = $this->input->post('userid');
        $check_user = $this->db_model->count_all('tbl_users', ['id' => $userid]);
        $today_date = date('Y-m-d');

        if ($check_user <= 0) {
            $response = [
                'status'  => 'error',
                'message' => 'Invalid Userid'
            ];
        } else {
            $win_bet = $this->db
                ->select_sum('amount')
                ->where("DATE(date) =", $today_date)
                ->where('userid', $userid)
                ->where('status', 'won')
                ->where('game_id', $this->get_game_id())
                ->get('tbl_cards_bet')
                ->row()
                ->amount + 0;

            $response = [
                'status' => 'success',
                'win_bets' => $win_bet
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // CANCEL LAST BET
    // ======================================================

    public function cancel_last_bet()
    {
        $userid = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        if (!$userid || !$period_id) {
            $response = [
                'status'  => 'error',
                'message' => 'Userid & Period ID Required'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $this->db->where([
            'userid'   => $userid,
            'period_id'=> $period_id,
            'status'   => 'pending'
        ]);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $bet = $this->db->get('tbl_cards_bet')->row();

        if (!$bet) {
            $response = [
                'status'  => 'error',
                'message' => 'No Pending Bet Found'
            ];
        } else {
            // Cancel bet
            $this->db->where('id', $bet->id);
            $this->db->update('tbl_cards_bet', ['status' => 'cancelled']);

            // Refund
            $this->db->set('wallet', 'wallet + '.$bet->amount, FALSE);
            $this->db->where('id', $userid);
            $this->db->update('tbl_users');

            // Transaction
            $this->db->insert('tbl_transactions', [
                'userid' => $userid,
                'amount' => $bet->amount,
                'status' => 'credit',
                'type'   => 'Cancel Bet Refund'
            ]);

            $response = [
                'status'  => 'success',
                'message' => 'Last Bet Cancelled',
                'refund'  => $bet->amount
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // CANCEL ALL BETS
    // ======================================================

    public function cancel_all_bets()
    {
        $userid = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        if (!$userid || !$period_id) {
            $response = [
                'status'  => 'error',
                'message' => 'Userid & Period ID Required'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $this->db->where([
            'userid'   => $userid,
            'period_id'=> $period_id,
            'status'   => 'pending'
        ]);
        $bets = $this->db->get('tbl_cards_bet')->result();

        if (empty($bets)) {
            $response = [
                'status'  => 'error',
                'message' => 'No Pending Bets Found'
            ];
        } else {
            $total_refund = 0;
            foreach ($bets as $bet) {
                $total_refund += $bet->amount;
                $this->db->where('id', $bet->id);
                $this->db->update('tbl_cards_bet', ['status' => 'cancelled']);
                $this->db->insert('tbl_transactions', [
                    'userid' => $userid,
                    'amount' => $bet->amount,
                    'status' => 'credit',
                    'type'   => 'Cancel Bet Refund'
                ]);
            }

            $this->db->set('wallet', 'wallet + '.$total_refund, FALSE);
            $this->db->where('id', $userid);
            $this->db->update('tbl_users');

            $response = [
                'status' => 'success',
                'message' => 'All Bets Cancelled',
                'refund' => $total_refund
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // REPEAT LAST BET
    // ======================================================

    public function repeat_last_bet()
    {
        $userid = $this->input->post('userid');
        $gameId = $this->get_game_id();

        $current_period_id = $this->db_model->select(
            'period_id',
            'tbl_games',
            ['id' => $gameId]
        );

        if (!$userid) {
            $response = [
                'status'  => 'error',
                'message' => 'Userid Required'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $last_bet = $this->db
            ->where('userid', $userid)
            ->where('game_id', $gameId)
            ->where_in('status', ['pending','won','lost'])
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('tbl_cards_bet')
            ->row();

        if (!$last_bet) {
            $response = [
                'status'  => 'error',
                'message' => 'No Previous Bet Found'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $user = $this->db->get_where('tbl_users', ['id' => $userid])->row();
        $wallet = floatval($user->wallet);

        if ($wallet < $last_bet->amount) {
            $response = [
                'status'  => 'error',
                'message' => 'Insufficient Balance'
            ];
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        $dealer_commission = round(($last_bet->amount * self::DEALER_COMMISSION_RATE) / 100, 2);
        $distributor_commission = round(($last_bet->amount * self::DISTRIBUTOR_RATE) / 100, 2);
        $admin_commission = round(($last_bet->amount * self::ADMIN_COMMISSION_RATE) / 100, 2);

        $insertData = [
            'userid'      => $userid,
            'bet'         => $last_bet->bet,
            'bet_type'    => $last_bet->bet_type,
            'period_id'   => $current_period_id,
            'game_id'     => $gameId,
            'amount'      => $last_bet->amount,
            'dealer_commission' => $dealer_commission,
            'distributor_commission' => $distributor_commission,
            'admin_commission' => $admin_commission,
            'user_amount' => $wallet,
            'date'        => date('Y-m-d H:i:s'),
            'status'      => 'pending'
        ];

        $this->db->insert('tbl_cards_bet', $insertData);
        $bet_id = $this->db->insert_id();

        $dealer_id = $user->dealer_id ?? 0;
        $distributor_id = $user->distributor_id ?? 0;

        if ($dealer_commission > 0) {
            $this->db->insert('tbl_commission_history', [
                'source_user_id' => $userid,
                'dealer_id' => $dealer_id,
                'distributor_id' => $distributor_id,
                'commission_type' => 'dealer',
                'amount' => $dealer_commission,
                'bet_amount' => $last_bet->amount,
                'dealer_commission' => $dealer_commission,
                'dealer_commission_credited' => $dealer_commission,
                'rate' => self::DEALER_COMMISSION_RATE,
                'period_id' => $current_period_id,
                'game_id' => $gameId,
                'game_type' => 'cards',
                'bet_id' => $bet_id,
                'note' => 'Dealer commission from cards game',
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        if ($distributor_commission > 0) {
            $this->db->insert('tbl_commission_history', [
                'source_user_id' => $userid,
                'dealer_id' => $dealer_id,
                'distributor_id' => $distributor_id,
                'commission_type' => 'distributor',
                'amount' => $distributor_commission,
                'bet_amount' => $last_bet->amount,
                'distributor_commission' => $distributor_commission,
                'distributor_commission_credited' => $distributor_commission,
                'rate' => self::DISTRIBUTOR_RATE,
                'period_id' => $current_period_id,
                'game_id' => $gameId,
                'game_type' => 'cards',
                'bet_id' => $bet_id,
                'note' => 'Distributor commission from cards game',
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        if ($admin_commission > 0) {
            $this->db->insert('tbl_commission_history', [
                'source_user_id' => $userid,
                'dealer_id' => $dealer_id,
                'distributor_id' => $distributor_id,
                'commission_type' => 'admin',
                'amount' => $admin_commission,
                'bet_amount' => $last_bet->amount,
                'rate' => self::ADMIN_COMMISSION_RATE,
                'period_id' => $current_period_id,
                'game_id' => $gameId,
                'game_type' => 'cards',
                'bet_id' => $bet_id,
                'note' => 'Admin commission from cards game',
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        $new_wallet = $wallet - $last_bet->amount;
        $this->db->where('id', $userid);
        $this->db->update('tbl_users', ['wallet' => $new_wallet]);

        $this->db->insert('tbl_transactions', [
            'userid' => $userid,
            'amount' => $last_bet->amount,
            'status' => 'debit',
            'type'   => 'Repeat Last Bet'
        ]);

        $response = [
            'status' => 'success',
            'message' => 'Last Bet Repeated',
            'period_id' => $current_period_id,
            'bet_type' => $last_bet->bet_type,
            'bet_value' => $last_bet->bet,
            'amount' => $last_bet->amount,
            'wallet' => $new_wallet
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // USER HISTORY
    // ======================================================

    public function userhistory()
    {
        $userid = $this->input->post('userid');
        $gameid = $this->input->post('gameid');

        if (!$userid || !$gameid) {
            $body = json_decode(file_get_contents('php://input'), true);
            $userid = $userid ?: ($body['userid'] ?? null);
            $gameid = $gameid ?: ($body['gameid'] ?? null);
        }

        if (empty($userid) || empty($gameid)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'status' => 'error',
                    'message' => 'userid and gameid are required'
                ]));
        }

        $this->db->select('id, date, period_id, amount, result_card, win_amount, status, game_id');
        $this->db->from('tbl_cards_bet');
        $this->db->where('userid', $userid);
        $this->db->where('game_id', $gameid);
        $this->db->order_by('id', 'DESC');

        $history = $this->db->get()->result();

        $data = [];
        $sr = 1;

        foreach ($history as $row) {
            $data[] = [
                'sr_no'      => $sr++,
                'date'       => $row->date,
                'period_id'  => $row->period_id,
                'amount'     => $row->amount,
                'result'     => $row->result_card,
                'win_amount' => $row->win_amount,
                'status'     => $row->status
            ];
        }

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'success',
                'data'   => $data
            ]));
    }
}
// END OF FILE