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
	public function admin_commission()
{
    // Get filter parameters
    $from_date = $this->input->get('from_date');
    $to_date = $this->input->get('to_date');
    $game_id = $this->input->get('game_id');
    
    $this->db->select('ac.*, g.name as game_name')
        ->from('tbl_admin_commissions ac')
        ->join('tbl_games g', 'g.id = ac.game_id', 'left');
    
    if($from_date) {
        $this->db->where('DATE(ac.created_at) >=', $from_date);
    }
    if($to_date) {
        $this->db->where('DATE(ac.created_at) <=', $to_date);
    }
    if($game_id && $game_id != 'all') {
        $this->db->where('ac.game_id', $game_id);
    }
    
    $this->db->order_by('ac.id', 'DESC');
    $commissions = $this->db->get()->result();
    
    // Calculate totals
    $total_admin_commission = array_sum(array_column($commissions, 'admin_commission'));
    $total_bet_amount = array_sum(array_column($commissions, 'total_bet_amount'));
    $total_rounds = count($commissions);
    $avg_commission = $total_rounds > 0 ? $total_admin_commission / $total_rounds : 0;
    
    $data = [
        'title' => 'Admin Commission Report',
        'commissions' => $commissions,
        'total_admin_commission' => $total_admin_commission,
        'total_bet_amount' => $total_bet_amount,
        'total_rounds' => $total_rounds,
        'avg_commission' => $avg_commission,
        'from_date' => $from_date,
        'to_date' => $to_date,
        'game_id' => $game_id
    ];
    
    $this->load->view('admin/header', $data);
    $this->load->view('admin/admin_commission_view', $data);
    $this->load->view('admin/footer');
}

public function admin_commission_details()
{
    $period_id = $this->input->get('period_id');
    $game_id = $this->input->get('game_id');
    
    // Get period summary
    $summary = $this->db->get_where('tbl_admin_commissions', [
        'period_id' => $period_id,
        'game_id' => $game_id
    ])->row();
    
    if(!$summary) {
        show_404();
    }
    
    // Get all bets for this period
    $bets = $this->db->select('b.*, u.name, u.username')
        ->from('tbl_lucky36_bet b')
        ->join('tbl_users u', 'u.id = b.userid')
        ->where('b.period_id', $period_id)
        ->where('b.game_id', $game_id)
        ->get()
        ->result();
    
    $total_dealer_commission = array_sum(array_column($bets, 'dealer_commission'));
    $total_distributor_commission = array_sum(array_column($bets, 'distributor_commission'));
    
    $data = [
        'title' => 'Commission Details - Period #' . $period_id,
        'period_id' => $period_id,
        'total_bet_amount' => $summary->total_bet_amount,
        'admin_commission' => $summary->admin_commission,
        'winning_number' => $summary->winning_number,
        'bets' => $bets,
        'total_dealer_commission' => $total_dealer_commission,
        'total_distributor_commission' => $total_distributor_commission
    ];
    
    $this->load->view('admin/header', $data);
    $this->load->view('admin/admin_commission_details', $data);
    $this->load->view('admin/footer');
}

public function export_admin_commission()
{
    $from_date = $this->input->get('from_date');
    $to_date = $this->input->get('to_date');
    $game_id = $this->input->get('game_id');
    
    $this->db->select('ac.*, g.name as game_name')
        ->from('tbl_admin_commissions ac')
        ->join('tbl_games g', 'g.id = ac.game_id', 'left');
    
    if($from_date) {
        $this->db->where('DATE(ac.created_at) >=', $from_date);
    }
    if($to_date) {
        $this->db->where('DATE(ac.created_at) <=', $to_date);
    }
    if($game_id && $game_id != 'all') {
        $this->db->where('ac.game_id', $game_id);
    }
    
    $commissions = $this->db->get()->result();
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="admin_commission_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Period ID', 'Game', 'Total Bet Amount', 'Admin Commission (20%)', 'Winning Number', 'Date']);
    
    foreach($commissions as $c) {
        fputcsv($output, [
            $c->id,
            $c->period_id,
            $c->game_name ?? 'Lucky36',
            $c->total_bet_amount,
            $c->admin_commission,
            $c->winning_number,
            $c->created_at
        ]);
    }
    
    fclose($output);
    exit();
}
}
