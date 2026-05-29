<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Distributor_login extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('db_model');
    }

    public function index() {
        if($this->session->distributor_id) {
            redirect(site_url('distributor/dashboard'));
        }
        $data['title'] = 'Distributor Login';
        $this->load->view('distributor/login', $data);
    }

    public function authenticate() {
        $phone = $this->input->post('phone');
        $password = $this->input->post('password');
        
        $distributor = $this->db_model->select_multi('*', 'tbl_distributors', 
            array('phone' => $phone, 'status' => 1));
        
        if($distributor && $distributor->password == $password) {
            $this->load->model('rbac_model');
            $this->rbac_model->ensure_schema();
            $role_id = !empty($distributor->role_id) ? (int) $distributor->role_id : 2;
            $role = $this->rbac_model->get_role_by_id($role_id);
            $this->session->set_userdata(array(
                'distributor_id' => $distributor->id,
                'distributor_name' => $distributor->name,
                'distributor_phone' => $distributor->phone,
                'distributor_wallet' => $distributor->wallet,
                'distributor_role' => 'distributor',
                'role_id' => $role_id,
                'panel' => 'distributor',
                'dashboard_url' => $role ? $role->dashboard_url : 'distributor/dashboard',
            ));
            $this->load->helper('rbac');
            $this->rbac_model->sync_permissions_to_session($role_id);
            redirect(site_url($role && $role->dashboard_url ? $role->dashboard_url : 'distributor/dashboard'));
        } else {
            $this->session->set_flashdata('error', 'Invalid phone or password');
            redirect(site_url('distributor_login'));
        }
    }

    public function logout() {
        $this->session->unset_userdata('distributor_id');
        $this->session->unset_userdata('distributor_name');
        redirect(site_url('distributor_login'));
    }
}
?>