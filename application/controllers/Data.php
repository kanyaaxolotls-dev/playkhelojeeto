<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Data extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
        // $this->load->model('notifications_model'); 
        // $this->load->model('earning'); 
		if ($this->session->admin_id == NULL)
        {
            redirect(site_url('backend/login'));
        }
    }

    public function view_user($userid){
        $this->db->where('userid', $userid);
        $this->db->limit(15);
        $data['data'] = $this->db->get('tbl_deposit')->result();
        
        $this->db->where('userid', $userid);
        $this->db->limit(15);
        $data['data2'] = $this->db->get('tbl_withdraw')->result();
        
        $data['title']  = 'View User Data';
        $data['title2']  = 'Last 15 Recharge History';
        $data['title3']  = 'Last 15 Withdraw History';
        $data['detail'] = $this->db_model->select_multi('*', 'tbl_users', array('id' => $userid ));
        $this->load->view('admin/users/user_detail', $data);
    }
    
    public function todays_recharge(){
        $this->db->select('*')->where("DATE(date) =", date('Y-m-d'));
        $query = $this->db->get('tbl_deposit')->result();
        $view  = 'admin/wallet/deposit_history';
        $data['data']   = $query;
        $data['title']  = "Today's Requests Of Recharge";
        $this->load->view($view,$data);
    }
   
    public function todays_withdraw(){
        $this->db->select('*')->where("DATE(date) =", date('Y-m-d'));
        $query = $this->db->get('tbl_withdraw')->result();
        $view  = 'admin/wallet/withdraw_requests';
        $data['data']   = $query;
        $data['title']  = "Today's Requests Of Withdraw";
        $this->load->view($view,$data);
    }
   
    public function todays_users(){
        $this->db->select('*')->where("DATE(date)", date('Y-m-d'));
        $query = $this->db->get('tbl_users')->result_array();
        $view  = 'admin/users/users';
        $data['users']  = $query;
        $data['txt']    = '';
        $data['clr']    = '';
        $data['data']   = $query;
        $data['title']  = "New Registration";
        $this->load->view($view,$data);
    }

}