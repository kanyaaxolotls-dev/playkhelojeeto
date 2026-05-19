<?php

use phpDocumentor\Reflection\Types\Object_;
use Restserver\Libraries\REST_Controller;

include APPPATH . '/libraries/REST_Controller.php';
include APPPATH . '/libraries/Format.php';
class Spinner extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();
        // $header = $this->input->request_headers('token');

        // if (!isset($header['token'])) {
        //     $data['message'] = 'Invalid Request';
        //     $data['code'] = HTTP_UNAUTHORIZED;
        //     $this->response($data, HTTP_OK);
        //     exit();
        // }

        // if ($header['token'] != getToken()) {
        //     $data['message'] = 'Invalid Authorization';
        //     $data['code'] = HTTP_METHOD_NOT_ALLOWED;
        //     $this->response($data, HTTP_OK);
        //     exit();
        // }

        $this->data = $this->input->post();
        // print_r($this->data['user_id']);
        $this->load->model([
            'Spinner_model',
            'Setting_model',
            'Users_model'
        ]);
    }

   
    public function get_active_game_post()
    {
        

        if (empty($this->data['user_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

       

        $game = $this->Spinner_model->getActiveGameOnTable();
        if (empty($game)) {
            $data['message'] = 'Invalid Game';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

              if ($game) {
            $data['game'] = $game;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        }
        
    }


     public function place_bet_post()
    {
        if (empty($this->data['user_id']) || empty($this->data['game_id']) || ($this->data['bet'] =="") || empty($this->data['amount'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
        //     $data['message'] = 'Invalid User';
        //     $data['code'] = HTTP_INVALID;
        //     $this->response($data, HTTP_OK);
        //     exit();
        // }

        if (!in_array($this->data['bet'], array(0,1,2,3,4,5,6,7,8,9))) {
            $data['message'] = 'Invalid Bet';
            $data['code'] = HTTP_INVALID;
            $this->response($data, HTTP_OK);
            exit();
        }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<100) {
            $data['message'] = 'Required Minimum 100 Coins to Play';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($user[0]->wallet<$this->data['amount']) {
            $data['message'] = 'Insufficient Wallet Amount';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game = $this->Spinner_model->View($this->data['game_id']);
        if (!$game) {
            $data['message'] = 'Invalid Game Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        if ($game->status == 1) {
            $data['message'] = 'Can\'t Place Bet, Game Has Been Ended';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

         $bet_data= [
            'game_id' => $this->data['game_id'],
            'user_id' => $this->data['user_id'],
            'bet' => $this->data['bet'],
            'amount' => $this->data['amount'],
            'added_date' => date('Y-m-d H:i:s')

        ];

        $bet_id = $this->Spinner_model->PlaceBet($bet_data);

        if ($bet_id) {
            $this->Spinner_model->MinusWallet($this->data['user_id'], $this->data['amount']);
            $data['message'] = 'Success';
            $data['bet_id'] = $bet_id;
            $user_wallet = $this->Users_model->UserProfile($this->data['user_id']);
            $data['wallet'] = $user_wallet[0]->wallet;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
        } else {
            $data['message'] = 'Something Wents Wrong';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
    }




    public function get_active_game_details_post()
{
  if (empty($this->data['user_id'])  || empty($this->data['game_id'])) {
            $data['message'] = 'Invalid Parameter';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        // if (!$this->Users_model->TokenConfirm($this->data['user_id'], $this->data['token'])) {
        //     $data['message'] = 'Invalid User';
        //     $data['code'] = HTTP_INVALID;
        //     $this->response($data, HTTP_OK);
        //     exit();
        // }

        $user = $this->Users_model->UserProfile($this->data['user_id']);
        if (empty($user)) {
            $data['message'] = 'Invalid User';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $room = $this->Spinner_model->getRoom($this->data['game_id'], $this->data['user_id']);
        if (empty($room)) {
            $data['message'] = 'Invalid Game Id';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }

        $game_data = $this->Spinner_model->getActiveGameOnTable($this->data['game_id']);
        if ($game_data) {


             $data['message'] = 'Success';
            // $data['online_users'] = $this->Spinner_model->getRoomOnlineUser($this->data['game_id']);
             $data['online'] = rand(300, 350);

            $data['my_no_zero_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 0, $this->data['user_id']);
            $data['my_no_one_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 1, $this->data['user_id']);
            $data['my_no_two_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 2, $this->data['user_id']);
            $data['my_no_three_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 3, $this->data['user_id']);
            $data['my_no_four_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 4, $this->data['user_id']);
            $data['my_no_five_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 5, $this->data['user_id']);
            $data['my_no_six_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 6, $this->data['user_id']);
            $data['my_no_seven_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 7, $this->data['user_id']);
            $data['my_no_eight_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 8, $this->data['user_id']);
            $data['my_no_nine_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 9, $this->data['user_id']);

            $data['total_zero_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 0);
            $data['total_one_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 1);
            $data['total_two_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 2);
            $data['total_three_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 3);
            $data['total_four_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 4);
            $data['total_five_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 5);
            $data['total_six_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 6);
            $data['total_seven_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 7);
            $data['total_eight_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 8);
            $data['total_nine_bet'] = $this->Spinner_model->TotalBetAmount($game_data->id, 9);

            $data['profile'] = $user;
            $data['code'] = HTTP_OK;
            $this->response($data, HTTP_OK);
            exit();
}

else {
            $data['message'] = 'Game Starting Soon';
            $data['code'] = HTTP_NOT_ACCEPTABLE;
            $this->response($data, 200);
            exit();
        }
}



}