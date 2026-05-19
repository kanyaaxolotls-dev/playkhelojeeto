<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class DragonTiger extends CI_Model
{
    public function GetCards($limit = '')
    {
        $this->db->from('tbl_cards');
        $this->db->where('cards !=', 'JKR1');
        $this->db->where('cards !=', 'JKR2');
        $this->db->limit($limit);
        $this->db->order_by('id', 'RANDOM');
        $query = $this->db->get();
        return $query->result();
    }

    public function GetResults($limit = '')
    {
        $this->db->select('id, win_card'); 
        $this->db->from('tbl_dragon_results');
        $this->db->limit($limit);
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get();
        return $query->result();
    }

    public function update_winning($id,$status,$table)
    {
        $array = array(
            'status'  => $status,
        );
        $where_condition  = "id = ".$id;
        $this->db_model->update($array,$table,$where_condition);
    } 
    
}


