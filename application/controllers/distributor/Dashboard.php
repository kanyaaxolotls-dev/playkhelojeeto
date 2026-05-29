<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends Distributor_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Hierarchy_model');
        $this->load->model('db_model');
    }

/*    public function index() {
        $distributor_id = $this->session->distributor_id;
        
        // Get distributor data
        $data['distributor'] = $this->Hierarchy_model->get_distributor($distributor_id);
        
        // Get all dealers under this distributor
        $data['dealers'] = $this->Hierarchy_model->get_my_dealers($distributor_id);
        
        // Get statistics
        $data['total_dealers'] = count($data['dealers']);
        $data['total_users'] = $this->Hierarchy_model->count_users_under_distributor($distributor_id);
        $data['total_commission'] = $this->Hierarchy_model->get_distributor_commission($distributor_id);
        $data['today_commission'] = $this->Hierarchy_model->get_distributor_commission_today($distributor_id);
        
        // Get recent transactions
        $data['recent_transactions'] = $this->Hierarchy_model->get_distributor_transactions($distributor_id, 10);
        
        $data['title'] = 'Distributor Dashboard';
        $this->load->view('distributor/header', $data);
        $this->load->view('distributor/dashboard', $data);
        $this->load->view('distributor/footer');
    }*/
public function index() {
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Get distributor data
    $data['distributor'] = $this->Hierarchy_model->get_distributor($distributor_id);
    
    // Get all dealers under this distributor
    $data['dealers'] = $this->Hierarchy_model->get_my_dealers($distributor_id);
    
    // Calculate statistics
    $data['total_dealers'] = count($data['dealers']);
    
    $total_users = 0;
    foreach($data['dealers'] as $dealer) {
        $total_users += $this->Hierarchy_model->count_users_under_dealer($dealer->id);
    }
    $data['total_users'] = $total_users;
    
    $data['total_commission'] = $this->Hierarchy_model->get_distributor_commission($distributor_id);
    $data['today_commission'] = $this->Hierarchy_model->get_distributor_commission_today($distributor_id);
    $data['recent_transactions'] = $this->Hierarchy_model->get_distributor_transactions($distributor_id, 10);
    $data['title'] = 'Distributor Dashboard';
    
    $this->load->view('distributor/header', $data);
    $this->load->view('distributor/dashboard', $data);
    $this->load->view('distributor/footer');
}
   
    public function dealers() {
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Get distributor data
    $data['distributor'] = $this->Hierarchy_model->get_distributor($distributor_id);
    
    // Get all dealers under this distributor
    $data['dealers'] = $this->Hierarchy_model->get_my_dealers($distributor_id);
    
    // Calculate total users under all dealers
    $total_users = 0;
    foreach($data['dealers'] as $dealer) {
        $total_users += $this->Hierarchy_model->count_users_under_dealer($dealer->id);
    }
    $data['total_users'] = $total_users;
    $data['title'] = 'Manage Dealers';
    
    $this->load->view('distributor/header', $data);
    $this->load->view('distributor/dealers', $data);
    $this->load->view('distributor/footer');
}

    public function create_dealer() {
        if ($this->input->post('name')) {
            rbac_require('create_dealer');
            $distributor_id = $this->session->distributor_id;
            
            $data = array(
                'name' => $this->input->post('name'),
                'phone' => $this->input->post('phone'),
                'password' => $this->input->post('password'),
                'commission_rate' => $this->input->post('commission_rate') ?: 2.00,
                'distributor_id' => $distributor_id,
                'wallet' => 0,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s')
            );
            
            // Check if phone already exists
            $exists = $this->db_model->count_all('tbl_dealers', array('phone' => $data['phone']));
            if($exists > 0) {
                $this->session->set_flashdata('error', 'Phone number already exists!');
            } else {
                $this->db->insert('tbl_dealers', $data);
                $this->session->set_flashdata('success', 'Dealer created successfully!');
            }
            redirect(site_url('distributor/dashboard/dealers'));
        }
        
        $data['title'] = 'Create Dealer';
        $this->load->view('distributor/header', $data);
        $this->load->view('distributor/create_dealer', $data);
        $this->load->view('distributor/footer');
    }

    public function update_dealer_wallet() {
        $transaction_type = $this->input->post('transaction_type');
        if ($transaction_type === 'debit') {
            rbac_require('wallet_debit');
        } else {
            rbac_require('wallet_credit');
        }
        $dealer_id = $this->input->post('dealer_id');
        $amount = $this->input->post('amount');
        $distributor_id = $this->session->distributor_id;
        
        // Verify dealer belongs to this distributor
        $dealer = $this->db_model->select_multi('*', 'tbl_dealers', 
            array('id' => $dealer_id, 'distributor_id' => $distributor_id));
        
        if(!$dealer) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        // Get distributor wallet
        $distributor = $this->db_model->select_multi('*', 'tbl_distributors', 
            array('id' => $distributor_id));
        
        if($transaction_type == 'debit') {
            if($dealer->wallet < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient dealer wallet balance']);
                return;
            }
            $new_dealer_wallet = $dealer->wallet - $amount;
        } else {
            if($distributor->wallet < $amount) {
                echo json_encode(['success' => false, 'message' => 'Insufficient distributor wallet balance']);
                return;
            }
            $new_dealer_wallet = $dealer->wallet + $amount;
            // Deduct from distributor wallet
            $new_distributor_wallet = $distributor->wallet - $amount;
            $this->db->where('id', $distributor_id);
            $this->db->update('tbl_distributors', array('wallet' => $new_distributor_wallet));
        }
        
        // Update dealer wallet
        $this->db->where('id', $dealer_id);
        $result = $this->db->update('tbl_dealers', array('wallet' => $new_dealer_wallet));
        
        // Log transaction
        $log_data = array(
            'distributor_id' => $distributor_id,
            'dealer_id' => $dealer_id,
            'amount' => $amount,
            'type' => $transaction_type,
            'balance_before' => $dealer->wallet,
            'balance_after' => $new_dealer_wallet,
            'created_at' => date('Y-m-d H:i:s')
        );
        $this->db->insert('tbl_distributor_transactions', $log_data);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Wallet updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update wallet']);
        }
    }


