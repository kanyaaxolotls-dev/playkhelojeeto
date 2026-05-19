<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Users extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
		if ($this->session->admin_id == NULL)
        {
            redirect(site_url('backend/login'));
        }
    }

    public function inactive()
	{
	    $role = $this->session->role;
	    if($role == 'Admin'){
	        $where_condition    = "status = 0";
	    } else{
	        $where_condition    = "status = 0 AND added_by = ".$this->session->admin_id;
	    }
		$data['users']      = $this->db_model->get_all_data('tbl_users',$where_condition);
        $data['title']      = 'Manage users';
        $this->load->view('admin/users/users',$data);
    }

    public function index()
	{
	    $name          = $this->input->post('name');
	    $phone         = $this->input->post('phone');
	    $pass          = $this->input->post('password');
	    $ref_code      = $this->input->post('invite_code');
	    $invite_code   = preg_replace('/[^0-9]/', '', $ref_code) ?? '1';
	    if(empty($invite_code) or $invite_code == NULL){
	        $invite_code = '1';
	    }
	    if($name == NULL){
	        $role = $this->session->role;
	        if($role == 'Admin'){
			    $where_condition    = "status = 1";
	        } else{
	            $where_condition    = "status = 1 AND added_by = ".$this->session->admin_id;
	        }
			$data['users']      = $this->db_model->get_all_data('tbl_users',$where_condition);
            $data['title']      = 'Manage users';
            $this->load->view('admin/users/users',$data);
	    }
	    else{
	        $user_chec     = $this->db_model->count_all('tbl_users', array('phone' => $phone));
	        $reffer_chec   = $this->db_model->count_all('tbl_users', array('id' => $invite_code));
            $filename      = $_FILES["img"]["name"];
			$tempname      = $_FILES["img"]["tmp_name"];
			$folder        = "./axxests/user_img/" . $filename;
			move_uploaded_file($tempname, $folder);
	        if($user_chec == 0 and $reffer_chec > 0){
	            $array = array(
                    'name'            => $name,
                    'img'             => $filename,
                    'phone'           => $phone,
                    'password'        => $pass,
                    'referral_code'   => $invite_code,
                    'usercode'        => $this->db_model->generate_random_string(6),
                    'status'          => 1,
                    'user_type'       => 'admin',
                    'added_by'        => $this->session->admin_id
                );
                $this->db->insert('tbl_users', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">New User Added.</div>');
                redirect(site_url('backend/users'));
	        }
            elseif($reffer_chec == 0){
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid Invite Code.</div>');
                redirect(site_url('backend/users'));
            }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Phone number Already Exist.</div>');
                redirect(site_url('backend/users'));
	        }
	    }
	}

    public function edit_user($id = 1)
	{
	    $name          = $this->input->post('name');
	    $email         = $this->input->post('email');
	    $phone         = $this->input->post('phone');
	    $pass          = $this->input->post('password');
	    $img           = $this->input->post('img');
	    if($name == NULL){
			$where_condition    = "status = 1 AND id = ".$id;
			$data['user']       = $this->db_model->select_multi('*', 'tbl_users', array('id' => $id));
            $data['title']      = 'Edit userid : '.$id;
            $this->load->view('admin/users/edit_user',$data);
	    }
	    else{
            if($img){
                $user_chec     = $this->db_model->count_all('tbl_users', array('phone' => $phone));
                $reffer_chec   = $this->db_model->count_all('tbl_users', array('usercode' => $invite_code));
                $filename      = $_FILES["img"]["name"];
                $tempname      = $_FILES["img"]["tmp_name"];
                $folder        = "./axxests/user_img/" . $filename;
                move_uploaded_file($tempname, $folder);
            }
            else{
                $filename      = $this->db_model->select('img', 'tbl_users', array('id' => $id));
            }
	        $array = array(
                'name'            => $name,
                'img'             => $filename ?? NULL,
                'phone'           => $phone,
                'password'        => $pass,
                'email'           => $email,
                'status'          => 1,
            );
            $this->db->where('id', $id);
            $result = $this->db->update('tbl_users', $array);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">User Profile Updated.</div>');
            redirect(site_url('backend/users/edit_user/'.$id));
	    }
	}
    
    public function process_transaction() {
        $user_id           = $this->input->post('user_id');
        $transactionType   = $this->input->post('transaction');
        $wallet_type       = $this->input->post('wallet_type');
        $c_bal             = $this->db_model->select($wallet_type, 'tbl_users', array('id' => $user_id));
        $transactionAmount = $this->input->post('amount');
        
        $response = array('success' => false, 'message' => 'Unknown error occurred');
        
        if($this->session->role == 'Admin'){
            if($transactionType == 'debit'){
                $balance = $c_bal - $transactionAmount;
            }
            else{
                $balance = $c_bal + $transactionAmount;
            }
            $array = array(
                $wallet_type => $balance, 
            );
            $this->db->where('id', $user_id);
            $result = $this->db->update('tbl_users', $array);
        
            if ($result) {
                $response = array('success' => true, 'userid' => $user_id, 'balance' => $balance, 'amount' => $transactionAmount);
            } 
            else {
                $response = array('success' => false, 'message' => 'Failed to update user wallet');
            }
        } else {
            $admin_bal = $this->db_model->select('wallet', 'tbl_admin', array('id' => $this->session->admin_id));
            if($transactionType == 'debit'){
                if($c_bal < $transactionAmount){
                    $response = array('success' => false, 'message' => 'insufficient fund in users wallet');
                    echo json_encode($response);
                    return;
                }
                $balance       = $c_bal     - $transactionAmount;
                $balance_admin = $admin_bal + $transactionAmount;
            } else {
                if($admin_bal < $transactionAmount){
                    $response = array('success' => false, 'message' => 'insufficient fund in your wallet');
                    echo json_encode($response);
                    return;
                }
                $balance       = $c_bal     + $transactionAmount;
                $balance_admin = $admin_bal - $transactionAmount;
            }
            
            $array = array(
                $wallet_type => $balance, 
            );
            $this->db->where('id', $user_id);
            $result1 = $this->db->update('tbl_users', $array);
            
            $array2 = array(
                'wallet' => $balance_admin, 
            );
            $this->db->where('id', $this->session->admin_id);
            $result2 = $this->db->update('tbl_admin', $array2);
            
            if ($result1 && $result2) {
                $response = array('success' => true, 'userid' => $user_id, 'balance' => $balance, 'amount' => $transactionAmount);
            } else {
                $response = array('success' => false, 'message' => 'Failed to update wallets');
            }
        }
        
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    
    public function delete_user($id,$status = 0)
	{
        if($id == 1){
            $this->session->set_flashdata('site_flash', '<div class="alert alert-warning">This User Is Not Deletable...</div>');
            redirect(site_url('backend/users/'));
        }else{
            $where_condition  = "id = ".$id;
            $array            =  ['status'   => $status];
            $result           = $this->db_model->update($array,'tbl_users',$where_condition);
            if($status == 0){
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">User Deactivated.</div>');
                redirect(site_url('backend/users/'));
            }else{
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">User Activated.</div>');
                redirect(site_url('backend/users/inactive'));
            }
        }
	}


}

