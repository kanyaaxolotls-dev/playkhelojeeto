<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Aviator extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
		if ($this->session->admin_id == NULL)
        {
            redirect(site_url('backend/login'));
        }
    }

    public function results(){
        $where_condition    = "period_id >= 1";
        $data['data']       = $this->db_model->get_limited_records('tbl_aviator_results',500,'DESC',$where_condition);
        $data['title']      = 'Aviator Results';
        $this->load->view('admin/aviator/results',$data);
    }

    public function betting(){
        $where_condition    = "period_id >= 1";
        $data['data']       = $this->db_model->get_limited_records('tbl_aviator_bet',1000,'DESC',$where_condition);
        $data['title']      = 'Aviator Betting';
        $this->load->view('admin/aviator/betting',$data);
    }
}