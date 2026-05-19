<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rewards extends CI_Controller {

    public function vip_list()
    {
        $where_condition = "status = 1";
        $userid          = $this->input->post('userid');
        $total_deposit   = $this->db_model->sum('amount', 'tbl_deposit', array('userid' => $userid)) + 0;
        $total_loss      = $this->db_model->sum('amount', 'tbl_transactions', array('userid' => $userid, 'status' => 'Loss')) + 0;
        $vip_list        = $this->db_model->get_specific_records('tbl_vip', $where_condition, '*');
        
        if ($vip_list) {
            usort($vip_list, function ($a, $b) {
                return $a->cash - $b->cash;
            });
    
            foreach ($vip_list as $key => $vip_record) {
                if (isset($vip_list[$key])) {
                    $next_level = $vip_list[$key];
                    $vip_record->remaining_amount = $next_level->cash - $total_deposit;
                } else {
                    $vip_record->remaining_amount = 0;
                }
            }                    
            $response = array('status' => 'success', 'loss_amount' => $total_loss, 'vip_list' => $vip_list);
        } else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
    
    public function daily_task()
	{
        $where_condition = "status = 1";
        $daily_task        = $this->db_model->get_specific_records('tbl_daily_task', $where_condition, '*');
        if($daily_task){
            $response = array('status' => 'success', 'daily_task' => $daily_task);
        }
        else {
            $response = array('status' => 'error', 'message' => 'No records found.');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function claim_task()
	{
        $userid    = $this->input->post('userid');
        $task_id   = $this->input->post('task_id');
        $response = array('status' => 'error', 'message' => 'Will verify and add task amount in your wallet.');
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function daily_bonus()
	{
        $userid          = $this->input->post('userid');
        $amount          = 100;
        if($userid){
            $chk_bonus   = $this->db_model->count_all('tbl_transactions', array('userid' => $userid, 'type' => 'Daily Bonus', 'DATE(date)' => date('Y-m-d')));
            if($chk_bonus == 0){
                $wallet  = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
                $array = array(
                    'wallet'  => $wallet + $amount,
                );
                $where_condition  = "id = ".$userid;
                $this->db_model->update($array,'tbl_users',$where_condition);

                $array2 = array(
                    'userid'        => $userid,
                    'amount'        => $amount,
                    'type'          => 'Daily Bonus',
                );
                $this->db->insert('tbl_transactions', $array2);
                $response   = array('status' => 'success', 'message' => 'Bonus Added To Your Wallet...');
            } else{
                $response   = array('status' => 'error', 'message' => 'Todays bonus already claimed..');
            }
        } else{
            $response   = array('status' => 'error', 'message' => 'Invalid userid..');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}