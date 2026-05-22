<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('db_model');
        $this->load->library('session');
    }

    public function index() {
        if($this->session->userdata('dealer_id')) {
            redirect(site_url('dealer/dashboard'));
        }
        $data['title'] = 'Dealer Login';
        $this->load->view('dealer/login', $data);
    }

    public function authenticate() {
        $phone = $this->input->post('phone');
        $password = $this->input->post('password');
        
        $dealer = $this->db_model->select_multi('*', 'tbl_dealers', 
            array('phone' => $phone, 'status' => 1));
        
        if($dealer && $dealer->password == $password) {
            $session_data = array(
                'dealer_id' => $dealer->id,
                'dealer_name' => $dealer->name,
                'dealer_phone' => $dealer->phone,
                'dealer_wallet' => $dealer->wallet,
                'distributor_id' => $dealer->distributor_id,
                'dealer_logged_in' => TRUE
            );
            $this->session->set_userdata($session_data);
            redirect(site_url('dealer/dashboard'));
        } else {
            $this->session->set_flashdata('error', 'Invalid phone or password');
            redirect(site_url('dealer/login'));
        }
    }

    public function logout() {
        $this->session->unset_userdata('dealer_id');
        $this->session->unset_userdata('dealer_name');
        $this->session->unset_userdata('dealer_wallet');
        $this->session->sess_destroy();
        redirect(site_url('dealer/login'));
    }
}
?>