<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fa fa-user"></i> User Details: <?= htmlspecialchars($user->name) ?>
                </h6>
                <a href="<?= site_url('distributor/dashboard/users') ?>" class="btn btn-sm btn-secondary float-right">
                    <i class="fa fa-arrow-left"></i> Back to Users
                </a>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">User ID</th>
                                <td><?= $user->id ?></td>
                            </tr>
                            <tr>
                                <th>Name</th>
                                <td><?= htmlspecialchars($user->name) ?></td>
                            </tr>
                            <tr>
                                <th>Phone</th>
                                <td><?= $user->phone ?></td>
                            </tr>
                            <tr>
                                <th>User Code</th>
                                <td><?= $user->usercode ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Referral Code</th>
                                <td><?= $user->referral_code ?? 'N/A' ?></td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    <?php if($user->status == 1): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">Inactive</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tr>
                                <th width="30%">Wallet Balance</th>
                                <td class="text-success"><strong>₹ <?= number_format($user->wallet, 2) ?></strong></td>
                            </tr>
                            <tr>
                                <th>Winning Wallet</th>
                                <td class="text-info">₹ <?= number_format($user->winning_wallet ?? 0, 2) ?></td>
                            </tr>
                            <tr>
                                <th>Joined Date</th>
                                <td><?= date('d-m-Y H:i:s', strtotime($user->date ?? $user->created_at)) ?></td>
                            </tr>
                            <tr>
                                <th>Dealer</th>
                                <td>
                                    <?php 
                                    $dealer = $this->Hierarchy_model->get_dealer($user->dealer_id);
                                    echo $dealer ? htmlspecialchars($dealer->name) : 'N/A';
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th>Distributor</th>
                                <td>
                                    <?php 
                                    $distributor = $this->Hierarchy_model->get_distributor($user->distributor_id);
                                    echo $distributor ? htmlspecialchars($distributor->name) : 'N/A';
                                    ?>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Betting History -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fa fa-history"></i> Betting History
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="bettingTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Game</th>
                                <th>Bet Type</th>
                                <th>Bet Amount</th>
                                <th>Win Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($betting_history)): ?>
                                <?php foreach($betting_history as $bet): ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i:s', strtotime($bet->date ?? $bet->created_at)) ?></td>
                                    <td><?= ucfirst($bet->game_name ?? 'Unknown') ?></td>
                                    <td><?= $bet->bet_type ?? 'N/A' ?></td>
                                    <td>₹ <?= number_format($bet->amount ?? $bet->bet, 2) ?></td>
                                    <td>₹ <?= number_format($bet->win_amount ?? 0, 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($bet->status ?? 'pending') == 'win' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($bet->status ?? 'Pending') ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">No betting history found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Commission History -->
<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fa fa-money"></i> Commission History
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="commissionTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Rate</th>
                                <th>Game</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(!empty($commissions)): ?>
                                <?php foreach($commissions as $comm): ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i:s', strtotime($comm->created_at)) ?></td>
                                    <td><?= ucfirst($comm->commission_type) ?></td>
                                    <td class="text-success">₹ <?= number_format($comm->amount, 2) ?></td>
                                    <td><?= $comm->rate ?>%</td>
                                    <td><?= ucfirst($comm->game_type ?? 'N/A') ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center">No commission history found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#bettingTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25
    });
    $('#commissionTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>