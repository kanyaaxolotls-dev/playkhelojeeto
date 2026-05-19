<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Win_model extends CI_Model
{

    public function update_wallet($user_id = 1, $wallet = 'winning_wallet', $amount = 0, $transactionType = 'NA',$status = 'NA')
    {
        $charges     = $this->db_model->select('admin_charge', 'tbl_settings', array('id' => 1));
        $adminCharge = ($charges / 100) * $amount;
        $win_amt     = $amount - $adminCharge;
    
        $this->db->set('wallet', 'wallet + ' . (float)$win_amt, false);
        $this->db->set('winning_wallet', 'winning_wallet + ' . (float)$win_amt, false);
        
        $this->db->where('id', $user_id); 
        $this->db->update('tbl_users');
    
        $transactionData = array(
            'amount'   => $win_amt,
            'userid'   => $user_id,
            'type'     => $transactionType,
            'status'   => $status,
        );
        $this->db->insert('tbl_transactions', $transactionData);
    }
    
    

}