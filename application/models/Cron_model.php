<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Cron_model extends CI_Model
{
    public function update_data($current_period,$game_id,$method,$data)
    {
        $array = array(
            'period_id'  => $current_period + 1,
        );
        $where_condition  = "id = ".$game_id;
        $this->db_model->update($array,'tbl_games',$where_condition);
        $this->$method($current_period,$data);
    }
    
    public function DragonTiger($current_period,$data)
    {
        $this->db->insert('tbl_dragon_results', $data);
    }
}


