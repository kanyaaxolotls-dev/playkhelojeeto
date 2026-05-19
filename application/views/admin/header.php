<?php
    $name           = $this->db_model->select('role', 'tbl_admin', array('id' => $this->session->admin_id));
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
                <?php
                    $options = $this->db_model->get_all_data('tbl_task_manager', 'status = 1', 'DESC', 'position');
                    $tasks = $this->db_model->select('tasks', 'tbl_roles', array('name' => $name));
                    $array = explode(",", $tasks);
                    $this->db->where_in('id', $array);
                    $this->db->where('status',1);
                    $this->db->where('child_of',0);
                    $this->db->order_by('position', 'asc');
                    $query  = $this->db->get('tbl_task_manager');     
                    $optins = $query->result();
                    $i      = 1;
                    foreach($optins as $menu){
                    $parts = explode('/', $menu->url);
                    if($this->uri->segment(2) == $parts[1]){
                        $cls     = 'active';
                    }
                    else{
                        $cls     = '';
                    }
                    $this->db->where_in('id', $array);
                    $this->db->where('child_of',$menu->id);
                    $resultr = $this->db->get('tbl_task_manager')->num_rows();
                    if($menu->child_of == 0 and $resultr == 0 ){
                        $i++;
                ?>
                  <li>
                      <a class="<?= $cls ?>" href="<?= base_url($menu->url) ?>">
                          <i class="<?= $menu->img ?>"></i>
                          <span><?= $menu->name ?></span>
                      </a>
                  </li>
                  <?php }else{ ?>
                  <li class="sub-menu">
                      <a href="javascript:;" class="<?= $cls ?>">
                          <i class="<?= $menu->img ?>"></i>
                          <span><?= $menu->name ?></span>
                      </a>
                      <ul class="sub">
                        <?php
                            $child_options     = $this->db_model->get_all_data('tbl_task_manager','status = 1 And child_of ='.$menu->id);
                            foreach($child_options as $child_menu){
                                $parts = explode('/', $child_menu->url);
                                if($this->uri->segment(3) == $parts[2]){
                                    $cls     = 'active';
                                }
                                else{
                                    $cls     = '';
                                }
                        ?>
                          <li class="<?= $cls ?>"><a  href="<?= base_url($child_menu->url) ?>"><?= $child_menu->name ?></a></li>
                        <?php } ?>
                      </ul>
                  </li>
                  <?php } } ?>
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