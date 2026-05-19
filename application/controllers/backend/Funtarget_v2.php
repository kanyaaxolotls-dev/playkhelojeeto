<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Funtarget_v2 extends CI_Controller {

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
        $data['data']       = $this->db_model->get_all_data('tbl_funtarget_results',$where_condition,'DESC');
        $data['title']      = 'Funtarget Results';
        $this->load->view('admin/funtarget_v2/results',$data);
    }

    public function betting(){
        $where_condition    = "period_id >= 1";
        $data['data']       = $this->db_model->get_all_data('tbl_funtarget_bet',$where_condition,'DESC');
        $data['title']      = 'Funtarget Betting';
        $this->load->view('admin/funtarget_v2/betting',$data);
    }

    public function update_secret()
    {
        $secret    = $this->input->post('secret');
        $period_id = $this->input->post('period_id');
        $is_joker  = $this->input->post('is_joker');

        if ($secret !== null && is_numeric($secret) && $secret >= 0 && $secret <= 9) {
            $ct = $this->db_model->count_all('tbl_funtarget_results', array('period_id' => $period_id));
            if ($ct == 0) {
                $this->db->where('id', 7)->update('tbl_games', ['win_number' => $secret, 'manual_set' => 1, 'is_joker' => 1]);
                $this->session->set_flashdata('success', 'Result Updated.');
            } else {
                $this->session->set_flashdata('error', 'Result Already Declared');
            }
        } else {
            $this->session->set_flashdata('error', 'Please enter a valid number between 0 and 9.');
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

}