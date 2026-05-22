<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
defined('BASEPATH') OR exit('No direct script access allowed');

class Distributors extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Hierarchy_model');
        $this->load->model('db_model');
    }
    
    private function check_admin() {
        if ($this->session->admin_id == NULL) {
            if ($this->input->is_ajax_request()) {
                return false;
            }
            redirect(site_url('backend/login'));
            return false;
        }
        return true;
    }

    // =============================================
    // LIST ALL DISTRIBUTORS
    // =============================================
    public function index() {
        if (!$this->check_admin()) return;
        
        $data['distributors'] = $this->Hierarchy_model->get_all_distributors(['status' => 1]);
        $data['title'] = 'Manage Distributors';
        $this->load->view('admin/header', $data);
        $this->load->view('admin/distributors/index', $data);
        $this->load->view('admin/footer');
    }

    // =============================================
    // CREATE DISTRIBUTOR (Page & Form Submit)
    // =============================================
    public function create() {
        if (!$this->check_admin()) return;
        
        if ($this->input->post('name')) {
            $name = trim($this->input->post('name'));
            $phone = trim($this->input->post('phone'));
            $password = trim($this->input->post('password'));
            $commission_rate = floatval($this->input->post('commission_rate'));
            $initial_wallet = floatval($this->input->post('initial_wallet') ?? 0);

            if ($name && $phone && $password) {
                // Check if phone already exists
                if ($this->db_model->count_all('tbl_distributors', ['phone' => $phone]) > 0) {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Distributor phone already exists.</div>');
                } else {
                    // Get admin wallet for initial funding
                    $admin = $this->db->get_where('tbl_admin', ['id' => $this->session->admin_id])->row();
                    $admin_wallet = floatval($admin->wallet ?? 0);
                    
                    if ($initial_wallet > 0 && $admin_wallet < $initial_wallet) {
                        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Insufficient admin wallet balance for initial funding.</div>');
                    } else {
                        // Start transaction
                        $this->db->trans_start();
                        
                        // Insert distributor
                        $this->db->insert('tbl_distributors', [
                            'name' => $name,
                            'phone' => $phone,
                            'password' => $password,
                            'wallet' => $initial_wallet,
                            'commission_rate' => $commission_rate ?: 0.50,
                            'status' => 1,
                            'admin_id' => $this->session->admin_id,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        $distributor_id = $this->db->insert_id();
                        
                        // Deduct from admin wallet if initial wallet given
                        if ($initial_wallet > 0) {
                            $new_admin_wallet = $admin_wallet - $initial_wallet;
                            $this->db->where('id', $this->session->admin_id);
                            $this->db->update('tbl_admin', ['wallet' => $new_admin_wallet]);
                            
                            // Record wallet transfer
                            $this->db->insert('tbl_wallet_transfers', [
                                'from_type' => 'admin',
                                'from_id' => $this->session->admin_id,
                                'to_type' => 'distributor',
                                'to_id' => $distributor_id,
                                'amount' => $initial_wallet,
                                'transfer_type' => 'credit',
                                'remarks' => 'Initial wallet funding',
                                'status' => 'completed',
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
                        }
                        
                        $this->db->trans_complete();
                        
                        if ($this->db->trans_status() !== FALSE) {
                            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Distributor created successfully with ₹' . number_format($initial_wallet, 2) . ' wallet balance.</div>');
                        } else {
                            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Failed to create distributor.</div>');
                        }
                    }
                }
            } else {
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please fill all required fields.</div>');
            }
            redirect(site_url('backend/distributors'));
        }

        // Get admin wallet balance for display
        $admin = $this->db->get_where('tbl_admin', ['id' => $this->session->admin_id])->row();
        $data['admin_wallet'] = floatval($admin->wallet ?? 0);
        $data['title'] = 'Create Distributor';
        $this->load->view('admin/header', $data);
        $this->load->view('admin/distributors/create', $data);
        $this->load->view('admin/footer');
    }

    // =============================================
    // VIEW DISTRIBUTOR DASHBOARD
    // =============================================
    public function view($id = 0) {
        if (!$this->check_admin()) return;
        
        $distributor = $this->Hierarchy_model->get_distributor($id);
        if (!$distributor) {
            show_404();
            return;
        }

        $data['distributor'] = $distributor;
        $data['dealers'] = $this->Hierarchy_model->get_my_dealers($id);
        $data['users'] = $this->Hierarchy_model->get_my_users_for_distributor($id);
        $data['commission_total'] = $this->Hierarchy_model->get_distributor_commission($id);
        $data['user_count'] = count($data['users']);
        $data['dealer_count'] = count($data['dealers']);
        $data['title'] = 'Distributor Dashboard - ' . $distributor->name;
        
        $this->load->view('admin/header', $data);
        $this->load->view('admin/distributors/view', $data);
        $this->load->view('admin/footer');
    }

    // =============================================
    // UPDATE DISTRIBUTOR WALLET (AJAX)
    // =============================================
public function update_wallet() {
    $this->output->set_content_type('application/json');
    
    // Check session
    if ($this->session->admin_id == NULL) {
        echo json_encode(['success' => false, 'message' => 'Session expired. Please login again.']);
        return;
    }
    
    // Get data - support both JSON and FormData
    $distributor_id = null;
    $amount = 0;
    $transaction_type = null;
    $remarks = '';
    
    // Check if it's JSON request
    $input = json_decode(file_get_contents('php://input'), true);
    
    if($input && isset($input['distributor_id'])) {
        // JSON data
        $distributor_id = $input['distributor_id'] ?? null;
        $amount = floatval($input['amount'] ?? 0);
        $transaction_type = $input['transaction_type'] ?? null;
        $remarks = $input['remarks'] ?? 'Admin wallet update';
    } else {
        // FormData (POST) - FIXED: Get values directly from $_POST
        $distributor_id = $this->input->post('distributor_id');
        $amount = floatval($this->input->post('amount'));
        $transaction_type = $this->input->post('transaction_type');
        $remarks = $this->input->post('remarks') ?: 'Admin wallet update';
    }
    
    // Validation
    if(!$distributor_id || $amount <= 0 || !$transaction_type) {
        echo json_encode([
            'success' => false, 
            'message' => 'Distributor ID, amount, and transaction type are required',
            'debug' => [
                'distributor_id' => $distributor_id,
                'amount' => $amount,
                'transaction_type' => $transaction_type
            ]
        ]);
        return;
    }
    
    // Get distributor
    $distributor = $this->db->get_where('tbl_distributors', ['id' => $distributor_id, 'status' => 1])->row();
    if(!$distributor) {
        echo json_encode(['success' => false, 'message' => 'Distributor not found']);
        return;
    }
    
    // Get admin wallet
    $admin = $this->db->get_where('tbl_admin', ['id' => $this->session->admin_id])->row();
    $admin_wallet = floatval($admin->wallet ?? 0);
    $old_balance = floatval($distributor->wallet);
    
    // Start transaction for data integrity
    $this->db->trans_start();
    $result = false;
    if($transaction_type == 'credit') {
       if($admin_wallet < $amount) {

    $this->db->trans_complete();

    echo json_encode([
        'success' => false,
        'message' => 'Insufficient admin wallet balance. Available: ₹' . number_format($admin_wallet, 2)
    ]);
    return;
}
        
        $new_balance = $old_balance + $amount;
        $new_admin_wallet = $admin_wallet - $amount;
        
        // Update admin wallet
        $this->db->where('id', $this->session->admin_id);
        $this->db->update('tbl_admin', ['wallet' => $new_admin_wallet]);
        
        // Update distributor wallet
        $this->db->where('id', $distributor_id);
        $result = $this->db->update('tbl_distributors', ['wallet' => $new_balance]);
        
        if($result) {
            // Insert transaction record
            $this->db->insert('tbl_wallet_transfers', [
                'from_type' => 'admin',
                'from_id' => $this->session->admin_id,
                'to_type' => 'distributor',
                'to_id' => $distributor_id,
                'amount' => $amount,
                'transfer_type' => $transaction_type,
                'remarks' => $remarks,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
    } elseif($transaction_type == 'debit') {
        // Debit logic
       if($old_balance < $amount) {

    $this->db->trans_complete();

    echo json_encode([
        'success' => false,
        'message' => 'Insufficient distributor wallet balance. Available: ₹' . number_format($old_balance, 2)
    ]);
    return;
}
        
        $new_balance = $old_balance - $amount;
        $new_admin_wallet = $admin_wallet + $amount;
        
        $this->db->where('id', $this->session->admin_id);
        $this->db->update('tbl_admin', ['wallet' => $new_admin_wallet]);
        
        $this->db->where('id', $distributor_id);
        $result = $this->db->update('tbl_distributors', ['wallet' => $new_balance]);
        
        if($result) {
            // Insert transaction record for debit
            $this->db->insert('tbl_wallet_transfers', [
                'from_type' => 'distributor',
                'from_id' => $distributor_id,
                'to_type' => 'admin',
                'to_id' => $this->session->admin_id,
                'amount' => $amount,
                'transfer_type' => $transaction_type,
                'remarks' => $remarks,
                'status' => 'completed',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    } else {
        $this->db->trans_complete();

    echo json_encode(['success' => false, 'message' => 'Invalid transaction type']);
    return;
    }
    
    $this->db->trans_complete();
    
    if ($this->db->trans_status() !== FALSE && $result) {
        echo json_encode([
            'success' => true,
            'message' => 'Wallet updated successfully',
            'data' => [
                'distributor_id' => $distributor_id,
                'distributor_name' => $distributor->name,
                'old_balance' => number_format($old_balance, 2),
                'new_balance' => number_format($new_balance, 2),
                'amount' => number_format($amount, 2),
                'transaction_type' => $transaction_type,
                'admin_wallet_balance' => number_format($new_admin_wallet, 2)
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update wallet. Please try again.']);
    }
}

    // =============================================
    // DELETE DISTRIBUTOR (Soft Delete)
    // =============================================
    public function delete($id) {
        $this->output->set_content_type('application/json');
        
        if ($this->session->admin_id == NULL) {
            echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
            return;
        }
        
        $distributor = $this->db->get_where('tbl_distributors', ['id' => $id])->row();
        if(!$distributor) {
            echo json_encode(['success' => false, 'message' => 'Distributor not found']);
            return;
        }
        
        $this->db->where('id', $id);
        $result = $this->db->update('tbl_distributors', ['status' => 0]);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Distributor deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete distributor']);
        }
    }
}
?>