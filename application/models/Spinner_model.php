<?php

class Spinner_model extends MY_Model
{

    public function getRoom($gameid='', $user_id='')
    {
        // $this->db->select('id,main_card,status,added_date');
        $this->db->from('tbl_spinner');
        // $this->db->where('isDeleted', false);
        if (!empty($gameid)) {
            $this->db->where('id', $gameid);
        }
        $this->db->order_by('id', 'asc');
        $Query = $this->db->get();

        $this->db->set('spinner_game_id', $gameid); //value that used to update column
        $this->db->where('id', $user_id); //which row want to upgrade
        $this->db->update('tbl_users');  //table name
        return $Query->result();
    }

   

    public function Create($game_id)
    {
        $ander_data = ['game_id' => $game_id, 'status'=>0,'added_date' => date('Y-m-d H:i:s')];
        $this->db->insert('tbl_spinner', $ander_data);
        return $this->db->insert_id();
    }

   public function getActiveGameOnTable($gameid='')
    {
        $this->db->from('tbl_spinner');
        if (!empty($gameid)) {
            $this->db->where('game_id', $gameid);
        }
        $this->db->order_by('id', 'desc');
        $this->db->limit(1);
        $Query = $this->db->get();
        return $Query->row();
    }


    public function PlaceBet($bet_data)
    {
        $this->db->insert('tbl_spinner_bet', $bet_data);
        return $this->db->insert_id();
    }

      public function View($id)
    {
        $this->db->from('tbl_spinner');
        $this->db->where('id', $id);
        $Query = $this->db->get();
        // echo $this->db->last_query();
        // die();
        return $Query->row();
    }


      public function MinusWallet($user_id, $amount)
    {
        $this->db->set('wallet', 'wallet-' . $amount, false);
        $this->db->where('id', $user_id);
        $this->db->update('tbl_users');

        $this->db->select('winning_wallet');
        $this->db->from('tbl_users');
        $this->db->where('id', $user_id);
        $Query = $this->db->get();
        $winning_wallet = $Query->row()->winning_wallet;

        $winning_wallet_minus = ($winning_wallet>$amount) ? $amount : $winning_wallet;

        if ($winning_wallet_minus>0) {
            $this->db->set('winning_wallet', 'winning_wallet-' . $winning_wallet_minus, false);
            $this->db->where('id', $user_id);
            $this->db->update('tbl_users');
        }

        return $this->db->affected_rows();
    }

 public function getRoomOnlineUser($gameid)
    {
        $Query = $this->db->query('SELECT * FROM `tbl_users`  WHERE spinner_game_id = '.$gameid);
        return $Query->result();
    }

        public function TotalBetAmount($game_id, $bet='', $user_id='')
    {
    $this->db->select('COALESCE(SUM(amount), 0) as amount', false);
        $this->db->from('tbl_spinner_bet');
        $this->db->where('game_id', $game_id);
        if ($user_id!='') {
            $this->db->where('user_id', $user_id);
        }
        // if ($bet!=='') {
            $this->db->where('bet', $bet);
        // }
        $Query = $this->db->get();
        // echo $this->db->last_query();
        if ($Query->row()) {
            return $Query->row()->amount;
        }
        return '0';
    }



   
}