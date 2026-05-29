<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter 3 only auto-loads MY_Controller.php from application/core/.
 * All panel base controllers must live in this file.
 */
class MY_Controller extends CI_Controller {

    protected $required_panel = null;
    protected $rbac_whitelist = [];

    public function __construct()
    {
        parent::__construct();
        $this->load->helper('rbac');
        $this->load->model('rbac_model');
    }

    protected function rbac_guard()
    {
        $route = rbac_route_key();

        $whitelist = array_merge([
            'auth',
            'auth/index',
            'auth/authenticate',
            'auth/logout',
            'backend/login',
            'dealer/login',
            'dealer/login/authenticate',
            'dealer/login/logout',
            'distributor_login',
            'distributor_login/index',
            'distributor_login/authenticate',
            'distributor_login/logout',
            'backend/distributor_login',
            'backend/distributor_login/authenticate',
        ], $this->rbac_whitelist);

        if (in_array($route, $whitelist, true)) {
            return;
        }

        if ($this->required_panel && !rbac_can($route, $this->required_panel)) {
            show_error('You do not have permission to access this page.', 403);
        }
    }
}

class Distributor_Controller extends MY_Controller {

    protected $required_panel = 'distributor';

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->distributor_id) {
            redirect(site_url('distributor_login'));
        }
        $this->rbac_guard();
    }
}

class Dealer_Controller extends MY_Controller {

    protected $required_panel = 'dealer';

    public function __construct()
    {
        parent::__construct();
        if (!$this->session->userdata('dealer_id')) {
            redirect(site_url('dealer/login'));
        }
        $this->rbac_guard();
    }
}
