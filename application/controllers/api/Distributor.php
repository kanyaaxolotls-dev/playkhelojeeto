<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Distributor extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('db_model');
        $this->load->model('Hierarchy_model');
    }

    private function send_response($response) {
        return $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function register() {
        $name = trim($this->input->post('name'));
        $phone = trim($this->input->post('phone'));
        $password = trim($this->input->post('password'));
        $admin_id = intval($this->input->post('admin_id'));

        if (!$name || !$phone || !$password || !$admin_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required fields.']);
        }

        if ($this->db_model->count_all('tbl_distributors', ['phone' => $phone]) > 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor phone already exists.']);
        }

        if ($this->db_model->count_all('tbl_admin', ['id' => $admin_id]) == 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Invalid admin ID.']);
        }

        $data = [
            'name' => $name,
            'phone' => $phone,
            'password' => $password,
            'admin_id' => $admin_id,
            'wallet' => 0,
            'status' => 1,
            'commission_rate' => floatval($this->input->post('commission_rate') ?? 0.5),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbl_distributors', $data);
        return $this->send_response(['status' => 'success', 'message' => 'Distributor created successfully.', 'data' => $data]);
    }

    public function login() {
        $phone = trim($this->input->post('phone'));
        $password = trim($this->input->post('password'));

        if (!$phone || !$password) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing phone or password.']);
        }

        $distributor = $this->db_model->select_multi('*', 'tbl_distributors', ['phone' => $phone]);
        if (!$distributor) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor not found.']);
        }

        if ($distributor->password !== $password) {
            return $this->send_response(['status' => 'error', 'message' => 'Incorrect password.']);
        }

        if ($distributor->status != 1) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor is inactive.']);
        }

        $session = $this->db_model->generate_random_string(30);
        $this->db->where('id', $distributor->id)->update('tbl_distributors', ['login_session' => $session]);
        $distributor->login_session = $session;

        return $this->send_response(['status' => 'success', 'message' => 'Login successful.', 'data' => $distributor]);
    }

    public function dashboard() {
        $distributor_id = intval($this->input->post('distributor_id'));
        if (!$distributor_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor ID required.']);
        }

        $distributor = $this->Hierarchy_model->get_distributor($distributor_id);
        if (!$distributor) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor not found.']);
        }

        $dealer_count = $this->db_model->count_all('tbl_dealers', ['distributor_id' => $distributor_id, 'status' => 1]);
        $user_count = $this->db_model->count_all('tbl_users', ['distributor_id' => $distributor_id, 'status' => 1]);
        $commission_total = $this->Hierarchy_model->get_commission_summary(['distributor_id' => $distributor_id]);
        $commission_today = $this->Hierarchy_model->get_commission_summary(['distributor_id' => $distributor_id, 'commission_type' => 'distributor', 'DATE(created_at)' => date('Y-m-d')]);
        $commission_month = $this->Hierarchy_model->get_commission_summary(['distributor_id' => $distributor_id, 'commission_type' => 'distributor', 'MONTH(created_at)' => date('m'), 'YEAR(created_at)' => date('Y')]);

        return $this->send_response([
            'status' => 'success',
            'dashboard' => [
                'distributor' => $distributor,
                'dealer_count' => $dealer_count,
                'user_count' => $user_count,
                'wallet' => floatval($distributor->wallet),
                'commission_total' => $commission_total['total'],
                'commission_today' => $commission_today['total'],
                'commission_month' => $commission_month['total'],
            ]
        ]);
    }

    public function create_dealer() {
        $distributor_id = intval($this->input->post('distributor_id'));
        $name = trim($this->input->post('name'));
        $phone = trim($this->input->post('phone'));
        $password = trim($this->input->post('password'));

        if (!$distributor_id || !$name || !$phone || !$password) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required fields.']);
        }

        $distributor = $this->Hierarchy_model->get_distributor($distributor_id);
        if (!$distributor) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor not found.']);
        }

        $exists = $this->db_model->count_all('tbl_dealers', ['phone' => $phone]);
        if ($exists > 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer phone already exists.']);
        }

        $data = [
            'distributor_id' => $distributor_id,
            'name' => $name,
            'phone' => $phone,
            'password' => $password,
            'wallet' => 0,
            'status' => 1,
            'commission_rate' => floatval($this->input->post('commission_rate') ?? 2.0),
            'created_at' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbl_dealers', $data);
        return $this->send_response(['status' => 'success', 'message' => 'Dealer created successfully.', 'data' => $data]);
    }

    public function dealer_list() {
        $distributor_id = intval($this->input->post('distributor_id'));
        if (!$distributor_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor ID required.']);
        }

        $dealers = $this->Hierarchy_model->get_my_dealers($distributor_id);
        return $this->send_response(['status' => 'success', 'data' => $dealers]);
    }

    public function user_list() {
        $distributor_id = intval($this->input->post('distributor_id'));
        if (!$distributor_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor ID required.']);
        }

        $users = $this->Hierarchy_model->get_my_users_for_distributor($distributor_id);
        return $this->send_response(['status' => 'success', 'data' => $users]);
    }

    public function commission_history() {
        $distributor_id = intval($this->input->post('distributor_id'));
        if (!$distributor_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor ID required.']);
        }

        $history = $this->Hierarchy_model->get_commission_history(['distributor_id' => $distributor_id]);
        return $this->send_response(['status' => 'success', 'data' => $history]);
    }

    public function transfer_wallet() {
        $distributor_id = intval($this->input->post('distributor_id'));
        $dealer_id = intval($this->input->post('dealer_id'));
        $amount = floatval($this->input->post('amount'));

        if (!$distributor_id || !$dealer_id || $amount <= 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required transfer details.']);
        }

        $result = $this->Hierarchy_model->transfer_between_entities('distributor', $distributor_id, 'dealer', $dealer_id, $amount, 'distributor_to_dealer', 'Distributor funds assigned to dealer');
        return $this->send_response($result);
    }

    public function assign_wallet() {
        $admin_id = intval($this->input->post('admin_id'));
        $distributor_id = intval($this->input->post('distributor_id'));
        $amount = floatval($this->input->post('amount'));

        if (!$admin_id || !$distributor_id || $amount <= 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required assignment details.']);
        }

        $result = $this->Hierarchy_model->transfer_between_entities('admin', $admin_id, 'distributor', $distributor_id, $amount, 'admin_to_distributor', 'Admin funds assigned to distributor');
        return $this->send_response($result);
    }

    public function game_report() {
        $distributor_id = intval($this->input->post('distributor_id'));
        $game_type = trim($this->input->post('game_type'));
        $start_date = trim($this->input->post('start_date')) ?: null;
        $end_date = trim($this->input->post('end_date')) ?: null;

        if (!$distributor_id || !$game_type) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor ID and game type required.']);
        }

        $filters = ['distributor_id' => $distributor_id];
        $report = $this->Hierarchy_model->get_game_report($game_type, $start_date, $end_date, $filters);
        return $this->send_response(['status' => 'success', 'data' => $report]);
    }
}
