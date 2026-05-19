<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends CI_Controller {
     public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('db_model');
        
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function send_otp()
	{
		$phone    = $this->input->post('phone');
		$otp      = rand(10000,99999);
		$response = $this->db_model->sendOtpSms($otp, $phone);
		$this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

    public function login() {
        $phone    = $this->input->post('phone');
        $password = $this->input->post('password');
        $u_chec   = $this->db_model->count_all('tbl_users', array('phone' => $phone));
        $login_session = $this->db_model->generate_random_string(20);
        if ($u_chec > 0) {
            $user_data  = $this->db_model->select_multi('*', 'tbl_users', array('phone' => $phone));
            if($user_data->status == 0){
                $response   = array('status' => 'error', 'message' => 'Invalid User');
            }
            elseif($password == $user_data->password){
                $array = array(
                    'is_login'        => 1,
                    'login_session'   => $login_session,
                );
                $where_condition  = "phone = ".$phone;
                $this->db_model->update($array,'tbl_users',$where_condition);
                $response   = array('status' => 'success', 'message' => 'Login successful', 'login_session' => $login_session,'data' => $user_data);
            }
            else{
                $response   = array('status' => 'error', 'message' => 'Incorrect Password');
            }
        } 
        else {
            $response = array('status' => 'error', 'message' => 'Mobile Number Not Registered With us');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function get_session() {
        $phone    = $this->input->post('phone');
        $u_chec   = $this->db_model->count_all('tbl_users', array('phone' => $phone));
        if ($u_chec > 0) {
            $login_session  = $this->db_model->select('login_session', 'tbl_users', array('phone' => $phone));
            $response       = array('status' => 'success', 'message' => 'Session get successful', 'current_login_session' => $login_session);
        } else{
            $response = array('status' => 'error', 'message' => 'Mobile Number Not Registered With us');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function check_state() {
        $this->load->library('Curl');
        $ip_address = $this->input->ip_address();
        $api_url    = "https://ipinfo.io/{$ip_address}/json";
        $response   = $this->curl->simple_get($api_url);
        $data       = json_decode($response, true);
        $state_data = $this->db_model->select('state_availability', 'tbl_settings', array('id' => 1));
        $stateArray = explode(",", $state_data);
        if (in_array($data['region'], $stateArray)) {
            $response = array('status' => 'success', 'message' => 'State Matched');
        } else {
            $response = array('status' => 'error', 'message' => 'This Game Is Not For Your State...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function bound_number() {
        $phone     = $this->input->post('phone');
        $fcmid     = $this->input->post('fcmid');
        $deviceid  = $this->input->post('deviceid');
        if($this->db_model->select('is_bounded', 'tbl_users', array('phone' => $phone)) == 1){
            $response = array('status' => 'error', 'message' => 'Mobile Number With This Device Already Bounded...');
        }
        else{
            $array = array(
                'device_id'   => $deviceid,
                'fcm_id'      => $fcmid,
                'is_bounded'  => 1,
            );
            $where_condition  = "phone = ".$phone;
            $this->db_model->update($array,'tbl_users',$where_condition);
            $response = array('status' => 'success', 'message' => 'Mobile Number Bounded With Your Device...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function register() {
        $name      = $this->input->post('name');
        $phone     = $this->input->post('phone');
        $pass      = $this->input->post('password');
        $ref_code  = $this->input->post('invite_code') ;
        $ref_code  = preg_replace('/[^0-9]/', '', $ref_code) ?? '1';
        $usercode  = $this->db_model->generate_random_string(6);
        if($ref_code != '1' and $this->db_model->count_all('tbl_users', array('id' => $ref_code)) == 0 and $ref_code != NULL){
            $response = array('status' => 'error', 'message' => 'Invalid Invitation Code');
        }
        elseif($this->db_model->count_all('tbl_users', array('phone' => $phone)) > 0){
            $response = array('status' => 'error', 'message' => 'Mobile Number Already Registered With Us');
        }
        elseif($name and $phone and $pass and $usercode){
            $array = array(
                'name'            => $name,
                'phone'           => $phone,
                'password'        => $pass,
                'referral_code'   => $ref_code,
                'usercode'        => $usercode,
                'is_bounded'      => 1,
            );
            $this->db->insert('tbl_users', $array);
            $response = array('status' => 'success', 'message' => 'User Registered Successfully');
        }
        else{
            $response = array('status' => 'error', 'message' => 'Something Went Wrong Try Again Later');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}
