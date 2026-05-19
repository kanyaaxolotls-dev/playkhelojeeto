<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rewards extends CI_Controller
{
    public function give_reward()
    { 
        $amount   = $this->input->post('amount');
        $phone    = $this->input->post('phone'); 
        $inc_name = $this->input->post('inc_name');

        if ($amount == NULL or $phone == NULL) {
            $data['title']  = 'Rewards' ;
            $data['title2'] = 'Distribute rewards' ;
            $data['data']   = $this->db->get('tbl_users')->result();
            $this->load->view('admin/Rewards/give', $data);
        } else {
           
            $user = $this->db->get_where('tbl_users', array('phone' => $phone))->row();
            
            if (!$user) {
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">User not found with the provided phone number.</div>');
                redirect(site_url('backend/rewards/give_reward'));
            }

            $userid = $user->id;

            $c_bal = $this->db_model->select('wallet', 'tbl_users', array('id' => $userid));

            $data_transaction = array(
                'userid'    => $userid,
                'amount'    => $amount,
                'wallet'    => $c_bal + $amount, 
                'type'      => $inc_name,
                'date'      => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tbl_transactions', $data_transaction);
            
            $data_rewards = array(
                'added_by'  => $this->session->admin_id,
                'userid'    => $userid,
                'amount'    => $amount,
                'type'      => $inc_name,
                'date'      => date('Y-m-d H:i:s'),
            );
            $this->db->insert('tbl_rewards', $data_rewards);

            if ($this->db->affected_rows() > 0) {
                $this->db->update('tbl_users', array('wallet' => $c_bal + $amount), array('id' => $userid));

                $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Reward Credited Successfully...</div>');
                redirect(site_url('backend/rewards/give_reward'));
            } else {
                $error_message = $this->db->error()['message'];
                echo "Error inserting into Rewards table: $error_message";
            }
        }
    }
    
    public function reward_history()
    {
        $this->db->order_by('id', 'desc');  
        if ($this->session->role == 'Admin') {
            $data['data'] = $this->db->get('tbl_rewards')->result();
        } else {
            $this->db->where('added_by', $this->session->admin_id);
            $data['data'] = $this->db->get('tbl_rewards')->result();
        }
        $data['title']  = 'Rewards';
        $data['title2'] = 'Rewards History';
        $this->load->view('admin/Rewards/history', $data);
    }
}
