<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Dealer_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Hierarchy_model');
        $this->load->model('db_model');
    }

    public function index() {
        $dealer_id = $this->session->userdata('dealer_id');
        
        // Get dealer data
        $data['dealer'] = $this->Hierarchy_model->get_dealer($dealer_id);
        
        // Get all users under this dealer
        $data['users'] = $this->Hierarchy_model->get_my_users_for_dealer($dealer_id);
        
        // Debug - check if users exist
        // echo "Users count: " . count($data['users']); die();
        
        // Statistics
        $data['total_users'] = count($data['users']);
        $data['total_commission'] = $this->get_dealer_commission($dealer_id);
        $data['today_commission'] = $this->get_dealer_commission_today($dealer_id);
        
        // Calculate total user wallet balance
        $total_wallet = 0;
        foreach($data['users'] as $user) {
            $total_wallet += $user->wallet;
        }
        $data['total_user_wallet'] = $total_wallet;
        $data['title'] = 'Dealer Dashboard';
        
        $this->load->view('dealer/header', $data);
        $this->load->view('dealer/dashboard', $data);
        $this->load->view('dealer/footer');
    }

    public function users() {
        $dealer_id = $this->session->userdata('dealer_id');
        
        $data['users'] = $this->Hierarchy_model->get_my_users_for_dealer($dealer_id);
        $data['total_users'] = count($data['users']);
        $data['dealer'] = $this->Hierarchy_model->get_dealer($dealer_id);
        $data['title'] = 'Manage Users';
        
        $this->load->view('dealer/header', $data);
        $this->load->view('dealer/users', $data);
        $this->load->view('dealer/footer');
    }

