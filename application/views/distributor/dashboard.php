<div class="row">
    <!-- Stats Cards -->
    <div class="col-md-3 mb-3">
        <div class="card card-stats text-white bg-primary">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <h5 class="card-title">Total Dealers</h5>
                        <h2><?= $total_dealers ?></h2>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fa fa-users fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stats text-white bg-success">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <h5 class="card-title">Total Users</h5>
                        <h2><?= $total_users ?></h2>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fa fa-user fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stats text-white bg-warning">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <h5 class="card-title">Wallet Balance</h5>
                        <h2>₹ <?= number_format($distributor->wallet, 2) ?></h2>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fa fa-money fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 mb-3">
        <div class="card card-stats text-white bg-info">
            <div class="card-body">
                <div class="row">
                    <div class="col-8">
                        <h5 class="card-title">Total Commission</h5>
                        <h2>₹ <?= number_format($total_commission, 2) ?></h2>
                    </div>
                    <div class="col-4 text-right">
                        <i class="fa fa-line-chart fa-3x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Commission -->
<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5><i class="fa fa-calendar"></i> Today's Commission</h5>
            </div>
            <div class="card-body">
                <h3 class="text-success">₹ <?= number_format($today_commission, 2) ?></h3>
                <small>Earned today from dealer activities</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 mb-3">
        <div class="card">
            <div class="card-header">
                <h5><i class="fa fa-percent"></i> Commission Rate</h5>
            </div>
            <div class="card-body">
                <h3><?= $distributor->commission_rate ?>%</h3>
                <small>Commission on every user bet</small>
            </div>
        </div>
    </div>
</div>

<!-- Recent Dealers List -->
<div class="card">
    <div class="card-header">
        <h5><i class="fa fa-list"></i> Recent Dealers</h5>
        <?php if (rbac_has('create_dealer')) { ?>
        <a href="<?= site_url('distributor/dashboard/create_dealer') ?>" class="btn btn-sm btn-primary float-right">
            <i class="fa fa-plus"></i> Add New Dealer
        </a>
        <?php } ?>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Phone</th>
                    <th>Wallet</th>
                    <th>Commission</th>
                    <th>Users</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($dealers)): ?>
                    <?php foreach(array_slice($dealers, 0, 5) as $index => $dealer): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($dealer->name) ?></strong></td>
                            <td><?= $dealer->phone ?></td>
                            <td><span class="badge badge-warning text-dark">₹ <?= number_format($dealer->wallet, 2) ?></span></td>
                            <td><?= $dealer->commission_rate ?>%</td>
                            <td><?= $this->Hierarchy_model->count_users_under_dealer($dealer->id) ?></td>
                            <td>
                                <button class="btn btn-sm btn-info" onclick="viewDealer(<?= $dealer->id ?>)">
                                    <i class="fa fa-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-success" onclick="updateWallet(<?= $dealer->id ?>, <?= $dealer->wallet ?>)">
                                    <i class="fa fa-money"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7" class="text-center">No dealers found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Recent Transactions -->
<div class="card mt-3">
    <div class="card-header">
        <h5><i class="fa fa-history"></i> Recent Transactions</h5>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-sm">
            <thead>
                <tr><th>Date</th><th>Dealer</th><th>Type</th><th>Amount</th><th>Balance</th></tr>
            </thead>
            <tbody>
                <?php if(!empty($recent_transactions)): ?>
                    <?php foreach($recent_transactions as $trans): ?>
                        <tr>
                            <td><?= date('d M H:i', strtotime($trans->created_at)) ?></td>
                            <td><?= $trans->dealer_name ?></td>
                            <td><span class="badge badge-<?= $trans->type == 'credit' ? 'success' : 'danger' ?>">
                                <?= ucfirst($trans->type) ?>
                            </span></td>
                            <td>₹ <?= number_format($trans->amount, 2) ?></td>
                            <td>₹ <?= number_format($trans->balance_after, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center">No transactions found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Update Wallet Modal -->
<div class="modal fade" id="walletModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fa fa-money"></i> Update Dealer Wallet</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="updateWalletForm">
                <div class="modal-body">
                    <input type="hidden" name="dealer_id" id="dealer_id">
                    <div class="form-group">
                        <label>Current Balance</label>
                        <h4 id="current_balance" class="text-success">₹ 0.00</h4>
                    </div>
                    <div class="form-group">
                        <label>Transaction Type</label>
                        <select name="transaction_type" id="transaction_type" class="form-control" required>
                            <option value="credit">Credit (+ Add Money)</option>
                            <option value="debit">Debit (- Deduct Money)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (₹)</label>
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" required>
                    </div>
                    <div class="alert alert-info">
                        <small>Your wallet balance: ₹ <?= number_format($distributor->wallet, 2) ?></small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Update Wallet</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
function updateWallet(id, currentWallet) {
    $('#dealer_id').val(id);
    $('#current_balance').text('₹ ' + parseFloat(currentWallet).toFixed(2));
    $('#amount').val(''); // Clear previous amount
    $('#transaction_type').val('credit'); // Reset to credit
    $('#walletModal').modal('show');
}

function viewDealer(id) {
    window.location.href = '<?= site_url("distributor/dashboard/dealer_view/") ?>' + id;
}

$(document).ready(function() {
    $('#updateWalletForm').on('submit', function(e) {
        e.preventDefault();
        
        // Get form data
        var dealer_id = $('#dealer_id').val();
        var amount = $('#amount').val();
        var transaction_type = $('#transaction_type').val();
        
        // Validate amount
        if(!amount || parseFloat(amount) <= 0) {
            Swal.fire('Error!', 'Please enter a valid amount', 'error');
            return;
        }
        
        // Show loading state
        Swal.fire({
            title: 'Processing...',
            text: 'Please wait',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        
        // Prepare data
        var formData = {
            dealer_id: dealer_id,
            amount: amount,
            transaction_type: transaction_type,
            '<?= $this->security->get_csrf_token_name() ?>': '<?= $this->security->get_csrf_hash() ?>'
        };
        
        // Send AJAX request
        $.ajax({
            url: '<?= site_url("distributor/dashboard/update_dealer_wallet") ?>',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: true
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error!', response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.log(xhr.responseText);
                Swal.fire('Error!', 'Something went wrong. Please try again.', 'error');
            }
        });
    });
});
</script>