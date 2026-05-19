<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && isset($_SESSION['login_session']);
$current_user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$current_user_name = $_SESSION['user_name'] ?? '';
$current_user_phone = $_SESSION['user_phone'] ?? '';

// Get settings
$data = $this->db_model->select_multi('*', 'tbl_settings', array('id' => 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Dashboard | <?= isset($data->name) ? $data->name : 'Game Platform' ?></title>
  <link href="<?= base_url('axxests/setting/'.$data->logo); ?>" rel="icon">
  <link href="<?= base_url('Web-assets/vendor/bootstrap/css/bootstrap.min.css'); ?>" rel="stylesheet">
  <link href="<?= base_url('Web-assets/vendor/bootstrap-icons/bootstrap-icons.css'); ?>" rel="stylesheet">
  <link href="<?= base_url('Web-assets/css/style.css'); ?>" rel="stylesheet">
  <style>
    body {
      background: #f0f2f5;
      font-family: 'Segoe UI', Roboto, 'Helvetica Neue', sans-serif;
    }
    
    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      width: 280px;
      background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
      color: #fff;
      transition: all 0.3s;
      z-index: 1030;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    }
    
    .sidebar-header {
      padding: 1.5rem;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      text-align: center;
    }
    
    .sidebar-header h4 {
      margin: 0;
      font-weight: 600;
      color: #fff;
    }
    
    .sidebar-header p {
      font-size: 0.75rem;
      margin: 0.5rem 0 0;
      opacity: 0.7;
    }
    
    .sidebar-nav {
      padding: 1rem 0;
    }
    
    .nav-item {
      list-style: none;
      margin: 0;
      padding: 0;
    }
    
    .nav-link-custom {
      display: flex;
      align-items: center;
      padding: 0.85rem 1.5rem;
      color: rgba(255,255,255,0.8);
      text-decoration: none;
      transition: all 0.3s;
      gap: 12px;
      font-size: 0.95rem;
    }
    
    .nav-link-custom:hover {
      background: rgba(255,255,255,0.1);
      color: #fff;
    }
    
    .nav-link-custom.active {
      background: linear-gradient(90deg, #e94560 0%, #ff6b6b 100%);
      color: #fff;
      border-left: 3px solid #fff;
    }
    
    .nav-link-custom i {
      width: 24px;
      font-size: 1.2rem;
    }
    
    /* Main Content */
    .main-content {
      margin-left: 280px;
      padding: 20px;
      transition: all 0.3s;
    }
    
    /* Login Container */
    .login-container {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    
    .login-card {
      background: white;
      border-radius: 1.5rem;
      padding: 2rem;
      width: 100%;
      max-width: 400px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .login-card h3 {
      text-align: center;
      margin-bottom: 1.5rem;
      color: #1a1a2e;
    }
    
    /* Cards */
    .dashboard-card {
      border: none;
      border-radius: 1rem;
      background: #ffffff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border-radius: 1rem;
      padding: 1rem;
    }
    
    .stat-card-warning {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    
    .stat-card-success {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    
    .stat-card-info {
      background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    
    .stat-value {
      font-size: 1.75rem;
      font-weight: 700;
    }
    
    .badge-status {
      padding: 0.35rem 0.75rem;
      border-radius: 50px;
      font-size: 0.7rem;
      font-weight: 600;
    }
    
    .filter-section {
      background: #fff;
      border-radius: 1rem;
      padding: 1.25rem;
      margin-bottom: 1.5rem;
    }
    
    .section-title {
      font-size: 1.25rem;
      font-weight: 600;
      margin-bottom: 1.25rem;
      padding-bottom: 0.75rem;
      border-bottom: 2px solid #e94560;
      display: inline-block;
    }
    
    .user-info-bar {
      background: white;
      border-radius: 1rem;
      padding: 0.75rem 1.5rem;
      margin-bottom: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
    }
    
    .logout-btn {
      background: #dc3545;
      color: white;
      border: none;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      cursor: pointer;
      transition: all 0.3s;
    }
    
    .logout-btn:hover {
      background: #c82333;
    }
    
    @media (max-width: 768px) {
      .sidebar {
        left: -280px;
      }
      .sidebar.active {
        left: 0;
      }
      .main-content {
        margin-left: 0;
      }
      .menu-toggle {
        display: block;
        position: fixed;
        top: 15px;
        left: 15px;
        z-index: 1040;
        background: #1a1a2e;
        color: white;
        border: none;
        border-radius: 8px;
        padding: 8px 12px;
      }
    }
    
    @media (min-width: 769px) {
      .menu-toggle {
        display: none;
      }
    }
  </style>
</head>
<body>

<?php if (!$is_logged_in): ?>
<!-- Login Page -->
<div class="login-container">
  <div class="login-card">
    <h3><i class="bi bi-controller"></i> <?= isset($data->name) ? $data->name : 'Game Platform' ?></h3>
    <div id="loginMessage" class="alert alert-danger d-none"></div>
    <form id="loginForm">
      <div class="mb-3">
        <label class="form-label">Phone Number</label>
        <input type="tel" id="loginPhone" class="form-control" placeholder="Enter your phone number" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Login</button>
    </form>
   
  </div>
</div>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault();
  const phone = document.getElementById('loginPhone').value;
  const password = document.getElementById('loginPassword').value;
  const messageDiv = document.getElementById('loginMessage');
  
  try {
    const response = await fetch('<?= base_url('index.php/api/Auth/login') ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ phone, password })
    });
    const data = await response.json();
    
    if (data.status === 'success') {
      // Store session info
      sessionStorage.setItem('user_id', data.data.id);
      sessionStorage.setItem('user_name', data.data.name);
      sessionStorage.setItem('user_phone', data.data.phone);
      sessionStorage.setItem('login_session', data.login_session);
      window.location.reload();
    } else {
      messageDiv.textContent = data.message;
      messageDiv.classList.remove('d-none');
    }
  } catch (error) {
    messageDiv.textContent = 'Login failed. Please try again.';
    messageDiv.classList.remove('d-none');
  }
});
</script>

<?php else: ?>
<!-- Dashboard Content -->
<button class="menu-toggle" id="menuToggle">
  <i class="bi bi-list"></i>
</button>

<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <h4><i class="bi bi-controller"></i> <?= isset($data->name) ? $data->name : 'Game Platform' ?></h4>
    <p>Welcome, <?= htmlspecialchars($current_user_name) ?></p>
  </div>
  <ul class="sidebar-nav">
    <li class="nav-item">
      <a href="#" class="nav-link-custom active" data-section="dashboard">
        <i class="bi bi-speedometer2"></i> Dashboard
      </a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link-custom" data-section="wallet">
        <i class="bi bi-wallet2"></i> Wallet Overview
      </a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link-custom" data-section="history">
        <i class="bi bi-clock-history"></i> Game History
      </a>
    </li>
    <li class="nav-item">
      <a href="#" class="nav-link-custom" data-section="statistics">
        <i class="bi bi-graph-up"></i> Statistics
      </a>
    </li>
  </ul>
</div>

<div class="main-content">
  <div class="container-fluid">
    
    <!-- User Info Bar -->
    <div class="user-info-bar">
      <div>
        <i class="bi bi-person-circle"></i> 
        <strong><?= htmlspecialchars($current_user_name) ?></strong> 
        (<?= htmlspecialchars($current_user_phone) ?>)
      </div>
      <button class="logout-btn" id="logoutBtn">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </div>

    <div id="dashboardMessage" class="alert alert-warning d-none"></div>

    <!-- Dashboard Section -->
    <div id="dashboardSection">
      <div class="row g-4 mb-4">
        <div class="col-md-3 col-sm-6">
          <div class="stat-card">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small>Main Wallet</small>
                <div class="stat-value" id="mainWallet">0</div>
              </div>
              <i class="bi bi-wallet2 fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stat-card stat-card-success">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small>Winning Wallet</small>
                <div class="stat-value" id="winningWallet">0</div>
              </div>
              <i class="bi bi-trophy fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stat-card stat-card-warning">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small>Total Bets</small>
                <div class="stat-value" id="totalBetAmount">0</div>
              </div>
              <i class="bi bi-dice-6 fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
        <div class="col-md-3 col-sm-6">
          <div class="stat-card stat-card-info">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <small>Total Wins</small>
                <div class="stat-value" id="totalWinningAmount">0</div>
              </div>
              <i class="bi bi-gem fs-1 opacity-50"></i>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4 mb-4">
        <div class="col-md-2 col-6">
          <div class="dashboard-card card text-center p-3">
            <small class="text-muted">Win Bets</small>
            <h4 class="mb-0 text-success" id="statWinBets">0</h4>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="dashboard-card card text-center p-3">
            <small class="text-muted">Lost Bets</small>
            <h4 class="mb-0 text-danger" id="statLostBets">0</h4>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="dashboard-card card text-center p-3">
            <small class="text-muted">Pending Bets</small>
            <h4 class="mb-0 text-warning" id="statPendingBets">0</h4>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="dashboard-card card text-center p-3">
            <small class="text-muted">Cancelled Bets</small>
            <h4 class="mb-0 text-secondary" id="statCancelledBets">0</h4>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="dashboard-card card text-center p-3">
            <small class="text-muted">Profit / Loss</small>
            <h4 class="mb-0" id="statProfitLoss">0</h4>
          </div>
        </div>
        <div class="col-md-2 col-6">
          <div class="dashboard-card card text-center p-3">
            <small class="text-muted">Most Played</small>
            <h4 class="mb-0 small" id="statMostPlayed">—</h4>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="dashboard-card card">
            <div class="card-body">
              <h5 class="section-title"><i class="bi bi-clock me-2"></i>Recent Activity</h5>
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead class="table-light">
                    <tr><th>Game</th><th>Details</th><th>Status</th><th>Amount</th><th>Time</th></tr>
                  </thead>
                  <tbody id="recentTableBody"><tr><td colspan="5" class="text-center">Loading...</td></tr></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Wallet Section -->
    <div id="walletSection" style="display: none;">
      <div class="row">
        <div class="col-md-6 mb-4">
          <div class="stat-card"><i class="bi bi-cash-stack fs-1"></i><h5>Main Wallet Balance</h5><h2 id="walletMainBalance">0</h2></div>
        </div>
        <div class="col-md-6 mb-4">
          <div class="stat-card stat-card-success"><i class="bi bi-trophy fs-1"></i><h5>Winning Wallet</h5><h2 id="walletWinningBalance">0</h2></div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-4"><div class="dashboard-card card p-3 text-center"><small>Freeze Wallet</small><h4 id="walletFreezeBalance">0</h4></div></div>
        <div class="col-md-4"><div class="dashboard-card card p-3 text-center"><small>Bonus</small><h4 id="walletBonus">0</h4></div></div>
        <div class="col-md-4"><div class="dashboard-card card p-3 text-center"><small>Net Profit</small><h4 id="walletNetProfit">0</h4></div></div>
      </div>
    </div>

    <!-- History Section -->
    <div id="historySection" style="display: none;">
      <div class="filter-section">
        <div class="row g-3">
          <div class="col-md-2"><label class="form-label">Game</label><select id="filterGame" class="form-select"><option value="">All Games</option><option value="1">Funtarget</option><option value="2">Lucky36</option><option value="3">Lucky36gme</option></select></div>
          <div class="col-md-2"><label class="form-label">Status</label><select id="filterStatus" class="form-select"><option value="">All Status</option><option value="Pending">Pending</option><option value="Won">Won</option><option value="Lost">Lost</option><option value="Cancelled">Cancelled</option></select></div>
          <div class="col-md-2"><label class="form-label">From Date</label><input id="filterStart" type="date" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">To Date</label><input id="filterEnd" type="date" class="form-control"></div>
          <div class="col-md-2"><label class="form-label">Period ID</label><input id="filterPeriod" type="text" class="form-control" placeholder="Search period"></div>
          <div class="col-md-2"><button id="applyFilters" class="btn btn-primary w-100"><i class="bi bi-search"></i> Apply</button></div>
        </div>
      </div>
      <div class="dashboard-card card">
        <div class="card-body">
          <h5 class="section-title"><i class="bi bi-table me-2"></i>Game History</h5>
          <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
              <thead><tr><th>Game</th><th>Period ID</th><th>Bet Type</th><th>Bet Value</th><th>Amount</th><th>Result</th><th>Status</th><th>Win Amount</th><th>Date / Time</th></tr></thead>
              <tbody id="historyTableBody"><tr><td colspan="9" class="text-center">Loading...</td></tr></tbody>
            </table>
          </div>
          <div class="d-flex justify-content-between align-items-center mt-4">
            <div><span id="historySummary"></span></div>
            <div><button id="historyPrev" class="btn btn-sm btn-outline-secondary me-2">Previous</button><button id="historyNext" class="btn btn-sm btn-outline-secondary">Next</button></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Section -->
    <div id="statisticsSection" style="display: none;">
      <div class="row">
        <div class="col-md-6 mb-4"><div class="dashboard-card card p-4"><h5 class="mb-3">Bet Summary</h5><canvas id="betChart" height="200"></canvas></div></div>
        <div class="col-md-6 mb-4"><div class="dashboard-card card p-4"><h5 class="mb-3">Game Distribution</h5><canvas id="gameChart" height="200"></canvas></div></div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= base_url('Web-assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>
<script>
const baseUrl = '<?= base_url(''); ?>';
const LOGGED_IN_USER_ID = <?= json_encode($current_user_id); ?>;

let state = { userId: LOGGED_IN_USER_ID, page: 1, limit: 10 };

// Sidebar Navigation
const sections = ['dashboardSection', 'walletSection', 'historySection', 'statisticsSection'];
document.querySelectorAll('.nav-link-custom').forEach(link => {
  link.addEventListener('click', (e) => {
    e.preventDefault();
    const section = link.dataset.section;
    document.querySelectorAll('.nav-link-custom').forEach(l => l.classList.remove('active'));
    link.classList.add('active');
    sections.forEach(s => document.getElementById(s).style.display = 'none');
    if (section === 'dashboard') document.getElementById('dashboardSection').style.display = 'block';
    else if (section === 'wallet') document.getElementById('walletSection').style.display = 'block';
    else if (section === 'history') document.getElementById('historySection').style.display = 'block';
    else if (section === 'statistics') document.getElementById('statisticsSection').style.display = 'block';
  });
});

// Logout
document.getElementById('logoutBtn')?.addEventListener('click', async () => {
  window.location.href = '<?= base_url('User_dashboard/logout') ?>';
});

function showMessage(text, type = 'warning') {
  const msg = document.getElementById('dashboardMessage');
  msg.textContent = text;
  msg.className = `alert alert-${type}`;
  msg.classList.remove('d-none');
  setTimeout(() => msg.classList.add('d-none'), 5000);
}

function apiPost(endpoint, data) {
  return fetch(baseUrl + 'api/Dashboard/' + endpoint, {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams(data)
  }).then(res => res.json());
}

function formatCurrency(value) {
  return Number(value || 0).toLocaleString('en-IN', { style: 'currency', currency: 'INR', maximumFractionDigits: 2 });
}

function renderStatusBadge(status) {
  const s = (status || '').toLowerCase();
  const cls = s === 'won' ? 'bg-success text-white' : s === 'lost' ? 'bg-danger text-white' : s === 'pending' ? 'bg-warning' : 'bg-secondary text-white';
  return `<span class="badge-status ${cls}">${status || '-'}</span>`;
}

function updateUI(wallet, summary) {
  document.getElementById('mainWallet').innerHTML = formatCurrency(wallet.main_wallet);
  document.getElementById('winningWallet').innerHTML = formatCurrency(wallet.winning_wallet);
  document.getElementById('totalBetAmount').innerHTML = formatCurrency(summary.total_bets_amount);
  document.getElementById('totalWinningAmount').innerHTML = formatCurrency(summary.total_winning_amount);
  document.getElementById('statWinBets').textContent = summary.total_win_bets;
  document.getElementById('statLostBets').textContent = summary.total_lost_bets;
  document.getElementById('statPendingBets').textContent = summary.total_pending_bets;
  document.getElementById('statCancelledBets').textContent = summary.total_cancelled_bets;
  document.getElementById('statProfitLoss').innerHTML = formatCurrency(summary.profit_loss);
  document.getElementById('statMostPlayed').textContent = summary.most_played_game || 'N/A';
  document.getElementById('walletMainBalance').innerHTML = formatCurrency(wallet.main_wallet);
  document.getElementById('walletWinningBalance').innerHTML = formatCurrency(wallet.winning_wallet);
  document.getElementById('walletFreezeBalance').innerHTML = formatCurrency(wallet.freeze_wallet || 0);
  document.getElementById('walletBonus').innerHTML = formatCurrency(wallet.bonus || 0);
  document.getElementById('walletNetProfit').innerHTML = formatCurrency(summary.profit_loss);
}

async function loadSummary() {
  const data = await apiPost('dashboard_summary', { userid: state.userId });
  if (data.status === 'success') updateUI(data.wallet, data.summary);
  return data;
}

async function loadRecent() {
  const data = await apiPost('recent_bets', { userid: state.userId });
  const tbody = document.getElementById('recentTableBody');
  if (data.status !== 'success' || !data.recent_bets?.length) {
    tbody.innerHTML = '<tr><td colspan="5" class="text-center">No recent bets found.</td></tr>';
    return;
  }
  tbody.innerHTML = data.recent_bets.map(bet => `
    <tr><td><strong>${bet.game_name}</strong></td><td>${bet.bet_type || bet.bet_value || '-'}</td><td>${renderStatusBadge(bet.status)}</td><td>${formatCurrency(bet.bet_amount)}</td><td><small>${bet.date}</small></td></tr>
  `).join('');
}

async function loadHistory() {
  const data = await apiPost('game_history', {
    userid: state.userId, page: state.page, limit: state.limit,
    game_id: document.getElementById('filterGame')?.value || '',
    status: document.getElementById('filterStatus')?.value || '',
    start_date: document.getElementById('filterStart')?.value || '',
    end_date: document.getElementById('filterEnd')?.value || '',
    period_id: document.getElementById('filterPeriod')?.value || '',
  });
  const tbody = document.getElementById('historyTableBody');
  if (data.status !== 'success' || !data.history?.length) {
    tbody.innerHTML = '<tr><td colspan="9" class="text-center">No history found.</td></tr>';
    return;
  }
  tbody.innerHTML = data.history.map(row => `
    <tr><td><strong>${row.game_name}</strong></td><td>${row.period_id || '-'}</td><td>${row.bet_type || '-'}</td><td>${row.bet_value || '-'}</td><td>${formatCurrency(row.bet_amount)}</td><td>${row.win_number || '-'}</td><td>${renderStatusBadge(row.status)}</td><td>${formatCurrency(row.win_amount)}</td><td><small>${row.date}</small></td></tr>
  `).join('');
  const totalPages = Math.ceil((data.total_records || 0) / state.limit);
  document.getElementById('historySummary').textContent = `Page ${state.page} of ${totalPages || 1} • ${data.total_records || 0} records`;
  document.getElementById('historyPrev').disabled = state.page <= 1;
  document.getElementById('historyNext').disabled = state.page >= totalPages;
}

async function loadDashboard() {
  try {
    await Promise.all([loadSummary(), loadRecent(), loadHistory()]);
  } catch(e) { showMessage('Failed to load dashboard data.', 'danger'); }
}

// Event Listeners
document.getElementById('applyFilters')?.addEventListener('click', () => { state.page = 1; loadHistory(); loadSummary(); });
document.getElementById('historyPrev')?.addEventListener('click', () => { if (state.page > 1) { state.page--; loadHistory(); } });
document.getElementById('historyNext')?.addEventListener('click', () => { state.page++; loadHistory(); });

// Load dashboard on page load
loadDashboard();

// Mobile menu toggle
document.getElementById('menuToggle')?.addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('active');
});
</script>
<?php endif; ?>
</body>
</html>