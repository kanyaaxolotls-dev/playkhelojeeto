<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tournament extends CI_Controller {

    public function index()
	{
        $where_condition  = "status = 1";
        $show_data        = $this->db_model->get_specific_records('tbl_tournament', $where_condition, 'id,game_name,game_id');
        if($show_data){
            $response = array('status' => 'success', 'games' => $show_data);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function details($id = 1)
	{
        $tournament_data        = $this->db_model->select_multi('*', 'tbl_tournament', array('id' => $id));
        if($tournament_data){
            $response = array('status' => 'success', 'tournament_data' => $tournament_data,'registered_players' => $this->db_model->count_all('tbl_tournament_registration', array('tournament_id' => $tournament_data->id)));
        }
        else{
            $response = array('status' => 'error', 'message' => 'Something Went Wrong Try Again Later...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

    public function reward_data($id = 1)
	{
        $tournament_data        = $this->db_model->select_multi('rank,award', 'tbl_tournament', array('id' => $id));
        $rank_string  = $tournament_data->rank;
        $award_string = $tournament_data->award;
        
        $rank  = explode(",", $rank_string);
        $award = explode(",", $award_string);
        
        $rank_data = array();
        
        for ($i = 0; $i < count($rank); $i++) {
            $rank_data[$rank[$i]] = $award[$i];
        }

        if($tournament_data){
            $response = array('status' => 'success', 'rank_rewards' => $rank_data);
        }
        else{
            $response = array('status' => 'error', 'message' => 'Something Went Wrong Try Again Later...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
    
    public function register()
	{
        $userid           = $this->input->post('userid');
        $tournament_id    = $this->input->post('tournament_id');
        if($this->db_model->count_all('tbl_users', array('id' => $userid)) <= 0){
            $response = array('status' => 'error','message' => 'Invalid Userid');
        }
        elseif($this->db_model->count_all('tbl_tournament', array('id' => $tournament_id)) <= 0){
            $response = array('status' => 'error','message' => 'Invalid Tournament Id Passed');
        }
        else{
            $tournament_data  = $this->db_model->select_multi('*', 'tbl_tournament', array('id' => $tournament_id));
            $ticket_type      = 'Daily';
            $tournament_type  = 'Daily Tournament';
            $expiry_time      = $tournament_data->end_date;
            $wallet_bal       = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
            $amount           = $tournament_data->register_fee;
            if($wallet_bal < $amount){
                $response = array('status' => 'error','message' => 'Insufficient Balance');
            }
            else{
                $array = array(
                    'wallet'  => $wallet_bal - $amount,
                );
                $where_condition  = "id = ".$userid;
                $this->db_model->update($array,'tbl_users',$where_condition);
                $array   = array(
                    'userid'            => $userid,
                    'tournament_id'     => $tournament_id,
                    'ticket_type'       => $ticket_type,
                    'tournament_type'   => $tournament_type,
                    'expiry_time'       => $expiry_time,
                    'status'            => 'Running',
                    'collection_time'   => date('Y-m-d H:i:s'),
                );
                $this->db->insert('tbl_tournament_registration', $array);
                $response = array('status' => 'success', 'message' => 'User Entry Registered..');
            }
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function my_tickets()
	{
		$userid          = $this->input->post('userid');
        $where_condition = "userid = ".$userid;
        $show_data       = $this->db_model->get_specific_records('tbl_tournament_registration', $where_condition, '*');
        if($show_data){
            $response = array('status' => 'success', 'data' => $show_data);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function my_tournaments()
    {
        $userid = $this->input->post('userid');
        $this->db->select('t.game_name, t.start_date, t.id as tournament_id, tr.id as ticket_id, tr.rank, tr.status, tr.winning_amount');
        $this->db->from('tbl_tournament t');
        $this->db->join('tbl_tournament_registration tr', 't.id = tr.tournament_id');
        $this->db->where('tr.userid', $userid);
        $query = $this->db->get();
        $tournaments = $query->result();
    
        if ($tournaments) {
            $response = array('status' => 'success', 'data' => $tournaments);
        } else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function rank_data()
    {
        $where_condition = "id != 0";
        $show_data = $this->db_model->get_specific_records('tbl_users', $where_condition, 'name');
        if ($show_data) {
            $data = array();
            foreach ($show_data as $tr) {
                $user_data = array(
                    'name'   => $tr->name,
                    'awards' => rand(1000, 5000)
                );
                $data[] = $user_data;
            }
            $response = array('status' => 'success', 'data' => $data);
        } else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
}