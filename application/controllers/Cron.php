<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller {

    public function index(){
        $currentSeconds = date('s');
        echo $currentSeconds;die();
        // if ((($currentSeconds >= 20 && $currentSeconds <= 30) || ($currentSeconds >= 50 && $currentSeconds <= 59))) {
            redirect('api/Dragon_tiger/cron');
        // } 
    }

    public function live_bet_totals()
    {

        $period_id = $this->db_model->select('period_id', 'tbl_games', array('id' => 7));

        $bets = [];
        for ($i = 0; $i <= 9; $i++) {
            $bets[$i] = $this->db_model->sum('amount', 'tbl_funtarget_bet', [
                'period_id' => $period_id,
                'bet'       => $i
            ]) + 0;
        }

        $response = [
            'status'    => 'success',
            'bets'      => $bets,
            'highlight' => $this->db_model->select('win_number', 'tbl_games', array('id' => 7)), 
        ];

        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

}