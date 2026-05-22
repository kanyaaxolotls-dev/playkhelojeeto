<div class="container-fluid p-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-dark">
            <i class="fas fa-user-circle text-primary"></i> User Details: <?= htmlspecialchars($user->name) ?>
        </h2>
        <a href="<?= site_url('dealer/dashboard/users') ?>" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Back to Users
        </a>
    </div>

    <!-- User Info Cards Row -->
    <div class="row">
        <!-- Left Column -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle text-primary"></i> Personal Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 35%; background: #f8f9fa;">User ID</th>
                            <td><?= $user->id ?></td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">Name</th>
                            <td><?= htmlspecialchars($user->name) ?></td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">Phone</th>
                            <td><?= $user->phone ?></td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">Email</th>
                            <td><?= $user->email ?? 'N/A' ?></td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">User Code</th>
                            <td><code><?= $user->usercode ?? 'N/A' ?></code></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-wallet text-primary"></i> Account Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th style="width: 35%; background: #f8f9fa;">Wallet Balance</th>
                            <td class="text-success fw-bold">₹ <?= number_format($user->wallet, 2) ?></td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">Winning Wallet</th>
                            <td class="text-info fw-bold">₹ <?= number_format($user->winning_wallet ?? 0, 2) ?></td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">Status</th>
                            <td>
                                <?php if($user->status == 1): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Inactive</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="background: #f8f9fa;">Joined Date</th>
                            <td><?= date('d-m-Y H:i:s', strtotime($user->date ?? $user->created_at)) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h3 class="text-primary mb-0"><?= $game_summary['total_bets'] ?? 0 ?></h3>
                <small class="text-muted">Total Bets</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h3 class="text-warning mb-0">₹ <?= number_format($game_summary['total_bet_amount'] ?? 0, 2) ?></h3>
                <small class="text-muted">Total Bet Amount</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h3 class="text-success mb-0">₹ <?= number_format($game_summary['total_win_amount'] ?? 0, 2) ?></h3>
                <small class="text-muted">Total Win Amount</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm text-center p-3">
                <h3 class="text-info mb-0"><?= $game_summary['win_rate'] ?? 0 ?>%</h3>
                <small class="text-muted">Win Rate</small>
            </div>
        </div>
    </div>

    <!-- Game History Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0"><i class="fas fa-history text-primary"></i> Game History</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover" id="gameHistoryTable">
                <thead class="table-light">
                    <tr>
                        <th>Game Name</th>
                        <th>Period ID</th>
                        <th>Bet Type</th>
                        <th>Bet Number</th>
                        <th>Bet Amount (₹)</th>
                        <th>Win Amount (₹)</th>
                        <th>Status</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($game_history) && count($game_history) > 0): ?>
                        <?php foreach($game_history as $bet): ?>
                        <tr>
                            <td><strong><?= $bet->game_name ?? 'N/A' ?></strong></td>
                            <td><?= $bet->period_id ?? '-' ?></td>
                            <td><?= $bet->bet_type ?? '-' ?></td>
                            <td><?= $bet->bet_number ?? '-' ?></td>
                            <td class="text-danger">₹ <?= number_format($bet->bet_amount ?? 0, 2) ?></td>
                            <td class="text-success">₹ <?= number_format($bet->win_amount ?? 0, 2) ?></td>
                            <td>
                                <?php
                                $status = $bet->result_status ?? $bet->status ?? 'pending';
                                $badge_class = 'secondary';
                                if(strtolower($status) == 'win') $badge_class = 'success';
                                elseif(strtolower($status) == 'loss') $badge_class = 'danger';
                                elseif(strtolower($status) == 'pending') $badge_class = 'warning';
                                ?>
                                <span class="badge bg-<?= $badge_class ?>"><?= ucfirst($status) ?></span>
                            </td>
                            <td><?= date('d-m-Y H:i:s', strtotime($bet->created_at ?? $bet->date ?? 'now')) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-info-circle"></i> No game history found for this user.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>

<script>
$(document).ready(function() {
    // Initialize DataTable regardless of data
    $('#gameHistoryTable').DataTable({
        "order": [[7, "desc"]],
        "pageLength": 25,
        "language": {
            "search": "Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "emptyTable": "No game history found for this user"
        }
    });
});

</script>



