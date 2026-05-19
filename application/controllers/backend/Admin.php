<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Admin extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
		if ($this->session->admin_id == NULL)
        {
            redirect(site_url('backend/login'));
        }
    }
    
	public function index()
	{
		$data['games']        = $this->db_model->get_limited_records('tbl_users',4);
	    $data['title']        = 'Dashboard';
		$this->load->view('admin/index',$data);
	}
    
	public function change_pass()
	{
		$old_pass = $this->input->post('opass');
		$pass     = $this->input->post('pass');
		$new_pass = $this->input->post('cpass');
		if($old_pass and $pass){
			$my_pass = $this->db_model->select('password', 'tbl_admin', array('id' => $this->session->admin_id));
			if($pass != $new_pass){
				$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Password and confirm password not matched.</div>');
				redirect(site_url('backend/admin/change_pass'));
			}elseif($my_pass != $old_pass){
				$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">You Entered Wrong Old Password.</div>');
				redirect(site_url('backend/admin/change_pass'));
			}else{
				$data = array(
					'password'    => $pass,
				);
				$where_condition  = "id = ".$this->session->admin_id;
				$this->db_model->update($data,'tbl_admin',$where_condition);
				$this->session->set_flashdata('site_flash', '<div class="alert alert-success">Password Changed Successfully !!.</div>');
				redirect(site_url('backend/admin/change_pass'));
			}
		}else{
			$data['title']       = 'Profile';
			$this->load->view('admin/setting/c_pass',$data);
		}
	}
    
	public function profile()
	{
		$this->form_validation->set_rules('name', 'Name', 'required');
        $this->form_validation->set_rules('state', 'State', 'required');
        $this->form_validation->set_rules('city', 'City', 'required');
        $this->form_validation->set_rules('address', 'Address', 'required');
        $this->form_validation->set_rules('upi', 'Upi', 'required');
		if ($this->form_validation->run() == FALSE) {
	    	$data['data']        = $this->db_model->select_multi('*', 'tbl_admin', array('id' => $this->session->admin_id));
	    	$data['title']       = 'Profile';
			$this->load->view('admin/setting/profile',$data);
		}else{
            $data = array(
                'name'    => $this->input->post('name'),
                'state'   => $this->input->post('state'),
                'city'    => $this->input->post('city'),
                'address' => $this->input->post('address'),
                'upi'     => $this->input->post('upi')
            );
			$where_condition  = "id = ".$this->session->admin_id;
            $this->db_model->update($data,'tbl_admin',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Profile Updated.</div>');
            redirect(site_url('backend/admin/profile'));
		}
	}

	public function setting()
	{
		$this->form_validation->set_rules('name', 'Company Name', 'required');
        $this->form_validation->set_rules('upi', 'Upi Id', 'required');
		if ($this->form_validation->run() == FALSE) {
	    	$data['data']        = $this->db_model->select_multi('*', 'tbl_settings', array('id' => 1));
	    	$data['title']       = 'Setting';
			$this->load->view('admin/setting/setting',$data);
		}else{
			$logoFilename = $_FILES["img"]["name"];
			$logoTempname = $_FILES["img"]["tmp_name"];
			$logoFolder   = "./axxests/setting/" . $logoFilename;
			if (!empty($logoFilename)) {
				move_uploaded_file($logoTempname, $logoFolder);
			}
			$qrFilename = $_FILES["qr"]["name"];
			$qrTempname = $_FILES["qr"]["tmp_name"];
			$qrFolder   = "./axxests/qr/" . $qrFilename;
			if (!empty($qrFilename)) {
				move_uploaded_file($qrTempname, $qrFolder);
			}
			$data = array(
				'name'   => $this->input->post('name'),
				'upi_id' => $this->input->post('upi'),
			);
			if (!empty($logoFilename)) {
				$data['logo'] = $logoFilename;
			}
			if (!empty($qrFilename)) {
				$data['qr_img'] = $qrFilename;
			}
			$where_condition = "id = 1";
			$this->db_model->update($data, 'tbl_settings', $where_condition);					
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Setting Updated.</div>');
            redirect(site_url('backend/admin/setting'));
		}
	}

	public function logout()
	{
		$this->session->sess_destroy();
		$this->session->set_flashdata('site_flash', '<div class="alert alert-success">Logout Successfully.</div>');
		redirect(site_url('backend/login'));
	}
}