public function commission() {
    rbac_require('view_transaction_history');
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Get commission data
    $data['commissions'] = $this->Hierarchy_model->get_distributor_commission_details($distributor_id);
    $data['total_commission'] = $this->Hierarchy_model->get_distributor_commission($distributor_id);
    $data['today_commission'] = $this->Hierarchy_model->get_distributor_commission_today($distributor_id);
    $data['monthly_commission'] = $this->Hierarchy_model->get_distributor_commission_monthly($distributor_id);
    $data['distributor'] = $this->Hierarchy_model->get_distributor($distributor_id);
    $data['title'] = 'Commission Report';
    
    $this->load->view('distributor/header', $data);
    $this->load->view('distributor/commission', $data);
    $this->load->view('distributor/footer');
}
    /*public function users() {
        $distributor_id = $this->session->distributor_id;
        $data['users'] = $this->Hierarchy_model->get_all_users_under_distributor($distributor_id);
        $data['title'] = 'Users Under My Dealers';
        $this->load->view('distributor/header', $data);
        $this->load->view('distributor/users', $data);
        $this->load->view('distributor/footer');
    }*/

    public function reports() {
        rbac_require('view_reports');
        $distributor_id = $this->session->distributor_id;
        
        $from_date = $this->input->get('from_date');
        $to_date = $this->input->get('to_date');
        $dealer_id = $this->input->get('dealer_id');
        
        $data['reports'] = $this->Hierarchy_model->get_distributor_reports($distributor_id, $from_date, $to_date, $dealer_id);
        $data['dealers'] = $this->Hierarchy_model->get_my_dealers($distributor_id);
        
        $data['title'] = 'Reports';
        $this->load->view('distributor/header', $data);
        $this->load->view('distributor/reports', $data);
        $this->load->view('distributor/footer');
    }
    public function delete_dealer($dealer_id) {
    rbac_require('delete_user');
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Verify dealer belongs to this distributor
    $dealer = $this->db->get_where('tbl_dealers', [
        'id' => $dealer_id,
        'distributor_id' => $distributor_id
    ])->row();
    
    if(!$dealer) {
        echo json_encode(['success' => false, 'message' => 'Dealer not found or unauthorized']);
        return;
    }
    
    // Soft delete - just update status
    $this->db->where('id', $dealer_id);
    $result = $this->db->update('tbl_dealers', ['status' => 0]);
    
    if($result) {
        echo json_encode(['success' => true, 'message' => 'Dealer deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete dealer']);
    }
}
public function users() {
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Get all users under this distributor (through all dealers)
    $data['users'] = $this->Hierarchy_model->get_all_users_under_distributor($distributor_id);
    
    // Get total count
    $data['total_users'] = count($data['users']);
    
    // Get distributor info
    $data['distributor'] = $this->Hierarchy_model->get_distributor($distributor_id);
    
    $data['title'] = 'View Users - Under My Dealers';
    
    $this->load->view('distributor/header', $data);
    $this->load->view('distributor/users', $data);
    $this->load->view('distributor/footer');
}

