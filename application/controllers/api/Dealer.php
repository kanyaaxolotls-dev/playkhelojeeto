<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dealer extends CI_Controller {

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
        $distributor_id = intval($this->input->post('distributor_id'));

        if (!$name || !$phone || !$password || !$distributor_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required fields.']);
        }

        if ($this->db_model->count_all('tbl_dealers', ['phone' => $phone]) > 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer phone already exists.']);
        }

        $distributor = $this->Hierarchy_model->get_distributor($distributor_id);
        if (!$distributor) {
            return $this->send_response(['status' => 'error', 'message' => 'Distributor not found.']);
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

    public function login() {
        $phone = trim($this->input->post('phone'));
        $password = trim($this->input->post('password'));

        if (!$phone || !$password) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing phone or password.']);
        }

        $dealer = $this->db_model->select_multi('*', 'tbl_dealers', ['phone' => $phone]);
        if (!$dealer) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer not found.']);
        }

        if ($dealer->password !== $password) {
            return $this->send_response(['status' => 'error', 'message' => 'Incorrect password.']);
        }

        if ($dealer->status != 1) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer is inactive.']);
        }

        $session = $this->db_model->generate_random_string(30);
        $this->db->where('id', $dealer->id)->update('tbl_dealers', ['login_session' => $session]);
        $dealer->login_session = $session;

        return $this->send_response(['status' => 'success', 'message' => 'Login successful.', 'data' => $dealer]);
    }

    public function dashboard() {
        $dealer_id = intval($this->input->post('dealer_id'));
        if (!$dealer_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer ID required.']);
        }

        $dealer = $this->Hierarchy_model->get_dealer($dealer_id);
        if (!$dealer) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer not found.']);
        }

        $user_count = $this->db_model->count_all('tbl_users', ['dealer_id' => $dealer_id, 'status' => 1]);
        $commission_total = $this->Hierarchy_model->get_commission_summary(['dealer_id' => $dealer_id]);
        $commission_today = $this->Hierarchy_model->get_commission_summary(['dealer_id' => $dealer_id, 'commission_type' => 'dealer', 'DATE(created_at)' => date('Y-m-d')]);
        $commission_month = $this->Hierarchy_model->get_commission_summary(['dealer_id' => $dealer_id, 'commission_type' => 'dealer', 'MONTH(created_at)' => date('m'), 'YEAR(created_at)' => date('Y')]);

        return $this->send_response([
            'status' => 'success',
            'dashboard' => [
                'dealer' => $dealer,
                'user_count' => $user_count,
                'wallet' => floatval($dealer->wallet),
                'commission_total' => $commission_total['total'],
                'commission_today' => $commission_today['total'],
                'commission_month' => $commission_month['total'],
            ]
        ]);
    }

    public function create_user() {
        $dealer_id = intval($this->input->post('dealer_id'));
        $name = trim($this->input->post('name'));
        $phone = trim($this->input->post('phone'));
        $password = trim($this->input->post('password'));

        if (!$dealer_id || !$name || !$phone || !$password) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required fields.']);
        }

        $dealer = $this->Hierarchy_model->get_dealer($dealer_id);
        if (!$dealer) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer not found.']);
        }

        if ($this->db_model->count_all('tbl_users', ['phone' => $phone]) > 0) {
            return $this->send_response(['status' => 'error', 'message' => 'User phone already exists.']);
        }

        $data = [
            'name' => $name,
            'phone' => $phone,
            'password' => $password,
            'status' => 1,
            'dealer_id' => $dealer_id,
            'distributor_id' => $dealer->distributor_id,
            'usercode' => $this->db_model->generate_random_string(6),
            'date' => date('Y-m-d H:i:s'),
        ];

        $this->db->insert('tbl_users', $data);
        return $this->send_response(['status' => 'success', 'message' => 'User created successfully.', 'data' => $data]);
    }

    public function user_list() {
        $dealer_id = intval($this->input->post('dealer_id'));
        if (!$dealer_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer ID required.']);
        }

        $users = $this->Hierarchy_model->get_my_users_for_dealer($dealer_id);
        return $this->send_response(['status' => 'success', 'data' => $users]);
    }

    public function commission_history() {
        $dealer_id = intval($this->input->post('dealer_id'));
        if (!$dealer_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer ID required.']);
        }

        $history = $this->Hierarchy_model->get_commission_history(['dealer_id' => $dealer_id]);
        return $this->send_response(['status' => 'success', 'data' => $history]);
    }

    public function transfer_wallet() {
        $dealer_id = intval($this->input->post('dealer_id'));
        $userid = intval($this->input->post('userid'));
        $amount = floatval($this->input->post('amount'));

        if (!$dealer_id || !$userid || $amount <= 0) {
            return $this->send_response(['status' => 'error', 'message' => 'Missing required transfer details.']);
        }

        $result = $this->Hierarchy_model->transfer_between_entities('dealer', $dealer_id, 'user', $userid, $amount, 'dealer_to_user', 'Dealer funds assigned to user');
        return $this->send_response($result);
    }

    public function user_report() {
        $dealer_id = intval($this->input->post('dealer_id'));
        $user_id = intval($this->input->post('userid'));
        $game_type = trim($this->input->post('game_type')) ?: 'lucky36';
        $start_date = trim($this->input->post('start_date')) ?: null;
        $end_date = trim($this->input->post('end_date')) ?: null;

        if (!$dealer_id || !$user_id) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer ID and User ID required.']);
        }

        $dealer = $this->Hierarchy_model->get_dealer($dealer_id);
        if (!$dealer) {
            return $this->send_response(['status' => 'error', 'message' => 'Dealer not found.']);
        }

        $user = $this->Hierarchy_model->get_user($user_id);
        if (!$user || $user->dealer_id != $dealer_id) {
            return $this->send_response(['status' => 'error', 'message' => 'User not found under this dealer.']);
        }

        $table = $this->Hierarchy_model->get_bet_table($game_type);
        if (!$table) {
            return $this->send_response(['status' => 'error', 'message' => 'Unsupported game type.']);
        }

        $this->db->where('userid', $user_id);
        if ($start_date) {
            $this->db->where('DATE(date) >=', $start_date);
        }
        if ($end_date) {
            $this->db->where('DATE(date) <=', $end_date);
        }
        $bets = $this->db->get($table)->result();

        return $this->send_response(['status' => 'success', 'data' => $bets]);
    }
}
