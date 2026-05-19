<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard_model extends CI_Model {

    private function normalizeStatusValue($status)
    {
        $status = strtolower(trim($status));
        switch ($status) {
            case 'proccessing':
            case 'processing':
            case 'pending':
                return 'Pending';
            case 'won':
            case 'win':
            case 'success':
                return 'Won';
            case 'loss':
            case 'lost':
                return 'Lost';
            case 'cancelled':
            case 'canceled':
                return 'Cancelled';
            default:
                return ucfirst($status);
        }
    }

    private function normalizeStatusSql()
    {
        return "CASE LOWER(TRIM(IFNULL(status, ''))) " .
               "WHEN 'proccessing' THEN 'Pending' " .
               "WHEN 'processing' THEN 'Pending' " .
               "WHEN 'pending' THEN 'Pending' " .
               "WHEN 'won' THEN 'Won' " .
               "WHEN 'win' THEN 'Won' " .
               "WHEN 'success' THEN 'Won' " .
               "WHEN 'loss' THEN 'Lost' " .
               "WHEN 'lost' THEN 'Lost' " .
               "WHEN 'cancelled' THEN 'Cancelled' " .
               "WHEN 'canceled' THEN 'Cancelled' " .
               "ELSE CONCAT(UPPER(LEFT(status,1)), LOWER(SUBSTRING(status,2))) END";
    }

    private function getUserBetUnion($userid)
    {
        $userid = intval($userid);
        $statusExpr = $this->normalizeStatusSql();

        $queries = [];

        // 1. Funtarget Game (game_id = 1)
        $queries[] = "SELECT userid, 1 AS game_id, 'Funtarget' AS game_name, period_id, 'Funtarget' AS bet_type, CAST(IFNULL(bet, '') AS CHAR) AS bet_value, IFNULL(amount, 0) AS bet_amount, IFNULL(winning_amount, 0) AS win_amount, '' AS win_number, {$statusExpr} AS status, IFNULL(date, NOW()) AS date FROM tbl_funtarget_bet WHERE userid = {$userid}";

        // 2. Lucky36 Game (game_id = 2)
        $queries[] = "SELECT userid, 2 AS game_id, 'Lucky36' AS game_name, period_id, IFNULL(bet_type, '') AS bet_type, CAST(IFNULL(bet, '') AS CHAR) AS bet_value, IFNULL(amount, 0) AS bet_amount, 0 AS win_amount, '' AS win_number, {$statusExpr} AS status, IFNULL(date, NOW()) AS date FROM tbl_lucky36_bet WHERE userid = {$userid}";

        // 3. Lucky36gme Game (game_id = 3)
        $queries[] = "SELECT userid, 3 AS game_id, 'Lucky36gme' AS game_name, period_id, IFNULL(bet_type, '') AS bet_type, CAST(IFNULL(bet, '') AS CHAR) AS bet_value, IFNULL(amount, 0) AS bet_amount, 0 AS win_amount, '' AS win_number, {$statusExpr} AS status, IFNULL(date, NOW()) AS date FROM tbl_lucky36_bet2 WHERE userid = {$userid}";

        if (empty($queries)) {
            return "(SELECT NULL as userid, NULL as game_id, NULL as game_name, NULL as period_id, NULL as bet_type, NULL as bet_value, 0 as bet_amount, 0 as win_amount, NULL as win_number, NULL as status, NULL as date LIMIT 0)";
        }

        return '(' . implode(') UNION ALL (', $queries) . ')';
    }

    private function buildHistoryFilters($filters)
    {
        $clauses = [];
        
        // Filter by game_id
        if (!empty($filters['game_id'])) {
            $clauses[] = "game_id = " . intval($filters['game_id']);
        } elseif (!empty($filters['game'])) {
            $gameName = $filters['game'];
            if ($gameName == 'Funtarget' || $gameName == '1') {
                $clauses[] = "game_id = 1";
            } elseif ($gameName == 'Lucky36' || $gameName == '2') {
                $clauses[] = "game_id = 2";
            } elseif ($gameName == 'Lucky36gme' || $gameName == '3') {
                $clauses[] = "game_id = 3";
            }
        }
        
        if (!empty($filters['status'])) {
            $normalized = $this->normalizeStatusValue($filters['status']);
            $clauses[] = "status = " . $this->db->escape($normalized);
        }
        if (!empty($filters['period_id'])) {
            $period = $this->db->escape_like_str($filters['period_id']);
            $clauses[] = "CONCAT('', IFNULL(period_id, '')) LIKE " . $this->db->escape('%' . $period . '%');
        }
        if (!empty($filters['start_date'])) {
            $clauses[] = "DATE(date) >= " . $this->db->escape($filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $clauses[] = "DATE(date) <= " . $this->db->escape($filters['end_date']);
        }
        return implode(' AND ', $clauses);
    }

    public function getWalletDetails($userid)
    {
        $query = $this->db->select('id, name, phone, email, wallet, winning_wallet, freeze_wallet')
                          ->from('tbl_users')
                          ->where('id', intval($userid))
                          ->limit(1)
                          ->get();

        $result = $query->row_array();
        
        if ($result) {
            $result['username'] = $result['name'];
        }
        
        return $result;
    }

    public function getUserTotalBetsAmount($userid)
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $sql = "SELECT IFNULL(SUM(bet_amount), 0) AS total_amount FROM ({$union}) AS bets";
            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'getUserTotalBetsAmount Error: ' . $this->db->error()['message']);
                return 0;
            }
            $row = $query->row();
            return floatval($row->total_amount ?? 0);
        } catch (Exception $e) {
            log_message('error', 'getUserTotalBetsAmount Exception: ' . $e->getMessage());
            return 0;
        }
    }

    public function getUserTotalWinningAmount($userid)
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $sql = "SELECT IFNULL(SUM(win_amount), 0) AS total_win_amount FROM ({$union}) AS bets";
            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'getUserTotalWinningAmount Error: ' . $this->db->error()['message']);
                return 0;
            }
            $row = $query->row();
            return floatval($row->total_win_amount ?? 0);
        } catch (Exception $e) {
            log_message('error', 'getUserTotalWinningAmount Exception: ' . $e->getMessage());
            return 0;
        }
    }

    public function countUserHistory($userid, $filters = [])
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $where = $this->buildHistoryFilters($filters);
            $sql = "SELECT COUNT(*) AS total FROM ({$union}) AS bets";
            if ($where !== '') {
                $sql .= " WHERE {$where}";
            }
            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'countUserHistory Error: ' . $this->db->error()['message']);
                return 0;
            }
            $row = $query->row();
            return intval($row->total ?? 0);
        } catch (Exception $e) {
            log_message('error', 'countUserHistory Exception: ' . $e->getMessage());
            return 0;
        }
    }

    public function getMostPlayedGame($userid)
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $sql = "SELECT game_name, COUNT(*) AS count_rows FROM ({$union}) AS bets GROUP BY game_name ORDER BY count_rows DESC LIMIT 1";
            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'getMostPlayedGame Error: ' . $this->db->error()['message']);
                return null;
            }
            return $query->row();
        } catch (Exception $e) {
            log_message('error', 'getMostPlayedGame Exception: ' . $e->getMessage());
            return null;
        }
    }

    public function getGameSummaryCounts($userid)
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $sql = "SELECT status, COUNT(*) AS count_items, IFNULL(SUM(bet_amount), 0) AS total_amount FROM ({$union}) AS bets GROUP BY status";
            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'getGameSummaryCounts Error: ' . $this->db->error()['message']);
                return [];
            }
            return $query->result();
        } catch (Exception $e) {
            log_message('error', 'getGameSummaryCounts Exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getUserHistory($userid, $filters, $limit, $offset)
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $where = $this->buildHistoryFilters($filters);

            $sql = "SELECT game_id, game_name, period_id, bet_type, bet_value, bet_amount, win_amount, win_number, status, date FROM ({$union}) AS bets";
            if ($where !== '') {
                $sql .= " WHERE {$where}";
            }
            $sql .= " ORDER BY date DESC LIMIT " . intval($offset) . ", " . intval($limit);

            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'getUserHistory Error: ' . $this->db->error()['message']);
                return [];
            }
            return $query->result();
        } catch (Exception $e) {
            log_message('error', 'getUserHistory Exception: ' . $e->getMessage());
            return [];
        }
    }

    public function getRecentBets($userid, $limit = 10)
    {
        try {
            $union = $this->getUserBetUnion($userid);
            $sql = "SELECT game_id, game_name, period_id, bet_type, bet_value, bet_amount, win_amount, win_number, status, date FROM ({$union}) AS bets ORDER BY date DESC LIMIT " . intval($limit);
            $query = $this->db->query($sql);
            if ($query === false) {
                log_message('error', 'getRecentBets Error: ' . $this->db->error()['message']);
                return [];
            }
            return $query->result();
        } catch (Exception $e) {
            log_message('error', 'getRecentBets Exception: ' . $e->getMessage());
            return [];
        }
    }
}
?>