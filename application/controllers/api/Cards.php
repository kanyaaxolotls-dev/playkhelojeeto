<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cards extends CI_Controller {

    // ======================================================
    // ALL CARDS
    // ======================================================

    private $allCards = [

        'RHA','RH2','RH3','RH4','RH5','RH6','RH7','RH8','RH9','RH10','RHJ','RHQ','RHK',

        'BSA','BS2','BS3','BS4','BS5','BS6','BS7','BS8','BS9','BS10','BSJ','BSQ','BSK',

        'BCA','BC2','BC3','BC4','BC5','BC6','BC7','BC8','BC9','BC10','BCJ','BCQ','BCK',

        'BDA','BD2','BD3','BD4','BD5','BD6','BD7','BD8','BD9','BD10','BDJ','BDQ','BDK'
    ];

    // ======================================================
    // CRON
    // ======================================================

    public function cron()
    {
        $this->db->trans_start();

        $gameId = $this->get_game_id();

        $game = $this->db->get_where('tbl_games', [
            'id' => $gameId
        ])->row();

        if (!$game) {
            return;
        }

        $period_id  = $game->period_id;
        $manual_set = $game->manual_set;
        $win_card   = $game->win_number;

        $already = $this->db->get_where('tbl_cards_results', [
            'period_id' => $period_id,
            'game_id'   => $gameId
        ])->row();

        if ($already) {

            $this->db->trans_complete();

            return;
        }

        // ======================================================
        // MANUAL RESULT
        // ======================================================

        if ($manual_set == 1 && !empty($win_card)) {

            $selectedCard = $win_card;

        } else {

            $bets = $this->db->get_where('tbl_cards_bet', [

                'period_id' => $period_id,
                'game_id'   => $gameId,
                'status'    => 'pending'

            ])->result();

            // ======================================================
            // NO BETS
            // ======================================================

            if (empty($bets)) {

                $selectedCard = $this->allCards[array_rand($this->allCards)];

            } else {

                // ======================================================
                // TOTAL BET
                // ======================================================

                $totalBetAmount = 0;

                $totalDealerCommission = 0;

                $totalDistributorCommission = 0;

                foreach ($bets as $bet) {

                    $amount = floatval($bet->amount);

                    $dealerCommission = floatval($bet->dealer_commission ?? 0);

                    $distributorCommission = floatval($bet->distributor_commission ?? 0);

                    $totalBetAmount += $amount;

                    $totalDealerCommission += $dealerCommission;

                    $totalDistributorCommission += $distributorCommission;

                    $bet->exposure_amount = $amount;
                }

                // ======================================================
                // ADMIN 20%
                // ======================================================

                $adminCommission = round($totalBetAmount * 20 / 100, 2);

                $totalEffectiveAmount =

                    $totalBetAmount
                    - $totalDealerCommission
                    - $totalDistributorCommission;

                $remainingProfit =

                    $totalEffectiveAmount
                    - $adminCommission;

                if ($remainingProfit < 0) {

                    $remainingProfit = 0;
                }

                // ======================================================
                // EXPOSURE CALCULATION
                // ======================================================

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
                            $bet->exposure_amount
                        );
                    }
                }

                // ======================================================
                // SAFEST CARD
                // ======================================================

                $safestCards = [];

                $maxEligiblePayout = null;

                foreach ($exposures as $card => $payout) {

                    if ($payout <= $remainingProfit) {

                        if (

                            $maxEligiblePayout === null
                            || $payout > $maxEligiblePayout

                        ) {

                            $maxEligiblePayout = $payout;

                            $safestCards = [$card];

                        } elseif ($payout == $maxEligiblePayout) {

                            $safestCards[] = $card;
                        }
                    }
                }

                // ======================================================
                // SAFE CARD FOUND
                // ======================================================

                if (!empty($safestCards)) {

                    $selectedCard =

                        $safestCards[array_rand($safestCards)];

                } else {

                    // ======================================================
                    // MINIMUM LOSS CARD
                    // ======================================================

                    $closestCards = [];

                    $closestDistance = null;

                    foreach ($exposures as $card => $payout) {

                        $distance = abs($payout - $remainingProfit);

                        if (

                            $closestDistance === null
                            || $distance < $closestDistance

                        ) {

                            $closestDistance = $distance;

                            $closestCards = [$card];

                        } elseif ($distance == $closestDistance) {

                            $closestCards[] = $card;
                        }
                    }

                    $selectedCard =

                        $closestCards[array_rand($closestCards)];
                }
            }
        }

        // ======================================================
        // INSERT RESULT
        // ======================================================

        $this->db->insert('tbl_cards_results', [

            'period_id' => $period_id,
            'game_id'   => $gameId,
            'win_card'  => $selectedCard

        ]);
        
        $this->db->insert('tbl_admin_commissions', [
    'period_id'         => $period_id,
    'game_id'           => $gameId,
    'total_bet_amount'  => $totalBetAmount,
    'admin_commission'  => $adminCommission,
    'winning_number'    => $selectedCard,
    'created_at'        => date('Y-m-d H:i:s')
]);

        // ======================================================
        // FETCH BETS
        // ======================================================

        $bets = $this->db->get_where('tbl_cards_bet', [

            'period_id' => $period_id,
            'game_id'   => $gameId,
            'status'    => 'pending'

        ])->result();

        // ======================================================
        // WIN / LOSS PROCESS
        // ======================================================

        foreach ($bets as $bet) {

            $win_amount = $this->calculate_payout(

                $bet->bet_type,
                $bet->bet,
                $selectedCard,
                $bet->amount
            );

            if ($win_amount > 0) {

                // CREDIT WINNING WALLET

                $this->db->set(

                    'winning_wallet',
                    'winning_wallet + '.$win_amount,
                    FALSE
                );

                $this->db->where('id', $bet->userid);

                $this->db->update('tbl_users');

                // TRANSACTION

                $this->db->insert('tbl_transactions', [

                    'userid' => $bet->userid,
                    'amount' => $win_amount,
                    'type'   => 'game_win',
                    'status' => 'credit'
                ]);

                // UPDATE BET

                $this->db->where('id', $bet->id);

                $this->db->update('tbl_cards_bet', [

                    'win_amount' => $win_amount
                ]);

                $status = 'won';

            } else {

                $status = 'lost';
            }

            // UPDATE STATUS

            $this->db->where('id', $bet->id);

            $this->db->update('tbl_cards_bet', [

                'status'      => $status,
                'result_card' => $selectedCard

            ]);
        }

        // ======================================================
        // NEXT PERIOD
        // ======================================================

        $this->db->where('id', $gameId);

        $this->db->update('tbl_games', [

            'period_id' => $period_id + 1,
            'win_number'  => $selectedCard,
            'manual_set'=> 0

        ]);

        $this->db->trans_complete();
    }

    // ======================================================
    // PAYOUT LOGIC
    // ======================================================

    private function calculate_payout(
        $bet_type,
        $bet_value,
        $winning_card,
        $amount
    )
    {
        $amount = floatval($amount);

        $bet_type  = strtolower(trim($bet_type));

        $bet_value = strtoupper(trim($bet_value));

        $winning_card = strtoupper(trim($winning_card));

        // ======================================================
        // SUITS
        // ======================================================

        $heart = [

            'RHA','RH2','RH3','RH4','RH5','RH6',
            'RH7','RH8','RH9','RH10','RHJ','RHQ','RHK'
        ];

        $spade = [

            'BSA','BS2','BS3','BS4','BS5','BS6',
            'BS7','BS8','BS9','BS10','BSJ','BSQ','BSK'
        ];

        $club = [

            'BCA','BC2','BC3','BC4','BC5','BC6',
            'BC7','BC8','BC9','BC10','BCJ','BCQ','BCK'
        ];

        $diamond = [

            'BDA','BD2','BD3','BD4','BD5','BD6',
            'BD7','BD8','BD9','BD10','BDJ','BDQ','BDK'
        ];

        // ======================================================
        // GROUPS
        // ======================================================

        $group_A_6 = [

            'RHA','RH2','RH3','RH4','RH5','RH6',

            'BSA','BS2','BS3','BS4','BS5','BS6',

            'BCA','BC2','BC3','BC4','BC5','BC6',

            'BDA','BD2','BD3','BD4','BD5','BD6'
        ];

        $group_8_K = [

            'RH8','RH9','RH10','RHJ','RHQ','RHK',

            'BS8','BS9','BS10','BSJ','BSQ','BSK',

            'BC8','BC9','BC10','BCJ','BCQ','BCK',

            'BD8','BD9','BD10','BDJ','BDQ','BDK'
        ];

        // ======================================================
        // STRAIGHT CARD
        // PAYOUT = AMOUNT * 12
        // ======================================================

        if ($bet_type == 'straight') {

            if ($bet_value == $winning_card) {

                return round($amount * 12, 2);
            }

            return 0;
        }

        // ======================================================
        // SUIT BET
        // PAYOUT = AMOUNT * 3.6
        // ======================================================

        if ($bet_type == 'suit') {

            if (

                $bet_value == 'HEART'
                && in_array($winning_card, $heart)

            ) {

                return round($amount * 3.6, 2);
            }

            if (

                $bet_value == 'SPADE'
                && in_array($winning_card, $spade)

            ) {

                return round($amount * 3.6, 2);
            }

            if (

                $bet_value == 'CLUB'
                && in_array($winning_card, $club)

            ) {

                return round($amount * 3.6, 2);
            }

            if (

                $bet_value == 'DIAMOND'
                && in_array($winning_card, $diamond)

            ) {

                return round($amount * 3.6, 2);
            }

            return 0;
        }

        // ======================================================
        // GROUP BET
        // PAYOUT = AMOUNT * 1.8
        // ======================================================

        if ($bet_type == 'group') {

            if (

                $bet_value == 'A-6'
                && in_array($winning_card, $group_A_6)

            ) {

                return round($amount * 1.8, 2);
            }

            if (

                $bet_value == '8-K'
                && in_array($winning_card, $group_8_K)

            ) {

                return round($amount * 1.8, 2);
            }

            return 0;
        }

// ======================================================
// SPLIT RED / BLACK
// PAYOUT = AMOUNT * 1.8
// ======================================================

if ($bet_type == 'split') {

    $splitAmount = $amount / 2;

    // =========================================
    // RED = HEART + DIAMOND
    // =========================================

    if ($bet_value == 'RED') {

        $totalWin = 0;

        if (in_array($winning_card, $heart)) {

            $totalWin += ($splitAmount * 1.8);
        }

        if (in_array($winning_card, $diamond)) {

            $totalWin += ($splitAmount * 1.8);
        }

        return round($totalWin, 2);
    }

    // =========================================
    // BLACK = SPADE + CLUB
    // =========================================

    if ($bet_value == 'BLACK') {

        $totalWin = 0;

        if (in_array($winning_card, $spade)) {

            $totalWin += ($splitAmount * 1.8);
        }

        if (in_array($winning_card, $club)) {

            $totalWin += ($splitAmount * 1.8);
        }

        return round($totalWin, 2);
    }

    return 0;
}


        // ======================================================
        // ANDAR / BAHAR
        // PAYOUT = AMOUNT * 12
        // ======================================================

        if ($bet_type == 'andar_bahar') {

            $andarCards = array_merge($heart, $diamond,$spade,$club);

            $baharCards = array_merge($spade, $club,$heart, $diamond);

            if (

                $bet_value == 'ANDAR'
                && in_array($winning_card, $andarCards)

            ) {

                return round($amount * 12, 2);
            }

            if (

                $bet_value == 'BAHAR'
                && in_array($winning_card, $baharCards)

            ) {

                return round($amount * 12, 2);
            }

            return 0;
        }

        return 0;
    }

    // ======================================================
    // PLACE BET
    // ======================================================

    public function place_bet()
    {
        $userid     = $this->input->post('userid');

        $amount     = floatval($this->input->post('amount'));

        $bet_type   = $this->input->post('bet_type');

        $bet_value  = strtoupper(trim($this->input->post('bet_value')));

        $gameId     = $this->get_game_id();

        $period_id  = $this->db_model->select(

            'period_id',
            'tbl_games',
            ['id' => $gameId]
        );

        // ======================================================

        if (

            !$userid
            || !$bet_type
            || !$bet_value
            || $amount <= 0

        ) {

            $response = [

                'status'  => 'error',
                'message' => 'Invalid Input'
            ];

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        // ======================================================

        $user = $this->db->get_where('tbl_users', [

            'id' => $userid

        ])->row();

        if (!$user) {

            $response = [

                'status'  => 'error',
                'message' => 'Invalid User'
            ];

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        // ======================================================

        if ($user->wallet < $amount) {

            $response = [

                'status'  => 'error',
                'message' => 'Insufficient Balance'
            ];

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }

        // ======================================================
        // COMMISSION
        // ======================================================

        $dealer_commission = round($amount * 2 / 100, 2);

        $distributor_commission = round($amount * 0.5 / 100, 2);

        $admin_commission = round($amount * 20 / 100, 2);

        // ======================================================
        // INSERT BET
        // ======================================================

        $insertData = [

            'userid'       => $userid,
            'bet'          => $bet_value,
            'bet_type'     => $bet_type,
            'period_id'    => $period_id,
            'game_id'      => $gameId,
            'amount'       => $amount,
            'dealer_commission' => $dealer_commission,
            'distributor_commission' => $distributor_commission,
            'admin_commission' => $admin_commission,
            'user_amount'  => $user->wallet,
            'date'         => date('Y-m-d H:i:s'),
            'status'       => 'pending'
        ];

        $this->db->insert('tbl_cards_bet', $insertData);
        $bet_id = $this->db->insert_id();

// =============================================
// USER DEALER / DISTRIBUTOR FETCH
// =============================================

$dealer_id = $user->dealer_id ?? 0;

$distributor_id = $user->distributor_id ?? 0;

// =============================================
// DEALER COMMISSION ENTRY
// =============================================

if ($dealer_commission > 0) {

    $this->db->insert('tbl_commission_history', [

        'source_user_id'               => $userid,

        'dealer_id'                   => $dealer_id,

        'distributor_id'              => $distributor_id,

        'commission_type'             => 'dealer',

        'amount'                      => $dealer_commission,

        'bet_amount'                  => $amount,

        'dealer_commission'           => $dealer_commission,

        'dealer_commission_credited'  => $dealer_commission,

        'rate'                        => 2,

        'period_id'                   => $period_id,

        'game_id'                     => $gameId,

        'game_type'                   => 'cards',

        'bet_id'                      => $bet_id,

        'note'                        => 'Dealer commission from cards game',

        'status'                      => 'completed',

        'created_at'                  => date('Y-m-d H:i:s')
    ]);
}

// =============================================
// DISTRIBUTOR COMMISSION ENTRY
// =============================================

if ($distributor_commission > 0) {

    $this->db->insert('tbl_commission_history', [

        'source_user_id'                    => $userid,

        'dealer_id'                        => $dealer_id,

        'distributor_id'                   => $distributor_id,

        'commission_type'                  => 'distributor',

        'amount'                           => $distributor_commission,

        'bet_amount'                       => $amount,

        'distributor_commission'           => $distributor_commission,

        'distributor_commission_credited'  => $distributor_commission,

        'rate'                             => 0.5,

        'period_id'                        => $period_id,

        'game_id'                          => $gameId,

        'game_type'                        => 'cards',

        'bet_id'                           => $bet_id,

        'note'                             => 'Distributor commission from cards game',

        'status'                           => 'completed',

        'created_at'                       => date('Y-m-d H:i:s')
    ]);
}

// =============================================
// ADMIN COMMISSION ENTRY
// =============================================

if ($admin_commission > 0) {

    $this->db->insert('tbl_commission_history', [

        'source_user_id' => $userid,

        'dealer_id' => $dealer_id,

        'distributor_id' => $distributor_id,

        'commission_type' => 'admin',

        'amount' => $admin_commission,

        'bet_amount' => $amount,

        'rate' => 20,

        'period_id' => $period_id,

        'game_id' => $gameId,

        'game_type' => 'cards',

        'bet_id' => $bet_id,

        'note' => 'Admin commission from cards game',

        'status' => 'completed',

        'created_at' => date('Y-m-d H:i:s')
    ]);
}

        // ======================================================
        // DEDUCT WALLET
        // ======================================================

        $new_wallet = $user->wallet - $amount;

        $this->db->where('id', $userid);

        $this->db->update('tbl_users', [

            'wallet' => $new_wallet
        ]);

        // ======================================================
        // TRANSACTION
        // ======================================================

        $this->db->insert('tbl_transactions', [

            'userid' => $userid,
            'amount' => $amount,
            'status' => 'debit',
            'type'   => 'Cards Bet Placed'
        ]);

        // ======================================================

        $response = [

            'status'  => 'success',
            'message' => 'Bet Placed Successfully',

            'data' => [

                'wallet' => $new_wallet,

                'period_id' => $period_id,

                'dealer_commission' => $dealer_commission,

                'distributor_commission' => $distributor_commission,

                'admin_commission' => $admin_commission
            ]
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================
    // GAME ID
    // ======================================================

    private function get_game_id()
    {
        $gameId =

            $this->input->post('gameid')
            ?? $this->input->post('game_id');

        return $gameId ? intval($gameId) : 11;
    }
    
  // ======================================================
// SHOW RESULT
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

    $this->db->select('win_card');

    $this->db->from('tbl_cards_results');

    $this->db->where('game_id', $gameId);

    $this->db->order_by('id', 'DESC');

    $this->db->limit($limit);

    $lstresult = $this->db->get()->result();

    if ($result > 0) {

        $card = $this->db_model->select(
            'win_card',
            'tbl_cards_results',
            [
                'period_id' => $period_id,
                'game_id'   => $gameId
            ]
        );

        $response = [

            'status'   => 'success',

            'card'     => $card,

            'last_res' => $lstresult
        ];

    } elseif ($result2 != null) {

        $response = [

            'status'   => 'success',

            'card'     => $result2,

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

    $this->db->select('win_card');

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

                'winning_wallet' =>
                    floatval($user->winning_wallet),

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

    $user = $this->db->get_where('tbl_users', [

        'id' => $userid

    ])->row();

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

        $new_wallet =
            $user->wallet + $user->winning_wallet;

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

    $check_user = $this->db_model->count_all(
        'tbl_users',
        ['id' => $userid]
    );

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

        $total_amount = ($result)
            ? $result->total_amount + 0
            : 0;

        $total_count = ($result)
            ? $result->total_count
            : 0;

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

    $check_user = $this->db_model->count_all(
        'tbl_users',
        ['id' => $userid]
    );

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

        // CANCEL BET

        $this->db->where('id', $bet->id);

        $this->db->update('tbl_cards_bet', [

            'status' => 'cancelled'
        ]);

        // REFUND

        $this->db->set(

            'wallet',
            'wallet + '.$bet->amount,
            FALSE
        );

        $this->db->where('id', $userid);

        $this->db->update('tbl_users');

        // TRANSACTION

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

            // CANCEL

            $this->db->where('id', $bet->id);

            $this->db->update('tbl_cards_bet', [

                'status' => 'cancelled'
            ]);

            // TRANSACTION

            $this->db->insert('tbl_transactions', [

                'userid' => $userid,

                'amount' => $bet->amount,

                'status' => 'credit',

                'type'   => 'Cancel Bet Refund'
            ]);
        }

        // REFUND WALLET

        $this->db->set(

            'wallet',
            'wallet + '.$total_refund,
            FALSE
        );

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

    // ======================================================

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

    // ======================================================

    $wallet = floatval(

        $this->db_model->select(
            'wallet',
            'tbl_users',
            ['id' => $userid]
        )
    );

    if ($wallet < $last_bet->amount) {

        $response = [

            'status'  => 'error',

            'message' => 'Insufficient Balance'
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ======================================================

    $insertData = [

        'userid'      => $userid,

        'bet'         => $last_bet->bet,

        'bet_type'    => $last_bet->bet_type,

        'period_id'   => $current_period_id,

        'game_id'     => $gameId,

        'amount'      => $last_bet->amount,

        'user_amount' => $wallet,

        'date'        => date('Y-m-d H:i:s'),

        'status'      => 'pending'
    ];

    $this->db->insert('tbl_cards_bet', $insertData);
    $bet_id = $this->db->insert_id();

// =============================================
// USER DEALER / DISTRIBUTOR FETCH
// =============================================

$dealer_id = $user->dealer_id ?? 0;

$distributor_id = $user->distributor_id ?? 0;

// =============================================
// DEALER COMMISSION ENTRY
// =============================================

if ($dealer_commission > 0) {

    $this->db->insert('tbl_commission_history', [

        'source_user_id'               => $userid,

        'dealer_id'                   => $dealer_id,

        'distributor_id'              => $distributor_id,

        'commission_type'             => 'dealer',

        'amount'                      => $dealer_commission,

        'bet_amount'                  => $amount,

        'dealer_commission'           => $dealer_commission,

        'dealer_commission_credited'  => $dealer_commission,

        'rate'                        => 2,

        'period_id'                   => $period_id,

        'game_id'                     => $gameId,

        'game_type'                   => 'cards',

        'bet_id'                      => $bet_id,

        'note'                        => 'Dealer commission from cards game',

        'status'                      => 'completed',

        'created_at'                  => date('Y-m-d H:i:s')
    ]);
}

// =============================================
// DISTRIBUTOR COMMISSION ENTRY
// =============================================

if ($distributor_commission > 0) {

    $this->db->insert('tbl_commission_history', [

        'source_user_id'                    => $userid,

        'dealer_id'                        => $dealer_id,

        'distributor_id'                   => $distributor_id,

        'commission_type'                  => 'distributor',

        'amount'                           => $distributor_commission,

        'bet_amount'                       => $amount,

        'distributor_commission'           => $distributor_commission,

        'distributor_commission_credited'  => $distributor_commission,

        'rate'                             => 0.5,

        'period_id'                        => $period_id,

        'game_id'                          => $gameId,

        'game_type'                        => 'cards',

        'bet_id'                           => $bet_id,

        'note'                             => 'Distributor commission from cards game',

        'status'                           => 'completed',

        'created_at'                       => date('Y-m-d H:i:s')
    ]);
}

// =============================================
// ADMIN COMMISSION ENTRY
// =============================================

if ($admin_commission > 0) {

    $this->db->insert('tbl_commission_history', [

        'source_user_id' => $userid,

        'dealer_id' => $dealer_id,

        'distributor_id' => $distributor_id,

        'commission_type' => 'admin',

        'amount' => $admin_commission,

        'bet_amount' => $amount,

        'rate' => 20,

        'period_id' => $period_id,

        'game_id' => $gameId,

        'game_type' => 'cards',

        'bet_id' => $bet_id,

        'note' => 'Admin commission from cards game',

        'status' => 'completed',

        'created_at' => date('Y-m-d H:i:s')
    ]);
}

    // ======================================================

    $new_wallet = $wallet - $last_bet->amount;

    $this->db->where('id', $userid);

    $this->db->update('tbl_users', [

        'wallet' => $new_wallet
    ]);

    // ======================================================

    $this->db->insert('tbl_transactions', [

        'userid' => $userid,

        'amount' => $last_bet->amount,

        'status' => 'debit',

        'type'   => 'Repeat Last Bet'
    ]);

    // ======================================================

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
}
    
    
