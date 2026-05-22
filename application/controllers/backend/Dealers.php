<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dealers extends CI_Controller {

    public function __construct() {
        parent::__construct();
        if ($this->session->admin_id == NULL) {
            redirect(site_url('backend/login'));
        }
        $this->load->model('Hierarchy_model');
        $this->load->model('db_model');
    }

    public function index() {
        $data['dealers'] = $this->Hierarchy_model->get_all_dealers();
        $data['title'] = 'Manage Dealers';
        $this->load->view('admin/dealers/index', $data);
    }

    public function create() {
        if ($this->input->post('name')) {
            $name = trim($this->input->post('name'));
            $phone = trim($this->input->post('phone'));
            $password = trim($this->input->post('password'));
            $distributor_id = intval($this->input->post('distributor_id'));
            $commission_rate = floatval($this->input->post('commission_rate'));

            if ($name && $phone && $password && $distributor_id) {
                if ($this->db_model->count_all('tbl_dealers', ['phone' => $phone]) > 0) {
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Dealer phone already exists.</div>');
                } else {
                    $this->db->insert('tbl_dealers', [
                        'name' => $name,
                        'phone' => $phone,
                        'password' => $password,
                        'wallet' => 0,
                        'commission_rate' => $commission_rate ?: 2.0,
                        'status' => 1,
                        'distributor_id' => $distributor_id,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Dealer created successfully.</div>');
                }
            } else {
                $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Please fill all required fields.</div>');
            }
            redirect(site_url('backend/dealers'));
        }

        $data['distributors'] = $this->Hierarchy_model->get_all_distributors();
        $data['title'] = 'Create Dealer';
        $this->load->view('admin/dealers/create', $data);
    }

    public function view($id = 0) {
        $dealer = $this->Hierarchy_model->get_dealer($id);
        if (!$dealer) {
            show_404();
            return;
        }

        $data['dealer'] = $dealer;
        $data['users'] = $this->Hierarchy_model->get_my_users_for_dealer($id);
        $summary = $this->Hierarchy_model->get_commission_summary(['dealer_id' => $id, 'commission_type' => 'dealer']);
        $data['commission_total'] = $summary['total'];
        $data['user_count'] = count($data['users']);
        $data['title'] = 'Dealer Dashboard';
        $this->load->view('admin/dealers/view', $data);
    }
}
