<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Car_roullete extends CI_Controller {

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
        $data['data']       = $this->db_model->get_limited_records('tbl_car_results',500,'DESC',$where_condition);
        $data['title']      = 'Car Roullete Results';
        $this->load->view('admin/car_roullete/results',$data);
    }

    public function betting(){
        $where_condition    = "period_id >= 1";
        $data['data']       = $this->db_model->get_limited_records('tbl_car_betting',1000,'DESC',$where_condition);
        $data['title']      = 'Car Roullete Betting';
        $this->load->view('admin/car_roullete/betting',$data);
    }
}