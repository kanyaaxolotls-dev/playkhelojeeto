<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Car_roullete extends CI_Controller {

    public function __construct() {
        parent::__construct();
        date_default_timezone_set('Asia/Kolkata');
    }

    public function index(){
        $data      = $this->db_model->select_multi('id, name, period_id', 'tbl_games', array('id' => 6));
        $response  = array('status' => 'success', 'message' => 'Game data found', 'data' => $data);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function cron(){
        $period  = $this->db_model->select('period_id', 'tbl_games', array('id' => 6));
        $win_img = $this->chek_min_betting($period);
        $this->db->select('*');
        $this->db->from('tbl_car_betting');
        $this->db->where('status', 'Proccessing');
        $this->db->where('period_id', $period);
        $query   = $this->db->get();
        $results = $query->result();
        $win_sum = [];
        $all_sum = [];
        foreach($results as $tr){
            $all_sum[] = $tr->bet_amount;
            if($tr->img_id == $win_img){
               $win_amount = $tr->bet_amount * 5;
               $wallet = $this->db_model->select('wallet', 'tbl_users', array('id' => $tr->userid));
               $array  = array(
                   'wallet'  => $wallet + $win_amount,
               );
               $this->db->where('id', $tr->userid);
               $this->db->update('tbl_users', $array);
               $status = 'Won';
               $win_sum[] = $win_amount;
            }
            else{
               $status = 'Loss';
            }
            $array = array(
               'status'       => $status,
               'result_date'  => date('Y-m-d H:i:s'),
            );
            $this->db->where('id', $tr->id);
            $this->db->update('tbl_car_betting', $array);
        }
 
        $data = array(
            'img_id'      => $win_img,
            'period_id'   => $period,
            'win_amount'  => array_sum($win_sum),
            'bet_amount'  => array_sum($all_sum),
            'date'        => date("Y-m-d H:i:s"),
        );
        $this->db->insert('tbl_car_results',$data);

        $array = array(
            'period_id'  => $period + 1,
            'date'       => date("Y-m-d H:i:s"),
        );
        $this->db->where('id', 6);
        $this->db->update('tbl_games', $array);
        $response  = array('status' => 'success', 'message' => 'Results updated successfully!');
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function get_wallet_user(){
        $userid      = $this->input->post('userid');
        $chek_user   = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if ($chek_user != 0) {
            $wallet   = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
            $response = array('status' => 'success', 'wallet_balance' => $wallet);
        } 
        else {
            $response = array('status' => 'error', 'message' => 'Invalid User.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function timer()
    {
        $currentTime   = time();
        $currentSecond = ($currentTime % 60) - 3;
        if ($currentSecond < 0) {
            $currentSecond += 60;
        }
    
        $phase = '';
        if ($currentSecond >= 0 && $currentSecond <= 15) {
            $phase = 'betting';
        } elseif ($currentSecond >= 16 && $currentSecond <= 30) {
            $phase = 'result';
        } elseif ($currentSecond >= 31 && $currentSecond <= 45) {
            $phase = 'betting';
        } elseif ($currentSecond >= 46 && $currentSecond <= 60) {
            $phase = 'result';
        } else{
            $phase = 'undefined';
        }
        $response = array(
            'time'           => date('H:i:s', $currentTime),
            'phase'          => $phase,
            'current_second' => $currentSecond
        );
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function place_bet(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userid       = $this->input->post('userid');
            $img_id       = $this->input->post('img_id');
            $bet_amount   = $this->input->post('bet_amount');
            $period       = $this->input->post('period');
            $chek_period  = $this->db_model->count_all('tbl_games', array('id' => 6, 'period_id' => $period));
            $chek_user    = $this->db_model->count_all('tbl_users', array('id' => $userid));
            if($chek_user <= 0){
                $response = array('status' => 'error', 'message' => 'Invalid Userid');
            } elseif($chek_period == 0){
                $response = array('status' => 'error', 'message' => 'Invalid Period Or Betting Closed On This Period Id');
            }
            else {
                $wallet       = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
                if($wallet >= $bet_amount){
                    $data = array(
                        'userid'      => $userid,
                        'bet_amount'  => $bet_amount,
                        'img_id'      => $img_id,
                        'period_id'   => $period,
                        'status'      => 'Proccessing',
                    );
                    $this->db->insert('tbl_car_betting',$data);
                    $array = array(
                        'wallet'  => $wallet - $bet_amount,
                    );
                    $this->db->where('id', $userid);
                    $this->db->update('tbl_users', $array);
                    $response = array('status' => 'success', 'message' => 'Bet Placed Successfully...');
                }
                else{
                    $response = array('status' => 'error', 'message' => 'Insufficient Wallet Balance...');
                }
            }
        }
        else{
            $response = array('status' => 'error', 'message' => 'Invalid Parameters...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function betting_history(){
        $userid       = $this->input->post('userid');
        $chek_user    = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        } else{
            $this->db->select('*');
            $this->db->from('tbl_car_betting');
            $this->db->where('userid', $userid);
            $this->db->order_by('id', 'DESC');
            $query   = $this->db->get();
            $results = $query->result();
            if ($results) {
                $response = array('status' => 'success', 'data' => $results);
            } else {
                $response = array('status' => 'error', 'message' => 'No records found.');
            }
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function results(){
        $this->db->select('*');
        $this->db->from('tbl_car_results');
        $this->db->order_by('id', 'DESC');
        $this->db->limit(20);
        $query   = $this->db->get();
        $results = $query->result();
        if ($results) {
            $response = array('status' => 'success', 'data' => $results);
        } else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function pre_win_id(){
        $period       = $this->input->post('period');
        $chek_period  = $this->db_model->count_all('tbl_car_results', array('period_id' => $period));
        if($chek_period <= 0){
            $response = array('status' => 'error', 'message' => 'Result Not Inserted Yet');
        } else{
            $number   = $this->db_model->select('img_id', 'tbl_car_results', array('period_id' => $period));
            $response = array('status' => 'success', 'win_number' => $number);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function chek_min_betting($period){
        for ($i = 0; $i <= 8; $i++) {
            $betAmounts[$i] = $this->db_model->sum('bet_amount', 'tbl_car_betting', ['period_id' => $period,'img_id' => $i,'status' => 'Proccessing']) + 0;
        }
        $minBetAmount  = min($betAmounts);
        $minBetIndices = [];
        foreach ($betAmounts as $index => $amount) {
            if ($amount == $minBetAmount) {
                $minBetIndices[] = $index;
            }
        }
        if (count($minBetIndices) > 1) {
            $randomIndex      = array_rand($minBetIndices);
            $selectedBetIndex = $minBetIndices[$randomIndex];
        } else {
            $selectedBetIndex = $minBetIndices[0];
        }
        return $selectedBetIndex;
    }

}