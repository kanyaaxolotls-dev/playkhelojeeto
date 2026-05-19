<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Cpg extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
		if ($this->session->admin_id == NULL)
        {
            redirect(site_url('backend/login'));
        }
    }

    public function betting(){
        $where_condition    = "period >= 1";
        $data['data']       = $this->db_model->get_all_data('emredbetting',$where_condition,'DESC');
        $data['title']      = 'Color Prediction Betting';
        $this->load->view('admin/cpg/betting',$data);
    }

    public function results(){
        $where_condition    = "period >= 1";
        $data['data']       = $this->db_model->get_all_data('emredbetrec',$where_condition,'DESC');
        $data['title']      = 'Color Prediction Results';
        $this->load->view('admin/cpg/results',$data);
    }

    public function man_result(){
        $this->load->model('EmredModel');
        $data['result']   = $this->EmredModel->getResult();
        $data['title']    = 'Color Prediction Manual Result';
        $this->load->view('admin/cpg/man_ress',$data);
    }

    public function set_manual(){
        $number  = $this->input->post('number');
		$data    = array(
			'nxt'   => $number,
		);
        $this->db->where('id', 1);
        $this->db->update('emredperiod', $data);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-success">Result Set.</div>');
		redirect(site_url('backend/cpg/man_result'));
    }
}