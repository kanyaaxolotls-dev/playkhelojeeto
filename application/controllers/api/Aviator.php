<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Aviator extends CI_Controller {

    // For testing purpose by AK
    public function view_point()
    {
        $this->load->view('test.php');
    }

    public function add2()
    {
        $limit    = 8;
        $results  = $this->db_model->get_last_records('tbl_aviator_results', 'winning', $limit);
        $data     = $this->db_model->select_multi('secret, fly_at, period_id', 'tbl_games', array('id' => 2));
    
        // Extract only the winning values
        $winningResults = array_map(function($row) {
            return array('winning' => $row->winning); 
        }, $results);
    
        $response = array(
            'status'      => 'success',
            'crash_value' => $data->secret,
            'period'      => $data->period_id,
            'flying'      => $data->fly_at,
            'results'     => $winningResults
        );
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    // END

    public function add()
    {
        $currentDatetime = date("Y-m-d H:i:s");
        $lastDatetime    = $this->db->select('date')->order_by('id', 'DESC')->limit(1)->get('tbl_aviator_results')->row()->date;
        $diffInSeconds   = strtotime($currentDatetime) - strtotime($lastDatetime);

        if($diffInSeconds > 15){
            $data      = $this->db_model->select_multi('secret, fly_at, period_id', 'tbl_games', array('id' => 2));
            $period_id = $data->period_id;
            $fly_at    = $data->fly_at + 0.2;
    
            $bet_amt = $this->db_model->sum('amount', 'tbl_aviator_bet', array('period_id' => $period_id, 'status !=' => 'success'));
            if($bet_amt > 0){
                $max_amt  = $this->db->select_max('amount')->where('period_id', $period_id)->where('status !=', 'success')->get('tbl_aviator_bet')->row()->amount ?? 0;
                $crash_at = $bet_amt / $max_amt;
            }else{
                $crash_at = $data->secret;
            }
            $array = array(
                'fly_at'  => $fly_at,
                'secret'  => $crash_at,
            );
            $where_condition  = "id = 2";
            $this->db_model->update($array,'tbl_games',$where_condition);
    
            if($fly_at >= $data->secret){
                $this->update_game($data->secret);
                $response = array('status' => 'success','crash_value' => $data->secret,'flying' => $data->fly_at,'message' => 'Game rocords updated.');
            }else{
                $response = array('status' => 'success','crash_value' => $data->secret,'flying' => $data->fly_at);
            }
        }else{
            $response = array('status' => 'success','crash_value' => 0.00,'flying' => 0.00,'message' => 'Waiting Time.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function update_game($crash_at)
    {
        $currentDatetime = date("Y-m-d H:i:s");
        $lastDatetime    = $this->db->select('date')->order_by('id', 'DESC')->limit(1)->get('tbl_aviator_results')->row()->date;
        $diffInSeconds   = strtotime($currentDatetime) - strtotime($lastDatetime);
        $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 2));
        $chek_data       = $this->db_model->count_all('tbl_aviator_results', array('period_id' => $period_id));
        $bet_amt         = $this->db_model->sum('amount', 'tbl_aviator_bet', array('period_id' => $period_id));
        $won_amt         = $this->db_model->sum('win_amount', 'tbl_aviator_bet', array('period_id' => $period_id)) + 0;
        if($crash_at){
            $where_condition = array('period_id' => $period_id,'status' => 'pending');
            $data            = $this->db_model->get_all_data('tbl_aviator_bet',$where_condition);
            foreach($data as $bet){
                $array = array(
                    'status'       => 'loss',
                );
                $where_condition  = "id = ".$bet->id;
                $this->db_model->update($array,'tbl_aviator_bet',$where_condition);
            }

            $array = array(
                'period_id'      => $period_id,
                'winning'        => $crash_at,
                'total_amount'   => $bet_amt,
                'total_win'      => $won_amt,
                'date'           => date("Y-m-d H:i:s")
            );
            $this->db->insert('tbl_aviator_results', $array);

            $array = array(
                'period_id'  => $period_id + 1,
                'secret'     => number_format(mt_rand(10, 50) / 10, 2),
                'fly_at'     => 1.00,
            );
            $where_condition  = "id = 2";
            $this->db_model->update($array,'tbl_games',$where_condition);

            $response = array('status' => 'success','message' => 'Game rocords updated');
        }else{
            $response = array('status' => 'error','message' => 'Invalid parameters');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function cash_out()
    {
        $period_id = $this->input->post('period_id');
        $userid    = $this->input->post('userid');
        $cash_at   = $this->input->post('cash_at');
        $chek_data = $this->db_model->count_all('tbl_aviator_bet', array('userid' => $userid, 'period_id' => $period_id, 'status' => 'pending'));
        
        if ($chek_data <= 0) {
            $response = array('status' => 'error', 'message' => 'Data Not Found.');
        } else {
            $bet_data   = $this->db_model->select_multi('*', 'tbl_aviator_bet', array('userid' => $userid, 'period_id' => $period_id, 'status' => 'pending'));
            $wallet_bal = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
            $win_amount = $bet_data->amount * $cash_at;
    
            $crash_status = $this->crash_logic($cash_at, $period_id, $win_amount);
    
            // if ($crash_status === 'success') {
                $array = array(
                    'status'      => 'success',
                    'cash_out_on' => $cash_at,
                    'win_amount'  => $win_amount,
                );
                $where_condition = "id = " . $bet_data->id;
                $this->db_model->update($array, 'tbl_aviator_bet', $where_condition);
    
                $this->Win_model->update_wallet($bet_data->userid, 'wallet', $win_amount, 'Aviator Winning','Won');
                $response = array('status' => 'success', 'message' => 'Cash Out Successfully !!', 'winning' => $win_amount);
            // } else {
            //     $response = array('status' => 'error', 'message' => 'Cash Out Unsuccessful. Try again later.');
            // }
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));  
    }
    
    public function crash_logic($cash_at, $period_id, $win_amount)
    {
        $bet_amt = $this->db_model->sum('amount', 'tbl_aviator_bet', array('period_id' => $period_id, 'status !=' => 'success'));
        $win_amt = $this->db_model->sum('amount', 'tbl_aviator_bet', array('period_id' => $period_id, 'status' => 'success')) + $win_amount;
        
        if ($win_amt > $bet_amt) {
            $array = array(
                'secret' => $cash_at,
            );
            $where_condition = "id = 2";
            $this->db_model->update($array, 'tbl_games', $where_condition);
            return 'failure';
        } else {
            return 'success';
        }
    }    

	public function crash_at()
	{
        $period_id   = $this->input->post('period_id');
        $chek_data   = $this->db_model->count_all('tbl_aviator_results', array('period_id' => $period_id));
        if($chek_data == 0){
            $crash_at   = $this->db_model->select('secret', 'tbl_games', array('id' => 2));
        }else{
            $crash_at   = $this->db_model->select('winning', 'tbl_aviator_results', array('period_id' => $period_id));
        }
        $response    = array('status' => 'success', 'crash_at' => $crash_at);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));        
    }

	public function place_bet()
	{
        $userid          = $this->input->post('userid');
        $period_id       = $this->input->post('period_id');
        $flee            = $this->input->post('flee_condition') ?? 0.00;
        $amount          = $this->input->post('amount');
        $wallet_bal      = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
        if($amount > $wallet_bal){
            $response = array('status' => 'error','message' => 'Insufficient Balance');
        }
        elseif($wallet_bal >= $amount){
            $array = array(
                'userid'            => $userid,
                'period_id'         => $period_id,
                'amount'            => $amount,
                'flee_condition'    => $flee,
                'status'            => 'pending',
                'type'              => 'manual',
                'date'              => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tbl_aviator_bet', $array);

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

    public function game_starts_in()
    {
        $data            = $this->db_model->select_multi('*', 'tbl_games', array('id' => 2));
        $currentDatetime = date("Y-m-d H:i:s");
        $lastDatetime    = $this->db->select('date')->order_by('id', 'DESC')->limit(1)->get('tbl_aviator_results')->row()->date;
        $diffInSeconds   = strtotime($currentDatetime) - strtotime($lastDatetime);
        $response        = array('status' => 'success','time' => $diffInSeconds,'period_id' => $data->period_id,'fly_at' => $data->fly_at);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function timer()
    {
        $currentTime   = time(); 
        $adjustedTime  = $currentTime; 
        $formattedTime = date('H:i:s', $adjustedTime);
        if ($formattedTime) {
            $response = $formattedTime;
        } else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function results()
	{
        $limit    = 8;
        $result   = $this->db_model->get_last_records('tbl_aviator_results','winning',$limit);
        $response = array('status' => 'success', 'results' => $result);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function bet_amount()
	{
        $userid          = $this->input->post('userid');
        $chek_user       = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 2));
            $bet_amount      = $this->db_model->sum('amount', 'tbl_aviator_bet', ['period_id' => $period_id,'userid' => $userid,'status' => 'pending']) + 0;
            $response = array('status' => 'success', 'bet_amount' => $bet_amount);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function clear_bet()
	{
        $userid          = $this->input->post('userid');
        $chek_user       = $this->db_model->count_all('tbl_users', array('id' => $userid));
        $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 2));
        if($chek_user <= 0){
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        else{
            $my_bets    = $this->db_model->count_all('tbl_aviator_bet', array('period_id' => $period_id,'userid' => $userid,'status' => 'pending'));
            if($my_bets <= 0){
                $response = array('status' => 'error', 'message' => 'No Bets Found !!!');
            }
            else{
                $array = array(
                    'status' => 'cancel',
                );
                $where_condition = "userid = '".$userid."' AND status = 'pending' AND period_id = ".$period_id;
                $this->db_model->update($array, 'tbl_aviator_bet', $where_condition);                 
                $response   = array('status' => 'success', 'message' => 'Bet cleared successfully !!!');
            }
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}