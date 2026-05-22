<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Session_check extends CI_Controller {

    public function __construct() {
        parent::__construct();
    }

    public function index() {
        // Set JSON response
        $this->output->set_content_type('application/json');
        
        // Get all session data
        $session_data = [
            'admin_id' => $this->session->admin_id,
            'user_id' => $this->session->user_id,
            'admin_logged_in' => $this->session->admin_logged_in,
            'username' => $this->session->username,
            'name' => $this->session->name,
            'role' => $this->session->role,
            'email' => $this->session->email,
            'all_session' => $_SESSION
        ];
        
        // Check if session exists
        if($this->session->admin_id != NULL) {
            echo json_encode([
                'success' => true,
                'message' => 'Admin is logged in',
                'session_data' => $session_data,
                'admin_id' => $this->session->admin_id
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Admin is NOT logged in',
                'session_data' => $session_data
            ]);
        }
    }
    
    // Simple check
    public function simple() {
        if($this->session->admin_id != NULL) {
            echo "Admin is logged in. Admin ID: " . $this->session->admin_id;
        } else {
            echo "Admin is NOT logged in. Please login first.";
        }
    }
}
?>