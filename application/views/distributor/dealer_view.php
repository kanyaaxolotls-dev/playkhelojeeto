<style>
.profile-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 20px;
    color: white;
}
.info-card {
    background: white;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}
.info-label {
    font-size: 12px;
    color: #888;
    margin-bottom: 5px;
}
.info-value {
    font-size: 18px;
    font-weight: bold;
    color: #333;
}
.stats-box {
    text-align: center;
    padding: 15px;
    background: #f8f9fc;
    border-radius: 10px;
    margin-bottom: 15px;
}
.stats-number {
    font-size: 28px;
    font-weight: bold;
}
.stats-label {
    font-size: 12px;
    color: #888;
}
.table th {
    background: #f8f9fc;
}
</style>

<div class="profile-header">
    <div class="row">
        <div class="col-md-8">
            <h3><i class="fa fa-user-secret"></i> <?= htmlspecialchars($dealer->name ?? 'Dealer') ?></h3>
            <p><i class="fa fa-phone"></i> <?= $dealer->phone ?? 'N/A' ?> | 
               <i class="fa fa-calendar"></i> Joined: <?= date('d-m-Y', strtotime($dealer->created_at ?? 'now')) ?></p>
        </div>
        <div class="col-md-4 text-right">
            <a href="<?= site_url('distributor/dashboard/dealers') ?>" class="btn btn-light">
                <i class="fa fa-arrow-left"></i> Back to Dealers
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">
        <div class="stats-box">
            <div class="stats-number"><?= $total_users ?? 0 ?></div>
            <div class="stats-label">Total Users</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-box">
            <div class="stats-number"><?= $dealer->commission_rate ?? 0 ?>%</div>
            <div class="stats-label">Commission Rate</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-box">
            <div class="stats-number">₹ <?= number_format($total_commission ?? 0, 2) ?></div>
            <div class="stats-label">Total Commission</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-box">
            <div class="stats-number">₹ <?= number_format($dealer->wallet ?? 0, 2) ?></div>
            <div class="stats-label">Wallet Balance</div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-label">Dealer ID</div>
            <div class="info-value">#<?= $dealer->id ?? 'N/A' ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-label">Phone Number</div>
            <div class="info-value"><?= $dealer->phone ?? 'N/A' ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-label">Email Address</div>
            <div class="info-value"><?= $dealer->email ?? 'Not Provided' ?></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="info-card">
            <div class="info-label">Status</div>
            <div class="info-value">
                <?php if(($dealer->status ?? 0) == 1): ?>
                    <span class="badge bg-success" style="background: #28a745; padding: 5px 10px; border-radius: 20px;">Active</span>
                <?php else: ?>
                    <span class="badge bg-danger" style="background: #dc3545; padding: 5px 10px; border-radius: 20px;">Inactive</span>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Users Under This Dealer -->
<div class="card" style="margin-top: 20px;">
    <div class="card-header" style="background: white; border-bottom: 2px solid #667eea;">
        <h5 class="mb-0"><i class="fa fa-users"></i> Users Under This Dealer</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="usersTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Wallet (₹)</th>
                        <th>Winning Wallet</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($users) && count($users) > 0): ?>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td><strong><?= htmlspecialchars($user->name) ?></strong></td>
                            <td><?= $user->phone ?></td>
                            <td class="text-success">₹ <?= number_format($user->wallet, 2) ?></td>
                            <td>₹ <?= number_format($user->winning_wallet ?? 0, 2) ?></td>
                            <td>
                                <?php if($user->status == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                             </td>
                            <td><?= date('d-m-Y', strtotime($user->date ?? $user->created_at)) ?></td>
                            <td>
                                <a href="<?= site_url('distributor/dashboard/view_user/'.$user->id) ?>" class="btn btn-sm btn-info">
                                    <i class="fa fa-eye"></i> View
                                </a>
                             </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="alert alert-warning mb-0">
                                    <i class="fa fa-warning"></i> No users found under this dealer. 
                                    Please assign users to this dealer in the database.
                                </div>
                             </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    if(typeof $.fn.DataTable !== 'undefined') {
        $('#usersTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 25
        });
    }
    
    // Debug: Log users data
    console.log('Users count: <?= count($users ?? []) ?>');
});
</script>