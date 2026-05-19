<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Daily_bonus extends CI_Controller {

    public function index()
    {
        $userid          = $this->input->post('userid');
        $u_check         = $this->db_model->count_all('tbl_users', array('id' => $userid));
        if($u_check > 0){
            $login_date      = $this->db_model->select('date', 'tbl_users', array('id' => $userid));
            $login_date_str  = $this->db_model->select('date', 'tbl_users', array('id' => $userid));
            $login_date      = new DateTime($login_date_str);
            $current_date    = new DateTime();
            $user_login_days = abs($current_date->diff($login_date)->days);
            $where_condition = "status = 1";
            $status_intro    = '0 = Eligible , 1 = Claimed , 2 = Not Eligible';
            $show_games      = $this->db_model->get_specific_records('tbl_dailybonus', $where_condition, 'id, day, amount');
            if (is_array($show_games) && count($show_games) > 0) {
                foreach ($show_games as &$game) {
                    $transaction_count = $this->get_transaction_count($userid, $game->id);
                    if ($transaction_count == 0) {
                        $game->status = ($user_login_days >= $game->day) ? '0' : '2';
                    } else {
                        $game->status = ($user_login_days < $game->day) ? '0' : '1';
                    }
                }
                $response = array('status' => 'success','status_overview' => $status_intro, 'data' => $show_games);
            } else {
                $response = array('status' => 'error', 'message' => 'No records found.');
            }
        }
        else{
            $response = array('status' => 'error', 'message' => 'Invalid Userid');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    public function get_transaction_count($userid, $game_id)
    {
        $conditions        = array('userid' => $userid, 'type' => 'Day ' .$game_id. ' Bonus');
        $transaction_count = $this->db_model->count_all('tbl_transactions', $conditions);
        return $transaction_count;
    }
    
    public function claim_amount()
    {
        $userid         = $this->input->post('userid');
        $bonus_id       = $this->input->post('bonus_id');
        $bonus_id_chek  = $this->db_model->count_all('tbl_dailybonus', array('id' => $bonus_id));
        if($bonus_id_chek > 0){
            $transaction_count = $this->get_transaction_count($userid, $bonus_id);
            if($transaction_count == 0){
                $amount  = $this->db_model->select('amount', 'tbl_dailybonus', array('id' => $bonus_id));
                $wallet  = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
                $array   = array(
                    'wallet'  => $wallet + $amount,
                );
                $where_condition  = "id = ".$userid;
                $this->db_model->update($array,'tbl_users',$where_condition);

                $array2 = array(
                    'userid'        => $userid,
                    'amount'        => $amount,
                    'type'          => 'Day ' .$bonus_id. ' Bonus',
                );
                $this->db->insert('tbl_transactions', $array2);

                $response   = array('status' => 'success', 'message' => 'Bonus Claimed');
            }
            else{
                $response = array('status' => 'error', 'message' => 'Bonus Already Claimed');
            }
        }
        else{
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    

}