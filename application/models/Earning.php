<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Earning extends CI_Model
{
    public function pay_earning($userid, $amount, $income_name, $ref_id = NULL, $levlno = 0,$earning_type,$package = 0,$purchase_id = 0){
        $data = array(
            'userid'            => $userid,
            'amount'            => $amount,
            'type'              => $income_name,
            'ref_id'            => $ref_id,
            'levlno'            => $levlno,
            'package'           => $package,
            'earning_type'      => $earning_type,
            'secret'            => $purchase_id,
            'status'            => $status,
            'date'              => date('Y-m-d'),
        );
        $this->db->insert('earning', $data);
        return TRUE;
    }
    
    public function roi_inc(){
        $this->db->select('*')->from('purchase_history')->where(array('status' => 0));
        $purchase_data = $this->db->get()->result();
        foreach ($purchase_data as $users){
            $userid        = $users->userid;
            $c_bal         = $this->db_model->select('balance', 'wallet', array('userid' => $userid));
            $roi           = $this->db_model->select_multi('*', 'packages', array('id' => $users->package));
            $roi_days      = $roi->roi_days;
            $roi_amount    = $roi->roi_amount * $users->qty;
            
            if($users->records < $roi->roi_days){
                $earning_status = $this->pay_earning($userid, $roi_amount,'Commision', NULL, 0,'Package',$users->package,$users->id);  
            }
            if($this->db_model->select('records', 'purchase_history', array('id' => $users->id)) + 1 == $roi->roi_days){
                $status = 1;
            }
            else{
                $status = 0;
            }

	        $data = array(
                'userid	'    => $userid,
                'amount'     => $roi_amount,
                'wallet'     => $c_bal,
                'type2'      => 'Credit',
                'type'       => 'Commision',
                'date'       => date('Y-m-d H:i:s'),
            );
            $this->db->insert('transactions', $data);
            $array = array(
                'records'    => $users->records + 1, 
                'status'     => $status, 
            );
            $this->db->where('id', $users->id);
            $this->db->update('purchase_history', $array);
        } 
    }
    
    public function level_inc($userid,$sponsor,$packageid){
        $data = $this->db_model->select_multi('*', 'packages', array('id' => $packageid));  
        if (trim($data->incomes) !== "") {              
            $ex1 = explode(',', $data->incomes);
            $i = 1;
            foreach ($ex1 as $e1) {                    
                $e1 = trim($e1);
                if ($i == 0) {
                    $pay_gen_sponsor = $sponsor;
                } else {                     
                    $pay_gen_sponsor =  $this->find_sponsor($userid, $i);
                }
                if ($pay_gen_sponsor > 0 && $e1 > 0) {                      
                    $this->pay_earning($pay_gen_sponsor, $e1,'Level '. $i .' Commision', $userid, $i,'Team');    
                 }
                $i++;
            }
        }   
    }
    
    private function find_sponsor($sponsor, $i) {      
        if ($i > 0) {
            $this->db->select('sponsor')->from('users')->where(array('id' => $sponsor));
            $result = $this->db->get()->row();
            if (!$result) {
                return FALSE;
            }
            else {
                $i = ($i - 1); 
                return $this->find_sponsor($result->sponsor, $i);               
            }
        } 
        else {
            return $sponsor;
        }
    }
    
}


