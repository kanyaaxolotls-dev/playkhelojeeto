<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dragon_tiger extends CI_Controller {

    public function cron()
    {
        $current_period = $this->db_model->select('period_id', 'tbl_games', array('id' => 1));
        $total_betting  = $this->db_model->sum('amount', 'tbl_bet_dragon', array('period_id' => $current_period)) + 0;

        // Set condition true if want to compare all with minimum amount of bet this condition take only dragon, tiger winning tie include untill hit that condition amount....
        if($total_betting > 10000){
            $betAmounts = array(
                'Dragon' => $this->db_model->sum('amount', 'tbl_bet_dragon', array('period_id' => $current_period, 'betting_on' => 'DRAGON')) + 0,
                'Tiger'  => $this->db_model->sum('amount', 'tbl_bet_dragon', array('period_id' => $current_period, 'betting_on' => 'TIGER')) + 0,
                'Tie'    => $this->db_model->sum('amount', 'tbl_bet_dragon', array('period_id' => $current_period, 'betting_on' => 'TIE')) + 0,
            );
        }else{
            $betAmounts = array(
                'Dragon' => $this->db_model->sum('amount', 'tbl_bet_dragon', array('period_id' => $current_period, 'betting_on' => 'DRAGON')) + 0,
                'Tiger'  => $this->db_model->sum('amount', 'tbl_bet_dragon', array('period_id' => $current_period, 'betting_on' => 'TIGER')) + 0,
            );
        }

        $maxBetAmount = min($betAmounts);
        $tiedValues   = array_keys($betAmounts, $maxBetAmount);
        
        if (count($tiedValues) > 1) {
            shuffle($tiedValues);
            $winning = $tiedValues[0];
        } else {
            $winning = array_search($maxBetAmount, $betAmounts);
        }
        
        $card_small = ''; 
        $card_big   = '';   
        
        if ($winning == 'Tie') {
            $number = rand(2, 10);
            $card_dragon = 'BP' . $number;
            $card_tiger  = 'RP' . $number;
        } 
        else {
            do {
                $limit       = 2;
                $cards       = $this->DragonTiger->GetCards($limit);
                $card1_point = $this->card_points($cards[0]->cards);
                $card2_point = $this->card_points($cards[1]->cards);
                if ($card1_point > $card2_point) {
                    $card_big   = $cards[0]->cards;
                    $card_small = $cards[1]->cards;
                } else {
                    $card_big   = $cards[1]->cards;
                    $card_small = $cards[0]->cards;
                }
            } while ($card1_point == $card2_point);
        
            $card_dragon = ($winning == 'Dragon') ? $card_big : $card_small;
            $card_tiger  = ($winning == 'Tiger') ? $card_big : $card_small;
        }
        
        $this->update_betting($current_period,$winning);
        $this->Cron_model->update_data($current_period,1,'DragonTiger', array('period_id' => $current_period,'dragon_card_id' => $card_dragon,'tiger_card_id' => $card_tiger,'win_card' => $winning));
    }
    
    public function card_points($card)
    {
        $card_value = substr($card, 2);
        $point      = str_replace(array("J", "Q", "K", "A"),array(11, 12, 13, 1),$card_value);
        return $point;
    }
    
    public function update_betting($current_period,$winning)
    {
        $winning = strtoupper($winning);
        if($winning == 'TIE'){
            $win_percent     = 5;
        }else{
            $win_percent     = 2;
        }
        $where_condition = array('period_id' => $current_period,'status' => 'Waiting');
        $data            = $this->db_model->get_all_data('tbl_bet_dragon',$where_condition);
        foreach($data as $bet){
            if($bet->betting_on == $winning){
                $win_amount = $win_percent * $bet->amount;
                $this->Win_model->update_wallet($bet->userid, 'wallet', $win_amount, 'Dragon Tiger Winning','Won');
                $this->DragonTiger->update_winning($bet->id,'Won','tbl_bet_dragon');
            }
            else{
                $this->DragonTiger->update_winning($bet->id,'Loss','tbl_bet_dragon');
            }
        }
    }

    public function timer()
    {
        $currentTime   = time(); 
        $adjustedTime  = $currentTime + 2; 
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
        $card            = strtoupper($this->input->post('card'));
        $amount          = $this->input->post('amount');
        $game_id         = $this->input->post('game_id') ?? 1;
        $period_id       = $this->db_model->select('period_id', 'tbl_games', array('id' => 1));
        $wallet_bal      = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
        if($amount > $wallet_bal){
            $response = array('status' => 'error','message' => 'Insufficient Balance');
        }
        elseif($wallet_bal >= $amount){
            $array = array(
                'userid'       => $userid,
                'period_id'    => $period_id,
                'betting_on'   => $card,
                'amount'       => $amount,
            );
            $this->db->insert('tbl_bet_dragon', $array);
            $array = array(
                'wallet'  => $wallet_bal - $amount,
            );
            $where_condition  = "id = ".$userid;
            $this->db_model->update($array,'tbl_users',$where_condition);
            $response = array('status' => 'success','message' => 'Bet Placed On '.$card);
        }
        else{
            $response = array('status' => 'error','message' => 'Something Went Wrong Try Again Later...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}
    
	public function show_card()
	{
        $period_id    = $this->db_model->select('period_id', 'tbl_games', array('id' => 1)) - 1;
        $cards        = $this->db_model->select_multi('*', 'tbl_dragon_results', array('period_id' => $period_id));
        if($cards){
            $dragon_card  = $this->db_model->select('id', 'tbl_cards', array('cards' => $cards->dragon_card_id));
            $tiger_card   = $this->db_model->select('id', 'tbl_cards', array('cards' => $cards->tiger_card_id));
            $response = array('status' => 'success', 'tiger_card_id' => $tiger_card, 'dragon_card_id' => $dragon_card, 'win_card' => $cards->win_card);
        }
        else{
            $response = array('status' => 'error', 'message' => 'Something Went Wrong Try Again Later...');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
	}

    public function chips()
	{
        $response = array('status' => 'success', 'chips' => $this->db_model->select('chips', 'tbl_games', array('id' => 1)));
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function results()
	{
        $limit = 20;
        $result       = $this->DragonTiger->GetResults($limit);
        $response = array('status' => 'success', 'results' => $result);
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function dragon_betting()
	{
		$userid          = $this->input->post('userid');
        $where_condition = "userid = ".$userid;
        $show_trans      = $this->db_model->get_specific_records('tbl_bet_dragon', $where_condition, 'amount, betting_on, status, date');
        if($show_trans){
            $response = array('status' => 'success', 'data' => $show_trans);
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
}
