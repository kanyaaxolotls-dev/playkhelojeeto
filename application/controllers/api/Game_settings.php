<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Game_settings extends CI_Controller {
  
  public function timer()
	{
        $time = date('H:i:s');
        if ($time) {
            $response = $time;
        } 
        else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
  
	public function games()
	{
        $game_cat        = $this->input->post('game_category') ?? 'All';
        $where_condition = "status = 1";
        if ($game_cat !== 'All') {
            $where_condition .= " AND cat_id = '" . $this->db->escape_str($game_cat) . "'";
        }
        $show_games = $this->db_model->get_specific_records('tbl_games', $where_condition, 'id, name');
        if($show_games){
            $response = array('status' => 'success', 'games' => $show_games);
        }
        else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function shop_chips()
	{
        $img_path        = base_url('axxests/chips_img/');
        $where_condition = "status = 1";
        $show_games      = $this->db_model->get_specific_records('tbl_chips', $where_condition, 'id, img, amount, principle');
        if($show_games){
            $response = array('status' => 'success', 'data' => $show_games,'img_path' => $img_path);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function play_chips()
	{
        $img_path        = base_url('axxests/chips_img/');
        $where_condition = "status = 1";
        $show_games      = $this->db_model->get_specific_records('tbl_play_chips', $where_condition, 'id, img, amount');
        if($show_games){
            $response = array('status' => 'success', 'data' => $show_games,'img_path' => $img_path);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function payment_gateways()
	{
        $img_path        = base_url('axxests/img/');
        $where_condition = "status = 1";
        $show_games      = $this->db_model->get_specific_records('tbl_gateways', $where_condition, 'id, name ,webview_link, img');
        if($show_games){
            $response = array('status' => 'success', 'data' => $show_games,'img_path' => $img_path);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function get_qr_upi()
	{
        $where_condition = "id = 1";
        $img_path        = base_url('axxests/qr/');
        $show_data       = $this->db_model->get_specific_records('tbl_settings', $where_condition, 'qr_img,upi_id');
        if($show_data){
            $response = array('status' => 'success', 'data' => $show_data,'img_path' => $img_path);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function spin_values()
	{
        $where_condition = "status = 1";
        $show_spins      = $this->db_model->get_specific_records('tbl_spins', $where_condition, 'id, amount');
        if($show_spins){
            $response = array('status' => 'success', 'data' => $show_spins);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function spin_count()
	{
        $userid          = $this->input->post('userid');
        $daily_spin      = $this->db_model->select('daily_spin', 'tbl_settings', array('id' => 1));
        $count_pre_spins = $this->db_model->count_all('tbl_transactions', array('userid' => $userid,'DATE(date)' => date('Y-m-d'),'type' => 'Daily Spin Bonus'));
        $spin_count      = $daily_spin - $count_pre_spins;
        if($spin_count > 0){
            $response = array('status' => 'success', 'spin_count' => $spin_count);
        }
        else{
            $response = array('status' => 'error', 'message' => 'Daily Spin Claimed');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function lvl_inc_setting()
	{
        $where_condition = "status = 1";
        $show_inc        = $this->db_model->get_specific_records('tbl_lvl_inc',$where_condition, 'members, amount');
        if($show_inc){
            $response = array('status' => 'success', 'data' => $show_inc);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function support_list()
	{
        $img_path        = base_url('axxests/support/');
        $where_condition = "status = 1";
        $show_inc        = $this->db_model->get_specific_records('tbl_support',$where_condition, 'name, link, img');
        if($show_inc){
            $response = array('status' => 'success', 'data' => $show_inc, 'img_path' => $img_path);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function notices()
	{
        $img_path         = base_url('axxests/notice_img/');
        $where_condition  = "status = 1";
        $show_data        = $this->db_model->get_specific_records('tbl_notices',$where_condition, 'title, category, img');
        if($show_data){
            $response = array('status' => 'success', 'data' => $show_data, 'img_path' => $img_path);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
  
	public function cpg_link()
	{
        $response = array('status' => 'success', 'link' => 'https://royalpanda.club/cpg/login.php');
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function user_note()
	{
        $max_id              = $this->db->select_max('id')->where('show_app', 'show')->get('tbl_transactions')->row()->id;
        if($max_id ){
            $userdata            = $this->db_model->select_multi('*', 'tbl_transactions', array('id' => $max_id));
            $show_data['id']     = $userdata->userid;
            $show_data['name']   = $this->db_model->select('name', 'tbl_users', array('id' => $userdata->userid));
            $show_data['game']   = str_replace('Winning', '', $userdata->type);
            $show_data['amount'] = number_format((float) $userdata->amount, 0, '.', ',');        
            $response            = array('status' => 'success', 'data' => $show_data);
        }else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}