public function create_user() {
    if ($this->input->post('name')) {
        rbac_require('create_user');
        $dealer_id = $this->session->userdata('dealer_id');
        
        // Get distributor_id from dealer table, not from session
        $dealer = $this->Hierarchy_model->get_dealer($dealer_id);
        $distributor_id = $dealer->distributor_id ?? null;
        
        // Check if phone already exists
        $exists = $this->db_model->count_all('tbl_users', array('phone' => $this->input->post('phone')));
        
        if($exists > 0) {
            $this->session->set_flashdata('error', 'Phone number already exists!');
        } else {
            $data = array(
                'name' => $this->input->post('name'),
                'phone' => $this->input->post('phone'),
                'password' => $this->input->post('password'),
                'dealer_id' => $dealer_id,
                'distributor_id' => $distributor_id,
                'wallet' => 0,
                'winning_wallet' => 0,
                'status' => 1,
                'usercode' => $this->generate_usercode(),
                'date' => date('Y-m-d H:i:s')
            );
            
            // Debug - check if data is correct
            // print_r($data); die();
            
            $insert = $this->db->insert('tbl_users', $data);
            
            if($insert) {
                $this->session->set_flashdata('success', 'User created successfully!');
            } else {
                $this->session->set_flashdata('error', 'Failed to create user!');
            }
        }
        redirect(site_url('dealer/dashboard/users'));
    }
    
    $data['title'] = 'Create User';
    $this->load->view('dealer/header', $data);
    $this->load->view('dealer/create_user', $data);
    $this->load->view('dealer/footer');
}
    /*public function view_user($user_id) {
        $dealer_id = $this->session->userdata('dealer_id');
        
        // Get user details
        $user = $this->db->get_where('tbl_users', [
            'id' => $user_id,
            'dealer_id' => $dealer_id
        ])->row();
        
        if(!$user) {
            show_404();
        }
        
        $data['user'] = $user;
        $data['title'] = 'User Details - ' . $user->name;
        
        $this->load->view('dealer/header', $data);
        $this->load->view('dealer/view_user', $data);
        $this->load->view('dealer/footer');
    }*/

    public function update_user_wallet() {
        $transaction_type = $this->input->post('transaction_type');
        if ($transaction_type === 'debit') {
            rbac_require('wallet_debit');
        } else {
            rbac_require('wallet_credit');
        }
        $user_id = $this->input->post('user_id');
        $amount = $this->input->post('amount');
        $dealer_id = $this->session->userdata('dealer_id');
        
        // Verify user belongs to this dealer
        $user = $this->db->get_where('tbl_users', [
            'id' => $user_id,
            'dealer_id' => $dealer_id
        ])->row();
        
        if(!$user) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        // Get dealer wallet
        $dealer = $this->Hierarchy_model->get_dealer($dealer_id);
        
        if($transaction_type == 'debit') {
            if($user->wallet < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient user wallet balance']);
                return;
            }
            $new_user_wallet = $user->wallet - $amount;
        } else {
            if($dealer->wallet < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient dealer wallet balance']);
                return;
            }
            $new_user_wallet = $user->wallet + $amount;
            // Deduct from dealer wallet
            $new_dealer_wallet = $dealer->wallet - $amount;
            $this->db->where('id', $dealer_id);
            $this->db->update('tbl_dealers', array('wallet' => $new_dealer_wallet));
            
            // Update session wallet
            $this->session->set_userdata('dealer_wallet', $new_dealer_wallet);
        }
        
        // Update user wallet
        $this->db->where('id', $user_id);
        $result = $this->db->update('tbl_users', array('wallet' => $new_user_wallet));
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Wallet updated successfully', 'new_balance' => $new_user_wallet]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update wallet']);
        }
    }

    public function commission() {
        rbac_require('view_transaction_history');
        $dealer_id = $this->session->userdata('dealer_id');
        
        $data['commissions'] = $this->get_dealer_commission_details($dealer_id);
        $data['total_commission'] = $this->get_dealer_commission($dealer_id);
        $data['today_commission'] = $this->get_dealer_commission_today($dealer_id);
        $data['monthly_commission'] = $this->get_dealer_commission_monthly($dealer_id);
        $data['dealer'] = $this->Hierarchy_model->get_dealer($dealer_id);
        $data['title'] = 'Commission Report';
        
        $this->load->view('dealer/header', $data);
        $this->load->view('dealer/commission', $data);
        $this->load->view('dealer/footer');
    }

    // Private helper methods
    private function generate_usercode() {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $code;
    }

    private function get_dealer_commission($dealer_id) {
        $this->db->select_sum('amount');
        $this->db->where('dealer_id', $dealer_id);
        $this->db->where('commission_type', 'dealer');
        $result = $this->db->get('tbl_commission_history')->row();
        return floatval($result->amount ?? 0);
    }

    private function get_dealer_commission_today($dealer_id) {
        $this->db->select_sum('amount');
        $this->db->where('dealer_id', $dealer_id);
        $this->db->where('commission_type', 'dealer');
        $this->db->where('DATE(created_at)', date('Y-m-d'));
        $result = $this->db->get('tbl_commission_history')->row();
        return floatval($result->amount ?? 0);
    }

    private function get_dealer_commission_monthly($dealer_id) {
        $this->db->select_sum('amount');
        $this->db->where('dealer_id', $dealer_id);
        $this->db->where('commission_type', 'dealer');
        $this->db->where('MONTH(created_at)', date('m'));
        $this->db->where('YEAR(created_at)', date('Y'));
        $result = $this->db->get('tbl_commission_history')->row();
        return floatval($result->amount ?? 0);
    }

   /* private function get_dealer_commission_details($dealer_id) {
        $this->db->select('ch.*, u.name as user_name');
        $this->db->from('tbl_commission_history ch');
        $this->db->join('tbl_users u', 'u.id = ch.source_user_id', 'left');
        $this->db->where('ch.dealer_id', $dealer_id);
        $this->db->where('ch.commission_type', 'dealer');
        $this->db->order_by('ch.created_at', 'DESC');
        $this->db->limit(100);
        return $this->db->get()->result();
    }*/
    private function get_dealer_commission_details($dealer_id) {

    $this->db->select('
        ch.*, 
        u.name as user_name,
        g.name as game_name
    ');

    $this->db->from('tbl_commission_history ch');

    $this->db->join(
        'tbl_users u',
        'u.id = ch.source_user_id',
        'left'
    );

    $this->db->join(
        'tbl_games g',
        'g.id = ch.game_id',
        'left'
    );

    $this->db->where('ch.dealer_id', $dealer_id);
    $this->db->where('ch.commission_type', 'dealer');

    $this->db->order_by('ch.created_at', 'DESC');
    $this->db->limit(100);

    return $this->db->get()->result();
}
    
public function view_user($user_id) {
    $dealer_id = $this->session->userdata('dealer_id');
    
    // Get user details
    $user = $this->db->get_where('tbl_users', [
        'id' => $user_id,
        'dealer_id' => $dealer_id
    ])->row();
    
    if(!$user) {
        show_404();
    }
    
    // Get game history and summary
    $data['game_history'] = $this->Hierarchy_model->get_user_game_history($user_id, 100);
    $data['game_summary'] = $this->Hierarchy_model->get_user_game_summary($user_id);
    $data['user'] = $user;
    $data['title'] = 'User Details - ' . $user->name;
    
    $this->load->view('dealer/header', $data);
    $this->load->view('dealer/view_user', $data);
    $this->load->view('dealer/footer');
}}
?>