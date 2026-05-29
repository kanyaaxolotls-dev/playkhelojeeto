<?php
    $admin_row      = $this->db_model->select_multi('role, role_id', 'tbl_admin', array('id' => $this->session->admin_id));
    $name           = $admin_row ? $admin_row->role : 'Admin';
    if ($admin_row && !empty($admin_row->role_id)) {
        $this->session->set_userdata('role_id', (int) $admin_row->role_id);
        $this->session->set_userdata('panel', 'admin');
    } elseif (!$this->session->userdata('role_id')) {
        $this->session->set_userdata('role_id', 1);
        $this->session->set_userdata('panel', 'admin');
    }
    $role_id = (int) $this->session->userdata('role_id');
    if ($role_id > 0) {
        $this->load->model('rbac_model');
        if (!is_array($this->session->userdata('permission_slugs'))) {
            $this->rbac_model->sync_permissions_to_session($role_id);
        }
        $admin_whitelist = ['backend/admin', 'backend/admin/profile', 'backend/admin/change_pass', 'backend/admin/logout'];
        $rk = rbac_route_key();
        $roles_module = (strpos($rk, 'backend/roles/') === 0);
        if ($roles_module && rbac_can_manage_roles_module()) {
            // allow role setup / management
        } elseif (!in_array($rk, $admin_whitelist, true) && !rbac_can($rk, 'admin')) {
            show_error('You do not have permission to access this page.', 403);
        }
    }
    $data           = $this->db_model->select_multi('*', 'tbl_settings', array('id' => 1));
    $length         = strlen($data->name);
    $split_position = intval($length / 2); 
    $first_half     = substr($data->name, 0, $split_position);
    $second_half    = substr($data->name, $split_position);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="keyword" content="Multigame, Dragon Tiger, Aviator, color prediction, cpg, rummy">
    <link rel="shortcut icon" href="<?= base_url('axxests/setting/'.$data->logo) ?>">
    <title>Admin | <?= $data->name ?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    <link href="<?= base_url('axxests/assets/advanced-datatable/media/css/demo_page.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('axxests/assets/advanced-datatable/media/css/demo_table.css') ?>" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('axxests/assets/data-tables/DT_bootstrap.css') ?>" />
    <link href="<?= base_url('axxests/css/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/css/bootstrap-reset.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/assets/font-awesome/css/font-awesome.css') ?>" rel="stylesheet" />
    <link href="<?= base_url('axxests/assets/jquery-easy-pie-chart/jquery.easy-pie-chart.css') ?>" rel="stylesheet" type="text/css" media="screen"/>
    <link rel="stylesheet" href="<?= base_url('axxests/css/owl.carousel.css') ?>" type="text/css">
    <link href="<?= base_url('axxests/css/slidebars.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/css/style.css') ?>" rel="stylesheet">
    <link href="<?= base_url('axxests/css/style-responsive.css') ?>" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/bootstrap-datepicker/css/datepicker.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/bootstrap-colorpicker/css/colorpicker.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('assets/bootstrap-daterangepicker/daterangepicker.css') ?>" />
    <link rel="stylesheet" type="text/css" href="<?= base_url('axxests/assets/bootstrap-switch/static/stylesheets/bootstrap-switch.css') ?>" />
    <link href="<?= base_url('axxests/assets/xchart/xcharts.css') ?>" rel="stylesheet" />
  </head>
  <body class="light-sidebar-nav">

  <section id="container">
      <header class="header white-bg">
              <div class="sidebar-toggle-box">
                  <i class="fa fa-bars"></i>
              </div>
            <a href="" class="logo"><?= $first_half ?><span><?= $second_half ?></span></a>
            <div class="top-nav ">
                <ul class="nav pull-right top-menu">
                    <li class="dropdown">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <img alt="" src="<?= base_url('axxests/img/avatar1_small.jpg') ?>">
                            <span class="username"><?= $this->db_model->select('username', 'tbl_admin', array('id' => $this->session->admin_id)); ?></span>
                            <b class="caret"></b>
                        </a>
                        <ul class="dropdown-menu extended logout dropdown-menu-right">
                            <div class="log-arrow-up"></div>
                            <li><a href="<?= base_url('backend/admin/profile') ?>"><i class=" fa fa-suitcase"></i>Profile</a></li>
                            <?php if($this->session->role == 'Admin'){ ?>
                            <li><a href="<?= base_url('backend/admin/setting') ?>"><i class="fa fa-cog"></i> Settings</a></li>
                            <?php } else{ ?>
                            <li><a ><i class="fa fa-money"></i> <?= $this->db_model->select('wallet', 'tbl_admin', array('id' => $this->session->admin_id)); ?></a></li>
                            <?php } ?>
                            <li><a href="<?= base_url('backend/admin/change_pass') ?>"><i class="fa fa-key"></i>  Password</a></li>
                            <li><a href="<?= base_url('backend/admin/logout') ?>"><i class="fa fa-key"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </header>
      <aside>
          <div id="sidebar"  class="nav-collapse ">
              <ul class="sidebar-menu" id="nav-accordion">
                <?php $this->load->view('partials/rbac_sidebar_admin'); ?>
                  <li>
                    <a class="text-danger" href="<?= base_url('backend/admin/logout') ?>">
                        <i class="fa fa-key"></i>
                        <span>Logout</span>
                    </a>
                </li>
              </ul>
          </div>
        </aside>
        <section id="main-content">
        <section class="wrapper">