<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Support extends CI_Controller {

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
        $name        = $this->input->post('name');
	    $link        = $this->input->post('link');
	    if($name == NULL){
			$where_condition    = "status = 1";
			$data['data']       = $this->db_model->get_all_data('tbl_support',$where_condition);
            $data['title']      = 'Manage support';
            $this->load->view('admin/support/manage_support',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_support', array('name' => $name));
            $filename      = $_FILES["img"]["name"];
			$tempname      = $_FILES["img"]["tmp_name"];
			$folder        = "./axxests/support/" . $filename;
			move_uploaded_file($tempname, $folder);
	        if($cat_chec == 0){
	            $array = array(
                    'name'         => $name,
                    'link'         => $link,
                    'img'          => $filename,
                    'status'       => 1,
                );
                $this->db->insert('tbl_support', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">New Support Added.</div>');
                redirect(site_url('backend/support'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Support Option Already Exist.</div>');
                redirect(site_url('backend/support'));
	        }
	    }
    }

    public function edit_support($id)
	{
		$data['title']    = 'Edit Support';
		$data['detail']   = $this->db_model->select_multi('*', 'tbl_support', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/support/edit_support',$data);
	}

    public function update_support()
	{
	    $name          = $this->input->post('name');
	    $link          = $this->input->post('link');
	    $id            = $this->input->post('id');
	    $cat_chec      = $this->db_model->count_all('tbl_support', array('name' => $name,'id !=' => $id));
        $filename      = $_FILES["img"]["name"];
		$tempname      = $_FILES["img"]["tmp_name"];
		$folder        = "./axxests/support/" . $filename;
		move_uploaded_file($tempname, $folder);
		if($filename == NULL){
			$filename = $this->db_model->select('img', 'tbl_support', array('id' => $id));
		}
	    if($cat_chec == 0){
	        $array = array(
                'name'         => $name,
                'link'         => $link,
                'img'          => $filename,
            );
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_support',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Support Updated.</div>');
            redirect(site_url('backend/support/edit_support/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Support Name Already Exist.</div>');
            redirect(site_url('backend/support/edit_support/'.$id));
	    }
	}

    public function delete_support($id)
	{
		$where_condition  = "id = ".$id;
		$array            =  ['status'   => 0];
		$result           = $this->db_model->update($array,'tbl_support',$where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Support Deleted.</div>');
        redirect(site_url('backend/support'));
	}

}