// View single user details
public function view_user($user_id) {
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Get user details
    $data['user'] = $this->db->get_where('tbl_users', ['id' => $user_id])->row();
    
    if(!$data['user']) {
        show_404();
    }
    
    // Verify this user belongs to this distributor's dealers
    $dealer = $this->db->get_where('tbl_dealers', [
        'id' => $data['user']->dealer_id,
        'distributor_id' => $distributor_id
    ])->row();
    
    if(!$dealer && $data['user']->distributor_id != $distributor_id) {
        show_404();
    }
    
    // Get user betting history
    $data['betting_history'] = $this->get_user_betting_history($user_id);
    
    // Get user transaction history
    $data['transactions'] = $this->get_user_transactions($user_id);
    
    // Get user commission earned
    $data['commissions'] = $this->Hierarchy_model->get_commission_history(['source_user_id' => $user_id]);
    
    $data['title'] = 'User Details - ' . $data['user']->name;
    
    $this->load->view('distributor/header', $data);
    $this->load->view('distributor/view_user', $data);
    $this->load->view('distributor/footer');
}

private function get_user_betting_history($user_id) {
    // Get bets from all game tables
    $bets = [];
    
    $game_tables = [
        'tbl_aviator_bet' => 'aviator',
        'tbl_funtarget_bet' => 'funtarget',
        'tbl_lucky36_bet' => 'lucky36',
        'tbl_car_betting' => 'car_roulette'
    ];
    
    foreach($game_tables as $table => $game) {
        $this->db->select('*, "' . $game . '" as game_name');
        $this->db->where('userid', $user_id);
        $this->db->order_by('date', 'DESC');
        $this->db->limit(50);
        $result = $this->db->get($table)->result();
        
        if($result) {
            $bets = array_merge($bets, $result);
        }
    }
    
    // Sort by date
    usort($bets, function($a, $b) {
        return strtotime($b->date) - strtotime($a->date);
    });
    
    return $bets;
}

private function get_user_transactions($user_id) {
    $this->db->where('user_id', $user_id);
    $this->db->or_where('source_user_id', $user_id);
    $this->db->order_by('created_at', 'DESC');
    $this->db->limit(50);
    return $this->db->get('tbl_transactions')->result();
}
public function dealer_view($dealer_id) {
    $distributor_id = $this->session->userdata('distributor_id');
    
    // Verify dealer belongs to this distributor
    $dealer = $this->db->get_where('tbl_dealers', [
        'id' => $dealer_id,
        'distributor_id' => $distributor_id
    ])->row();
    
    if(!$dealer) {
        show_404();
    }
    
    $data['dealer'] = $dealer;
    $data['users'] = $this->Hierarchy_model->get_my_users_for_dealer($dealer_id);
    $data['total_users'] = count($data['users']);
    $data['title'] = 'Dealer Details - ' . $dealer->name;
    
    $this->load->view('distributor/header', $data);
    $this->load->view('distributor/dealer_view', $data);
    $this->load->view('distributor/footer');
}
}
?>