<div class="row">
    <div class="col-md-4">
        <div class="card card-stats border-left-primary shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Total Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_users ?? 0 ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="fa fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stats border-left-success shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Active Users
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            <?php 
                            $active = 0;
                            foreach($users as $u) if($u->status == 1) $active++;
                            echo $active;
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fa fa-user-check fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-stats border-left-info shadow mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Total Wallet Balance
                        </div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            ₹ <?php 
                            $total_wallet = 0;
                            foreach($users as $u) $total_wallet += $u->wallet;
                            echo number_format($total_wallet, 2);
                            ?>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fa fa-money fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fa fa-users"></i> Users List (Under My Dealers)
        </h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="usersTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Dealer</th>
                        <th>Wallet (₹)</th>
                        <th>Winning Wallet</th>
                        <th>Total Bet</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($users)): ?>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?= $user->id ?></td>
                            <td>
                                <strong><?= htmlspecialchars($user->name) ?></strong>
                                <br>
                                <small class="text-muted"><?= $user->usercode ?? '' ?></small>
                            </td>
                            <td><?= $user->phone ?></td>
                            <td>
                                <?php 
                                $dealer = $this->Hierarchy_model->get_dealer($user->dealer_id);
                                if($dealer): ?>
                                    <span class="badge bg-info"><?= htmlspecialchars($dealer->name) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-success">
                                <strong>₹ <?= number_format($user->wallet, 2) ?></strong>
                            </td>
                            <td>₹ <?= number_format($user->winning_wallet ?? 0, 2) ?></td>
                            <td>
                                <?php 
                                // Calculate total bet amount
                                $total_bet = 0;
                                // You can calculate from betting tables if needed
                                echo '₹ 0';
                                ?>
                            </td>
                            <td>
                                <?php if($user->status == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d-m-Y', strtotime($user->date ?? $user->created_at)) ?></td>
                            <td>
                                <a href="<?= site_url('distributor/dashboard/view_user/'.$user->id) ?>" 
                                   class="btn btn-sm btn-info" title="View Details">
                                    <i class="fa fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">
                                <div class="alert alert-info mb-0">
                                    <i class="fa fa-info-circle"></i> No users found under your dealers.
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
    $('#usersTable').DataTable({
        "pageLength": 25,
        "order": [[0, "desc"]],
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries"
        }
    });
});
</script>

<!-- DataTables CSS and JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>