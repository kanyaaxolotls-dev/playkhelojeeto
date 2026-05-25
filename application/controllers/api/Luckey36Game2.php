<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Luckey36Game2 extends CI_Controller {
    
     public function cron()
    {
        $this->db->trans_start();

        $gameId = $this->get_game_id();
        $game = $this->db->get_where('tbl_games', ['id' => $gameId])->row();

        if (!$game) {
            return;
        }

        $period_id = $game->period_id;
        $manual_set = $game->manual_set;
        $win_number = $game->win_number;

        // ✅ Prevent duplicate cron run
        $already = $this->db->get_where('tbl_lucky36_results2', [
            'period_id' => $period_id,
            'game_id'   => $gameId
        ])->row();

        if ($already) {
            $this->db->trans_complete();
            echo "Result already declared for Period ID: " . $period_id;
            return;
        }

        // =========================
        // RESULT SELECTION LOGIC
        // =========================
        if ($manual_set == 1) {
            $selectedBetIndex = $win_number;
        } else {
            $bets = $this->db->get_where('tbl_lucky36_bet2', [
                'period_id' => $period_id,
                'game_id'   => $gameId,
                'status'    => 'pending'
            ])->result();

            if (empty($bets)) {
                $selectedBetIndex = rand(0, 35);
            } else {
                // ✅ Calculate totals for admin commission
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

                // ✅ ADMIN COMMISSION = 20% of TOTAL BET AMOUNT
                $adminCommission = round($totalBetAmount * 20 / 100, 2);
                $totalEffectiveAmount = $totalBetAmount - $totalDealerCommission - $totalDistributorCommission;
                $remainingProfit = $totalEffectiveAmount - $adminCommission;

                if ($remainingProfit < 0) {
                    $remainingProfit = 0;
                }

                // Debug output
                echo "Period: " . $period_id . "<br>";
                echo "Total Bet Amount: ₹" . $totalBetAmount . "<br>";
                echo "Total Dealer Commission: ₹" . $totalDealerCommission . "<br>";
                echo "Total Distributor Commission: ₹" . $totalDistributorCommission . "<br>";
                echo "✅ Admin Commission (20% of Total Bet): ₹" . $adminCommission . "<br>";
                echo "Remaining for Payouts: ₹" . $remainingProfit . "<br>";

                // Calculate exposures
                $exposures = array_fill(0, 36, 0.0);

                foreach ($bets as $bet) {
                    for ($number = 0; $number <= 35; $number++) {
                        $exposures[$number] += $this->calculate_payout(
                            $bet->bet_type,
                            $bet->bet,
                            $number,
                            $bet->exposure_amount
                        );
                    }
                }

                // Find safest numbers
                $safestNumbers = [];
                $maxEligiblePayout = null;

                foreach ($exposures as $number => $payout) {
                    if ($payout <= $remainingProfit) {
                        if ($maxEligiblePayout === null || $payout > $maxEligiblePayout) {
                            $maxEligiblePayout = $payout;
                            $safestNumbers = [$number];
                        } elseif ($payout === $maxEligiblePayout) {
                            $safestNumbers[] = $number;
                        }
                    }
                }

                if (!empty($safestNumbers)) {
                    $maxExposure = -1;
                    $selectedBetIndex = $safestNumbers[0];
                    foreach ($safestNumbers as $number) {
                        if ($exposures[$number] > $maxExposure) {
                            $maxExposure = $exposures[$number];
                            $selectedBetIndex = $number;
                        }
                    }
                } else {
                    $closestNumbers = [];
                    $closestDistance = null;

                    foreach ($exposures as $number => $payout) {
                        $distance = abs($payout - $remainingProfit);

                        if ($closestDistance === null || $distance < $closestDistance) {
                            $closestDistance = $distance;
                            $closestNumbers = [$number];
                        } elseif ($distance === $closestDistance) {
                            $closestNumbers[] = $number;
                        }
                    }

                    $minExposure = PHP_INT_MAX;
                    $selectedBetIndex = $closestNumbers[0];
                    foreach ($closestNumbers as $number) {
                        if ($exposures[$number] < $minExposure) {
                            $minExposure = $exposures[$number];
                            $selectedBetIndex = $number;
                        }
                    }
                }
            }
        }

        // ✅ PRINT RESULT
        echo "<br>Winning Number: " . $selectedBetIndex;

        // =========================
        // INSERT RESULT
        // =========================
        $this->db->insert('tbl_lucky36_results2', [
            'period_id'  => $period_id,
            'game_id'    => $gameId,
            'win_number' => $selectedBetIndex
        ]);

        // ✅✅✅ STORE ADMIN COMMISSION (Period-wise) ✅✅✅
        // Check if table exists
        if (!$this->db->table_exists('tbl_admin_commissions')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS `tbl_admin_commissions` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `period_id` int(11) NOT NULL,
                `game_id` int(11) NOT NULL,
                `total_bet_amount` decimal(12,2) DEFAULT '0.00',
                `admin_commission` decimal(12,2) DEFAULT '0.00',
                `winning_number` int(11) DEFAULT NULL,
                `created_at` datetime DEFAULT NULL,
                PRIMARY KEY (`id`)
            )");
        }
        
        $this->db->insert('tbl_admin_commissions', [
            'period_id' => $period_id,
            'game_id' => $gameId,
            'total_bet_amount' => $totalBetAmount,
            'admin_commission' => $adminCommission,
            'winning_number' => $selectedBetIndex,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        echo "<br>✅ Admin Commission (20% of ₹{$totalBetAmount}) = ₹{$adminCommission} stored for Period: {$period_id}";

        // =========================
        // FETCH BETS
        // =========================
        $bets = $this->db
            ->where('period_id', $period_id)
            ->where('game_id', $gameId)
            ->where('status', 'pending')
            ->get('tbl_lucky36_bet2')
            ->result();

        // =========================
        // PROCESS BETS (Win/Loss)
        // =========================
        foreach ($bets as $bet) {
            $win_amount = $this->calculate_payout(
                $bet->bet_type,
                $bet->bet,
                $selectedBetIndex,
                $bet->amount
            );

            if ($win_amount > 0) {
                // Update user winning wallet
                $this->db->set('winning_wallet', 'winning_wallet + ' . $win_amount, FALSE);
                $this->db->where('id', $bet->userid);
                $this->db->update('tbl_users');

                // Transaction log
                $this->db->insert('tbl_transactions', [
                    'userid' => $bet->userid,
                    'amount' => $win_amount,
                    'type'   => 'game_win',
                    'status' => 'credit'
                ]);
                
                // Update win_amount in bet table
                $this->db->where('id', $bet->id);
                $this->db->update('tbl_lucky36_bet2', ['win_amount' => $win_amount]);
                
                $status = 'won';
            } else {
                $status = 'lost';
            }

            // Update bet status
            $this->db->where('id', $bet->id);
            $this->db->update('tbl_lucky36_bet2', [
                'status' => $status,
                'result_number' => $selectedBetIndex
            ]);
        }

        // =============================================
        // CREDIT COMMISSIONS TO DEALER & DISTRIBUTOR
        // =============================================
        foreach ($bets as $bet) {
            $user = $this->db->get_where('tbl_users', ['id' => $bet->userid])->row();
            
            // Credit Dealer Commission
            if($user && $user->dealer_id) {
                $dealer_commission = floatval($bet->dealer_commission ?? 0);
                if($dealer_commission > 0) {
                    // Credit to dealer wallet
                    $this->db->set('wallet', 'wallet + ' . $dealer_commission, FALSE);
                    $this->db->where('id', $user->dealer_id);
                    $this->db->update('tbl_dealers');
                    
                    // Update commission history
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
            
            // Credit Distributor Commission
            if($user && $user->distributor_id) {
                $distributor_commission = floatval($bet->distributor_commission ?? 0);
                if($distributor_commission > 0) {
                    // Credit to distributor wallet
                    $this->db->set('wallet', 'wallet + ' . $distributor_commission, FALSE);
                    $this->db->where('id', $user->distributor_id);
                    $this->db->update('tbl_distributors');
                    
                    // Update commission history
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
        
        // =============================================
        // ✅ UPDATE ADMIN COMMISSION STATUS
        // =============================================
        foreach ($bets as $bet) {
            $admin_comm = round($bet->amount * 20 / 100, 2);
            if($admin_comm > 0) {
                $exists = $this->db->get_where('tbl_commission_history', [
                    'source_user_id' => $bet->userid,
                    'period_id' => $period_id,
                    'commission_type' => 'admin'
                ])->row();
                
                if(!$exists) {
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
                } else {
                    $this->db->where('id', $exists->id);
                    $this->db->update('tbl_commission_history', [
                        'amount' => $admin_comm,
                        'status' => 'completed',
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
        // =============================================

        // ✅ Update game for next round
        $this->db->where('id', $gameId);
        $this->db->update('tbl_games', [
            'period_id'  => $period_id + 1,
            'win_number' => $selectedBetIndex,
            'manual_set' => 0
        ]);

        // ✅ Complete transaction
        $this->db->trans_complete();

        // ✅ Success Message
        echo "<br>🎯 Cron Completed Successfully!";
    }

    public function place_bet()
    {
        $userid     = $this->input->post('userid');
        $amount     = floatval($this->input->post('amount'));
        $bet_type   = $this->input->post('bet_type');
        $bet_value  = $this->input->post('bet_value');
        $gameId     = $this->get_game_id();
        $period_id  = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));

        if (!$userid || !$bet_type || $bet_value === null || $amount <= 0) {
            $response = array('status' => 'error', 'message' => 'Invalid input for bet placement.');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $user = $this->db_model->select_multi('*', 'tbl_users', array('id' => $userid));
        if (!$user) {
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $wallet_bal = floatval($user->wallet);
        if ($wallet_bal < $amount) {
            $response = array('status' => 'error', 'message' => 'Insufficient Balance');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        // =============================================
        // COMMISSION CALCULATION
        // =============================================
        $dealer_id = $user->dealer_id ?? null;
        $distributor_id = $user->distributor_id ?? null;
        
        $dealer_commission = 0;
        $distributor_commission = 0;
        $admin_commission = 0;
        $dealer_rate = 0;
        $distributor_rate = 0;
        
        // Calculate Dealer Commission
        if($dealer_id) {
            $dealer = $this->db->get_where('tbl_dealers', ['id' => $dealer_id])->row();
            if($dealer) {
                $dealer_rate = floatval($dealer->commission_rate ?? 2.0);
                $dealer_commission = round($amount * $dealer_rate / 100, 2);
            }
        }
        
        // Calculate Distributor Commission
        if($distributor_id) {
            $distributor = $this->db->get_where('tbl_distributors', ['id' => $distributor_id])->row();
            if($distributor) {
                $distributor_rate = floatval($distributor->commission_rate ?? 0.5);
                $distributor_commission = round($amount * $distributor_rate / 100, 2);
            }
        }
        
        // ✅ Calculate Admin Commission (20% of bet amount)
        $admin_commission = round($amount * 20 / 100, 2);
        // =============================================

        // Insert bet with commissions
        $array = array(
            'userid'       => $userid,
            'bet'          => $bet_value,
            'bet_type'     => $bet_type,
            'period_id'    => $period_id,
            'game_id'      => $gameId,
            'amount'       => $amount,
            'dealer_commission' => $dealer_commission,
            'distributor_commission' => $distributor_commission,
            'admin_commission' => $admin_commission,  // ✅ ADD THIS
            'user_amount'  => $wallet_bal,
            'date'         => date('Y-m-d H:i:s'),
            'status'       => 'pending'
        );
        $this->db->insert('tbl_lucky36_bet2', $array);
        $bet_id = $this->db->insert_id();
        
        // Insert dealer commission record
        if($dealer_commission > 0) {
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
        
        // Insert distributor commission record
        if($distributor_commission > 0) {
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
        
        // ✅ Insert admin commission record
        if($admin_commission > 0) {
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
                'bet_id' => $bet_id,
                'status' => 'pending',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }

        // Deduct wallet
        $new_wallet = $wallet_bal - $amount;
        $this->db->where('id', $userid);
        $this->db->update('tbl_users', ['wallet' => $new_wallet]);

        // Transaction entry
        $this->db->insert('tbl_transactions', [
            'userid' => $userid,
            'amount' => $amount,
            'status' => 'debit',
            'type'   => 'Bet Placed'
        ]);

        $response = array(
            'status'         => 'success',
            'message'        => 'Bet Placed Successfully',
            'data' => array(
                'wallet'         => $new_wallet,
                'winning_wallet' => floatval($user->winning_wallet),
                'period_id'      => $period_id,
                'dealer_commission' => $dealer_commission,
                'distributor_commission' => $distributor_commission,
                'admin_commission' => $admin_commission  // ✅ ADD THIS
            )
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }


public function cron2205()
{
    $gameId    = $this->get_game_id();
    $period_id = $this->db_model->select('period_id', 'tbl_games', ['id' => $gameId]);

    // ✅ Prevent duplicate cron run
    $already = $this->db_model->count_all('tbl_lucky36_results2', [
        'period_id' => $period_id,
        'game_id'   => $gameId
    ]);

    if ($already > 0) {

        echo "Result already declared for Period ID : ".$period_id;

        return;
    }

    // ✅ Start transaction
    $this->db->trans_start();

    $manual_set = $this->db_model->select('manual_set', 'tbl_games', ['id' => $gameId]);
    $win_number = $this->db_model->select('win_number', 'tbl_games', ['id' => $gameId]);

    // ✅ Decide winning number
    if ($manual_set == 1) {

        $selectedBetIndex = $win_number;

    } else {

        $betAmounts = [];

        for ($i = 0; $i <= 35; $i++) {

            $betAmounts[$i] = $this->db_model->sum('amount', 'tbl_lucky36_bet2', [
                'period_id' => $period_id,
                'game_id'   => $gameId,
                'bet'       => $i,
                'status'    => 'pending'
            ]) + 0;
        }

        $minBetAmount  = min($betAmounts);
        $minBetIndices = array_keys($betAmounts, $minBetAmount);

        $selectedBetIndex = $minBetIndices[array_rand($minBetIndices)];
    }

    // ✅ PRINT RESULT
    echo "Period ID : ".$period_id."<br>";
    echo "Winning Number : ".$selectedBetIndex."<br>";

    // ✅ Insert result
    $this->db->insert('tbl_lucky36_results2', [
        'period_id'  => $period_id,
        'game_id'    => $gameId,
        'win_number' => $selectedBetIndex
    ]);

    // ✅ Get all pending bets
    $bets = $this->db
        ->where('period_id', $period_id)
        ->where('game_id', $gameId)
        ->where('status', 'pending')
        ->get('tbl_lucky36_bet2')
        ->result();

    foreach ($bets as $bet) {

        $win_amount = $this->calculate_payout(
            $bet->bet_type,
            $bet->bet,
            $selectedBetIndex,
            $bet->amount
        );

        if ($win_amount > 0) {

            // ✅ SAFE wallet update
            $this->db->set('winning_wallet', 'winning_wallet + ' . $win_amount, FALSE);
            $this->db->where('id', $bet->userid);
            $this->db->update('tbl_users');

            // ✅ Transaction log
            $this->db->insert('tbl_transactions', [
                'userid'      => $bet->userid,
                'amount'      => $win_amount,
                'type'        => 'game_win',
                'description' => 'Lucky 36 Game Win',
            ]);

            $status = 'won';

        } else {

            $status = 'lost';
        }

        // ✅ Update bet status
        $this->db->where('id', $bet->id);
        $this->db->update('tbl_lucky36_bet2', [
            'status' => $status
        ]);
    }

    // ✅ Update game for next round
    $this->db->where('id', $gameId);
    $this->db->update('tbl_games', [
        'period_id'  => $period_id + 1,
        'win_number' => $selectedBetIndex,
        'manual_set' => 0
    ]);

    // ✅ Complete transaction
    $this->db->trans_complete();

    // ✅ Success Message
    echo "<br>Result Declared Successfully";
}
public function cron2505()
    {
        $this->db->trans_start();

        $gameId = $this->get_game_id();
        $game = $this->db->get_where('tbl_games', ['id' => $gameId])->row();

        if (!$game) {
            return;
        }

        $period_id = $game->period_id;
        $manual_set = $game->manual_set;
        $win_number = $game->win_number;

        // ✅ Prevent duplicate cron run
        $already = $this->db->get_where('tbl_lucky36_results2', [
            'period_id' => $period_id,
            'game_id'   => $gameId
        ])->row();

        if ($already) {
            $this->db->trans_complete();
            echo "Result already declared for Period ID: " . $period_id;
            return;
        }

        // =========================
        // RESULT SELECTION LOGIC (Same as Luckey36)
        // =========================
        if ($manual_set == 1) {
            $selectedBetIndex = $win_number;
        } else {
            $bets = $this->db->get_where('tbl_lucky36_bet2', [
                'period_id' => $period_id,
                'game_id'   => $gameId,
                'status'    => 'pending'
            ])->result();

            if (empty($bets)) {
                $selectedBetIndex = rand(0, 35);
            } else {
                $totalEffectiveAmount = 0;

                foreach ($bets as $bet) {
                    $amount = floatval($bet->amount);
                    $dealerCommission = floatval($bet->dealer_commission ?? 0);
                    $distributorCommission = floatval($bet->distributor_commission ?? 0);
                    $effectiveBetAmount = $amount - $dealerCommission - $distributorCommission;
                    
                    $totalEffectiveAmount += $effectiveBetAmount;
                    $bet->exposure_amount = $amount; 
                    $bet->effective_amount = $effectiveBetAmount;
                }

                $adminCommission = round($totalEffectiveAmount * 0.20, 2);
                $remainingProfit = $totalEffectiveAmount - $adminCommission;

                if ($remainingProfit < 0) {
                    $remainingProfit = 0;
                }

                $exposures = array_fill(0, 36, 0.0);

                foreach ($bets as $bet) {
                    for ($number = 0; $number <= 35; $number++) {
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
                        if ($maxEligiblePayout === null || $payout > $maxEligiblePayout) {
                            $maxEligiblePayout = $payout;
                            $safestNumbers = [$number];
                        } elseif ($payout === $maxEligiblePayout) {
                            $safestNumbers[] = $number;
                        }
                    }
                }

                /*if (!empty($safestNumbers)) {
                    $selectedBetIndex = $safestNumbers[array_rand($safestNumbers)];
                } else {
                    $closestNumbers = [];
                    $closestDistance = null;

                    foreach ($exposures as $number => $payout) {
                        $distance = abs($payout - $remainingProfit);

                        if ($closestDistance === null || $distance < $closestDistance) {
                            $closestDistance = $distance;
                            $closestNumbers = [$number];
                        } elseif ($distance === $closestDistance) {
                            $closestNumbers[] = $number;
                        }
                    }

                    $selectedBetIndex = $closestNumbers[array_rand($closestNumbers)];
                }*/
                
                                if (!empty($safestNumbers)) {
                    $maxExposure = -1;
                    $selectedBetIndex = $safestNumbers[0];
                    foreach ($safestNumbers as $number) {
                        if ($exposures[$number] > $maxExposure) {
                            $maxExposure = $exposures[$number];
                            $selectedBetIndex = $number;
                        }
                    }
                } else {
                    $closestNumbers = [];
                    $closestDistance = null;

                    foreach ($exposures as $number => $payout) {
                        $distance = abs($payout - $remainingProfit);

                        if ($closestDistance === null || $distance < $closestDistance) {
                            $closestDistance = $distance;
                            $closestNumbers = [$number];
                        } elseif ($distance === $closestDistance) {
                            $closestNumbers[] = $number;
                        }
                    }

                    $minExposure = PHP_INT_MAX;
                    $selectedBetIndex = $closestNumbers[0];
                    foreach ($closestNumbers as $number) {
                        if ($exposures[$number] < $minExposure) {
                            $minExposure = $exposures[$number];
                            $selectedBetIndex = $number;
                        }
                    }
                }
            }
        }

        // ✅ PRINT RESULT
        echo "Period ID: " . $period_id . "<br>";
        echo "Winning Number: " . $selectedBetIndex . "<br>";

        // ✅ Insert result
        $this->db->insert('tbl_lucky36_results2', [
            'period_id'  => $period_id,
            'game_id'    => $gameId,
            'win_number' => $selectedBetIndex
        ]);

        // ✅ Get all pending bets
        $bets = $this->db
            ->where('period_id', $period_id)
            ->where('game_id', $gameId)
            ->where('status', 'pending')
            ->get('tbl_lucky36_bet2')
            ->result();

        // =========================
        // PROCESS BETS (Win/Loss)
        // =========================
        foreach ($bets as $bet) {
            $win_amount = $this->calculate_payout(
                $bet->bet_type,
                $bet->bet,
                $selectedBetIndex,
                $bet->amount
            );

            if ($win_amount > 0) {
                // Update user winning wallet
                $this->db->set('winning_wallet', 'winning_wallet + ' . $win_amount, FALSE);
                $this->db->where('id', $bet->userid);
                $this->db->update('tbl_users');

                // Transaction log
                $this->db->insert('tbl_transactions', [
                    'userid' => $bet->userid,
                    'amount' => $win_amount,
                    'type'   => 'game_win',
                    'status' => 'credit'
                ]);
                
                // Update win_amount in bet table
                $this->db->where('id', $bet->id);
                $this->db->update('tbl_lucky36_bet2', ['win_amount' => $win_amount]);
                
                $status = 'won';
            } else {
                $status = 'lost';
            }

            // Update bet status
            $this->db->where('id', $bet->id);
            $this->db->update('tbl_lucky36_bet2', [
                'status' => $status,
                'result_number' => $selectedBetIndex
            ]);
        }

        // =============================================
        // CREDIT COMMISSIONS TO DEALER & DISTRIBUTOR
        // =============================================
        foreach ($bets as $bet) {
            $user = $this->db->get_where('tbl_users', ['id' => $bet->userid])->row();
            
            // Credit Dealer Commission
            if($user && $user->dealer_id) {
                $dealer_commission = floatval($bet->dealer_commission ?? 0);
                if($dealer_commission > 0) {
                    // Credit to dealer wallet
                    $this->db->set('wallet', 'wallet + ' . $dealer_commission, FALSE);
                    $this->db->where('id', $user->dealer_id);
                    $this->db->update('tbl_dealers');
                    
                    // Update commission history
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
            
            // Credit Distributor Commission
            if($user && $user->distributor_id) {
                $distributor_commission = floatval($bet->distributor_commission ?? 0);
                if($distributor_commission > 0) {
                    // Credit to distributor wallet
                    $this->db->set('wallet', 'wallet + ' . $distributor_commission, FALSE);
                    $this->db->where('id', $user->distributor_id);
                    $this->db->update('tbl_distributors');
                    
                    // Update commission history
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
        // =============================================

        // ✅ Update game for next round
        $this->db->where('id', $gameId);
        $this->db->update('tbl_games', [
            'period_id'  => $period_id + 1,
            'win_number' => $selectedBetIndex,
            'manual_set' => 0
        ]);

        // ✅ Complete transaction
        $this->db->trans_complete();

        // ✅ Success Message
        echo "<br>Result Declared Successfully";
    }

    private function calculate_payout2205($bet_type, $bet_value, $winning_number, $amount)
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

    $multiplier = isset($payouts[$bet_type]) ? $payouts[$bet_type] : 0;

    if ($multiplier <= 0) {
        return 0;
    }

    $winning_number = intval($winning_number);
    $bet_value      = trim(strtolower($bet_value));
    $amount         = floatval($amount);

    // JSON array support
    if (strpos($bet_value, '[') === 0) {

        $decoded = json_decode($bet_value, true);

        if (is_array($decoded)) {
            $bet_value = implode(',', array_map('strval', $decoded));
        }
    }

    $isWin = false;

    switch ($bet_type) {

        // =========================
        // STRAIGHT (OLD LOGIC)
        // =========================

        case 'straight':

            $isWin = ($winning_number === intval($bet_value));

            break;


        // =========================
        // SPLIT / STREET / CORNER / LINE
        // NEW MULTIPLE VALUE LOGIC
        // =========================

        case 'split':
        case 'street':
        case 'corner':
        case 'line':

            preg_match_all('/\d+/', $bet_value, $matches);

            $numbers = array_unique(array_map('intval', $matches[0]));

            $isWin = in_array($winning_number, $numbers, true);

            if ($isWin) {

                $count = count($numbers);

                if ($count > 0) {

                    // Divide amount among selected numbers
                    $single_amount = $amount / $count;

                    return round($single_amount * $multiplier, 2);
                }
            }

            return 0;

        // =========================
        // COLUMN
        // =========================

        case 'column':

            if (in_array($bet_value, ['1', 'first', 'first_column'], true)) {

                $isWin = $winning_number > 0 && $winning_number % 3 === 1;

            } elseif (in_array($bet_value, ['2', 'second', 'second_column'], true)) {

                $isWin = $winning_number > 0 && $winning_number % 3 === 2;

            } elseif (in_array($bet_value, ['3', 'third', 'third_column'], true)) {

                $isWin = $winning_number > 0 && $winning_number % 3 === 0;
            }

            break;

        // =========================
        // DOZEN
        // =========================

        case 'dozen':

            if (in_array($bet_value, ['1', 'first', '1st', '1st12'], true)) {

                $isWin = $winning_number >= 1 && $winning_number <= 12;

            } elseif (in_array($bet_value, ['2', 'second', '2nd', '2nd12'], true)) {

                $isWin = $winning_number >= 13 && $winning_number <= 24;

            } elseif (in_array($bet_value, ['3', 'third', '3rd', '3rd12'], true)) {

                $isWin = $winning_number >= 25 && $winning_number <= 36;
            }

            break;

        // =========================
        // ODD / EVEN
        // =========================

        case 'odd_even':

            if ($bet_value === 'odd' || $bet_value === '1') {

                $isWin = $winning_number % 2 === 1;

            } elseif ($bet_value === 'even' || $bet_value === '2') {

                $isWin = $winning_number > 0 && $winning_number % 2 === 0;
            }

            break;

        // =========================
        // RED / BLACK
        // =========================

        case 'red_black':

            $redNumbers = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];

            if ($bet_value === 'red' || $bet_value === '1') {

                $isWin = in_array($winning_number, $redNumbers, true);

            } elseif ($bet_value === 'black' || $bet_value === '2') {

                $isWin = $winning_number > 0 && !in_array($winning_number, $redNumbers, true);
            }

            break;

        // =========================
        // HIGH / LOW
        // =========================

        case 'high_low':

            if ($bet_value === 'low' || $bet_value === '1') {

                $isWin = $winning_number >= 1 && $winning_number <= 18;

            } elseif ($bet_value === 'high' || $bet_value === '2') {

                $isWin = $winning_number >= 19 && $winning_number <= 36;
            }

            break;
    }

    $win_amount = round($amount * $multiplier, 2);

    return $isWin ? $win_amount : 0;
}
 private function calculate_payout($bet_type, $bet_value, $winning_number, $amount)
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

        $multiplier = isset($payouts[$bet_type]) ? $payouts[$bet_type] : 0;

        if ($multiplier <= 0) {
            return 0;
        }

        $winning_number = intval($winning_number);
        $bet_value      = trim(strtolower($bet_value));
        $amount         = floatval($amount);

        // JSON array support
        if (strpos($bet_value, '[') === 0) {
            $decoded = json_decode($bet_value, true);
            if (is_array($decoded)) {
                $bet_value = implode(',', array_map('strval', $decoded));
            }
        }

        $isWin = false;

        switch ($bet_type) {
            case 'straight':
                $isWin = ($winning_number === intval($bet_value));
                break;

            case 'split':
            case 'street':
            case 'corner':
            case 'line':
                preg_match_all('/\d+/', $bet_value, $matches);
                $numbers = array_unique(array_map('intval', $matches[0]));
                $isWin = in_array($winning_number, $numbers, true);

                if ($isWin) {
                    $count = count($numbers);
                    if ($count > 0) {
                        $single_amount = $amount / $count;
                        return round($single_amount * $multiplier, 2);
                    }
                }
                return 0;

            case 'column':
                if (in_array($bet_value, ['1', 'first', 'first_column'], true)) {
                    $isWin = $winning_number > 0 && $winning_number % 3 === 1;
                } elseif (in_array($bet_value, ['2', 'second', 'second_column'], true)) {
                    $isWin = $winning_number > 0 && $winning_number % 3 === 2;
                } elseif (in_array($bet_value, ['3', 'third', 'third_column'], true)) {
                    $isWin = $winning_number > 0 && $winning_number % 3 === 0;
                }
                break;

            case 'dozen':
                if (in_array($bet_value, ['1', 'first', '1st', '1st12'], true)) {
                    $isWin = $winning_number >= 1 && $winning_number <= 12;
                } elseif (in_array($bet_value, ['2', 'second', '2nd', '2nd12'], true)) {
                    $isWin = $winning_number >= 13 && $winning_number <= 24;
                } elseif (in_array($bet_value, ['3', 'third', '3rd', '3rd12'], true)) {
                    $isWin = $winning_number >= 25 && $winning_number <= 36;
                }
                break;

            case 'odd_even':
                if ($bet_value === 'odd' || $bet_value === '1') {
                    $isWin = $winning_number % 2 === 1;
                } elseif ($bet_value === 'even' || $bet_value === '2') {
                    $isWin = $winning_number > 0 && $winning_number % 2 === 0;
                }
                break;

            case 'red_black':
                $redNumbers = [1,3,5,7,9,12,14,16,18,19,21,23,25,27,30,32,34,36];
                if ($bet_value === 'red' || $bet_value === '1') {
                    $isWin = in_array($winning_number, $redNumbers, true);
                } elseif ($bet_value === 'black' || $bet_value === '2') {
                    $isWin = $winning_number > 0 && !in_array($winning_number, $redNumbers, true);
                }
                break;

            case 'high_low':
                if ($bet_value === 'low' || $bet_value === '1') {
                    $isWin = $winning_number >= 1 && $winning_number <= 18;
                } elseif ($bet_value === 'high' || $bet_value === '2') {
                    $isWin = $winning_number >= 19 && $winning_number <= 36;
                }
                break;
        }

        $win_amount = round($amount * $multiplier, 2);
        return $isWin ? $win_amount : 0;
    }

    private function get_request_body()
    {
        static $body = null;

        if ($body !== null) {
            return $body;
        }

        $rawInput = trim(file_get_contents('php://input'));
        $body = array();

        if ($rawInput !== '') {
            $decoded = json_decode($rawInput, true);
            if (is_array($decoded)) {
                $body = $decoded;
            }
        }

        return $body;
    }

    private function get_game_id()
    {
        $gameId = $this->input->post('gameid') ?? $this->input->post('game_id');
        if (!$gameId) {
            $body = $this->get_request_body();
            $gameId = $body['gameid'] ?? $body['game_id'] ?? null;
        }
        $gameId = intval($gameId);
        return $gameId > 0 ? $gameId : 9;
    }

    public function place_bet2205()
    {
        $userid     = $this->input->post('userid');
        $amount     = floatval($this->input->post('amount'));
        $bet_type   = $this->input->post('bet_type');
        $bet_value  = $this->input->post('bet_value');
        $gameId     = $this->get_game_id();
        $period_id  = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));

        if (!$userid || !$bet_type || $bet_value === null || $amount <= 0) {
            $response = array('status' => 'error', 'message' => 'Invalid input for bet placement.');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $chek_user = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if ($chek_user <= 0) {
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $wallet_bal = floatval($this->db_model->select('wallet', 'tbl_users', array('id' => $userid)));
        if ($wallet_bal < $amount) {
            $response = array('status' => 'error', 'message' => 'Insufficient Balance');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $array = array(
            'userid'       => $userid,
            'bet'          => $bet_value,
            'bet_type'     => $bet_type,
            'period_id'    => $period_id,
            'game_id'      => $gameId,
            'amount'       => $amount,
            'user_amount'  => $wallet_bal,
            'date'         => date('Y-m-d H:i:s'),
            'status'       => 'pending'
        );
        $this->db->insert('tbl_lucky36_bet2', $array);

        /*$new_wallet = $wallet_bal - $amount;
        $this->db->where('id', $userid);
        $this->db->update('tbl_users', [
            'wallet' => $new_wallet,
        ]);

        $winning_wallet = floatval($this->db_model->select('winning_wallet', 'tbl_users', array('id' => $userid)));
*/

$new_wallet = $wallet_bal - $amount;

$this->db->where('id', $userid);
$this->db->update('tbl_users', [
    'wallet' => $new_wallet,
]);

// Transaction entry
$this->db->insert('tbl_transactions', [
    'userid' => $userid,
    'amount' => $amount,
    'status' => 'debit',
    'type'   => 'Bet Placed'
]);

$winning_wallet = floatval(
    $this->db_model->select(
        'winning_wallet',
        'tbl_users',
        array('id' => $userid)
    )
);
        $response = array(
            'status'         => 'success',
            'message'        => 'Bet Placed Successfully',
            'wallet'         => $new_wallet,
            'winning_wallet' => $winning_wallet,
            'period_id'      => $period_id
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    public function place_bet2505()
    {
        $userid     = $this->input->post('userid');
        $amount     = floatval($this->input->post('amount'));
        $bet_type   = $this->input->post('bet_type');
        $bet_value  = $this->input->post('bet_value');
        $gameId     = $this->get_game_id();
        $period_id  = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));

        if (!$userid || !$bet_type || $bet_value === null || $amount <= 0) {
            $response = array('status' => 'error', 'message' => 'Invalid input for bet placement.');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $user = $this->db_model->select_multi('*', 'tbl_users', array('id' => $userid));
        if (!$user) {
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        $wallet_bal = floatval($user->wallet);
        if ($wallet_bal < $amount) {
            $response = array('status' => 'error', 'message' => 'Insufficient Balance');
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }

        // =============================================
        // COMMISSION CALCULATION
        // =============================================
        $dealer_id = $user->dealer_id ?? null;
        $distributor_id = $user->distributor_id ?? null;
        
        $dealer_commission = 0;
        $distributor_commission = 0;
        $dealer_rate = 0;
        $distributor_rate = 0;
        
        // Calculate Dealer Commission
        if($dealer_id) {
            $dealer = $this->db->get_where('tbl_dealers', ['id' => $dealer_id])->row();
            if($dealer) {
                $dealer_rate = floatval($dealer->commission_rate ?? 2.0);
                $dealer_commission = round($amount * $dealer_rate / 100, 2);
            }
        }
        
        // Calculate Distributor Commission
        if($distributor_id) {
            $distributor = $this->db->get_where('tbl_distributors', ['id' => $distributor_id])->row();
            if($distributor) {
                $distributor_rate = floatval($distributor->commission_rate ?? 0.5);
                $distributor_commission = round($amount * $distributor_rate / 100, 2);
            }
        }
        // =============================================

        // Insert bet with commissions
        $array = array(
            'userid'       => $userid,
            'bet'          => $bet_value,
            'bet_type'     => $bet_type,
            'period_id'    => $period_id,
            'game_id'      => $gameId,
            'amount'       => $amount,
            'dealer_commission' => $dealer_commission,
            'distributor_commission' => $distributor_commission,
            'user_amount'  => $wallet_bal,
            'date'         => date('Y-m-d H:i:s'),
            'status'       => 'pending'
        );
        $this->db->insert('tbl_lucky36_bet2', $array);
        $bet_id = $this->db->insert_id();
        
        // Insert dealer commission record
        if($dealer_commission > 0) {
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
        
        // Insert distributor commission record
        if($distributor_commission > 0) {
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

        // Deduct wallet
        $new_wallet = $wallet_bal - $amount;
        $this->db->where('id', $userid);
        $this->db->update('tbl_users', ['wallet' => $new_wallet]);

        // Transaction entry
        $this->db->insert('tbl_transactions', [
            'userid' => $userid,
            'amount' => $amount,
            'status' => 'debit',
            'type'   => 'Bet Placed'
        ]);

        $response = array(
            'status'         => 'success',
            'message'        => 'Bet Placed Successfully',
            'data' => array(
                'wallet'         => $new_wallet,
                'winning_wallet' => floatval($user->winning_wallet),
                'period_id'      => $period_id,
                'dealer_commission' => $dealer_commission,
                'distributor_commission' => $distributor_commission
            )
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function wallet_balance()
    {
        $userid = $this->input->post('userid');

        if (!$userid) {
            $response = array('status' => 'error', 'message' => 'User ID is required');
        } else {
            $user = $this->db->get_where('tbl_users', ['id' => $userid])->row();
            if (!$user) {
                $response = array('status' => 'error', 'message' => 'Invalid User ID');
            } else {
                $pending_amount = floatval($this->db_model->sum('amount', 'tbl_lucky36_bet2', ['userid' => $userid, 'status' => 'pending']));
                $response = array(
                    'status'          => 'success',
                    'wallet'          => floatval($user->wallet),
                    'winning_wallet'  => floatval($user->winning_wallet),
                    'pending_bets'    => $pending_amount
                );
            }
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function my_bets()
    {
        $userid    = $this->input->post('userid');
        $period_id = $this->input->post('period_id');
        $chek_user = $this->db_model->count_all('tbl_users', array('id' => $userid));
        
        if ($chek_user <= 0) {
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        } else {
            $this->db->select('bet_type, SUM(amount) as total_amount, COUNT(*) as bet_count');
            $this->db->from('tbl_lucky36_bet2');
            $this->db->where('userid', $userid);
            $this->db->where('period_id', $period_id);
            $this->db->where('status', 'pending');
            $this->db->group_by('bet_type');
            $bets = $this->db->get()->result();
            
            $betAmounts = [];
            $total_bet = 0;
            
            foreach($bets as $bet) {
                $betAmounts[$bet->bet_type] = [
                    'amount' => $bet->total_amount,
                    'count' => $bet->bet_count
                ];
                $total_bet += $bet->total_amount;
            }
            
            $win_bets = $this->db_model->sum('amount', 'tbl_lucky36_bet2', ['period_id' => $period_id - 1, 'status' => 'won', 'userid' => $userid]) + 0;
            
            $response = array(
                'status'    => 'success',
                'my_bets'   => $betAmounts,
                'total_bet' => $total_bet,
                'win_bets'  => $win_bets,
            );
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function show_result()
    {
        $gameId    = $this->get_game_id();
        $period_id = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));
        $result    = $this->db_model->count_all('tbl_lucky36_results2', array('period_id' => $period_id, 'game_id' => $gameId));
        $result2   = $this->db_model->select('win_number', 'tbl_games', array('id' => $gameId));
        $limit     = 10;
        $this->db->select('win_number');
        $this->db->from('tbl_lucky36_results2');
        $this->db->where('game_id', $gameId);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        $lstresult = $this->db->get()->result();
        
        if ($result > 0) {
            $number = $this->db_model->select('win_number', 'tbl_lucky36_results2', array('period_id' => $period_id,'game_id'   => $gameId));
            $response = array(
                'status'     => 'success',
                'number'     => $number,
                'last_res'   => $lstresult,
            );
        } elseif ($result2 != null) {
            $response = array(
                'status'     => 'success',
                'number'     => $result2,
                'last_res'   => $lstresult,
            );
        } else {
            $response = array(
                'status'  => 'error',
                'message' => 'Something Went Wrong Try Again Later...'
            );
        }
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function results()
    {
        $gameId    = $this->get_game_id();
        $period_id = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));
        $limit     = 10;
        $this->db->select('win_number');
        $this->db->from('tbl_lucky36_results2');
        $this->db->where('period_id', $period_id);
        $this->db->where('game_id', $gameId);
        $this->db->order_by('id', 'DESC');
        $this->db->limit($limit);
        $query = $this->db->get();
        $result = $query->num_rows() > 0 ? $query->result() : array();
        
        if (!empty($result)) {
            array_shift($result); 
        }
        $response = array('status' => 'success', 'results' => $result);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function period_id()
    {
        $gameId    = $this->get_game_id();
        $period_id = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));
        $response  = array(
            'status'    => 'success',
            'game_id'   => $gameId,
            'period_id' => $period_id
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function timer()
    {
        $gameId      = $this->get_game_id();
        $period_id   = $this->db_model->select('period_id', 'tbl_games', array('id' => $gameId));
        $currentTime = time();
        $remaining   = 59 - ($currentTime % 60);
        $response    = array(
            'status'      => 'success',
            'game_id'     => $gameId,
            'period_id'   => $period_id,
            'remaining_s' => $remaining
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function take()
    {
        $userid = $this->input->post('userid');
        if (empty($userid)) {
            $response = array(
                'status'      => 'error',
                'message'     => 'User ID is required.',
                'win_wallet'  => 0,
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        $user = $this->db->get_where('tbl_users', ['id' => $userid])->row();
        if (!$user) {
            $response = array(
                'status'      => 'error',
                'message'     => 'Invalid User ID.',
                'win_wallet'  => 0,
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        $wallet         = $user->wallet;
        $winning_wallet = $user->winning_wallet;
    
        if ($winning_wallet > 0) {
            $new_wallet = $wallet + $winning_wallet;
            $this->db->where('id', $userid);
            $this->db->update('tbl_users', [
                'wallet'         => $new_wallet,
                'winning_wallet' => 0
            ]);
            $this->db->insert('tbl_transactions', [
                'userid'      => $userid,
                'amount'      => $winning_wallet,
                'status'      => 'credit',
                'type'        => 'Winning transfer to wallet',
            ]);
            $response = array(
                'status'      => 'success',
                'message'     => 'Winning wallet amount added to main wallet successfully.',
                'win_wallet'  => 0
            );
        } else {
            $response = array(
                'status'      => 'error',
                'message'     => 'Winning wallet is already empty.',
                'win_wallet'  => 0,
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function total_bets()
    {
        $userid    = $this->input->post('userid');
        $chek_user = $this->db_model->count_all('tbl_users', array('id' => $userid));
        $today_date = date('Y-m-d');
        
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $result = $this->db
                ->select('SUM(amount) as total_amount, COUNT(*) as total_count')
                ->where("DATE(date) =", $today_date)
                ->where('userid', $userid)
                ->where('game_id', $this->get_game_id())
                ->get('tbl_lucky36_bet2')
                ->row();
            $total_amount = ($result) ? $result->total_amount + 0 : 0; 
            $total_count  = ($result) ? $result->total_count : 0;
            $response     = array('status' => 'success', 'total_bet_amount' => $total_amount,'total_bet_count' => $total_count);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function win_bets()
    {
        $userid    = $this->input->post('userid');
        $chek_user = $this->db_model->count_all('tbl_users', array('id' => $userid));
        $today_date = date('Y-m-d');
        
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $win_bet  = $this->db->select_sum('amount')->where("DATE(date) =", $today_date)->where('userid', $userid)->where('status', 'won')->where('game_id', $this->get_game_id())->get('tbl_lucky36_bet2')->row()->amount + 0;
            $response = array('status' => 'success', 'win_bets' => $win_bet);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function cancel_last_bet()
    {
        $userid    = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        if (!$userid || !$period_id) {
            $response = ['status' => 'error', 'message' => 'User ID and Period ID are required'];
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $this->db->where(['userid'  => $userid,'period_id' => $period_id,'status' => 'pending']);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $bet = $this->db->get('tbl_lucky36_bet2')->row();

        if (!$bet) {
            $response = ['status' => 'error', 'message' => 'No pending bet found.'];
        } else {
            $this->db->where('id', $bet->id);
            $this->db->update('tbl_lucky36_bet2', ['status' => 'cancelled']);

            $this->db->insert('tbl_transactions', [
                'userid'      => $userid,
                'amount'      => $bet->amount,
                'status'      => 'credit',
                'type'        => 'Refund for cancelled bet ID ' . $bet->id,
            ]);

            $this->db->set('wallet', 'wallet + ' . $bet->amount, FALSE);
            $this->db->where('id', $userid);
            $this->db->update('tbl_users');

            $response = [
                'status'       => 'success',
                'message'      => 'Last pending bet cancelled. ₹' . $bet->amount . ' refunded.',
                'cancelled_id' => $bet->id
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function cancel_all_bets()
    {
        $userid    = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        if (!$userid || !$period_id) {
            $response = ['status' => 'error', 'message' => 'User ID and Period ID are required'];
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $this->db->where(['userid'    => $userid, 'period_id' => $period_id, 'status'    => 'pending']);
        $bets = $this->db->get('tbl_lucky36_bet2')->result();

        if (empty($bets)) {
            $response = ['status' => 'error', 'message' => 'No pending bets found to cancel.'];
        } else {
            $total_refund = 0;
            $cancelled_ids = [];

            foreach ($bets as $bet) {
                $total_refund += $bet->amount;
                $cancelled_ids[] = $bet->id;

                $this->db->where('id', $bet->id);
                $this->db->update('tbl_lucky36_bet2', ['status' => 'cancelled']);

                $this->db->insert('tbl_transactions', [
                    'userid'      => $userid,
                    'amount'      => $bet->amount,
                    'status'      => 'credit',
                    'type'        => 'Refund for cancelled bet ID ' . $bet->id,
                ]);
            }

            $this->db->set('wallet', 'wallet + ' . $total_refund, FALSE);
            $this->db->where('id', $userid);
            $this->db->update('tbl_users');

            $response = [
                'status'        => 'success',
                'message'       => count($bets) . ' pending bet(s) cancelled. ₹' . $total_refund . ' refunded.',
                'cancelled_ids' => $cancelled_ids
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function play_chips()
    {
        $img_path        = base_url('axxests/chips_img/');
        $where_condition = "status = 1";
        $show_games      = $this->db_model->get_specific_records('tbl_play_chips', $where_condition, 'id, img, amount');
        
        if($show_games){
            $response = array('status' => 'success', 'img_path' => $img_path, 'data' => $show_games);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
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

    // Get only last bet
    $last_bet = $this->db
        ->where('userid', $userid)
        ->where('game_id', $gameId)
        ->where_in('status', ['pending','won','lost'])
        ->order_by('id', 'DESC')
        ->limit(1)
        ->get('tbl_lucky36_bet2')
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

    // Check wallet
    $wallet = floatval(
        $this->db_model->select(
            'wallet',
            'tbl_users',
            ['id' => $userid]
        )
    );

    if ($wallet < $amount) {

        $response = [
            'status'  => 'error',
            'message' => 'Insufficient Balance',
            'wallet'  => $wallet
        ];

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // Insert repeated bet
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

    $this->db->insert('tbl_lucky36_bet2', $insertData);

    // Deduct wallet
    $new_wallet = $wallet - $amount;

    $this->db->where('id', $userid);

    $this->db->update('tbl_users', [
        'wallet' => $new_wallet
    ]);

    // Transaction entry
    $this->db->insert('tbl_transactions', [
        'userid' => $userid,
        'amount' => $amount,
        'status' => 'debit',
        'type'   => 'Repeat Last Bet'
    ]);

    $response = [
        'status'      => 'success',
        'message'     => 'Last bet repeated successfully',
        'period_id'   => $current_period_id,
        'bet_type'    => $last_bet->bet_type,
        'bet_value'   => $last_bet->bet,
        'amount'      => $amount,
        'wallet'      => $new_wallet
    ];

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

}
