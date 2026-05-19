<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Games extends CI_Controller {

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
	    $name      = $this->input->post('name');
	    $cat_id    = $this->input->post('cat_id');
	    if($name == NULL){
			$where_condition    = "status = 1";
			$data['game_cat']   = $this->db_model->get_all_data('tbl_game_category',$where_condition);
			$data['games']      = $this->db_model->get_all_data('tbl_games','1 = 1');
            $data['title']      = 'Manage Games';
            $this->load->view('admin/games/manage_game',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_games', array('name' => $name));
            $filename      = $_FILES["img"]["name"];
			$tempname      = $_FILES["img"]["tmp_name"];
			$folder        = "./axxests/game_img/" . $filename;
			move_uploaded_file($tempname, $folder);
	        if($cat_chec == 0){
	            $array = array(
                    'name'     => $name,
                    'img'      => $filename,
                    'status'   => 1,
                );
                $this->db->insert('tbl_games', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">New Game Added.</div>');
                redirect(site_url('backend/games'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Game Already Exist.</div>');
                redirect(site_url('backend/games'));
	        }
	    }
	}

    public function game_cat()
	{
	    $name      = $this->input->post('name');
	    if($name == NULL){
			$where_condition  = "status = 1";
			$data['games']    = $this->db_model->get_all_data('tbl_game_category',$where_condition);
            $data['title']    = 'Manage Game Categories';
            $this->load->view('admin/games/game_cat',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_games', array('name' => $name));
	        if($cat_chec == 0){
	            $array = array(
                    'name'     => $name,
                    'status'   => 1,
                );
                $this->db->insert('tbl_game_category', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">New Game Category Added.</div>');
                redirect(site_url('backend/games/game_cat'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Game Already Exist.</div>');
                redirect(site_url('backend/games/game_cat'));
	        }
	    }
	}

    public function gateway()
	{
	    $name     = $this->input->post('name');
	    $webview  = $this->input->post('url');
	    if($name == NULL){
			$where_condition  = "status = 1";
			$data['gateways'] = $this->db_model->get_all_data('tbl_gateways',$where_condition);
            $data['title']    = 'Manage Payment Gateway';
            $this->load->view('admin/games/gateway',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_games', array('name' => $name));
	        if($cat_chec == 0){
				$filename      = $_FILES["img"]["name"];
				$tempname      = $_FILES["img"]["tmp_name"];
				$folder        = "./axxests/img/" . $filename;
				move_uploaded_file($tempname, $folder);
	            $array = array(
                    'img'           => $filename,
                    'name'          => $name,
                    'webview_link'  => $webview,
                    'status'        => 1,
                );
                $this->db->insert('tbl_gateways', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">New Payment Gateway Added.</div>');
                redirect(site_url('backend/games/gateway'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">This Payment Gateway Already Exist.</div>');
                redirect(site_url('backend/games/gateway'));
	        }
	    }
	}

    public function manage_chips()
	{
	    $amount      = $this->input->post('amount');
	    $p_amount    = $this->input->post('p_amount');
	    if($amount == NULL){
			$where_condition  = "status = 1";
			$data['chips']    = $this->db_model->get_all_data('tbl_chips',$where_condition);
            $data['title']    = 'Manage Shop Chips';
            $this->load->view('admin/games/manage_chips',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_chips', array('amount' => $amount,'status' => 1));
            $filename      = $_FILES["img"]["name"];
			$tempname      = $_FILES["img"]["tmp_name"];
			$folder        = "./axxests/chips_img/" . $filename;
			move_uploaded_file($tempname, $folder);
	        if($cat_chec == 0){
	            $array = array(
                    'amount'     => $amount,
                    'principle'  => $p_amount,
                    'img'        => $filename,
                    'status'     => 1,
                );
                $this->db->insert('tbl_chips', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Chips Added.</div>');
                redirect(site_url('backend/games/manage_chips'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Chips Amount Already Exist.</div>');
                redirect(site_url('backend/games/manage_chips'));
	        }
	    }
	}

    public function play_chips()
	{
	    $amount      = $this->input->post('amount');
	    if($amount == NULL){
			$where_condition  = "status = 1";
			$data['chips']    = $this->db_model->get_all_data('tbl_play_chips',$where_condition);
            $data['title']    = 'Manage Play Chips';
            $this->load->view('admin/games/play_chips',$data);
	    }
	    else{
	        $cat_chec      = $this->db_model->count_all('tbl_play_chips', array('amount' => $amount,'status' => 1));
            $filename      = $_FILES["img"]["name"];
			$tempname      = $_FILES["img"]["tmp_name"];
			$folder        = "./axxests/chips_img/" . $filename;
			move_uploaded_file($tempname, $folder);
	        if($cat_chec == 0){
	            $array = array(
                    'amount'     => $amount,
                    'img'        => $filename,
                    'status'     => 1,
                );
                $this->db->insert('tbl_play_chips', $array);
                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Chip Added.</div>');
                redirect(site_url('backend/games/play_chips'));
	        }
	        else{
	            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Chip Amount Already Exist.</div>');
                redirect(site_url('backend/games/play_chips'));
	        }
	    }
	}

    public function update_game()
	{
	    $name          = $this->input->post('name');
	    $id            = $this->input->post('id');
	    $cat_id        = $this->input->post('cat_id');
	    $cat_chec      = $this->db_model->count_all('tbl_games', array('name' => $name,'id !=' => $id));
        $filename      = $_FILES["img"]["name"];
		$tempname      = $_FILES["img"]["tmp_name"];
		$folder        = "./axxests/game_img/" . $filename;
		move_uploaded_file($tempname, $folder);
		if($filename == NULL){
			$filename = $this->db_model->select('img', 'tbl_games', array('id' => $id));
		}
	    if($cat_chec == 0){
	        $array = array(
                'name'     => $name,
                'img'      => $filename,
                'cat_id'   => $cat_id,
            );
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_games',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Game Updated.</div>');
            redirect(site_url('backend/games/edit_game/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Game Name Already Exist.</div>');
            redirect(site_url('backend/games/edit_game/'.$id));
	    }
	}

    public function update_chips()
	{
	    $amount        = $this->input->post('amount');
	    $id            = $this->input->post('id');
	    $p_amount      = $this->input->post('p_amount');
	    $cat_chec      = $this->db_model->count_all('tbl_chips', array('amount' => $amount,'id !=' => $id));
        $filename      = $_FILES["img"]["name"];
		$tempname      = $_FILES["img"]["tmp_name"];
		$folder        = "./axxests/chips_img/" . $filename;
		move_uploaded_file($tempname, $folder);
		if($filename == NULL){
			$filename = $this->db_model->select('img', 'tbl_chips', array('id' => $id));
		}
	    if($cat_chec == 0){
			$array = array(
				'amount'     => $amount,
				'principle'  => $p_amount,
				'img'        => $filename,
			);
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_chips',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Chip Updated.</div>');
            redirect(site_url('backend/games/edit_chips/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Chip Amount Already Exist.</div>');
            redirect(site_url('backend/games/edit_chips/'.$id));
	    }
	}

    public function update_play_chips()
	{
	    $amount        = $this->input->post('amount');
	    $id            = $this->input->post('id');
	    $cat_chec      = $this->db_model->count_all('tbl_play_chips', array('amount' => $amount,'id !=' => $id));
        $filename      = $_FILES["img"]["name"];
		$tempname      = $_FILES["img"]["tmp_name"];
		$folder        = "./axxests/chips_img/" . $filename;
		move_uploaded_file($tempname, $folder);
		if($filename == NULL){
			$filename = $this->db_model->select('img', 'tbl_play_chips', array('id' => $id));
		}
	    if($cat_chec == 0){
			$array = array(
				'amount'     => $amount,
				'img'        => $filename,
			);
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_play_chips',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Chip Updated.</div>');
            redirect(site_url('backend/games/edit_play_chips/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Chip Amount Already Exist.</div>');
            redirect(site_url('backend/games/edit_play_chips/'.$id));
	    }
	}

    public function update_spin()
	{
	    $amount        = $this->input->post('amount');
	    $id            = $this->input->post('id');
	    if($id){
			$array = array(
				'amount'     => $amount,
			);
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_spins',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Spin Value Updated.</div>');
            redirect(site_url('backend/games/edit_spin/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Something Went Wrong...</div>');
            redirect(site_url('backend/games/edit_spin/'.$id));
	    }
	}

    public function amount_setting()
	{
	    $min_deposit    = $this->input->post('min_deposit');
	    $max_deposit    = $this->input->post('max_deposit');
	    $min_withdraw   = $this->input->post('min_withdraw');
	    $max_withdraw   = $this->input->post('max_withdraw');
	    $with_charges   = $this->input->post('with_charges');
	    $level_inc      = $this->input->post('level_inc');
	    $id             = $this->input->post('id');
	    if($id){
			$array = array(
				'min_deposit'     => $min_deposit,
				'max_deposit'     => $max_deposit,
				'min_withdraw'    => $min_withdraw,
				'max_withdraw'    => $max_withdraw,
				'with_charges'    => $with_charges,
				'level_inc'       => $level_inc,
			);
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_settings',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Amount Settings Updated</div>');
            redirect(site_url('backend/games/amount_setting'));
	    }
	    else{
			$data['title']    = 'Edit Amount Setting';
			$data['detail']   = $this->db_model->select_multi('*', 'tbl_settings', array('id' => 1));
			$this->load->view('admin/setting/amount',$data);
	    }
	}

    public function edit_game($id)
	{
		$data['title']    = 'Edit Game';
		$where_condition  = "status = 1";
		$data['game_cat'] = $this->db_model->get_all_data('tbl_game_category',$where_condition);
		$data['detail']   = $this->db_model->select_multi('*', 'tbl_games', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/games/edit_game',$data);
	}

    public function edit_chips($id)
	{
		$data['title']    = 'Edit Chips';
		$where_condition  = "status = 1";
		$data['detail']   = $this->db_model->select_multi('*', 'tbl_chips', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/games/edit_chips',$data);
	}

    public function edit_play_chips($id)
	{
		$data['title']    = 'Edit Play Chips';
		$where_condition  = "status = 1";
		$data['detail']   = $this->db_model->select_multi('*', 'tbl_play_chips', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/games/edit_play_chips',$data);
	}

    public function edit_spin($id)
	{
		$data['title']    = 'Edit Spin Values';
		$where_condition  = "status = 1";
		$data['detail']   = $this->db_model->select_multi('*', 'tbl_spins', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/games/edit_spin_values',$data);
	}

    public function delete_game($id,$status)
	{
		if ($status == 1) {
			$stat = 'success';
			$msg = 'Game Activated';
		} else {
			$stat = 'danger';
			$msg = 'Game Deleted';
		}
		
		$where_condition = "id = " . $id;
		$array = ['status' => $status];
		$result = $this->db_model->update($array, 'tbl_games', $where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-' . $stat . '">' . $msg . '</div>'); // Fixed the syntax error here
		redirect(site_url('backend/games'));		
	}

    public function delete_game_cat($id)
	{
		$where_condition  = "id = ".$id;
		$array            =  ['status'   => 0];
		$result           = $this->db_model->update($array,'tbl_game_category',$where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Game Category Deleted.</div>');
        redirect(site_url('backend/games/game_cat'));
	}

    public function delete_chips($id)
	{
		$where_condition  = "id = ".$id;
		$array            =  ['status'   => 0];
		$result           = $this->db_model->update($array,'tbl_chips',$where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Chips Deleted.</div>');
        redirect(site_url('backend/games/manage_chips'));
	}

    public function delete_play_chips($id)
	{
		$where_condition  = "id = ".$id;
		$array            =  ['status'   => 0];
		$result           = $this->db_model->update($array,'tbl_play_chips',$where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Chips Deleted.</div>');
        redirect(site_url('backend/games/play_chips'));
	}

    public function delete_gateway($id)
	{
		$where_condition  = "id = ".$id;
		$array            =  ['status'   => 0];
		$result           = $this->db_model->update($array,'tbl_gateways',$where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Gateway Deleted.</div>');
        redirect(site_url('backend/games/play_chips'));
	}

    public function delete_spin($id)
	{
		$where_condition  = "id = ".$id;
		$array            =  ['status'   => 0];
		$result           = $this->db_model->update($array,'tbl_spins',$where_condition);
		$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Spin Value Deleted.</div>');
        redirect(site_url('backend/games/spin'));
	}

    public function spin()
	{
	    $amount      = $this->input->post('amount');
	    if($amount == NULL){
			$where_condition  = "status = 1";
			$data['spins']    = $this->db_model->get_all_data('tbl_spins',$where_condition);
            $data['title']    = 'Manage Spin Values';
            $this->load->view('admin/games/spin',$data);
	    }
	    else{
	        $array = array(
                'amount'     => $amount,
                'status'     => 1,
            );
            $this->db->insert('tbl_spins', $array);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Spin Value Added.</div>');
            redirect(site_url('backend/games/spin'));
	    }
	}

}

