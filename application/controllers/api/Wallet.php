<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet extends CI_Controller {
    
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

}