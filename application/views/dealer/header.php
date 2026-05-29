<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dealer Panel | <?= $title ?? 'Dashboard' ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f5;
            overflow-x: hidden;
        }
        
        /* ========== SIDEBAR STYLES ========== */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 280px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 2px 0 20px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 25px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        .sidebar-header h3 i {
            margin-right: 10px;
            color: #00b4d8;
        }
        
        .sidebar-header p {
            margin: 10px 0 0;
            font-size: 12px;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu .nav-item {
            list-style: none;
            margin-bottom: 5px;
        }
        
        .sidebar-menu .nav-link {
            padding: 12px 25px;
            color: rgba(255,255,255,0.8);
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
            border-left-color: #00b4d8;
        }
        
        .sidebar-menu .nav-link.active {
            background: rgba(0,180,216,0.15);
            color: white;
            border-left-color: #00b4d8;
        }
        
        .sidebar-menu .nav-link i {
            width: 22px;
            font-size: 16px;
            text-align: center;
        }
        
        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 280px;
            padding: 20px 30px;
            min-height: 100vh;
        }
        
        /* ========== TOP NAVBAR ========== */
        .top-navbar {
            background: white;
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .welcome-text h5 {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
            color: #1a1a2e;
        }
        
        .welcome-text p {
            margin: 5px 0 0;
            font-size: 12px;
            color: #6c757d;
        }
        
        .wallet-box {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            border-radius: 12px;
            padding: 10px 20px;
            text-align: center;
            color: white;
        }
        
        .wallet-box small {
            font-size: 11px;
            opacity: 0.8;
        }
        
        .wallet-box h6 {
            margin: 5px 0 0;
            font-size: 18px;
            font-weight: 700;
        }
        
        .user-dropdown {
            cursor: pointer;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .user-dropdown:hover {
            background: #e9ecef;
        }
        
        /* ========== CARDS ========== */
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            border: 1px solid rgba(0,0,0,0.03);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-number {
            font-size: 28px;
            font-weight: 800;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 13px;
            font-weight: 500;
        }
        
        /* Card Colors */
        .bg-primary-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .bg-success-gradient {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }
        
        .bg-warning-gradient {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .bg-info-gradient {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        /* Tables */
        .data-table {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .data-table thead th {
            background: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            font-size: 13px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .data-table tbody td {
            padding: 12px 15px;
            vertical-align: middle;
            font-size: 13px;
        }
        
        /* Buttons */
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
            color: white;
        }
        
        /* Badges */
        .badge-active {
            background: #28a745;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
        }
        
        .badge-inactive {
            background: #dc3545;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: -280px;
            }
            .main-content {
                margin-left: 0;
            }
            .sidebar.active {
                margin-left: 0;
            }
        }
        
        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 5px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
<?php
if ($this->session->userdata('dealer_id')) {
    $dealer = $this->db->get_where('tbl_dealers', ['id' => $this->session->userdata('dealer_id')])->row();
    if ($dealer && !empty($dealer->role_id)) {
        $this->session->set_userdata(['role_id' => (int) $dealer->role_id, 'panel' => 'dealer']);
    }
    if (!is_array($this->session->userdata('permission_slugs')) && $this->session->userdata('role_id')) {
        $this->load->model('rbac_model');
        $this->rbac_model->sync_permissions_to_session((int) $this->session->userdata('role_id'));
    }
}
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-user-tie"></i> Dealer Panel</h3>
        <p><?= $this->session->userdata('dealer_name') ?? 'Dealer' ?></p>
    </div>
    <div class="sidebar-menu">
        <ul class="nav flex-column">
            <?php $this->load->view('partials/rbac_sidebar_modern', ['panel' => 'dealer']); ?>
            <li class="nav-item">
                <a class="nav-link text-danger" href="<?= site_url('dealer/login/logout') ?>">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- Main Content -->
<div class="main-content" id="mainContent">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <div class="welcome-text">
            <h5><i class="fas fa-hand-wave"></i> Welcome back, <?= $this->session->userdata('dealer_name') ?? 'Dealer' ?>!</h5>
            <p><?= date('l, d F Y') ?></p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <div class="wallet-box">
                <small><i class="fas fa-wallet"></i> Wallet Balance</small>
                <h6>₹ <?= number_format($this->session->userdata('dealer_wallet') ?? 0, 2) ?></h6>
            </div>
            <div class="user-dropdown dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                    <div class="bg-primary-gradient rounded-circle" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user text-white"></i>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item" href="<?= site_url('dealer/profile') ?>"><i class="fas fa-user-circle me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="<?= site_url('dealer/change_password') ?>"><i class="fas fa-key me-2"></i> Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="<?= site_url('dealer/login/logout') ?>"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </div>