<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mt-4 mb-4">
                <i class="fas fa-chart-line text-success"></i> 
                Admin Commission Report
            </h2>
        </div>
    </div>

    <!-- Summary Card -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <h3>Total Admin Commission Collected</h3>
                    <h1>₹ <?= number_format($total_admin_commission, 2) ?></h1>
                    <p>Total Transactions: <?= $total_count ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Commission History Table -->
    <div class="card">
        <div class="card-header">
            <h5><i class="fas fa-history"></i> Admin Commission History</h5>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered" id="commissionTable">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Dealer</th>
                        <th>Distributor</th>
                        <th>Bet Amount</th>
                        <th>Admin Commission</th>
                        <th>Rate</th>
                        <th>Period ID</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($commissions)): ?>
                        <?php foreach($commissions as $comm): ?>
                        <tr>
                            <td><?= $comm->id ?></
                            <td><?= date('d-m-Y H:i:s', strtotime($comm->created_at)) ?></
                            <td><?= $comm->user_name ?? 'N/A' ?></
                            <td><?= $comm->dealer_name ?? 'N/A' ?></
                            <td><?= $comm->distributor_name ?? 'N/A' ?></
                            <td>₹ <?= number_format($comm->bet_amount, 2) ?></
                            <td class="text-success fw-bold">₹ <?= number_format($comm->amount, 2) ?></
                            <td><?= $comm->rate ?>%</
                            <td><?= $comm->period_id ?></
                            <td><span class="badge bg-success"><?= ucfirst($comm->status) ?></span></
                          
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">No admin commission records found</
                          
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#commissionTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25
    });
});
</script>