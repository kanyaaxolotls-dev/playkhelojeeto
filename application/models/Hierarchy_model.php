<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Hierarchy_model extends CI_Model {

    public function __construct() {
        parent::__construct();
    }

    public function get_user($user_id) {
        return $this->db->get_where('tbl_users', ['id' => $user_id])->row();
    }

    public function get_dealer($dealer_id) {
        return $this->db->get_where('tbl_dealers', ['id' => $dealer_id])->row();
    }

    public function get_distributor($distributor_id) {
        return $this->db->get_where('tbl_distributors', ['id' => $distributor_id])->row();
    }

    public function get_my_dealers($distributor_id) {
        return $this->db->get_where('tbl_dealers', ['distributor_id' => $distributor_id, 'status' => 1])->result();
    }

    public function get_all_distributors($where = []) {
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->get('tbl_distributors')->result();
    }

    public function get_all_dealers($where = []) {
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->get('tbl_dealers')->result();
    }

    public function get_my_users_for_dealer($dealer_id) {
        return $this->db->get_where('tbl_users', ['dealer_id' => $dealer_id, 'status' => 1])->result();
    }

    public function get_my_users_for_distributor($distributor_id) {
        return $this->db->get_where('tbl_users', ['distributor_id' => $distributor_id, 'status' => 1])->result();
    }

    public function get_commission_history($filters = []) {
        if (!empty($filters)) {
            $this->db->where($filters);
        }
        $this->db->order_by('created_at', 'DESC');
        return $this->db->get('tbl_commission_history')->result();
    }

    public function get_commission_summary($filters = []) {
        if (!empty($filters)) {
            $this->db->where($filters);
        }
        $this->db->select('SUM(amount) as total, COUNT(id) as count');
        $result = $this->db->get('tbl_commission_history')->row();
        return [
            'total' => floatval($result->total),
            'count' => intval($result->count),
        ];
    }

    public function get_game_report($game_type, $start_date = null, $end_date = null, $filters = []) {
        $table = $this->get_bet_table($game_type);
        if (!$table) {
            return [];
        }

        $this->db->select("{$table}.userid, tbl_users.name AS user_name, {$table}.bet_type, {$table}.bet, SUM({$table}.amount) AS total_amount, COUNT(*) AS bets");
        $this->db->from($table);
        $this->db->join('tbl_users', "tbl_users.id = {$table}.userid", 'left');

        if (isset($filters['dealer_id'])) {
            $this->db->where('tbl_users.dealer_id', $filters['dealer_id']);
            unset($filters['dealer_id']);
        }
        if (isset($filters['distributor_id'])) {
            $this->db->where('tbl_users.distributor_id', $filters['distributor_id']);
            unset($filters['distributor_id']);
        }

        if (!empty($filters)) {
            $this->db->where($filters);
        }
        if ($start_date) {
            $this->db->where('DATE(' . $table . '.date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(' . $table . '.date) <=', $end_date);
        }

        $this->db->group_by("{$table}.userid, {$table}.bet_type, {$table}.bet");
        return $this->db->get()->result();
    }

    public function get_bet_table($game_type) {
        $map = [
            'lucky36'   => 'tbl_lucky36_bet',
            'lucky36gme'=> 'tbl_lucky36_bet2',
            'aviator'   => 'tbl_aviator_bet',
        ];
        return isset($map[$game_type]) ? $map[$game_type] : null;
    }

    public function adjust_wallet($table, $id, $field, $amount) {
        if (!in_array($field, ['wallet', 'winning_wallet', 'freeze_wallet'])) {
            return false;
        }
        $this->db->set($field, "$field + ($amount)", FALSE);
        $this->db->where('id', $id);
        return $this->db->update($table);
    }

    public function record_commission($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbl_commission_history', $data);
    }

    public function record_wallet_transfer($data) {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->db->insert('tbl_wallet_transfers', $data);
    }

    public function allocate_commission($userid, $bet_amount, $game_id, $game_type, $bet_id = null) {
        $user = $this->get_user($userid);
        if (!$user) {
            return null;
        }

        $dealer_id = $user->dealer_id ?? null;
        $distributor_id = $user->distributor_id ?? null;

        $dealer = null;
        $distributor = null;
        $dealer_commission = 0;
        $distributor_commission = 0;

        if ($dealer_id) {
            $dealer = $this->get_dealer($dealer_id);
            $rate = floatval($dealer->commission_rate ?? 2.0);
            $dealer_commission = round($bet_amount * $rate / 100, 2);
            if ($dealer) {
                $this->adjust_wallet('tbl_dealers', $dealer_id, 'wallet', $dealer_commission);
                $this->record_commission([
                    'source_user_id' => $userid,
                    'dealer_id'      => $dealer_id,
                    'distributor_id' => $distributor_id,
                    'commission_type'=> 'dealer',
                    'amount'         => $dealer_commission,
                    'rate'           => $rate,
                    'game_id'        => $game_id,
                    'game_type'      => $game_type,
                    'bet_id'         => $bet_id,
                    'note'           => 'Dealer commission from user bet',
                ]);
            }
        }

        if ($distributor_id) {
            $distributor = $this->get_distributor($distributor_id);
            $rate = floatval($distributor->commission_rate ?? 0.5);
            $distributor_commission = round($bet_amount * $rate / 100, 2);
            if ($distributor) {
                $this->adjust_wallet('tbl_distributors', $distributor_id, 'wallet', $distributor_commission);
                $this->record_commission([
                    'source_user_id' => $userid,
                    'dealer_id'      => $dealer_id,
                    'distributor_id' => $distributor_id,
                    'commission_type'=> 'distributor',
                    'amount'         => $distributor_commission,
                    'rate'           => $rate,
                    'game_id'        => $game_id,
                    'game_type'      => $game_type,
                    'bet_id'         => $bet_id,
                    'note'           => 'Distributor commission from user bet',
                ]);
            }
        }

        return [
            'dealer_commission' => round($dealer_commission, 2),
            'distributor_commission' => round($distributor_commission, 2),
            'admin_profit' => round($bet_amount - $dealer_commission - $distributor_commission, 2),
        ];
    }

    public function transfer_between_entities($from_type, $from_id, $to_type, $to_id, $amount, $transfer_type = 'wallet_transfer', $note = '') {
        $map = [
            'admin' => 'tbl_admin',
            'distributor' => 'tbl_distributors',
            'dealer' => 'tbl_dealers',
            'user' => 'tbl_users',
        ];

        if (!isset($map[$from_type]) || !isset($map[$to_type])) {
            return ['status' => false, 'message' => 'Invalid entity type'];
        }

        if ($amount <= 0) {
            return ['status' => false, 'message' => 'Invalid transfer amount'];
        }

        $from_table = $map[$from_type];
        $to_table = $map[$to_type];

        $from_record = $this->db->get_where($from_table, ['id' => $from_id])->row();
        if (!$from_record) {
            return ['status' => false, 'message' => 'Sender not found'];
        }

        $from_wallet = floatval($from_record->wallet ?? 0);
        if ($from_wallet < $amount) {
            return ['status' => false, 'message' => 'Insufficient balance'];
        }

        $to_record = $this->db->get_where($to_table, ['id' => $to_id])->row();
        if (!$to_record) {
            return ['status' => false, 'message' => 'Receiver not found'];
        }

        $this->adjust_wallet($from_table, $from_id, 'wallet', -$amount);
        $this->adjust_wallet($to_table, $to_id, 'wallet', $amount);

        $this->record_wallet_transfer([
            'from_type' => $from_type,
            'from_id'   => $from_id,
            'to_type'   => $to_type,
            'to_id'     => $to_id,
            'amount'    => $amount,
            'transfer_type' => $transfer_type,
            'note'      => $note,
        ]);

        return ['status' => true, 'message' => 'Transfer successful'];
    }
    // ========== ADD THESE MISSING METHODS ==========

    /**
     * Count total users under a distributor (through all their dealers)
     */
    public function count_users_under_distributor($distributor_id) {
        // First get all dealers under this distributor
        $this->db->select('id');
        $this->db->where('distributor_id', $distributor_id);
        $this->db->where('status', 1);
        $dealers = $this->db->get('tbl_dealers')->result();
        
        if(empty($dealers)) {
            return 0;
        }
        
        $dealer_ids = array();
        foreach($dealers as $dealer) {
            $dealer_ids[] = $dealer->id;
        }
        
        // Count users where dealer_id is in the list
        $this->db->where_in('dealer_id', $dealer_ids);
        $this->db->where('status', 1);
        return $this->db->count_all_results('tbl_users');
    }

    /**
     * Get all users under a distributor (through all their dealers)
     */
    public function get_all_users_under_distributor($distributor_id) {
        // Get all dealers under this distributor
        $this->db->select('id');
        $this->db->where('distributor_id', $distributor_id);
        $this->db->where('status', 1);
        $dealers = $this->db->get('tbl_dealers')->result();
        
        if(empty($dealers)) {
            return array();
        }
        
        $dealer_ids = array();
        foreach($dealers as $dealer) {
            $dealer_ids[] = $dealer->id;
        }
        
        // Get users where dealer_id is in the list
        $this->db->where_in('dealer_id', $dealer_ids);
        $this->db->where('status', 1);
        $this->db->order_by('id', 'DESC');
        return $this->db->get('tbl_users')->result();
    }

    /**
     * Count users under a specific dealer
     */
    public function count_users_under_dealer($dealer_id) {
        $this->db->where('dealer_id', $dealer_id);
        $this->db->where('status', 1);
        return $this->db->count_all_results('tbl_users');
    }

    /**
     * Get distributor total commission
     */
    public function get_distributor_commission($distributor_id) {
        $this->db->select_sum('amount');
        $this->db->where('distributor_id', $distributor_id);
        $this->db->where('commission_type', 'distributor');
        $result = $this->db->get('tbl_commission_history')->row();
        return floatval($result->amount ?? 0);
    }

    /**
     * Get distributor today's commission
     */
    public function get_distributor_commission_today($distributor_id) {
        $this->db->select_sum('amount');
        $this->db->where('distributor_id', $distributor_id);
        $this->db->where('commission_type', 'distributor');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $result = $this->db->get('tbl_commission_history')->row();
        return floatval($result->amount ?? 0);
    }

    /**
     * Get distributor monthly commission
     */
    public function get_distributor_commission_monthly($distributor_id) {
        $this->db->select_sum('amount');
        $this->db->where('distributor_id', $distributor_id);
        $this->db->where('commission_type', 'distributor');
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $result = $this->db->get('tbl_commission_history')->row();
        return floatval($result->amount ?? 0);
    }

    /**
     * Get distributor commission details with pagination
     */
    public function get_distributor_commission_details($distributor_id, $limit = null, $offset = 0) {
        $this->db->select('ch.*, u.name as user_name, d.name as dealer_name');
        $this->db->from('tbl_commission_history ch');
        $this->db->join('tbl_users u', 'u.id = ch.source_user_id', 'left');
        $this->db->join('tbl_dealers d', 'd.id = ch.dealer_id', 'left');
        $this->db->where('ch.distributor_id', $distributor_id);
        $this->db->where('ch.commission_type', 'distributor');
        $this->db->order_by('ch.created_at', 'DESC');
        
        if($limit) {
            $this->db->limit($limit, $offset);
        }
        
        return $this->db->get()->result();
    }

    /**
     * Get distributor transactions history
     */
    public function get_distributor_transactions($distributor_id, $limit = 10) {
        $this->db->select('wt.*, d.name as dealer_name');
        $this->db->from('tbl_wallet_transfers wt');
        $this->db->join('tbl_dealers d', 'd.id = wt.to_id', 'left');
        $this->db->where('wt.from_type', 'distributor');
        $this->db->where('wt.from_id', $distributor_id);
        $this->db->where('wt.transfer_type', 'wallet_transfer');
        $this->db->order_by('wt.created_at', 'DESC');
        $this->db->limit($limit);
        return $this->db->get()->result();
    }

    /**
     * Get distributor reports with filters
     */
    public function get_distributor_reports($distributor_id, $from_date = null, $to_date = null, $dealer_id = null) {
        $this->db->select('
            ch.*, 
            u.name as user_name, 
            u.phone as user_phone,
            d.name as dealer_name,
            d.phone as dealer_phone
        ');
        $this->db->from('tbl_commission_history ch');
        $this->db->join('tbl_users u', 'u.id = ch.source_user_id', 'left');
        $this->db->join('tbl_dealers d', 'd.id = ch.dealer_id', 'left');
        $this->db->where('ch.distributor_id', $distributor_id);
        
        if($dealer_id) {
            $this->db->where('ch.dealer_id', $dealer_id);
        }
        
        if($from_date) {
            $this->db->where('DATE(ch.created_at) >=', $from_date);
        }
        
        if($to_date) {
            $this->db->where('DATE(ch.created_at) <=', $to_date);
        }
        
        $this->db->order_by('ch.created_at', 'DESC');
        return $this->db->get()->result();
    }

    /**
     * Update distributor wallet
     */
    public function update_distributor_wallet($distributor_id, $amount, $transaction_type) {
        $distributor = $this->get_distributor($distributor_id);
        if(!$distributor) {
            return ['status' => false, 'message' => 'Distributor not found'];
        }
        
        if($transaction_type == 'debit') {
            if($distributor->wallet < $amount) {
                return ['status' => false, 'message' => 'Insufficient balance'];
            }
            $new_balance = $distributor->wallet - $amount;
        } else {
            $new_balance = $distributor->wallet + $amount;
        }
        
        $this->db->where('id', $distributor_id);
        $result = $this->db->update('tbl_distributors', ['wallet' => $new_balance]);
        
        if($result) {
            // Log the transaction
            $log_data = [
                'distributor_id' => $distributor_id,
                'amount' => $amount,
                'type' => $transaction_type,
                'balance_before' => $distributor->wallet,
                'balance_after' => $new_balance,
                'created_at' => date('Y-m-d H:i:s')
            ];
            $this->db->insert('tbl_distributor_wallet_logs', $log_data);
            
            return ['status' => true, 'message' => 'Wallet updated successfully', 'new_balance' => $new_balance];
        }
        
        return ['status' => false, 'message' => 'Failed to update wallet'];
    }

    /**
     * Update dealer wallet (with distributor validation)
     */
    public function update_dealer_wallet_by_distributor($dealer_id, $distributor_id, $amount, $transaction_type) {
        // Verify dealer belongs to this distributor
        $dealer = $this->db->get_where('tbl_dealers', [
            'id' => $dealer_id, 
            'distributor_id' => $distributor_id
        ])->row();
        
        if(!$dealer) {
            return ['status' => false, 'message' => 'Dealer not found or unauthorized'];
        }
        
        // Get distributor wallet
        $distributor = $this->get_distributor($distributor_id);
        
        if($transaction_type == 'debit') {
            if($dealer->wallet < $amount) {
                return ['status' => false, 'message' => 'Insufficient dealer balance'];
            }
            $new_dealer_balance = $dealer->wallet - $amount;
        } else {
            if($distributor->wallet < $amount) {
                return ['status' => false, 'message' => 'Insufficient distributor balance'];
            }
            $new_dealer_balance = $dealer->wallet + $amount;
            
            // Deduct from distributor wallet
            $new_distributor_balance = $distributor->wallet - $amount;
            $this->db->where('id', $distributor_id);
            $this->db->update('tbl_distributors', ['wallet' => $new_distributor_balance]);
        }
        
        // Update dealer wallet
        $this->db->where('id', $dealer_id);
        $result = $this->db->update('tbl_dealers', ['wallet' => $new_dealer_balance]);
        
        if($result) {
            return ['status' => true, 'message' => 'Wallet updated successfully'];
        }
        
        return ['status' => false, 'message' => 'Failed to update wallet'];
    }
  /**
 * Get user game betting history from all games
 * Based on actual table structures
 */
public function get_user_game_history($user_id, $limit = 50, $offset = 0) {
    $all_bets = [];
    
    // Game tables mapping with their specific column names
    $game_tables = [
        'tbl_funtarget_bet' => [
            'name' => 'Funtarget',
            'columns' => [
                'bet_id' => 'id',
                'period_id' => 'period_id',
                'bet_type' => NULL,  // No bet_type column
                'bet_number' => 'bet',
                'bet_amount' => 'amount',
                'win_amount' => 'winning_amount',
                'status' => 'status',
                'date' => 'date'
            ]
        ],
        'tbl_funtarget_v2_bet' => [
            'name' => 'Funtarget V2',
            'columns' => [
                'bet_id' => 'id',
                'period_id' => 'period_id',
                'bet_type' => 'bet_type',
                'bet_number' => 'bet',
                'bet_amount' => 'amount',
                'win_amount' => 'win_amount',
                'status' => 'status',
                'date' => 'date'
            ]
        ],
        'tbl_lucky36_bet' => [
            'name' => 'Lucky36',
            'columns' => [
                'bet_id' => 'id',
                'period_id' => 'period_id',
                'bet_type' => 'bet_type',
                'bet_number' => 'bet',
                'bet_amount' => 'amount',
                'win_amount' => 'win_amount',
                'status' => 'status',
                'date' => 'date'
            ]
        ],
        'tbl_lucky36_bet2' => [
            'name' => 'Lucky36 GME',
            'columns' => [
                'bet_id' => 'id',
                'period_id' => 'period_id',
                'bet_type' => 'bet_type',
                'bet_number' => 'bet',
                'bet_amount' => 'amount',
                'win_amount' => 'win_amount',
                'status' => 'status',
                'date' => 'date'
            ]
        ]
    ];
    
    foreach($game_tables as $table => $config) {
        // Check if table exists
        if($this->db->table_exists($table)) {
            $columns = $config['columns'];
            
            // Build select query
            $select = "'{$config['name']}' as game_name, ";
            $select .= "{$table}.{$columns['bet_id']} as bet_id, ";
            $select .= "{$table}.userid, ";
            
            // Period ID
            if($columns['period_id']) {
                $select .= "{$table}.{$columns['period_id']} as period_id, ";
            } else {
                $select .= "NULL as period_id, ";
            }
            
            // Bet Type (if exists)
            if($columns['bet_type']) {
                $select .= "{$table}.{$columns['bet_type']} as bet_type, ";
            } else {
                $select .= "NULL as bet_type, ";
            }
            
            // Bet Number
            $select .= "{$table}.{$columns['bet_number']} as bet_number, ";
            
            // Bet Amount
            $select .= "{$table}.{$columns['bet_amount']} as bet_amount, ";
            
            // Win Amount
            $select .= "{$table}.{$columns['win_amount']} as win_amount, ";
            
            // Status
            $select .= "{$table}.{$columns['status']} as status, ";
            
            // Date
            $select .= "{$table}.{$columns['date']} as created_at, ";
            
            // Status case for display
            $select .= "CASE 
                WHEN {$table}.{$columns['status']} = 'win' THEN 'Win'
                WHEN {$table}.{$columns['status']} = 'loss' THEN 'Loss'
                WHEN {$table}.{$columns['status']} = 'cancelled' THEN 'Cancelled'
                ELSE 'Pending'
            END as result_status";
            
            $this->db->select($select, FALSE);
            $this->db->where('userid', $user_id);
            $this->db->order_by($columns['date'], 'DESC');
            $this->db->limit($limit, $offset);
            
            $result = $this->db->get($table)->result();
            
            if(!empty($result)) {
                $all_bets = array_merge($all_bets, $result);
            }
        }
    }
    
    // Sort all bets by date (newest first)
    usort($all_bets, function($a, $b) {
        return strtotime($b->created_at) - strtotime($a->created_at);
    });
    
    return array_slice($all_bets, 0, $limit);
}

/**
 * Get user game history summary (stats)
 */
public function get_user_game_summary($user_id) {
    $summary = [
        'total_bets' => 0,
        'total_bet_amount' => 0,
        'total_win_amount' => 0,
        'total_loss_amount' => 0,
        'win_rate' => 0,
        'game_wise' => []
    ];
    
    $game_tables = [
        'tbl_funtarget_bet' => 'Funtarget',
        'tbl_lucky36_bet' => 'Lucky36',
        'tbl_lucky36_bet2' => 'Lucky36 GME',
    ];
    
    foreach($game_tables as $table => $game_name) {
        if($this->db->table_exists($table)) {
            // Get column names for this table
            $columns = $this->db->field_data($table);
            $col_names = array_column($columns, 'name');
            
            // Determine win amount column name
            $win_column = in_array('winning_amount', $col_names) ? 'winning_amount' : 'win_amount';
            
            // Count total bets
            $total_bets = $this->db->where('userid', $user_id)->count_all_results($table);
            
            // Sum bet amounts
            $this->db->select_sum('amount');
            $this->db->where('userid', $user_id);
            $total_bet = $this->db->get($table)->row();
            
            // Sum win amounts
            $this->db->select_sum($win_column);
            $this->db->where('userid', $user_id);
            $this->db->where('status', 'win');
            $total_win = $this->db->get($table)->row();
            
            $bet_amount = floatval($total_bet->amount ?? 0);
            $win_amount = floatval($total_win->{$win_column} ?? 0);
            
            $summary['total_bets'] += $total_bets;
            $summary['total_bet_amount'] += $bet_amount;
            $summary['total_win_amount'] += $win_amount;
            
            if($total_bets > 0) {
                $summary['game_wise'][$game_name] = [
                    'bets' => $total_bets,
                    'bet_amount' => $bet_amount,
                    'win_amount' => $win_amount
                ];
            }
        }
    }
    
    $summary['total_loss_amount'] = $summary['total_bet_amount'] - $summary['total_win_amount'];
    
    if($summary['total_bet_amount'] > 0) {
        $summary['win_rate'] = round(($summary['total_win_amount'] / $summary['total_bet_amount']) * 100, 2);
    }
    
    return $summary;
}
    
}
