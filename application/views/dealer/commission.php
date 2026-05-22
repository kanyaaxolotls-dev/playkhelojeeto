<div class="row">
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-number">₹ <?= number_format($total_commission ?? 0, 2) ?></div>
            <div class="stats-label">Total Commission</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-number">₹ <?= number_format($today_commission ?? 0, 2) ?></div>
            <div class="stats-label">Today's Commission</div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card">
            <div class="stats-number">₹ <?= number_format($monthly_commission ?? 0, 2) ?></div>
            <div class="stats-label">This Month</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Commission History</div>
    <div class="card-body table-responsive">
        <table class="table table-bordered datatable">
            <thead>
                <tr><th>Date</th><th>User</th><th>Amount</th><th>Rate</th><th>Game</th></tr>
            </thead>
            <tbody>
                <?php foreach($commissions as $comm): ?>
                <tr>
                    <td><?= date('d-m-Y H:i', strtotime($comm->created_at)) ?></td>
                    <td><?= $comm->user_name ?? 'N/A' ?></td>
                    <td><span class="text-success">₹ <?= number_format($comm->amount, 2) ?></span></td>
                    <td><?= $comm->rate ?>%</td>
                    <td><?= ucfirst($comm->game_type ?? 'N/A') ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>