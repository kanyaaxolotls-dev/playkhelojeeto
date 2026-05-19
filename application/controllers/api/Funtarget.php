<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Funtarget extends CI_Controller {

    public function cron()
    {
        $period_id  = $this->db_model->select('period_id', 'tbl_games', array('id' => 4));
        $betAmounts = [];
        for ($i = 0; $i <= 9; $i++) {
            $betAmounts[$i] = $this->db_model->sum('amount', 'tbl_spinner_bet', ['period_id' => $period_id, 'bet' => $i,'status' => 'pending']) + 0;
        }
        $minBetAmount  = min($betAmounts);
        $minBetIndices = [];
        foreach ($betAmounts as $index => $amount) {
            if ($amount == $minBetAmount) {
                $minBetIndices[] = $index;
            }
        }
        if (count($minBetIndices) > 1) {
            $randomIndex = array_rand($minBetIndices);
            $selectedBetIndex = $minBetIndices[$randomIndex];
        } else {
            $selectedBetIndex = $minBetIndices[0];
        }
        $selectedBetAmount = $betAmounts[$selectedBetIndex];

        $array = array(
            'period_id'    => $period_id,
            'win_number'   => $selectedBetIndex,
        );
        $this->db->insert('tbl_funtarget_results', $array);

        $array = array(
            'period_id'  => $period_id + 1,
        );
        $where_condition  = "id = 4";
        $this->db_model->update($array,'tbl_games',$where_condition);
    }

    public function timer()
    {
        $currentTime   = time(); 
        // Adjust serve latency by 2 seconds
        $adjustedTime  = $currentTime - 2; 
        $formattedTime = date('H:i:s', $adjustedTime);
        if ($formattedTime) {
            $response = $formattedTime;
        } else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

	public function place_bet()
	{
        $userid          = $this->input->post('userid');
        $amount          = $this->input->post('amount');
        $number          = $this->input->post('number');
        $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 4));
        $wallet_bal      = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
        if($amount > $wallet_bal){
            $response = array('status' => 'error','message' => 'Insufficient Balance');
        }
        elseif($wallet_bal >= $amount){
            $array = array(
                'userid'       => $userid,
                'bet'          => $number,
                'period_id'    => $period_id,
                'amount'       => $amount,
                'user_amount'  => $wallet_bal,
                'date'         => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tbl_spinner_bet', $array);
            $array = array(
                'wallet'  => $wallet_bal - $amount,
            );
            $where_condition  = "id = ".$userid;
            $this->db_model->update($array,'tbl_users',$where_condition);         
            $response = array('status' => 'success','message' => 'Bet Placed');
        }
        else{
            $response = array('status' => 'error','message' => 'Something Went Wrong Try Again Later...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

    public function my_bets()
	{
        $userid          = $this->input->post('userid');
        $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 4));
        $chek_user       = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $betAmounts = [];
            for ($i = 0; $i <= 9; $i++) {
                $key = ($i === 0) ? 'zero' : ($i === 1 ? 'one' : ($i === 2 ? 'two' : ($i === 3 ? 'three' : ($i === 4 ? 'four' : ($i === 5 ? 'five' : ($i === 6 ? 'six' : ($i === 7 ? 'seven' : ($i === 8 ? 'eight' : 'nine'))))))));
                $betAmounts[$key] = $this->db_model->sum('amount', 'tbl_spinner_bet', ['period_id' => $period_id, 'bet' => $i, 'userid' => $userid]) + 0;
            }   
            $response = array('status' => 'success', 'my_bets' => $betAmounts);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function show_result()
	{
        $period_id    = $this->db_model->select('period_id', 'tbl_games', array('id' => 4)) - 1;
        $result       = $this->db_model->select('win_number', 'tbl_funtarget_results', array('period_id' => $period_id));
        if($result != null){
            $response = array('status' => 'success', 'number' => $result);
        }
        else{
            $response = array('status' => 'error', 'message' => 'Something Went Wrong Try Again Later...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}


    public function results()
	{
        $limit    = 10;
        $result   = $this->db_model->get_last_records('tbl_funtarget_results','win_number',$limit);
        $response = array('status' => 'success', 'results' => $result);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    public function test_api()
{
    $response = array(
        'status'  => 'success',
        'message' => 'Funtarget API is working fine',
        'time'    => date('Y-m-d H:i:s')
    );

    $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

    public function total_bets()
	{
        $userid          = $this->input->post('userid');
        $chek_user       = $this->db_model->count_all('tbl_users', array('id' => $userid));
        $today_date      = date('Y-m-d');
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $result = $this->db
                ->select('SUM(amount) as total_amount, COUNT(*) as total_count')
                ->where("DATE(date) =", $today_date)
                ->where('userid', $userid)
                ->get('tbl_spinner_bet')
                ->row();
            $total_amount = ($result) ? $result->total_amount + 0 : 0; 
            $total_count  = ($result) ? $result->total_count : 0;
            $response     = array('status' => 'success', 'total_bet_amount' => $total_amount,'total_bet_count' => $total_count);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function win_bets()
	{
        $userid          = $this->input->post('userid');
        $chek_user       = $this->db_model->count_all('tbl_users', array('id' => $userid));
        $today_date      = date('Y-m-d');
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $win_bet   = $this->db->select_sum('amount')->where("DATE(date) =", $today_date)->where('userid', $userid)->where('status', 'won')->get('tbl_spinner_bet')->row()->amount + 0;
            $response  = array('status' => 'success', 'win_bets' => $win_bet);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}