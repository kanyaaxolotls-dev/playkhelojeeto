<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_dashboard extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->model('db_model'); // Add this line
        // Start session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index()
    {
        $is_logged_in = false;
        $current_user_id = null;
        $current_user_name = null;
        $current_user_phone = null;
        
        // Check if user is logged in via session
        if (isset($_SESSION['user_id']) && isset($_SESSION['login_session'])) {
            // Verify session with database
            $user = $this->db_model->select_multi('*', 'tbl_users', array('id' => $_SESSION['user_id']));
            if ($user && $user->login_session == $_SESSION['login_session']) {
                $current_user_id = $user->id;
                $current_user_name = $user->name;
                $current_user_phone = $user->phone;
                $is_logged_in = true;
            } else {
                session_destroy();
            }
        }
        
        // Pass data to view
        $data = array(
            'is_logged_in' => $is_logged_in,
            'current_user_id' => $current_user_id,
            'current_user_name' => $current_user_name,
            'current_user_phone' => $current_user_phone
        );
        
        $this->load->view('user_dashboard', $data);
    }
    
    public function logout() {
        // Update database to set is_login = 0
        if (isset($_SESSION['user_id'])) {
            $where = "id = " . $_SESSION['user_id'];
            $this->db_model->update(array('is_login' => 0), 'tbl_users', $where);
        }
        
        // Destroy session
        session_destroy();
        
        // Redirect to login page
        redirect(base_url('User_dashboard'));
    }
}
?>