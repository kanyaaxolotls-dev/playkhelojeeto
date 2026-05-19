<?php
    defined('BASEPATH') OR exit('No direct script access allowed');
    
    class Teenpatti extends CI_Controller {
        
        public function tournament_details() {
            $tournament_id    = $this->input->post('tournament_id');
            $chek_type        = $this->db_model->count_all('tbl_teen_details', array('tournament_id' => $tournament_id));
            if ($chek_type == 0) {
                $response = array('status' => 'error', 'message' => 'Type does not match any records');
            }
            else{
                $where_condition = "tournament_id = '" . $tournament_id . "'";
                $result          = $this->db_model->get_all_data('tbl_teen_details', $where_condition);
                if (!empty($result)) {
                    $response = array('status' => 'success', 'data' => $result);
                } else {
                    $response = array('status' => 'error', 'message' => 'No data found');
                }
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        public function tables() {
            
            $type      = $this->input->post('type');
            $chek_type = $this->db_model->count_all('tbl_teenpatti', array('type' => $type));
            
            if ($chek_type == 0) {
                $response = array('status' => 'error', 'message' => 'Type does not match any records');
            }
            else{
                $where_condition = "status = 1 and type = '" . $type . "'";
                $result          = $this->db_model->get_all_data('tbl_teenpatti', $where_condition);
                if (!empty($result)) {
                    $response = array('status' => 'success', 'data' => $result);
                } else {
                    $response = array('status' => 'error', 'message' => 'No data found');
                }
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        public function table_enter() {
            $userid           = $this->input->post('userid');
            $table            = $this->input->post('table_id');
            $amount           = $this->input->post('entree_fee');
            $wallet_bal  = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
            if($amount > $wallet_bal){
                $response = array('status' => 'error','message' => 'Insufficient Balance');
            }
            elseif($wallet_bal >= $amount){
                $array = array(
                    'wallet'  => $wallet_bal - $amount,
                );
                $where_condition  = "id = ".$userid;
                $this->db_model->update($array,'tbl_users',$where_condition);
                $response = array('status' => 'success','message' => 'Table Joined Successfully...');
            }
            else{
                $response = array('status' => 'error','message' => 'Something Went Wrong Try Again Later...');
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        public function place_bet() {
            $userid      = $this->input->post('userid');
            $amount      = $this->input->post('amount');
            $room_id     = $this->input->post('table_id');
            $type        = $this->input->post('type');
            $wallet_bal  = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
            if($amount > $wallet_bal){
                $response = array('status' => 'error','message' => 'Insufficient Balance');
            }
            elseif($wallet_bal >= $amount){
                $array = array(
                    'userid'       => $userid,
                    'room_id'      => $room_id,
                    'type'         => $type,
                    'amount'       => $amount,
                    'status'       => 'Pending',
                );
                $this->db->insert('tbl_teenpatti_bet', $array);
                
                $array = array(
                    'wallet'  => $wallet_bal - $amount,
                );
                $where_condition  = "id = ".$userid;
                $this->db_model->update($array,'tbl_users',$where_condition);
                $response = array('status' => 'success','message' => 'Bet Placed Successfully...');
            }
            else{
                $response = array('status' => 'error','message' => 'Something Went Wrong Try Again Later...');
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
        
        public function update_table() {
            $userid      = $this->input->post('userid');
            $amount      = $this->input->post('amount');
            $room_id     = $this->input->post('table_id');
            $status      = $this->input->post('status');
            $chek_room   = $this->db_model->count_all('tbl_teenpatti_bet', array('room_id' => $room_id));
            if($chek_room == 0){
                $response = array('status' => 'error','message' => 'Invalid table id');
            } else{
                $wallet_bal  = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));
                if($status == 'Win'){
                    $array = array(
                        'wallet'  => $wallet_bal + $amount,
                    );
                    $where_condition  = "id = ".$userid;
                    $this->db_model->update($array,'tbl_users',$where_condition);
                }
                
                $array2 = array(
                    'status'  => $status,
                );
                $where_condition = array('room_id' => $room_id);
                $this->db_model->update($array2, 'tbl_teenpatti_bet', $where_condition);                
                $response = array('status' => 'success','message' => 'Table Data Successfully...');
            }
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
    }
    
    
    
    