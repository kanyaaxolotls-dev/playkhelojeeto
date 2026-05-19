<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    
	public function profile()
	{
		$userid       = $this->input->post('userid');
		$name         = $this->input->post('name');
		$upi_id       = $this->input->post('upi_id');
		$bank_acc     = $this->input->post('bank_acc');
		$bank_name    = $this->input->post('bank_name');
		$bank_ifsc    = $this->input->post('bank_ifsc');
		$u_check      = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if($u_check > 0){

			$array = array(
                'name'        => $name,
                'upi'         => $upi_id,
                'bank_acc'    => $bank_acc,
                'bank_name'   => $bank_name,
                'bank_ifsc'   => $bank_ifsc,
            );
            $where_condition  = "id = ".$userid;
            $this->db_model->update($array,'tbl_users',$where_condition);

			$response = array('status' => 'success','message' => 'Profile Updated !');
		}
		else{
			$response = array('status' => 'error','message' => 'Invalid Userid');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
    
	public function forgot_pass()
	{
		$phone        = $this->input->post('phone');
		$password     = $this->input->post('password');
		if($this->db_model->count_all('tbl_users', array('phone' => $phone)) == 0){
			$response = array('status' => 'error','message' => 'User Not Found...');
		}elseif($phone and $password){
			$array = array(
                'password'  => $password,
            );
            $where_condition  = "phone = ".$phone;
            $this->db_model->update($array,'tbl_users',$where_condition);
			$response = array('status' => 'success','message' => 'Password Changed Successfully !');
		}else{
			$response = array('status' => 'error','message' => 'Something Went Wrong Please Try Again Later...');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
    
	public function logout()
	{
		$phone        = $this->input->post('phone');
		if($this->db_model->count_all('tbl_users', array('phone' => $phone)) == 0){
			$response = array('status' => 'error','message' => 'User Not Found...');
		}elseif($phone){
			$array = array(
                'is_login'  => 0,
            );
            $where_condition  = "phone = ".$phone;
            $this->db_model->update($array,'tbl_users',$where_condition);
			$response = array('status' => 'success','message' => 'User Logged Out Successfully !');
		}else{
			$response = array('status' => 'error','message' => 'Something Went Wrong Please Try Again Later...');
		}
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

}