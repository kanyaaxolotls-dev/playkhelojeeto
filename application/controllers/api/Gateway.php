<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Gateway extends CI_Controller {

    public function check_transaction(){
        $tid      = $this->input->post('transaction_id'); 
        $u_chec   = $this->db_model->count_all('tbl_deposit', array('secret' => $tid));
        if($u_chec > 0){
            $detail = $this->db_model->select_multi('*', 'tbl_deposit', array('secret' => $tid));
            if($detail->status == 'Paid'){
                $response = array('status' => 'success', 'message' => 'Amount Credited To Your Wallet...');
            }
            else{
                $response = array('status' => 'error', 'message' => 'Payment Failed...');
            }
        }
        else{
            $response = array('status' => 'error', 'message' => 'Invalid Proccess');
        }
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }

    public function testpay(){
        $userid      = $this->input->get('userid');
        $chip_id     = $this->input->get('chip_id');
        $tid         = $this->input->get('transaction_id');

        if(empty($userid) or empty($chip_id) or empty($tid)){
            $response = array('status' => 'error', 'Values Not Received...');
        }
        else{
            $chipinfo      = $this->db_model->select_multi('*', 'tbl_chips', array('id' => $chip_id));
            $amount        = $chipinfo->amount + $chipinfo->principle;
            $data = array(
                'userid'          => $userid,
                'payment_type'    => 'Test',
                'status'          => 'Processing',
                'amount'          => $amount,
                'secret'          => $tid,
            ); 
            $this->db->insert('tbl_deposit', $data);
            $response = array('status' => 'success', 'userid' => $userid, 'Chip Id' => $chip_id, 'Transaction Id' => $tid);
            $this->output->set_content_type('application/json')->set_output(json_encode($response));
        }
    }

    public function phonepay(){
        $this->load->model('Phonepay');
        $userid      = $this->input->get('userid');
        $chip_id     = $this->input->get('chip_id');
        $tid         = $this->input->get('transaction_id');
        $userinfo    = $this->db_model->select_multi('*', 'tbl_users', array('id' => $userid));
        $chipinfo    = $this->db_model->select_multi('*', 'tbl_chips', array('id' => $chip_id));
        $amount      = $chipinfo->amount;
        $redirect_to = "https://cloudtechapps.in/axogamez/api/Gateway/phonepay_success/".$userid.'/'.$chip_id.'/'.$tid;
        $this->load->model('Phonepay');
        $paymentResult = $this->Phonepay->initiatePayment($amount, $userinfo->phone, $userinfo->email,$redirect_to);
        if ($paymentResult['success']) {
            redirect($paymentResult['payUrl']);
        } else {
            redirect('unitydl://mylink');
            // echo "Payment initiation failed. Error: " . $paymentResult['message'];
        }
    }
	
	public function phonepay_success($userid = 1, $chip_id = 1,$tid = 1)
    {
        $responseArray = $_POST;
        $transactionId = $responseArray['transactionId'];
        $responseCode  = $responseArray['code'];
        $chipinfo      = $this->db_model->select_multi('*', 'tbl_chips', array('id' => $chip_id));
        $amount        = $chipinfo->amount + $chipinfo->principle;

        if($responseCode == 'PAYMENT_SUCCESS'){

            $query = "UPDATE tbl_users SET wallet = wallet + $amount WHERE id = $userid";
            $this->db->query($query);

            $data = array(
                'userid'          => $userid,
                'payment_type'    => 'Phonepay',
                'status'          => 'Paid',
                'amount'          => $amount,
                'tid'             => $transactionId,
                'secret'          => $tid,
            ); 
            $this->db->insert('tbl_deposit', $data);                      
            $response = array('status' => 'success', 'message' => 'Wallet Updated');
        }
        else{
            $data = array(
                'userid'          => $userid,
                'payment_type'    => 'Phonepay',
                'status'          => $responseCode,
                'amount'          => $amount,
                'secret'          => $tid,
                'tid'             => $transactionId ?? '1234567890',
            ); 
            $this->db->insert('tbl_deposit', $data);
            $response = array('status' => 'error', 'message' => 'Payment Failed');
        }
        redirect('unitydl://mylink');
        $this->output->set_content_type('application/json')->set_output(json_encode($response));
    }
}