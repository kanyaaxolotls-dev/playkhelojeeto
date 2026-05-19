<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Funtarget extends CI_Controller {

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
        $data['data']       = $this->db_model->get_all_data('tbl_spinner_results',$where_condition,'DESC');
        $data['title']      = 'Funtarget Results';
        $this->load->view('admin/funtarget/results',$data);
    }

    public function betting(){
        $where_condition    = "period_id >= 1";
        $data['data']       = $this->db_model->get_all_data('tbl_spinner_bet',$where_condition,'DESC');
        $data['title']      = 'Funtarget Betting';
        $this->load->view('admin/funtarget/betting',$data);
    }
}