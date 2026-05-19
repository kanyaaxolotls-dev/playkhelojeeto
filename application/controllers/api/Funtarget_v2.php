<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Funtarget_v2 extends CI_Controller {

    public function cron()
    {
        $period_id  = $this->db_model->select('period_id', 'tbl_games', array('id' => 7));
        $manual_set = $this->db_model->select('manual_set', 'tbl_games', array('id' => 7));
        $is_joker   = $this->db_model->select('is_joker', 'tbl_games', array('id' => 7));
       
        if($is_joker == 1){
            $win_into = 18;
        } else{
            $win_into = 9;
        }
        if($manual_set == 1){
            $selectedBetIndex = $this->db_model->select('win_number', 'tbl_games', array('id' => 7));
        } else{
            $betAmounts = [];
            for ($i = 0; $i <= 9; $i++) {
                $betAmounts[$i] = $this->db_model->sum('amount', 'tbl_funtarget_bet', [
                    'period_id' => $period_id, 
                    'bet'       => $i,
                    'status'    => 'pending'
                ]) + 0;
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
        }
        echo $period_id.'<br>'.$selectedBetIndex;
        
        $this->db->insert('tbl_funtarget_results', ['period_id' => $period_id, 'win_number' => $selectedBetIndex]);
        // $this->db->insert('tbl_funtarget_results', ['period_id' => $period_id + 1, 'win_number' => $selectedBetIndex]);
        
        $this->db->select('*');
        $this->db->from('tbl_funtarget_bet');
        $this->db->where('period_id', $period_id);
        $this->db->where('status', 'pending');
        $bets = $this->db->get()->result();
        
        foreach ($bets as $bet) {
            if ($bet->bet == $selectedBetIndex) {
                $win_amount = $bet->amount * $win_into;
                
                $wallet = $this->db_model->select('winning_wallet', 'tbl_users', array('id' => $bet->userid));
                $this->db->where('id', $bet->userid);
                $this->db->update('tbl_users', [
                    'winning_wallet' => $wallet + $win_amount
                ]);
                
                $this->db->insert('tbl_transactions', [
                    'userid'      => $bet->userid,
                    'amount'      => $win_amount,
                    'type'        => 'game_win',
                    'type'        => 'Fun Target Game Win',
                ]);
                $status = 'won';
            } else {
                $status = 'lost';
            }
            
            $this->db->where('id', $bet->id);
            $this->db->update('tbl_funtarget_bet', [
                'status' => $status,
            ]);
        }
        
        $this->db->where('id', 7);
        $this->db->update('tbl_games', [
            'period_id'  => $period_id + 1,
            'win_number' => $selectedBetIndex,
            'is_joker'   => 0,
            'manual_set' => 0
        ]);
    }

    public function take()
    {
        $userid = $this->input->post('userid');
        if (empty($userid)) {
            $response = array(
                'status'      => 'error',
                'message'     => 'User ID is required.',
                'win_wallet'  => 0,
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        $user = $this->db->get_where('tbl_users', ['id' => $userid])->row();
        if (!$user) {
            $response = array(
                'status'      => 'error',
                'message'     => 'Invalid User ID.',
                'win_wallet'  => 0,
            );
            return $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        $wallet         = $user->wallet;
        $winning_wallet = $user->winning_wallet;
    
        if ($winning_wallet > 0) {
            $new_wallet = $wallet + $winning_wallet;
            $this->db->where('id', $userid);
            $this->db->update('tbl_users', [
                'wallet'         => $new_wallet,
                'winning_wallet' => 0
            ]);
            $this->db->insert('tbl_transactions', [
                'userid'      => $userid,
                'amount'      => $winning_wallet,
                'status'      => 'credit',
                'type'        => 'Winning transfer to wallet',
            ]);
            $response = array(
                'status'      => 'success',
                'message'     => 'Winning wallet amount added to main wallet successfully.',
                // 'wallet'      => $new_wallet,
                'win_wallet'  => 0
            );
    
        } else {
            $response = array(
                'status'      => 'error',
                'message'     => 'Winning wallet is already empty.',
                'win_wallet'  => 0,
            );
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function period_id()
    {
        $period_id   = $this->db_model->select('period_id', 'tbl_games', array('id' => 7));
        $response    = array('status' => 'success', 'period_id' => $period_id);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function timer()
    {
        $currentTime   = time(); 
        $adjustedTime  = $currentTime - 10; 
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
        $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 7));
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
            $this->db->insert('tbl_funtarget_bet', $array);
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
        $userid    = $this->input->post('userid');
        $period_id = $this->input->post('period_id');
        $chek_user = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if ($chek_user <= 0) {
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        } else {
            $betAmounts = [];
            $total_bet  = 0;

            for ($i = 0; $i <= 9; $i++) {
                $key    = ($i === 0) ? 'zero' : ($i === 1 ? 'one' : ($i === 2 ? 'two' : ($i === 3 ? 'three' : ($i === 4 ? 'four' : ($i === 5 ? 'five' : ($i === 6 ? 'six' : ($i === 7 ? 'seven' : ($i === 8 ? 'eight' : 'nine'))))))));
                $amount = $this->db_model->sum('amount', 'tbl_funtarget_bet', ['period_id' => $period_id, 'bet' => $i, 'userid' => $userid]) + 0;
                $betAmounts[$key] = $amount;
                $total_bet += $amount;
            }
            $win_bets = $this->db_model->sum('amount', 'tbl_funtarget_bet', ['period_id' => $period_id - 1, 'status' => 'won', 'userid' => $userid]) + 0;
            $response = array(
                'status'    => 'success',
                'my_bets'   => $betAmounts,
                'total_bet' => $total_bet,
                'win_bets'  => $win_bets,
            );
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function show_result()
    {
        // $period_id = $this->input->post('period_id');
        $period_id = $this->db_model->select('period_id', 'tbl_games', array('id' => 7));
        $result    = $this->db_model->count_all('tbl_funtarget_results', array('period_id' => $period_id));
        $result2   = $this->db_model->select('win_number', 'tbl_games', array('id' => 7));
        $is_joker  = $this->db_model->select('is_joker', 'tbl_games', array('id' => 7));
        $mtk_num   = rand(100, 999);
        $limit    = 10;
        $lstresult   = $this->db_model->get_last_records('tbl_funtarget_results','win_number',$limit);
        if ($result > 0) {
            $number   = $this->db_model->select('win_number', 'tbl_funtarget_results', array('period_id' => $period_id));
            $is_joker = $this->db_model->select('is_joker', 'tbl_funtarget_results', array('period_id' => $period_id));
            $response = array(
                'status'     => 'success',
                'mtk_number' => $mtk_num,
                'number'     => $number,
                'is_joker'   => $is_joker,
                'last_res'   => $lstresult,
            );
        } elseif ($result2 != null) {
            $response = array(
                'status'     => 'success',
                'mtk_number' => $mtk_num,
                'number'     => $result2,
                'is_joker'   => $is_joker,
                'last_res'   => $lstresult,
            );
        } else {
            $response = array(
                'status'  => 'error',
                'message' => 'Something Went Wrong Try Again Later...'
            );
        }
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

//     public function results()
// 	{
//         $limit    = 10;
//         $result   = $this->db_model->get_last_records('tbl_funtarget_results','win_number',$limit);
//         $response = array('status' => 'success', 'results' => $result);
//         $this->output->set_content_type('application/json')->set_output(json_encode($response));
//     }
    
    public function results()
    {
        $limit    = 10;
        $result   = $this->db_model->get_last_records('tbl_funtarget_results','win_number',$limit);
        // Skip the last row
        if (!empty($result)) {
            array_shift($result); 
        }
        $response = array('status' => 'success', 'results' => $result);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
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
                ->get('tbl_funtarget_bet')
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
            $win_bet   = $this->db->select_sum('amount')->where("DATE(date) =", $today_date)->where('userid', $userid)->where('status', 'won')->get('tbl_funtarget_bet')->row()->amount + 0;
            $response  = array('status' => 'success', 'win_bets' => $win_bet);
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function cancel_last_bet()
    {
        $userid    = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        if (!$userid || !$period_id) {
            $response = ['status' => 'error', 'message' => 'User ID and Period ID are required'];
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $this->db->where(['userid'  => $userid,'period_id' => $period_id,'status' => 'pending']);
        $this->db->order_by('id', 'DESC');
        $this->db->limit(1);
        $bet = $this->db->get('tbl_funtarget_bet')->row();

        if (!$bet) {
            $response = ['status' => 'error', 'message' => 'No pending bet found.'];
        } else {
            $this->db->where('id', $bet->id);
            $this->db->update('tbl_funtarget_bet', ['status' => 'cancelled']);

            $this->db->insert('tbl_transactions', [
                'userid'      => $userid,
                'amount'      => $bet->amount,
                'status'      => 'credit',
                'type'        => 'Refund for cancelled bet ID ' . $bet->id,
            ]);

            $this->db->set('wallet', 'wallet + ' . $bet->amount, FALSE);
            $this->db->where('id', $userid);
            $this->db->update('tbl_users');

            $response = [
                'status'       => 'success',
                'message'      => 'Last pending bet cancelled. ₹' . $bet->amount . ' refunded.',
                'cancelled_id' => $bet->id
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function cancel_all_bets()
    {
        $userid    = $this->input->post('userid');
        $period_id = $this->input->post('period_id');

        if (!$userid || !$period_id) {
            $response = ['status' => 'error', 'message' => 'User ID and Period ID are required'];
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
            return;
        }

        $this->db->where(['userid'    => $userid, 'period_id' => $period_id, 'status'    => 'pending']);
        $bets = $this->db->get('tbl_funtarget_bet')->result();

        if (empty($bets)) {
            $response = ['status' => 'error', 'message' => 'No pending bets found to cancel.'];
        } else {
            $total_refund = 0;
            $cancelled_ids = [];

            foreach ($bets as $bet) {
                $total_refund += $bet->amount;
                $cancelled_ids[] = $bet->id;

                $this->db->where('id', $bet->id);
                $this->db->update('tbl_funtarget_bet', ['status' => 'cancelled']);

                $this->db->insert('tbl_transactions', [
                    'userid'      => $userid,
                    'amount'      => $bet->amount,
                    'status'      => 'credit',
                    'type'        => 'Refund for cancelled bet ID ' . $bet->id,
                ]);
            }

            $this->db->set('wallet', 'wallet + ' . $total_refund, FALSE);
            $this->db->where('id', $userid);
            $this->db->update('tbl_users');

            $response = [
                'status'        => 'success',
                'message'       => count($bets) . ' pending bet(s) cancelled. ₹' . $total_refund . ' refunded.',
                'cancelled_ids' => $cancelled_ids
            ];
        }

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function play_chips()
	{
        $img_path        = base_url('axxests/chips_img/');
        $where_condition = "status = 1";
        $show_games      = $this->db_model->get_specific_records('tbl_play_chips', $where_condition, 'id, img, amount');
        if($show_games){
            $response = array('status' => 'success', 'img_path' => $img_path, 'data' => $show_games);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    public function get_grouped_numbers()
    {
        $query = $this->db->get_where('mtk_numbers', ['status' => 1]);
    
        if ($query->num_rows() > 0) {
            $result = $query->result();
            $data   = [];
    
            foreach ($result as $row) {
                $numbers = explode(',', $row->number_group);
                $data[$row->number] = $numbers;
            }
    
            $response = array('status' => 'success', 'data'   => $data);
        } else {
            $response = array('status'  => 'error', 'message' => 'No data found.');
        }
    
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}