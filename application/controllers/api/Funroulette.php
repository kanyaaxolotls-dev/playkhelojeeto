<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Funroulette extends CI_Controller {

    private $roulette_numbers = [
        '0','00',
        '1','2','3','4','5','6','7','8','9',
        '10','11','12','13','14','15','16','17','18',
        '19','20','21','22','23','24','25','26','27',
        '28','29','30','31','32','33','34','35','36'
    ];

    // =====================================================
    // CRON
    // =====================================================
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
        $win_number = (string)$game->win_number;

        $already = $this->db->get_where('tbl_funroulette_results', [
            'period_id' => $period_id,
            'game_id'   => $gameId
        ])->row();

        if ($already) {
            $this->db->trans_complete();
            return;
        }

        // =====================================================
        // RESULT LOGIC
        // =====================================================
        if ($manual_set == 1) {

            $selectedBetIndex = (string)$win_number;

        } else {

            $bets = $this->db->get_where('tbl_funroulette_bet', [
                'period_id' => $period_id,
                'game_id'   => $gameId,
                'status'    => 'pending'
            ])->result();

            if (empty($bets)) {

                $selectedBetIndex = $this->roulette_numbers[array_rand($this->roulette_numbers)];

            } else {

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

                // =====================================================
                // ADMIN COMMISSION
                // =====================================================
                $adminCommission = round($totalBetAmount * 20 / 100, 2);

                $totalEffectiveAmount =
                    $totalBetAmount
                    -
                    $totalDealerCommission
                    -
                    $totalDistributorCommission;

                $remainingProfit =
                    $totalEffectiveAmount
                    -
                    $adminCommission;

                if ($remainingProfit < 0) {
                    $remainingProfit = 0;
                }

                // =====================================================
                // EXPOSURES
                // =====================================================
                $exposures = [];

                foreach ($this->roulette_numbers as $number) {
                    $exposures[$number] = 0;
                }

                foreach ($bets as $bet) {

                    foreach ($this->roulette_numbers as $number) {

                        $exposures[$number] += $this->calculate_payout(
                            $bet->bet_type,
                            $bet->bet,
                            $number,
                            $bet->exposure_amount
                        );
                    }
                }

                $safestNumbers = [];

                $maxEligiblePayout = null;

                foreach ($exposures as $number => $payout) {

                    if ($payout <= $remainingProfit) {

                        if (
                            $maxEligiblePayout === null
                            ||
                            $payout > $maxEligiblePayout
                        ) {

                            $maxEligiblePayout = $payout;

                            $safestNumbers = [$number];

                        } elseif ($payout == $maxEligiblePayout) {

                            $safestNumbers[] = $number;
                        }
                    }
                }

                if (!empty($safestNumbers)) {

                    $selectedBetIndex =
                        $safestNumbers[array_rand($safestNumbers)];

                } else {

                    $closestNumbers = [];

                    $closestDistance = null;

                    foreach ($exposures as $number => $payout) {

                        $distance =
                            abs($payout - $remainingProfit);

                        if (
                            $closestDistance === null
                            ||
                            $distance < $closestDistance
                        ) {

                            $closestDistance = $distance;

                            $closestNumbers = [$number];

                        } elseif ($distance == $closestDistance) {

                            $closestNumbers[] = $number;
                        }
                    }

                    $selectedBetIndex =
                        $closestNumbers[array_rand($closestNumbers)];
                }
            }
        }
    echo $period_id.'<br>'.$selectedBetIndex;

        // =====================================================
        // INSERT RESULT
        // =====================================================
        $this->db->insert('tbl_funroulette_results', [
            'period_id' => $period_id,
            'game_id'   => $gameId,
            'win_number'=> $selectedBetIndex
        ]);

        // =====================================================
        // STORE ADMIN COMMISSION
        // =====================================================
        $this->db->insert('tbl_admin_commissions', [
            'period_id'        => $period_id,
            'game_id'          => $gameId,
            'total_bet_amount' => $totalBetAmount ?? 0,
            'admin_commission' => $adminCommission ?? 0,
            'winning_number'   => $selectedBetIndex,
            'created_at'       => date('Y-m-d H:i:s')
        ]);

        // =====================================================
        // FETCH BETS
        // =====================================================
        $bets = $this->db->get_where('tbl_funroulette_bet', [
            'period_id' => $period_id,
            'game_id'   => $gameId,
            'status'    => 'pending'
        ])->result();

        // =====================================================
        // PROCESS BETS
        // =====================================================
        foreach ($bets as $bet) {

            $win_amount = $this->calculate_payout(
                $bet->bet_type,
                $bet->bet,
                $selectedBetIndex,
                $bet->amount
            );

            if ($win_amount > 0) {

                // CREDIT WINNING
                $this->db->set(
                    'winning_wallet',
                    'winning_wallet + ' . $win_amount,
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

                $this->db->update('tbl_funroulette_bet', [
                    'win_amount' => $win_amount
                ]);

                $status = 'won';

            } else {

                $status = 'lost';
            }

            // FINAL UPDATE
            $this->db->where('id', $bet->id);

            $this->db->update('tbl_funroulette_bet', [
                'status'        => $status,
                'result_number' => $selectedBetIndex
            ]);
        }

        // =====================================================
        // CREDIT COMMISSIONS
        // =====================================================
        foreach ($bets as $bet) {

            $user = $this->db->get_where('tbl_users', [
                'id' => $bet->userid
            ])->row();

            // DEALER
            if ($user && $user->dealer_id) {

                $dealer_commission =
                    floatval($bet->dealer_commission ?? 0);

                if ($dealer_commission > 0) {

                    $this->db->set(
                        'wallet',
                        'wallet + ' . $dealer_commission,
                        FALSE
                    );

                    $this->db->where('id', $user->dealer_id);

                    $this->db->update('tbl_dealers');

                    $this->db->where('source_user_id', $bet->userid);

                    $this->db->where('period_id', $period_id);

                    $this->db->where('commission_type', 'dealer');

                    $this->db->update('tbl_commission_history', [
                        'dealer_commission_credited' => $dealer_commission,
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            // DISTRIBUTOR
            if ($user && $user->distributor_id) {

                $distributor_commission =
                    floatval($bet->distributor_commission ?? 0);

                if ($distributor_commission > 0) {

                    $this->db->set(
                        'wallet',
                        'wallet + ' . $distributor_commission,
                        FALSE
                    );

                    $this->db->where('id', $user->distributor_id);

                    $this->db->update('tbl_distributors');

                    $this->db->where('source_user_id', $bet->userid);

                    $this->db->where('period_id', $period_id);

                    $this->db->where('commission_type', 'distributor');

                    $this->db->update('tbl_commission_history', [
                        'distributor_commission_credited' => $distributor_commission,
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // =====================================================
        // ADMIN COMMISSION HISTORY
        // =====================================================
        foreach ($bets as $bet) {

            $admin_comm =
                round($bet->amount * 20 / 100, 2);

            if ($admin_comm > 0) {

                $exists = $this->db->get_where(
                    'tbl_commission_history',
                    [
                        'source_user_id' => $bet->userid,
                        'period_id'      => $period_id,
                        'commission_type'=> 'admin'
                    ]
                )->row();

                if (!$exists) {

                    $this->db->insert('tbl_commission_history', [

                        'source_user_id' => $bet->userid,

                        'dealer_id' => $bet->dealer_id ?? null,

                        'distributor_id' => $bet->distributor_id ?? null,

                        'commission_type' => 'admin',

                        'amount' => $admin_comm,

                        'bet_amount' => $bet->amount,

                        'rate' => 20,

                        'period_id' => $period_id,

                        'game_id' => $gameId,

                        'bet_id' => $bet->id,

                        'status' => 'completed',

                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }

        // =====================================================
        // UPDATE GAME PERIOD
        // =====================================================
        $this->db->where('id', $gameId);

        $this->db->update('tbl_games', [

            'period_id'  => $period_id + 1,

            'win_number' => $selectedBetIndex,

            'manual_set' => 0
        ]);

        $this->db->trans_complete();
    }

    // =====================================================
    // CALCULATE PAYOUT
    // =====================================================
    private function calculate_payout(
        $bet_type,
        $bet_value,
        $winning_number,
        $amount
    )
    {
        $payouts = [

            'straight'  => 35,

            'split'     => 17.5,

            'street'    => 11.6,

            'corner'    => 8.75,

            'line'      => 5.83,

            'column'    => 3,

            'dozen'     => 3,

            'odd_even'  => 2,

            'red_black' => 2,

            'high_low'  => 2
        ];

        $multiplier = $payouts[$bet_type] ?? 0;

        if ($multiplier <= 0) {
            return 0;
        }

        $winning_number = trim((string)$winning_number);

        $bet_value = trim(strtolower((string)$bet_value));

        $amount = floatval($amount);

        // JSON SUPPORT
        if (strpos($bet_value, '[') === 0) {

            $decoded = json_decode($bet_value, true);

            if (is_array($decoded)) {

                $bet_value =
                    implode(',', array_map('strval', $decoded));
            }
        }

        $isWin = false;

        switch ($bet_type) {

            // =====================================================
            // STRAIGHT
            // =====================================================
            case 'straight':

                $isWin =
                    strtolower(trim($winning_number))
                    ===
                    strtolower(trim($bet_value));

            break;

            // =====================================================
            // SPLIT / STREET / CORNER / LINE
            // =====================================================
            case 'split':
            case 'street':
            case 'corner':
            case 'line':

                $numbers = array_map(
                    'trim',
                    explode(',', $bet_value)
                );

                $numbers = array_unique($numbers);

                $isWin = in_array(
                    (string)$winning_number,
                    $numbers,
                    true
                );

                if ($isWin) {

                    $count = count($numbers);

                    if ($count > 0) {

                        $single_amount = $amount / $count;

                        return round(
                            $single_amount * $multiplier,
                            2
                        );
                    }
                }

                return 0;

            // =====================================================
            // COLUMN
            // =====================================================
            case 'column':

                if (
                    $winning_number == '0'
                    ||
                    $winning_number == '00'
                ) {
                    return 0;
                }

                $num = intval($winning_number);

                if (
                    in_array(
                        $bet_value,
                        ['1','first','first_column'],
                        true
                    )
                ) {

                    $isWin = $num > 0 && $num % 3 == 1;

                } elseif (
                    in_array(
                        $bet_value,
                        ['2','second','second_column'],
                        true
                    )
                ) {

                    $isWin = $num > 0 && $num % 3 == 2;

                } elseif (
                    in_array(
                        $bet_value,
                        ['3','third','third_column'],
                        true
                    )
                ) {

                    $isWin = $num > 0 && $num % 3 == 0;
                }

            break;

            // =====================================================
            // DOZEN
            // =====================================================
            case 'dozen':

                if (
                    $winning_number == '0'
                    ||
                    $winning_number == '00'
                ) {
                    return 0;
                }

                $num = intval($winning_number);

                if (
                    in_array(
                        $bet_value,
                        ['1','first','1st','1st12'],
                        true
                    )
                ) {

                    $isWin = $num >= 1 && $num <= 12;

                } elseif (
                    in_array(
                        $bet_value,
                        ['2','second','2nd','2nd12'],
                        true
                    )
                ) {

                    $isWin = $num >= 13 && $num <= 24;

                } elseif (
                    in_array(
                        $bet_value,
                        ['3','third','3rd','3rd12'],
                        true
                    )
                ) {

                    $isWin = $num >= 25 && $num <= 36;
                }

            break;

            // =====================================================
            // ODD EVEN
            // =====================================================
            case 'odd_even':

                if (
                    $winning_number == '0'
                    ||
                    $winning_number == '00'
                ) {
                    return 0;
                }

                $num = intval($winning_number);

                if (
                    $bet_value == 'odd'
                    ||
                    $bet_value == '1'
                ) {

                    $isWin = $num % 2 == 1;

                } elseif (
                    $bet_value == 'even'
                    ||
                    $bet_value == '2'
                ) {

                    $isWin = $num > 0 && $num % 2 == 0;
                }

            break;

            // =====================================================
            // RED BLACK
            // =====================================================
            case 'red_black':

                if (
                    $winning_number == '0'
                    ||
                    $winning_number == '00'
                ) {
                    return 0;
                }

                $redNumbers = [
                    1,3,5,7,9,12,14,16,18,
                    19,21,23,25,27,30,32,34,36
                ];

                $num = intval($winning_number);

                if (
                    $bet_value == 'red'
                    ||
                    $bet_value == '1'
                ) {

                    $isWin =
                        in_array($num, $redNumbers, true);

                } elseif (
                    $bet_value == 'black'
                    ||
                    $bet_value == '2'
                ) {

                    $isWin =
                        $num > 0
                        &&
                        !in_array($num, $redNumbers, true);
                }

            break;

            // =====================================================
            // HIGH LOW
            // =====================================================
            case 'high_low':

                if (
                    $winning_number == '0'
                    ||
                    $winning_number == '00'
                ) {
                    return 0;
                }

                $num = intval($winning_number);

                if (
                    $bet_value == 'low'
                    ||
                    $bet_value == '1'
                ) {

                    $isWin =
                        $num >= 1
                        &&
                        $num <= 18;

                } elseif (
                    $bet_value == 'high'
                    ||
                    $bet_value == '2'
                ) {

                    $isWin =
                        $num >= 19
                        &&
                        $num <= 36;
                }

            break;
        }

        $win_amount = round($amount * $multiplier, 2);

        return $isWin ? $win_amount : 0;
    }

    // =====================================================
    // GET REQUEST BODY
    // =====================================================
    private function get_request_body()
    {
        static $body = null;

        if ($body !== null) {
            return $body;
        }

        $rawInput = trim(file_get_contents('php://input'));

        $body = [];

        if ($rawInput !== '') {

            $decoded = json_decode($rawInput, true);

            if (is_array($decoded)) {

                $body = $decoded;
            }
        }

        return $body;
    }

    // =====================================================
    // GET GAME ID
    // =====================================================
    private function get_game_id()
    {
        $gameId =
            $this->input->post('gameid')
            ??
            $this->input->post('game_id');

        if (!$gameId) {

            $body = $this->get_request_body();

            $gameId =
                $body['gameid']
                ??
                $body['game_id']
                ??
                null;
        }

        $gameId = intval($gameId);

        return $gameId > 0 ? $gameId : 10;
    }



// =====================================================
// PLACE BET
// =====================================================
public function place_bet()
{
    $userid     = $this->input->post('userid');

    $amount     = floatval($this->input->post('amount'));

    $bet_type   = $this->input->post('bet_type');

    $bet_value  = trim($this->input->post('bet_value'));

    $gameId     = $this->get_game_id();

    $period_id  = $this->db_model->select(
        'period_id',
        'tbl_games',
        array('id' => $gameId)
    );

    if (
        !$userid
        ||
        !$bet_type
        ||
        $bet_value === null
        ||
        $amount <= 0
    ) {

        $response = array(
            'status'  => 'error',
            'message' => 'Invalid input for bet placement.'
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // =====================================================
    // VALIDATE 00 SUPPORT
    // =====================================================
    if ($bet_type == 'straight') {

        $validNumbers = $this->roulette_numbers;

        if (!in_array((string)$bet_value, $validNumbers, true)) {

            $response = array(
                'status'  => 'error',
                'message' => 'Invalid roulette number.'
            );

            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response));
        }
    }

    // =====================================================
    // CHECK USER
    // =====================================================
    $user = $this->db_model->select_multi(
        '*',
        'tbl_users',
        array('id' => $userid)
    );

    if (!$user) {

        $response = array(
            'status'  => 'error',
            'message' => 'Invalid Userid'
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // =====================================================
    // CHECK WALLET
    // =====================================================
    $wallet_bal = floatval($user->wallet);

    if ($wallet_bal < $amount) {

        $response = array(
            'status'  => 'error',
            'message' => 'Insufficient Balance'
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // =====================================================
    // COMMISSION LOGIC
    // =====================================================
    $dealer_id = $user->dealer_id ?? null;

    $distributor_id = $user->distributor_id ?? null;

    $dealer_commission = 0;

    $distributor_commission = 0;

    $dealer_rate = 0;

    $distributor_rate = 0;

    // DEALER
    if ($dealer_id) {

        $dealer = $this->db->get_where(
            'tbl_dealers',
            ['id' => $dealer_id]
        )->row();

        if ($dealer) {

            $dealer_rate =
                floatval($dealer->commission_rate ?? 2);

            $dealer_commission =
                round($amount * $dealer_rate / 100, 2);
        }
    }

    // DISTRIBUTOR
    if ($distributor_id) {

        $distributor = $this->db->get_where(
            'tbl_distributors',
            ['id' => $distributor_id]
        )->row();

        if ($distributor) {

            $distributor_rate =
                floatval($distributor->commission_rate ?? 0.5);

            $distributor_commission =
                round($amount * $distributor_rate / 100, 2);
        }
    }

    // ADMIN
    $admin_commission =
        round($amount * 20 / 100, 2);

    // =====================================================
    // INSERT BET
    // =====================================================
    $array = array(

        'userid' => $userid,

        'bet' => $bet_value,

        'bet_type' => $bet_type,

        'period_id' => $period_id,

        'game_id' => $gameId,

        'amount' => $amount,

        'dealer_commission' => $dealer_commission,

        'distributor_commission' => $distributor_commission,

        'admin_commission' => $admin_commission,

        'user_amount' => $wallet_bal,

        'date' => date('Y-m-d H:i:s'),

        'status' => 'pending'
    );

    $this->db->insert('tbl_funroulette_bet', $array);

    $bet_id = $this->db->insert_id();

    // =====================================================
    // COMMISSION HISTORY - DEALER
    // =====================================================
    if ($dealer_commission > 0) {

        $this->db->insert('tbl_commission_history', [

            'source_user_id' => $userid,

            'dealer_id' => $dealer_id,

            'distributor_id' => $distributor_id,

            'commission_type' => 'dealer',

            'amount' => $dealer_commission,

            'bet_amount' => $amount,

            'rate' => $dealer_rate,

            'period_id' => $period_id,

            'game_id' => $gameId,

            'bet_id' => $bet_id,

            'status' => 'pending',

            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // =====================================================
    // COMMISSION HISTORY - DISTRIBUTOR
    // =====================================================
    if ($distributor_commission > 0) {

        $this->db->insert('tbl_commission_history', [

            'source_user_id' => $userid,

            'dealer_id' => $dealer_id,

            'distributor_id' => $distributor_id,

            'commission_type' => 'distributor',

            'amount' => $distributor_commission,

            'bet_amount' => $amount,

            'rate' => $distributor_rate,

            'period_id' => $period_id,

            'game_id' => $gameId,

            'bet_id' => $bet_id,

            'status' => 'pending',

            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    // =====================================================
    // DEDUCT WALLET
    // =====================================================
    $new_wallet = $wallet_bal - $amount;

    $this->db->where('id', $userid);

    $this->db->update('tbl_users', [
        'wallet' => $new_wallet
    ]);

    // =====================================================
    // TRANSACTION ENTRY
    // =====================================================
    $this->db->insert('tbl_transactions', [

        'userid' => $userid,

        'amount' => $amount,

        'status' => 'debit',

        'type' => 'Bet Placed'
    ]);

    // =====================================================
    // RESPONSE
    // =====================================================
    $response = array(

        'status' => 'success',

        'message' => 'Bet Placed Successfully',

        'data' => array(

            'wallet' => $new_wallet,

            'winning_wallet' =>
                floatval($user->winning_wallet),

            'period_id' => $period_id,

            'dealer_commission' =>
                $dealer_commission,

            'distributor_commission' =>
                $distributor_commission,

            'admin_commission' =>
                $admin_commission
        )
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// WALLET BALANCE
// =====================================================
public function wallet_balance()
{
    $userid = $this->input->post('userid');

    if (!$userid) {

        $response = array(
            'status'  => 'error',
            'message' => 'User ID is required'
        );

    } else {

        $user = $this->db->get_where(
            'tbl_users',
            ['id' => $userid]
        )->row();

        if (!$user) {

            $response = array(
                'status'  => 'error',
                'message' => 'Invalid User ID'
            );

        } else {

            $pending_amount =
                floatval(
                    $this->db_model->sum(
                        'amount',
                        'tbl_funroulette_bet',
                        [
                            'userid' => $userid,
                            'status' => 'pending'
                        ]
                    )
                );

            $response = array(

                'status' => 'success',

                'wallet' =>
                    floatval($user->wallet),

                'winning_wallet' =>
                    floatval($user->winning_wallet),

                'pending_bets' =>
                    $pending_amount
            );
        }
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// MY BETS
// =====================================================
public function my_bets()
{
    $userid    = $this->input->post('userid');

    $period_id = $this->input->post('period_id');

    $chek_user = $this->db_model->count_all(
        'tbl_users',
        array('id' => $userid)
    );

    if ($chek_user <= 0) {

        $response = array(
            'status'  => 'error',
            'message' => 'Invalid Userid'
        );

    } else {

        $this->db->select(
            'bet_type,
            SUM(amount) as total_amount,
            COUNT(*) as bet_count'
        );

        $this->db->from('tbl_funroulette_bet');

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

                'count' => $bet->bet_count
            ];

            $total_bet += $bet->total_amount;
        }

        $win_bets =
            $this->db_model->sum(
                'amount',
                'tbl_funroulette_bet',
                [
                    'period_id' => $period_id - 1,
                    'status' => 'won',
                    'userid' => $userid
                ]
            ) + 0;

        $response = array(

            'status' => 'success',

            'my_bets' => $betAmounts,

            'total_bet' => $total_bet,

            'win_bets' => $win_bets
        );
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// SHOW RESULT
// =====================================================
public function show_result()
{
    $gameId = $this->get_game_id();

    $period_id = $this->db_model->select(
        'period_id',
        'tbl_games',
        array('id' => $gameId)
    );

    $result = $this->db_model->count_all(
        'tbl_funroulette_results',
        array(
            'period_id' => $period_id,
            'game_id'   => $gameId
        )
    );

    $result2 = $this->db_model->select(
        'win_number',
        'tbl_games',
        array('id' => $gameId)
    );

    $limit = 10;

    $this->db->select('win_number');

    $this->db->from('tbl_funroulette_results');

    $this->db->where('game_id', $gameId);

    $this->db->order_by('id', 'DESC');

    $this->db->limit($limit);

    $lstresult = $this->db->get()->result();

    if ($result > 0) {

        $number = $this->db_model->select(
            'win_number',
            'tbl_funroulette_results',
            array('period_id' => $period_id)
        );

        $response = array(

            'status'   => 'success',

            'number'   => $number,

            'last_res' => $lstresult
        );

    } elseif ($result2 != null) {

        $response = array(

            'status'   => 'success',

            'number'   => $result2,

            'last_res' => $lstresult
        );

    } else {

        $response = array(

            'status'  => 'error',

            'message' => 'Something Went Wrong Try Again Later...'
        );
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// RESULTS
// =====================================================
public function results()
{
    $gameId = $this->get_game_id();

    $period_id = $this->db_model->select(
        'period_id',
        'tbl_games',
        array('id' => $gameId)
    );

    $limit = 10;

    $this->db->select('win_number');

    $this->db->from('tbl_funroulette_results');

    $this->db->where('period_id', $period_id);

    $this->db->where('game_id', $gameId);

    $this->db->order_by('id', 'DESC');

    $this->db->limit($limit);

    $query = $this->db->get();

    $result =
        $query->num_rows() > 0
        ? $query->result()
        : array();

    if (!empty($result)) {

        array_shift($result);
    }

    $response = array(

        'status'  => 'success',

        'results' => $result
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// PERIOD ID
// =====================================================
public function period_id()
{
    $gameId = $this->get_game_id();

    $period_id = $this->db_model->select(
        'period_id',
        'tbl_games',
        array('id' => $gameId)
    );

    $response = array(

        'status'    => 'success',

        'game_id'   => $gameId,

        'period_id' => $period_id
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// TIMER
// =====================================================
public function timer()
{
    $gameId = $this->get_game_id();

    $period_id = $this->db_model->select(
        'period_id',
        'tbl_games',
        array('id' => $gameId)
    );

    $currentTime = time();

    $remaining = 59 - ($currentTime % 60);

    $response = array(

        'status' => 'success',

        'game_id' => $gameId,

        'period_id' => $period_id,

        'remaining_s' => $remaining
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// TAKE WINNING
// =====================================================
/*public function take()
{
    $userid = $this->input->post('userid');

    if (empty($userid)) {

        $response = array(

            'status' => 'error',

            'message' => 'User ID is required.',

            'win_wallet' => 0
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    $user = $this->db->get_where(
        'tbl_users',
        ['id' => $userid]
    )->row();

    if (!$user) {

        $response = array(

            'status' => 'error',

            'message' => 'Invalid User ID.',

            'win_wallet' => 0
        );

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    $wallet = $user->wallet;

    $winning_wallet = $user->winning_wallet;

    if ($winning_wallet > 0) {

        $new_wallet = $wallet + $winning_wallet;

        $this->db->where('id', $userid);

        $this->db->update('tbl_users', [

            'wallet' => $new_wallet,

            'winning_wallet' => 0
        ]);

        $this->db->insert('tbl_transactions', [

            'userid' => $userid,

            'amount' => $winning_wallet,

            'status' => 'credit',

            'type' => 'Winning transfer to wallet'
        ]);

        $response = array(

            'status' => 'success',

            'message' =>
                'Winning wallet amount added to main wallet successfully.',

            'win_wallet' => 0
        );

    } else {

        $response = array(

            'status' => 'error',

            'message' => 'Winning wallet is already empty.',

            'win_wallet' => 0
        );
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}*/

// =====================================================
// TOTAL BETS
// =====================================================
public function total_bets()
{
    $userid = $this->input->post('userid');

    $chek_user = $this->db_model->count_all(
        'tbl_users',
        array('id' => $userid)
    );

    $today_date = date('Y-m-d');

    if ($chek_user <= 0) {

        $response = array(

            'status' => 'error',

            'message' => 'Invalid Userid'
        );

    } else {

        $result = $this->db
            ->select(
                'SUM(amount) as total_amount,
                COUNT(*) as total_count'
            )
            ->where("DATE(date) =", $today_date)
            ->where('userid', $userid)
            ->where('game_id', $this->get_game_id())
            ->get('tbl_funroulette_bet')
            ->row();

        $total_amount =
            ($result)
            ? $result->total_amount + 0
            : 0;

        $total_count =
            ($result)
            ? $result->total_count
            : 0;

        $response = array(

            'status' => 'success',

            'total_bet_amount' => $total_amount,

            'total_bet_count' => $total_count
        );
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// WIN BETS
// =====================================================
public function win_bets()
{
    $userid = $this->input->post('userid');

    $chek_user = $this->db_model->count_all(
        'tbl_users',
        array('id' => $userid)
    );

    $today_date = date('Y-m-d');

    if ($chek_user <= 0) {

        $response = array(

            'status' => 'error',

            'message' => 'Invalid Userid'
        );

    } else {

        $win_bet = $this->db
            ->select_sum('amount')
            ->where("DATE(date) =", $today_date)
            ->where('userid', $userid)
            ->where('status', 'won')
            ->where('game_id', $this->get_game_id())
            ->get('tbl_funroulette_bet')
            ->row()
            ->amount + 0;

        $response = array(

            'status' => 'success',

            'win_bets' => $win_bet
        );
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// CANCEL LAST BET
// =====================================================
public function cancel_last_bet()
{
    $userid = $this->input->post('userid');

    $period_id = $this->input->post('period_id');

    if (!$userid || !$period_id) {

        $response = [

            'status'  => 'error',

            'message' => 'User ID and Period ID are required'
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

    $bet = $this->db->get('tbl_funroulette_bet')->row();

    if (!$bet) {

        $response = [

            'status'  => 'error',

            'message' => 'No pending bet found.'
        ];

    } else {

        // ============================================
        // UPDATE BET STATUS
        // ============================================
        $this->db->where('id', $bet->id);

        $this->db->update('tbl_funroulette_bet', [

            'status' => 'cancelled'
        ]);

        // ============================================
        // REFUND USER WALLET
        // ============================================
        $this->db->set(
            'wallet',
            'wallet + ' . $bet->amount,
            FALSE
        );

        $this->db->where('id', $userid);

        $this->db->update('tbl_users');

        // ============================================
        // TRANSACTION ENTRY
        // ============================================
        $this->db->insert('tbl_transactions', [

            'userid' => $userid,

            'amount' => $bet->amount,

            'status' => 'credit',

            'type' => 'Refund for cancelled bet ID ' . $bet->id
        ]);

        // ============================================
        // CANCEL COMMISSION ENTRIES
        // ============================================
        $this->db->where('bet_id', $bet->id);

        $this->db->where_in(
            'commission_type',
            ['dealer', 'distributor']
        );

        $this->db->update(
            'tbl_commission_history',
            [
                'status' => 'cancelled',
                'updated_at' => date('Y-m-d H:i:s')
            ]
        );

        $response = [

            'status' => 'success',

            'message' =>
                'Last pending bet cancelled. ₹' .
                $bet->amount .
                ' refunded.',

            'cancelled_id' => $bet->id
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// CANCEL ALL BETS
// =====================================================
public function cancel_all_bets()
{
    $userid = $this->input->post('userid');

    $period_id = $this->input->post('period_id');

    if (!$userid || !$period_id) {

        $response = [

            'status'  => 'error',

            'message' => 'User ID and Period ID are required'
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    $this->db->where([
        'userid'    => $userid,
        'period_id' => $period_id,
        'status'    => 'pending'
    ]);

    $bets = $this->db
        ->get('tbl_funroulette_bet')
        ->result();

    if (empty($bets)) {

        $response = [

            'status'  => 'error',

            'message' => 'No pending bets found to cancel.'
        ];

    } else {

        $total_refund = 0;

        $cancelled_ids = [];

        foreach ($bets as $bet) {

            $total_refund += $bet->amount;

            $cancelled_ids[] = $bet->id;

            // ============================================
            // CANCEL BET
            // ============================================
            $this->db->where('id', $bet->id);

            $this->db->update('tbl_funroulette_bet', [

                'status' => 'cancelled'
            ]);

            // ============================================
            // TRANSACTION ENTRY
            // ============================================
            $this->db->insert('tbl_transactions', [

                'userid' => $userid,

                'amount' => $bet->amount,

                'status' => 'credit',

                'type' =>
                    'Refund for cancelled bet ID ' .
                    $bet->id
            ]);

            // ============================================
            // CANCEL COMMISSION
            // ============================================
            $this->db->where('bet_id', $bet->id);

            $this->db->where_in(
                'commission_type',
                ['dealer', 'distributor']
            );

            $this->db->update(
                'tbl_commission_history',
                [
                    'status' => 'cancelled',
                    'updated_at' => date('Y-m-d H:i:s')
                ]
            );
        }

        // ============================================
        // REFUND TOTAL
        // ============================================
        $this->db->set(
            'wallet',
            'wallet + ' . $total_refund,
            FALSE
        );

        $this->db->where('id', $userid);

        $this->db->update('tbl_users');

        $response = [

            'status' => 'success',

            'message' =>
                count($bets) .
                ' pending bet(s) cancelled. ₹' .
                $total_refund .
                ' refunded.',

            'cancelled_ids' => $cancelled_ids
        ];
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// REPEAT LAST BET
// =====================================================
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

            'message' => 'User ID is required'
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ============================================
    // GET LAST BET
    // ============================================
    $last_bet = $this->db
        ->where('userid', $userid)
        ->where('game_id', $gameId)
        ->where_in('status', ['pending','won','lost'])
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get('tbl_funroulette_bet')
        ->row();

    if (!$last_bet) {

        $response = [

            'status'  => 'error',

            'message' => 'No previous bet found'
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    $amount = floatval($last_bet->amount);

    // ============================================
    // CHECK WALLET
    // ============================================
    $wallet = floatval(
        $this->db_model->select(
            'wallet',
            'tbl_users',
            ['id' => $userid]
        )
    );

    if ($wallet < $amount) {

        $response = [

            'status' => 'error',

            'message' => 'Insufficient Balance',

            'wallet' => $wallet
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // ============================================
    // INSERT REPEAT BET
    // ============================================
    $insertData = [

        'userid' => $userid,

        'bet' => $last_bet->bet,

        'bet_type' => $last_bet->bet_type,

        'period_id' => $current_period_id,

        'game_id' => $gameId,

        'amount' => $last_bet->amount,

        'dealer_commission' =>
            $last_bet->dealer_commission,

        'distributor_commission' =>
            $last_bet->distributor_commission,

        'admin_commission' =>
            $last_bet->admin_commission,

        'user_amount' => $wallet,

        'date' => date('Y-m-d H:i:s'),

        'status' => 'pending'
    ];

    $this->db->insert(
        'tbl_funroulette_bet',
        $insertData
    );

    $bet_id = $this->db->insert_id();

    // ============================================
    // DEDUCT WALLET
    // ============================================
    $new_wallet = $wallet - $amount;

    $this->db->where('id', $userid);

    $this->db->update('tbl_users', [

        'wallet' => $new_wallet
    ]);

    // ============================================
    // TRANSACTION ENTRY
    // ============================================
    $this->db->insert('tbl_transactions', [

        'userid' => $userid,

        'amount' => $amount,

        'status' => 'debit',

        'type' => 'Repeat Last Bet'
    ]);

    // ============================================
    // RESPONSE
    // ============================================
    $response = [

        'status' => 'success',

        'message' => 'Last bet repeated successfully',

        'period_id' => $current_period_id,

        'bet_type' => $last_bet->bet_type,

        'bet_value' => $last_bet->bet,

        'amount' => $amount,

        'wallet' => $new_wallet
    ];

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

// =====================================================
// PLAY CHIPS
// =====================================================
public function play_chips()
{
    $img_path = base_url('axxests/chips_img/');

    $where_condition = "status = 1";

    $show_games = $this->db_model
        ->get_specific_records(
            'tbl_play_chips',
            $where_condition,
            'id, img, amount'
        );

    if ($show_games) {

        $response = array(

            'status' => 'success',

            'img_path' => $img_path,

            'data' => $show_games
        );

    } else {

        $response = array(

            'status' => 'error',

            'message' => 'No records found.'
        );
    }

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}


public function userhistory()
{
    $userid = $this->input->post('userid');
    $gameid = $this->input->post('gameid');

    // fallback JSON support
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

    $this->db->select('id, date, period_id, amount, result_number, win_amount, status, game_id');
    $this->db->from('tbl_funroulette_bet');
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
            'result'     => $row->result_number,
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


public function userhistory_by_date()
{
    $userid = $this->input->post('userid');
    $gameid = $this->input->post('gameid');
    $date   = $this->input->post('date');

    // JSON support
    if (!$userid || !$gameid || !$date) {

        $body = json_decode(file_get_contents('php://input'), true);

        $userid = $userid ?: ($body['userid'] ?? null);
        $gameid = $gameid ?: ($body['gameid'] ?? null);
        $date   = $date ?: ($body['date'] ?? null);
    }

    // Validation
    if (empty($userid) || empty($gameid) || empty($date)) {

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => 'error',
                'message' => 'userid, gameid and date are required'
            ]));
    }

    $this->db->select('
        id,
        date,
        period_id,
        amount,
        result_number,
        win_amount,
        status
    ');

    $this->db->from('tbl_funroulette_bet');

    $this->db->where('userid', $userid);
    $this->db->where('game_id', $gameid);

    // Selected date filter
    $this->db->where('DATE(date)', $date);

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
            'result'     => $row->result_number,
            'win_amount' => $row->win_amount,
            'status'     => $row->status
        ];
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status' => 'success',
            'count'  => count($data),
            'data'   => $data
        ]));
}

public function cancel_bets()
{
    $userid  = $this->input->post('userid');
    $gameid  = $this->input->post('gameid');
    $bet_ids = $this->input->post('bet_ids');

    // JSON support
    if (!$userid || !$gameid || !$bet_ids) {

        $body = json_decode(file_get_contents('php://input'), true);

        $userid  = $userid ?: ($body['userid'] ?? null);
        $gameid  = $gameid ?: ($body['gameid'] ?? null);
        $bet_ids = $bet_ids ?: ($body['bet_ids'] ?? null);
    }

    // Validation
    if (empty($userid) || empty($gameid) || empty($bet_ids)) {

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => 'error',
                'message' => 'userid, gameid and bet_ids are required'
            ]));
    }

    // Single ID support
    if (!is_array($bet_ids)) {
        $bet_ids = [$bet_ids];
    }

    // Remove duplicate ids
    $bet_ids = array_unique($bet_ids);

    // Get pending bets only
    $this->db->where('userid', $userid);
    $this->db->where('game_id', $gameid);
    $this->db->where_in('id', $bet_ids);
    $this->db->where('status', 'pending');

    $bets = $this->db->get('tbl_funroulette_bet')->result();

    if (empty($bets)) {

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status'  => 'error',
                'message' => 'No pending bets found'
            ]));
    }

    $total_refund = 0;
    $cancelled_ids = [];

    foreach ($bets as $bet) {

        $total_refund += floatval($bet->amount);

        $cancelled_ids[] = $bet->id;

        // Cancel bet
        $this->db->where('id', $bet->id);
        $this->db->update('tbl_funroulette_bet', [
            'status' => 'cancelled'
        ]);

        // Refund transaction
        $this->db->insert('tbl_transactions', [
            'userid' => $userid,
            'amount' => $bet->amount,
            'status' => 'credit',
            'type'   => 'Bet Cancel Refund'
        ]);
    }

    // Refund wallet
    $this->db->set('wallet', 'wallet + ' . $total_refund, FALSE);
    $this->db->where('id', $userid);
    $this->db->update('tbl_users');

    // Current wallet
    $user = $this->db->get_where('tbl_users', [
        'id' => $userid
    ])->row();

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode([
            'status'         => 'success',
            'message'        => count($cancelled_ids) . ' bet(s) cancelled successfully',
            'game_id'        => $gameid,
            'cancelled_ids'  => $cancelled_ids,
            'refund_amount'  => $total_refund,
            'wallet_balance' => floatval($user->wallet)
        ]));
}

}

