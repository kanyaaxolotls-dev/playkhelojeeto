<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet extends CI_Controller {
     public function __construct() {
        parent::__construct();
        $this->load->model('db_model');
        $this->load->model('Hierarchy_model');
    }
    
	public function index()
	{
		$phone    = $this->input->post('phone');
		$u_chec   = $this->db_model->count_all('tbl_users', array('phone' => $phone));
		if($u_chec > 0){   
		    $user_info     = $this->db_model->select_multi('*', 'tbl_users', array('phone' => $phone));
			$this->db->select_sum('amount'); 
			$this->db->where('userid', $user_info->id);
			$this->db->like('type', 'bonus');
			$query        = $this->db->get('tbl_transactions');
			$result       = $query->row();
			$bonus        = $result->amount ? $result->amount : 0;
		    $response     = array('status' => 'success', 'wallet' => $user_info->wallet, 'winning_wallet' => $user_info->winning_wallet, 'freeze_wallet' => $user_info->freeze_wallet, 'upi_id' => $user_info->upi, 'bonus' => $bonus);
		}
		else{
		    $response     = array('status' => 'error', 'message' => 'Trying to get data with non registered number');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
    
	public function deposit()
{
    $phone  = $this->input->post('phone');
    $utr    = $this->input->post('utr'); // optional
    $amount = $this->input->post('amount');

    // Validate phone number
    $u_chec = $this->db_model->count_all('tbl_users', array('phone' => $phone));
    if ($u_chec <= 0) {
        $response = array('status' => 'error', 'message' => 'Invalid or unregistered phone number.');
        return $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    // Generate a unique UTR if not provided
    if (empty($utr)) {
        $utr = 'UTR' . time() . rand(1000, 9999);
    }

    // Check for duplicate UTR only if it was provided manually
    $utr_chec = $this->db_model->count_all('tbl_deposit', array('tid' => $utr, 'payment_type' => 'Manual'));
    if ($utr_chec > 0) {
        $response = array('status' => 'error', 'message' => 'UTR already exists.');
        return $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    // Get user ID
    $user = $this->db_model->select('id', 'tbl_users', array('phone' => $phone));
    $user_id = is_array($user) ? $user['id'] : $user;

    // Handle image upload (optional)
    $filename = null;
    if (!empty($_FILES['img']['name'])) {
        $filename = $_FILES["img"]["name"];
        $tempname = $_FILES["img"]["tmp_name"];
        $folder = "./axxests/deposit/" . $filename;
        move_uploaded_file($tempname, $folder);
    }

    // Prepare insert data
    $data = array(
        'userid'       => $user_id,
        'amount'       => $amount,
        'img'          => $filename,
        'tid'          => $utr,
        'payment_type' => 'Manual',
        'created_at'   => date('Y-m-d H:i:s')
    );

    $this->db->insert('tbl_deposit', $data);

    $response = array('status' => 'success', 'message' => 'Deposit added successfully.', 'utr' => $utr);
    $this->output->set_content_type('application/json')->set_output(json_encode($response));
}

	public function spin_winning()
	{
		$userid    = $this->input->post('userid');
		$amount    = $this->input->post('amount');
		$u_chec    = $this->db_model->count_all('tbl_users', array('id' => $userid));
		if($u_chec <= 0) {
			$response   = array('status' => 'error', 'message' => 'Trying to get data with invalid id');
		}
		else{
			$wallet  = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
			$array = array(
				'wallet'  => $wallet + $amount,
			);
			$where_condition  = "id = ".$userid;
			$this->db_model->update($array,'tbl_users',$where_condition);
			
			$array2 = array(
				'userid'        => $userid,
				'amount'        => $amount,
				'type'          => 'Daily Spin Bonus',
			);
			$this->db->insert('tbl_transactions', $array2);
			$response   = array('status' => 'success', 'message' => 'Winning Added To Your Wallet...');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
    
	public function withdraw()
	{
		date_default_timezone_set('Asia/Kolkata');
		$phone           = $this->input->post('phone');
		$amount          = $this->input->post('amount');
		$u_chec          = $this->db_model->count_all('tbl_users', array('phone' => $phone));
		$amount_setup    = $this->db_model->select_multi('*', 'tbl_settings', array('id' => 1));
		$current_time    = strtotime(date("H:i")); 
		$open_time       = strtotime("10:00"); 
		$close_time      = strtotime("18:00");
		if ($current_time < $open_time || $current_time > $close_time) {
			$response = array('status' => 'error', 'message' => 'Withdrawal is only allowed between 10:00 AM and 6:00 PM');
		}
		elseif($amount < $amount_setup->min_withdraw){
			$response   = array('status' => 'error', 'message' => 'Minimum withdraw is '.$amount_setup->min_withdraw);
		}
		elseif($amount > $amount_setup->max_withdraw){
			$response   = array('status' => 'error', 'message' => 'Maximum withdraw is '.$amount_setup->max_withdraw);
		}
		elseif($u_chec > 0){   
			$winning_wallet  = $this->db_model->select('winning_wallet', 'tbl_users', array('phone' => $phone));
			$wallet          = $this->db_model->select('wallet', 'tbl_users', array('phone' => $phone));
			$userid          = $this->db_model->select('id', 'tbl_users', array('phone' => $phone));
			$w_chec          = $this->db_model->count_all('tbl_withdraw', array('userid' => $userid,'manual_date' => date("Y-m-d")));
			if($w_chec == 0){
				if($winning_wallet >= $amount){
	
					$charges = $amount * $amount_setup->with_charges / 100;
					$array = array(
						'winning_wallet'  => $winning_wallet - $amount,
						'wallet'          => $wallet - $amount,
					);
					$where_condition  = "phone = ".$phone;
					$this->db_model->update($array,'tbl_users',$where_condition);
	
					$array2 = array(
						'userid'        => $userid,
						'amount'        => $amount,
						'charges'       => $charges,
						'manual_date'   => date("Y-m-d"),
					);
					$this->db->insert('tbl_withdraw', $array2);
	
					$response   = array('status' => 'success', 'message' => 'Withdraw Request Send...');
				}
				else{
					$response   = array('status' => 'error', 'message' => 'Insufficient Fund');
				}
			}
			else{
				$response   = array('status' => 'error', 'message' => 'Only one withdraw allowed in one day..');
			}
		}
		else{
		    $response   = array('status' => 'error', 'message' => 'Trying to get data with non registered number');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	public function user_transactions()
	{
		$userid          = $this->input->post('userid');
        $where_condition = "userid = ".$userid;
        $show_trans      = $this->db_model->get_specific_records('tbl_transactions', $where_condition, 'amount, type, date');
        if($show_trans){
            $response = array('status' => 'success', 'data' => $show_trans);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
	
	public function deposits()
	{
		$userid          = $this->input->post('userid');
        $where_condition = "userid = ".$userid;
        $show_trans      = $this->db_model->get_specific_records('tbl_deposit', $where_condition, 'amount,date,tid,status');
        if($show_trans){
            $response = array('status' => 'success', 'data' => $show_trans);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
	
	public function wid_records()
	{
		$userid          = $this->input->post('userid');
        $where_condition = "userid = ".$userid;
        $show_trans      = $this->db_model->get_specific_records('tbl_withdraw', $where_condition, 'amount,date,id,staus');
        if($show_trans){
            $response = array('status' => 'success', 'data' => $show_trans);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

	public function wallet_to_freeze()
	{
		$userid   = $this->input->post('userid');
		$amount   = $this->input->post('amount');
		$u_chec   = $this->db_model->count_all('tbl_users', array('id' => $userid));
		if($u_chec > 0){
			$wallets  = $this->db_model->select_multi('wallet,freeze_wallet', 'tbl_users', array('id' => $userid));
			if($wallets->wallet >= $amount){
				$array = array(
					'wallet'         => $wallets->wallet        - $amount,
					'freeze_wallet'  => $wallets->freeze_wallet + $amount,
				);
				$where_condition  = "id = ".$userid;
				$this->db_model->update($array,'tbl_users',$where_condition);
				$response = array('status' => 'success', 'message' => 'Amount Freezed');
			}
			else{
				$response = array('status' => 'error', 'message' => 'Insufficient Fund');
			}
		} 
		else{
			$response = array('status' => 'error', 'message' => 'Invalid Userid');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

	public function freeze_to_wallet()
	{
		$userid   = $this->input->post('userid');
		$amount   = $this->input->post('amount');
		$u_chec   = $this->db_model->count_all('tbl_users', array('id' => $userid));
		if($u_chec > 0){
			$wallets  = $this->db_model->select_multi('wallet,freeze_wallet', 'tbl_users', array('id' => $userid));
			if($wallets->freeze_wallet >= $amount){
				$array = array(
					'wallet'         => $wallets->wallet        + $amount,
					'freeze_wallet'  => $wallets->freeze_wallet - $amount,
				);
				$where_condition  = "id = ".$userid;
				$this->db_model->update($array,'tbl_users',$where_condition);
				$response = array('status' => 'success', 'message' => 'Amount Transffered');
			}
			else{
				$response = array('status' => 'error', 'message' => 'Insufficient Fund');
			}
		} 
		else{
			$response = array('status' => 'error', 'message' => 'Invalid Userid');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
	
	 // =============================================
    // NEW HIERARCHY WALLET TRANSFER APIs
    // =============================================

    /**
     * 1. ADMIN TO DISTRIBUTOR TRANSFER
     * POST /api/wallet/admin_to_distributor
     * Requires: Admin Login Session
     */
    public function admin_to_distributor() {
        $this->output->set_content_type('application/json');
        
        // Check if admin is logged in
        if (!$this->session->admin_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized. Admin login required.',
                'code' => 401
            ]);
            return;
        }
        
        $distributor_id = $this->input->post('distributor_id');
        $amount = floatval($this->input->post('amount'));
        $remarks = $this->input->post('remarks') ?? 'Admin to Distributor transfer';
        
        if(!$distributor_id || $amount <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Distributor ID and valid amount are required',
                'code' => 400
            ]);
            return;
        }
        
        // Get admin wallet
        $admin = $this->db->get_where('tbl_admin', ['id' => $this->session->admin_id])->row();
        if(!$admin) {
            echo json_encode([
                'success' => false,
                'message' => 'Admin not found',
                'code' => 404
            ]);
            return;
        }
        
        $admin_wallet = floatval($admin->wallet ?? 0);
        if($admin_wallet < $amount) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient admin wallet balance. Available: ₹' . number_format($admin_wallet, 2),
                'code' => 400
            ]);
            return;
        }
        
        // Get distributor
        $distributor = $this->db->get_where('tbl_distributors', ['id' => $distributor_id, 'status' => 1])->row();
        if(!$distributor) {
            echo json_encode([
                'success' => false,
                'message' => 'Distributor not found or inactive',
                'code' => 404
            ]);
            return;
        }
        
        $this->db->trans_start();
        
        // Deduct from admin
        $new_admin_wallet = $admin_wallet - $amount;
        $this->db->where('id', $this->session->admin_id);
        $this->db->update('tbl_admin', ['wallet' => $new_admin_wallet]);
        
        // Add to distributor
        $new_distributor_wallet = floatval($distributor->wallet) + $amount;
        $this->db->where('id', $distributor_id);
        $this->db->update('tbl_distributors', ['wallet' => $new_distributor_wallet]);
        
        // Record transaction
        $this->db->insert('tbl_wallet_transfers', [
            'from_type' => 'admin',
            'from_id' => $this->session->admin_id,
            'to_type' => 'distributor',
            'to_id' => $distributor_id,
            'amount' => $amount,
            'transfer_type' => 'wallet_transfer',
            'remarks' => $remarks,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete();
        
        if($this->db->trans_status() === FALSE) {
            echo json_encode([
                'success' => false,
                'message' => 'Transaction failed. Please try again.',
                'code' => 500
            ]);
            return;
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Amount transferred successfully from Admin to Distributor',
            'data' => [
                'from' => 'Admin',
                'to' => $distributor->name,
                'amount' => $amount,
                'admin_wallet_balance' => $new_admin_wallet,
                'distributor_wallet_balance' => $new_distributor_wallet
            ]
        ]);
    }

    /**
     * 2. DISTRIBUTOR TO DEALER TRANSFER
     * POST /api/wallet/distributor_to_dealer
     * Requires: Distributor Login Session OR API Token
     */
    public function distributor_to_dealer() {
        $this->output->set_content_type('application/json');
        
        // Get distributor_id from session or API token
        $distributor_id = $this->session->userdata('distributor_id');
        if(!$distributor_id) {
            $distributor_id = $this->validate_distributor_token();
        }
        
        if(!$distributor_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized. Distributor login required.',
                'code' => 401
            ]);
            return;
        }
        
        $dealer_id = $this->input->post('dealer_id');
        $amount = floatval($this->input->post('amount'));
        $remarks = $this->input->post('remarks') ?? 'Distributor to Dealer transfer';
        
        if(!$dealer_id || $amount <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Dealer ID and valid amount are required',
                'code' => 400
            ]);
            return;
        }
        
        // Get distributor wallet
        $distributor = $this->db->get_where('tbl_distributors', ['id' => $distributor_id, 'status' => 1])->row();
        if(!$distributor) {
            echo json_encode([
                'success' => false,
                'message' => 'Distributor not found',
                'code' => 404
            ]);
            return;
        }
        
        $distributor_wallet = floatval($distributor->wallet ?? 0);
        if($distributor_wallet < $amount) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient distributor wallet balance. Available: ₹' . number_format($distributor_wallet, 2),
                'code' => 400
            ]);
            return;
        }
        
        // Verify dealer belongs to this distributor
        $dealer = $this->db->get_where('tbl_dealers', [
            'id' => $dealer_id, 
            'distributor_id' => $distributor_id,
            'status' => 1
        ])->row();
        
        if(!$dealer) {
            echo json_encode([
                'success' => false,
                'message' => 'Dealer not found or not under your distribution',
                'code' => 404
            ]);
            return;
        }
        
        $this->db->trans_start();
        
        // Deduct from distributor
        $new_distributor_wallet = $distributor_wallet - $amount;
        $this->db->where('id', $distributor_id);
        $this->db->update('tbl_distributors', ['wallet' => $new_distributor_wallet]);
        
        // Add to dealer
        $new_dealer_wallet = floatval($dealer->wallet) + $amount;
        $this->db->where('id', $dealer_id);
        $this->db->update('tbl_dealers', ['wallet' => $new_dealer_wallet]);
        
        // Record transaction
        $this->db->insert('tbl_wallet_transfers', [
            'from_type' => 'distributor',
            'from_id' => $distributor_id,
            'to_type' => 'dealer',
            'to_id' => $dealer_id,
            'amount' => $amount,
            'transfer_type' => 'wallet_transfer',
            'remarks' => $remarks,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete();
        
        echo json_encode([
            'success' => true,
            'message' => 'Amount transferred successfully to dealer',
            'data' => [
                'from' => $distributor->name,
                'to' => $dealer->name,
                'amount' => $amount,
                'distributor_wallet_balance' => $new_distributor_wallet,
                'dealer_wallet_balance' => $new_dealer_wallet
            ]
        ]);
    }

    /**
     * 3. DEALER TO USER TRANSFER
     * POST /api/wallet/dealer_to_user
     * Requires: Dealer Login Session OR API Token
     */
    public function dealer_to_user() {
        $this->output->set_content_type('application/json');
        
        // Get dealer_id from session or API token
        $dealer_id = $this->session->userdata('dealer_id');
        if(!$dealer_id) {
            $dealer_id = $this->validate_dealer_token();
        }
        
        if(!$dealer_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized. Dealer login required.',
                'code' => 401
            ]);
            return;
        }
        
        $user_id = $this->input->post('user_id');
        $amount = floatval($this->input->post('amount'));
        $remarks = $this->input->post('remarks') ?? 'Dealer to User transfer';
        
        if(!$user_id || $amount <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'User ID and valid amount are required',
                'code' => 400
            ]);
            return;
        }
        
        // Get dealer wallet
        $dealer = $this->db->get_where('tbl_dealers', ['id' => $dealer_id, 'status' => 1])->row();
        if(!$dealer) {
            echo json_encode([
                'success' => false,
                'message' => 'Dealer not found',
                'code' => 404
            ]);
            return;
        }
        
        $dealer_wallet = floatval($dealer->wallet ?? 0);
        if($dealer_wallet < $amount) {
            echo json_encode([
                'success' => false,
                'message' => 'Insufficient dealer wallet balance. Available: ₹' . number_format($dealer_wallet, 2),
                'code' => 400
            ]);
            return;
        }
        
        // Verify user belongs to this dealer
        $user = $this->db->get_where('tbl_users', [
            'id' => $user_id, 
            'dealer_id' => $dealer_id,
            'status' => 1
        ])->row();
        
        if(!$user) {
            echo json_encode([
                'success' => false,
                'message' => 'User not found or not under your dealership',
                'code' => 404
            ]);
            return;
        }
        
        $this->db->trans_start();
        
        // Deduct from dealer
        $new_dealer_wallet = $dealer_wallet - $amount;
        $this->db->where('id', $dealer_id);
        $this->db->update('tbl_dealers', ['wallet' => $new_dealer_wallet]);
        
        // Add to user wallet
        $new_user_wallet = floatval($user->wallet) + $amount;
        $this->db->where('id', $user_id);
        $this->db->update('tbl_users', ['wallet' => $new_user_wallet]);
        
        // Record transaction
        $this->db->insert('tbl_transactions', [
            'userid' => $user_id,
            'amount' => $amount,
            'type' => 'wallet_transfer',
            'status' => 'credit',
            'remarks' => $remarks,
            'date' => date('Y-m-d H:i:s')
        ]);
        
        // Record wallet transfer
        $this->db->insert('tbl_wallet_transfers', [
            'from_type' => 'dealer',
            'from_id' => $dealer_id,
            'to_type' => 'user',
            'to_id' => $user_id,
            'amount' => $amount,
            'transfer_type' => 'wallet_transfer',
            'remarks' => $remarks,
            'status' => 'completed',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $this->db->trans_complete();
        
        echo json_encode([
            'success' => true,
            'message' => 'Amount transferred successfully to user',
            'data' => [
                'from' => $dealer->name,
                'to' => $user->name,
                'amount' => $amount,
                'dealer_wallet_balance' => $new_dealer_wallet,
                'user_wallet_balance' => $new_user_wallet
            ]
        ]);
    }

    /**
     * 4. GET DISTRIBUTOR WALLET BALANCE
     * GET /api/wallet/distributor_balance
     */
    public function distributor_balance() {
        $this->output->set_content_type('application/json');
        
        $distributor_id = $this->session->userdata('distributor_id');
        if(!$distributor_id) {
            $distributor_id = $this->validate_distributor_token();
        }
        
        if(!$distributor_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ]);
            return;
        }
        
        $distributor = $this->db->get_where('tbl_distributors', ['id' => $distributor_id])->row();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'distributor_id' => $distributor->id,
                'name' => $distributor->name,
                'wallet_balance' => floatval($distributor->wallet),
                'total_commission' => floatval($distributor->total_commission ?? 0)
            ]
        ]);
    }

    /**
     * 5. GET DEALER WALLET BALANCE
     * GET /api/wallet/dealer_balance
     */
    public function dealer_balance() {
        $this->output->set_content_type('application/json');
        
        $dealer_id = $this->session->userdata('dealer_id');
        if(!$dealer_id) {
            $dealer_id = $this->validate_dealer_token();
        }
        
        if(!$dealer_id) {
            echo json_encode([
                'success' => false,
                'message' => 'Unauthorized',
                'code' => 401
            ]);
            return;
        }
        
        $dealer = $this->db->get_where('tbl_dealers', ['id' => $dealer_id])->row();
        
        echo json_encode([
            'success' => true,
            'data' => [
                'dealer_id' => $dealer->id,
                'name' => $dealer->name,
                'wallet_balance' => floatval($dealer->wallet),
                'total_commission' => floatval($dealer->total_commission ?? 0)
            ]
        ]);
    }

    // =============================================
    // HELPER METHODS
    // =============================================
    
    private function validate_distributor_token() {
        $headers = $this->input->get_request_header('Authorization', TRUE);
        $token = str_replace('Bearer ', '', $headers);
        
        if(empty($token)) {
            return null;
        }
        
        // Verify token from distributor_sessions table
        $session = $this->db->get_where('tbl_distributor_sessions', [
            'token' => $token,
            'expires_at >' => date('Y-m-d H:i:s')
        ])->row();
        
        return $session ? $session->distributor_id : null;
    }
    
    private function validate_dealer_token() {
        $headers = $this->input->get_request_header('Authorization', TRUE);
        $token = str_replace('Bearer ', '', $headers);
        
        if(empty($token)) {
            return null;
        }
        
        // Verify token from dealer_sessions table
        $session = $this->db->get_where('tbl_dealer_sessions', [
            'token' => $token,
            'expires_at >' => date('Y-m-d H:i:s')
        ])->row();
        
        return $session ? $session->dealer_id : null;
    }


}