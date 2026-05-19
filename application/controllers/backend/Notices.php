<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Notices extends CI_Controller {

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
	    $cat_name    = $this->input->post('cat_name');
	    if($name == NULL){
			$where_condition    = "status = 1";
			$data['data']       = $this->db_model->get_all_data('tbl_notices',$where_condition);
            $data['title']      = 'Manage Notices';
            $this->load->view('admin/notices/manage_notice',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_notices', array('title' => $name));
            $filename      = $_FILES["img"]["name"];
			$tempname      = $_FILES["img"]["tmp_name"];
			$folder        = "./axxests/notice_img/" . $filename;
			move_uploaded_file($tempname, $folder);
	        if($cat_chec == 0){
	            $array = array(
                    'title'        => $name,
                    'category'     => $cat_name,
                    'img'          => $filename,
                    'status'       => 1,
                );
                $this->db->insert('tbl_notices', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">New Notice Added.</div>');
                redirect(site_url('backend/Notices'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Game Already Exist.</div>');
                redirect(site_url('backend/Notices'));
	        }
	    }
    }

    public function edit_notice($id)
	{
		$data['title']    = 'Edit Notice';
        $data['detail']   = $this->db_model->select_multi('*', 'tbl_notices', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/notices/edit_notice',$data);
	}

    public function update_notice()
	{
        $name          = $this->input->post('name');
	    $cat_name      = $this->input->post('cat_name');
	    $id            = $this->input->post('id');
	    $cat_chec      = $this->db_model->count_all('tbl_notices', array('title' => $name,'id !=' => $id));
        $filename      = $_FILES["img"]["name"];
		$tempname      = $_FILES["img"]["tmp_name"];
		$folder        = "./axxests/notice_img/" . $filename;
		move_uploaded_file($tempname, $folder);
		if($filename == NULL){
			$filename = $this->db_model->select('img', 'tbl_notices', array('id' => $id));
		}
	    if($cat_chec == 0){
			$array = array(
                'title'        => $name,
                'category'     => $cat_name,
                'img'          => $filename,
			);
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_notices',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Notice Updated.</div>');
            redirect(site_url('backend/notices/edit_notice/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Notice With This Name Already Exist.</div>');
            redirect(site_url('backend/notices/edit_notice/'.$id));
	    }
	}

}