<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
		if ($this->session->admin_id != NULL)
        {
            redirect(site_url('backend/admin'));
        }
    }
	public function index()
	{
        $this->form_validation->set_rules('uname', 'Username', 'trim|required');
        if ($this->form_validation->run() !== FALSE) {
			$uname      = $this->input->post('uname');
			$pass       = $this->input->post('pass');
			$data     = $this->db_model->select_multi("*", 'tbl_admin', array('username' => $uname));
			if($data == NULL){
				$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Username Not Found.</div>');
				redirect(site_url('backend/login'));
			}
			else{
				if($data->password==$pass){
					$this->load->model('rbac_model');
					$this->rbac_model->ensure_schema();
					$role_id = !empty($data->role_id) ? (int) $data->role_id : 1;
					$role = $this->rbac_model->get_role_by_id($role_id);
					if (!$role) {
						$role = $this->rbac_model->get_role_by_id(1);
						$role_id = 1;
					}
					$this->load->helper('rbac');
					$this->session->set_userdata(array(
						'admin_id'   => $data->id,
						'email'      => $data->email,
						'name'       => $data->name,
						'role'       => $data->role,
						'username'   => $uname,
						'role_id'    => $role_id,
						'panel'      => 'admin',
						'dashboard_url' => $role ? $role->dashboard_url : 'backend/admin',
					));
					$this->rbac_model->sync_permissions_to_session($role_id);
					redirect(site_url($role && $role->dashboard_url ? $role->dashboard_url : 'backend/admin'));
				}
				else{
					$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Wrong Password Try Again.</div>');
					redirect(site_url('backend/login'));
				}
			}
		}
		else{
			$this->load->view('admin/login');
		}
    }
}



