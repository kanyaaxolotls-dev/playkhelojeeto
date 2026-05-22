<!--<div class="row">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-number"><?= $total_users ?? 0 ?></div>
            <div class="stats-label">Total Users</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-number">₹ <?= number_format($dealer->wallet ?? 0, 2) ?></div>
            <div class="stats-label">My Wallet</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-number">₹ <?= number_format($total_user_wallet ?? 0, 2) ?></div>
            <div class="stats-label">Users Total Wallet</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-number">₹ <?= number_format($total_commission ?? 0, 2) ?></div>
            <div class="stats-label">Total Commission</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Today's Commission</div>
            <div class="card-body text-center">
                <h2 class="text-success">₹ <?= number_format($today_commission ?? 0, 2) ?></h2>
                <small>Earned today from user bets</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">Commission Rate</div>
            <div class="card-body text-center">
                <h2><?= $dealer->commission_rate ?? 0 ?>%</h2>
                <small>Commission on every user bet</small>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        Recent Users
        <a href="<?= site_url('dealer/dashboard/create_user') ?>" class="btn btn-sm btn-primary float-end">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Phone</th><th>Wallet</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($users ?? [], 0, 5) as $user): ?>
                <tr>
                    <td><?= $user->id ?></td>
                    <td><?= htmlspecialchars($user->name) ?></td>
                    <td><?= $user->phone ?></td>
                    <td>₹ <?= number_format($user->wallet, 2) ?></td>
                    <td><span class="badge bg-success">Active</span></td>
                    <td><a href="<?= site_url('dealer/dashboard/view_user/'.$user->id) ?>" class="btn btn-sm btn-info">View</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>-->