<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Reffer_earn extends CI_Controller {

    public function reffer_text()
    {
        $userid   = 'NG'.$this->input->post('userid');
        $base_url = base_url();
        // $base_url = base_url() . '/axxests/game.apk';
        $msg      = "Download Our APP and Enjoy With Your Friends Use the referral code:- $userid. Download the App now. Link:- $base_url";
        $response = array('status' => 'success', 'message' => $msg);
        $json_response = json_encode($response, JSON_UNESCAPED_SLASHES);
        $this->output->set_content_type('application/json')->set_output($json_response);
    }

    public function my_bonus()
    {
		$userid           = $this->input->post('userid');
        $total_bonus      = $this->db_model->sum('amount', 'tbl_earning', array('userid' => $userid,'status' => 'Paid')) + 0;
        $current_bonus    = $this->db_model->sum('amount', 'tbl_earning', array('userid' => $userid,'status' => 'Pending')) + 0;
        $past_day_bonus   = $this->db_model->sum('amount', 'tbl_earning', array('userid' => $userid,'date' => date('Y-m-d', strtotime('-1 day')))) + 0;
        if($userid){
            $response = array('status' => 'success', 'total' => $total_bonus, 'current' => $current_bonus, 'yesterday' => $past_day_bonus);
        }
        else{
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));    
    }

    public function rank()
    {
        $data            = $this->db_model->get_all_data('tbl_users');
        $rank_records = array();
        foreach ($data as $user) {
            $user_id              = $user->id;
            $rank_records[] = array(
                'rank'         => $user->rank,
                'gameid'       => $user->id,
                'prize'        => rand(100,1000),
            );
        }
    
        if ($rank_records) {
            $response = array('status' => 'success', 'data' => $rank_records);
        } else {
            $response = array('status' => 'error', 'message' => 'No data Available');
        }
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function my_bonus_records()
    {
        $userid     = $this->input->post('userid');
        $duration   = $this->input->post('duration'); 
        $start_date = '';

        if ($duration == 'last_7_days') {
            $start_date = date('Y-m-d', strtotime('-7 days'));
        } 
        elseif ($duration == 'last_month') {
            $start_date = date('Y-m-d', strtotime('-1 month'));
        }

        $where_condition = "userid = $userid";
        if ($start_date) {
            $where_condition .= " AND date >= '$start_date'";
        }

        $data = $this->db_model->get_specific_records('tbl_earning', $where_condition, 'id,amount,date');
        if ($data) {
            $response = array('status' => 'success', 'data' => $data);
        } else {
            $response = array('status' => 'error', 'message' => 'No data available for the selected duration');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function bonus_records_all()
    {
        $userid          = $this->input->post('userid');
        $where_condition = array('userid' => $userid);
        $data            = $this->db_model->get_specific_records('tbl_earning', $where_condition, 'amount,date,from_user,type');
        if ($data) {
            $response = array('status' => 'success', 'data' => $data);
        } else {
            $response = array('status' => 'error', 'message' => 'No data available for the selected duration');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function direct_team()
    {
        $userid          = $this->input->post('userid');
        $where_condition = array('referral_code' => $userid);
        $data            = $this->db_model->get_all_data('tbl_users', $where_condition);
        $earning_records = array();
    
        foreach ($data as $user) {
            $user_id              = $user->id;
            $user_bonus           = $this->db_model->sum('amount', 'tbl_earning', array('userid' => $user_id)) + 0;
            $user_today_day_bonus = $this->db_model->sum('amount', 'tbl_earning', array('userid' => $user_id,'DATE(date)' => date('Y-m-d'))) + 0;
    
            $earning_records[] = array(
                'userid'       => $user_id,
                'name'         => $user->name,
                'total_bonus'  => $user_bonus,
                'todays_bonus' => $user_today_day_bonus,
            );
        }
    
        if ($data) {
            $response = array('status' => 'success', 'data' => $earning_records);
        } else {
            $response = array('status' => 'error', 'message' => 'No data Available');
        }
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function claim_bonus()
    {
        $userid           = $this->input->post('userid');
        $check_bonus      = $this->db_model->count_all('tbl_earning', array('userid' => $userid, 'status' => 'Pending'));
    
        if ($check_bonus > 0) {
            $wallet_bal      = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
            $where_condition = array('userid' => $userid, 'status' => 'Pending');
            $data            = $this->db_model->get_all_data('tbl_earning', $where_condition);
            $bonus_amount    = 0;

            foreach ($data as $tr) {
                $bonus_amount += $tr->amount;
                $array = array(
                    'status'  => 'Paid',
                );
                $where_condition  = "id = " . $tr->id;
                $this->db_model->update($array, 'tbl_earning', $where_condition);
            }

            $wallet_bal += $bonus_amount;
            $array2 = array(
                'wallet'  => $wallet_bal,
            );
            $where_condition  = "id = " . $userid;
            $this->db_model->update($array2, 'tbl_users', $where_condition);
    
            $response = array('status' => 'success', 'msg' => 'Bonus Claimed');
        } 
        else {
            $response = array('status' => 'error', 'message' => 'No Bonus Available Now.');
        }
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    

}