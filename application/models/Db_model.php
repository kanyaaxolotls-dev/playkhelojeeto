<?php


defined('BASEPATH') OR exit('No direct script access allowed');

class Db_model extends CI_Model
{

    public function select($data, $table, $where = "1=1")
    {
        $this->db->select($data)->from($table)->where($where)->order_by('id', 'DESC')->limit(1);
        $result = $this->db->get()->row();
        return $result->$data;
    }

    public function select_multi($data, $table, $where = "1=1")
    {
        $this->db->select($data)->from($table)->where($where)->order_by('id', 'DESC')->limit(1);
        $result = $this->db->get()->row();
        return $result;
    }

    public function update($data, $table, $where = "1=1")
    {
        $this->db->where($where);
        if($this->db->update($table, $data)){
            return 1;
        }
        else{
            return 2;
        }
        
    }

    public function count_all($table, $where = "1=1")
    {
        $this->db->from($table);
        $this->db->where($where);
        return $this->db->count_all_results();
    }

    public function sum($data, $table, $where = "1=1")
    {
        $this->db->select_sum($data);
        $this->db->where($where);
        $this->db->from($table);
        $result = $this->db->get()->row();
        if($result->$data == NULL){
            $result->$data = 0;
        }
        return $result->$data;
    }

    public function get_all_data($table, $where = NULL,$order = 'ASC', $order_by = 'id') {
        if ($where){
            $this->db->where($where);
        }
        $this->db->order_by($order_by, $order);
        $query = $this->db->get($table);
        return $query->result();
    }  


    public function get_limited_records($table,$limit,$order = 'DESC',$where = NULL) {
        $this->db->select('*');
        if ($where){
            $this->db->where($where);
        }
        $this->db->from($table);
        $this->db->order_by('id', $order);
        $this->db->limit($limit);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array(); 
        }
    }

    public function get_last_records($table,$fields,$limit,$order = 'DESC') {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->order_by('id', $order);
        $this->db->limit($limit);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array(); 
        }
    }

    public function get_specific_records($table,$where,$fields) {
        $this->db->select($fields);
        $this->db->from($table);
        $this->db->where($where);
        $query = $this->db->get();
        if ($query->num_rows() > 0) {
            return $query->result();
        } else {
            return array(); 
        }
    }

    public function count_records_by_day($table) {
        $counts = array();
        $lastMonday = strtotime('last monday', strtotime('-1 week'));
        for ($i = 0; $i < 7; $i++) {
            $currentDay = date('Y-m-d', strtotime("+$i days", $lastMonday));
            $this->db->where("DATE_FORMAT(date, '%Y-%m-%d') = '$currentDay'");
            $counts[$currentDay] = $this->db->count_all_results($table);
        }
        return $counts;
    }

    public function sendOtpSms($otp, $phone_numbers) {
        $auth_key    = '6oXugmG1YdOEFZ4ebivPLxQrcUHKIVNn2JWlfM9tjw58SDBCTzOQVZyvzeikfpFo4wNIr3mgHhKPjq62';
        $api_url     = 'https://www.fast2sms.com/dev/bulkV2';
        $sender_id   = 'COMNRR'; 
        $message_id  = '169961'; 
        $variables_values = $otp; 
        $url = sprintf(
            "%s?authorization=%s&sender_id=%s&message=%s&variables_values=%s&route=dlt&numbers=%s",
            $api_url,
            urlencode($auth_key),
            urlencode($sender_id),
            urlencode($message_id),
            urlencode($variables_values),
            urlencode($phone_numbers)
        );
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
        ));
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return 'cURL Error #:' . $err;
        } else {
            return array('status' => 'success', 'message' => 'OTP has been sent successfully to your mobile number.','OTP' => $otp,'phone' => $phone_numbers);
        }
    }

    
    public function generate_random_string($length){
        $characters   = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;
    }

}
