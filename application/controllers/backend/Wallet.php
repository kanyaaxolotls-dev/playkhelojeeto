<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wallet extends CI_Controller {

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

    public function fund_requests_p(){
        $this->db->select('*')->where('status','Pending');
        $data['title']  = 'Pending Requests' ;
		$data['title2'] = 'Deposite History' ;
        $data['data']   = $this->db->get('tbl_deposit')->result();
        $this->load->view('admin/wallet/deposit_history',$data);
    }

    public function fund_requests_r(){
        $this->db->select('*')->where('status','Failed');
        $data['title']  = 'Failed Requests' ;
		$data['title2'] = 'Deposite History' ;
        $data['data'] = $this->db->get('tbl_deposit')->result();
        $this->load->view('admin/wallet/deposit_history',$data);
    }

    public function fund_requests_a(){
        $this->db->select('*')->where('status','Paid');
        $data['title']  = 'Paid Requests' ;
		$data['title2'] = 'Deposite History' ;
        $data['data'] = $this->db->get('tbl_deposit')->result();
        $this->load->view('admin/wallet/deposit_history',$data);
    }

    public function withdraw_requests_p(){
        $this->db->select('*')->where('staus','Proccessing');
        $data['title']  = 'Proccessing Transactions' ;
		$data['title2'] = 'Transactions' ;
        $data['data'] = $this->db->get('tbl_withdraw')->result();
        $this->load->view('admin/wallet/withdraw_requests',$data);
    }

    public function withdraw_requests_h(){
        $this->db->select('*')->where('staus','Hold');
        $data['title']  = 'Hold Transactions' ;
		$data['title2'] = 'Transactions' ;
        $data['data'] = $this->db->get('tbl_withdraw')->result();
        $this->load->view('admin/wallet/withdraw_requests',$data);
    }

    public function withdraw_requests_a(){
        $this->db->select('*')->where('staus','Paid');
        $data['title']  = 'Paid Transactions' ;
		$data['title2'] = 'Transactions' ;
        $data['data'] = $this->db->get('tbl_withdraw')->result();
        $this->load->view('admin/wallet/withdraw_requests',$data);
    }

    public function withdraw_requests_r(){
        $this->db->select('*')->where('staus','Rejected');
        $data['title']  = 'Rejected Transactions' ;
		$data['title2'] = 'Transactions' ;
        $data['data'] = $this->db->get('tbl_withdraw')->result();
        $this->load->view('admin/wallet/withdraw_requests',$data);
    }

    public function approve_fund_request($id){
        $balance     = $this->db_model->select_multi('amount,userid', 'tbl_deposit', array('id' => $id));
        
        $array = array(
            'status'           => 'Paid', 
            'response_date'    => date('Y-m-d H:i:s'), 
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_deposit', $array);
        
        $w_id    = $balance->userid;
        $w_amt   = $balance->amount;
        $this->db->query("UPDATE tbl_users SET wallet = wallet+$w_amt WHERE id = '$w_id'"); 
        // $this->earning->level_inc($w_id,$this->db_model->select('sponsor', 'tbl_users', array('id' => $w_id)),$w_amt);
        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Wallet Balance Approved</div>');
        redirect('backend/Wallet/fund_requests_p');
    }

    public function reject_fund_request($id){
        $array   = array(
            'status' => 'Failed', 
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_deposit', $array);
        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Wallet Balance Request Rejected</div>');
        redirect('backend/Wallet/fund_requests_p');
    }

    public function action_withdraw_requests($id,$status = "Proccessing"){
        if($status === 'Rejected'){
            $balance     = $this->db_model->select_multi('*', 'tbl_withdraw', array('id' => $id));
            $this->db->query("UPDATE tbl_users SET winning_wallet = winning_wallet + $balance->amount WHERE id = '$balance->userid'"); 
        }
        $array   = array(
            'staus'     => $status, 
            'paid_date' => date('Y-m-d H:i:s'), 
        );
        $this->db->where('id', $id);
        $this->db->update('tbl_withdraw', $array);
        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Withdraw Request Marked As a '.$status.'</div>');
        redirect('backend/Wallet/withdraw_requests_p');
    }

}

