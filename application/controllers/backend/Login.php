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
					$this->session->set_userdata(array(
						'admin_id'   => $data->id,
						'email'      => $data->email,
						'name'       => $data->name,
						'role'       => $data->role,
						'username'   => $uname,
					));
					redirect(site_url('backend/admin'));
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



