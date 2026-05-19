<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        
        // Check if model file exists
        $model_path = APPPATH . 'models/Dashboard_model.php';
        if (!file_exists($model_path)) {
            die('Model file not found at: ' . $model_path);
        }
        
        $this->load->model('Dashboard_model');
        
        // Check if model loaded successfully
        if (!class_exists('Dashboard_model')) {
            die('Dashboard_model class not found');
        }
    }

    public function test()
    {
        $response = [
            'status' => 'success',
            'message' => 'API is working',
            'model_exists' => file_exists(APPPATH . 'models/Dashboard_model.php'),
            'model_loaded' => class_exists('Dashboard_model'),
            'db_connected' => $this->db->conn_id ? true : false
        ];
        
        $this->outputJson($response);
    }

    private function outputJson($response)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function wallet_details()
    {
        $userid = intval($this->input->post('userid'));
        if ($userid <= 0) {
            return $this->outputJson(['status' => 'error', 'message' => 'User ID is required']);
        }

        $details = $this->Dashboard_model->getWalletDetails($userid);
        if (!$details) {
            return $this->outputJson(['status' => 'error', 'message' => 'Invalid User ID']);
        }

        $response = [
            'status' => 'success',
            'wallet' => $details
        ];
        $this->outputJson($response);
    }

    public function dashboard_summary()
    {
        try {
            $userid = intval($this->input->post('userid'));
            if ($userid <= 0) {
                return $this->outputJson(['status' => 'error', 'message' => 'User ID is required']);
            }

            $wallet = $this->Dashboard_model->getWalletDetails($userid);
            if (!$wallet) {
                return $this->outputJson(['status' => 'error', 'message' => 'Invalid User ID']);
            }
        } catch (Exception $e) {
            log_message('error', 'Dashboard Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }

        $filters = [
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'status' => $this->input->post('status'),
            'game_id' => $this->input->post('game_id'),
            'period_id' => $this->input->post('period_id'),
        ];

        try {
            $totalBetAmount = $this->Dashboard_model->getUserTotalBetsAmount($userid);
            $historyCount = $this->Dashboard_model->countUserHistory($userid, $filters);
            $playedGame = $this->Dashboard_model->getMostPlayedGame($userid);
            $statusRows = $this->Dashboard_model->getGameSummaryCounts($userid);
        } catch (Exception $e) {
            log_message('error', 'Dashboard Query Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Failed to load dashboard data: ' . $e->getMessage()]);
        }

        $totals = [
            'Total Bets Amount' => 0,
            'Total Win Bets' => 0,
            'Total Lost Bets' => 0,
            'Total Pending Bets' => 0,
            'Total Cancelled Bets' => 0,
            'Total Win Amount' => 0,
        ];

        foreach ($statusRows as $row) {
            $key = $row->status;
            if ($key === 'Won') {
                $totals['Total Win Bets'] = intval($row->count_items);
                $totals['Total Win Amount'] += floatval($row->total_amount);
            } elseif ($key === 'Lost') {
                $totals['Total Lost Bets'] = intval($row->count_items);
            } elseif ($key === 'Pending') {
                $totals['Total Pending Bets'] = intval($row->count_items);
            } elseif ($key === 'Cancelled') {
                $totals['Total Cancelled Bets'] = intval($row->count_items);
            }
        }

        $response = [
            'status' => 'success',
            'profile' => [
                'user_id' => $wallet['id'],
                'username' => $wallet['username'],
                'name' => $wallet['name'],
                'phone' => $wallet['phone'],
            ],
            'wallet' => [
                'main_wallet' => $wallet['wallet'],
                'winning_wallet' => $wallet['winning_wallet'],
                'freeze_wallet' => $wallet['freeze_wallet'],
                'bonus' => isset($wallet['bonus']) ? $wallet['bonus'] : null,
            ],
            'summary' => [
                'total_bets_amount' => floatval($totalBetAmount),
                'total_games_played' => intval($historyCount),
                'total_winning_amount' => floatval($this->Dashboard_model->getUserTotalWinningAmount($userid)),
                'total_win_bets' => intval($totals['Total Win Bets']),
                'total_lost_bets' => intval($totals['Total Lost Bets']),
                'total_pending_bets' => intval($totals['Total Pending Bets']),
                'total_cancelled_bets' => intval($totals['Total Cancelled Bets']),
                'profit_loss' => floatval($this->Dashboard_model->getUserTotalWinningAmount($userid) - $totalBetAmount),
                'most_played_game' => $playedGame ? $playedGame->game_name : null,
            ],
        ];

        $this->outputJson($response);
    }

    public function game_history()
    {
        try {
            $userid = intval($this->input->post('userid'));
            if ($userid <= 0) {
                return $this->outputJson(['status' => 'error', 'message' => 'User ID is required']);
            }
        } catch (Exception $e) {
            log_message('error', 'Game History Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }

        $page = max(1, intval($this->input->post('page')));
        $limit = max(1, min(100, intval($this->input->post('limit')) ?: 20));
        $offset = ($page - 1) * $limit;

        $filters = [
            'game_id' => $this->input->post('game_id'),
            'status' => $this->input->post('status'),
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'period_id' => $this->input->post('period_id') ?: $this->input->post('search'),
        ];

        try {
            $history = $this->Dashboard_model->getUserHistory($userid, $filters, $limit, $offset);
            $total = $this->Dashboard_model->countUserHistory($userid, $filters);
        } catch (Exception $e) {
            log_message('error', 'Game History Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Failed to load history: ' . $e->getMessage()]);
        }

        $response = [
            'status' => 'success',
            'page' => $page,
            'limit' => $limit,
            'total_records' => intval($total),
            'history' => $history,
        ];
        $this->outputJson($response);
    }

    public function bet_statistics()
    {
        try {
            $userid = intval($this->input->post('userid'));
            if ($userid <= 0) {
                return $this->outputJson(['status' => 'error', 'message' => 'User ID is required']);
            }
        } catch (Exception $e) {
            log_message('error', 'Bet Statistics Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }

        try {
            $statusRows = $this->Dashboard_model->getGameSummaryCounts($userid);
        } catch (Exception $e) {
            log_message('error', 'Bet Statistics Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Failed to load statistics: ' . $e->getMessage()]);
        }
        
        $counts = [
            'win_bets' => 0,
            'lost_bets' => 0,
            'pending_bets' => 0,
            'cancelled_bets' => 0,
            'total_bet_amount' => 0,
        ];

        foreach ($statusRows as $row) {
            if ($row->status === 'Won') {
                $counts['win_bets'] = intval($row->count_items);
            } elseif ($row->status === 'Lost') {
                $counts['lost_bets'] = intval($row->count_items);
            } elseif ($row->status === 'Pending') {
                $counts['pending_bets'] = intval($row->count_items);
            } elseif ($row->status === 'Cancelled') {
                $counts['cancelled_bets'] = intval($row->count_items);
            }
            $counts['total_bet_amount'] += floatval($row->total_amount);
        }

        $response = [
            'status' => 'success',
            'statistics' => $counts,
        ];
        $this->outputJson($response);
    }

    public function recent_bets()
    {
        try {
            $userid = intval($this->input->post('userid'));
            if ($userid <= 0) {
                return $this->outputJson(['status' => 'error', 'message' => 'User ID is required']);
            }
        } catch (Exception $e) {
            log_message('error', 'Recent Bets Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Server error: ' . $e->getMessage()]);
        }

        try {
            $recent = $this->Dashboard_model->getRecentBets($userid, 10);
        } catch (Exception $e) {
            log_message('error', 'Recent Bets Error: ' . $e->getMessage());
            return $this->outputJson(['status' => 'error', 'message' => 'Failed to load recent bets: ' . $e->getMessage()]);
        }
        
        $response = [
            'status' => 'success',
            'recent_bets' => $recent,
        ];
        $this->outputJson($response);
    }
}
?>