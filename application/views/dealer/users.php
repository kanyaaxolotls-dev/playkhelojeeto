<div class="card">
    <div class="card-header">
        My Users
        <a href="<?= site_url('dealer/dashboard/create_user') ?>" class="btn btn-sm btn-primary float-end">
            <i class="fas fa-plus"></i> Add User
        </a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered datatable">
            <thead>
                <tr>
                    <th>ID</th><th>Name</th><th>Phone</th><th>Wallet</th><th>Winning Wallet</th>
                    <th>Status</th><th>Joined Date</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= $user->id ?></td>
                    <td><?= htmlspecialchars($user->name) ?></td>
                    <td><?= $user->phone ?></td>
                    <td>₹ <?= number_format($user->wallet, 2) ?></td>
                    <td>₹ <?= number_format($user->winning_wallet ?? 0, 2) ?></td>
                    <td><?= $user->status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' ?></td>
                    <td><?= date('d-m-Y', strtotime($user->date)) ?></td>
                    <td>
                        <button class="btn btn-sm btn-success" onclick="updateWallet(<?= $user->id ?>, <?= $user->wallet ?>)">
                            <i class="fas fa-money-bill"></i> Wallet
                        </button>
                        <a href="<?= site_url('dealer/dashboard/view_user/'.$user->id) ?>" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Update Wallet Modal -->
<div class="modal fade" id="walletModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-money-bill"></i> Update User Wallet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="user_id">
                <div class="mb-3">
                    <label>Current Balance</label>
                    <h4 id="current_balance" class="text-success">₹ 0.00</h4>
                </div>
                <div class="mb-3">
                    <label>Transaction Type</label>
                    <select id="transaction_type" class="form-control">
                        <option value="credit">Credit (+ Add Money)</option>
                        <option value="debit">Debit (- Deduct Money)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Amount (₹)</label>
                    <input type="number" step="0.01" id="amount" class="form-control" placeholder="Enter amount">
                </div>
                <div class="alert alert-info">
                    <small>Your wallet balance: ₹ <?= number_format($dealer->wallet ?? 0, 2) ?></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitWalletUpdate()">Update Wallet</button>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentUserId = 0;

function updateWallet(id, currentWallet) {
    currentUserId = id;
    $('#user_id').val(id);
    $('#current_balance').text('₹ ' + parseFloat(currentWallet).toFixed(2));
    $('#walletModal').modal('show');
}

function submitWalletUpdate() {
    let amount = $('#amount').val();
    let transaction_type = $('#transaction_type').val();
    
    if(!amount || amount <= 0) {
        Swal.fire('Error!', 'Please enter valid amount', 'error');
        return;
    }
    
    $.ajax({
        url: '<?= site_url("dealer/dashboard/update_user_wallet") ?>',
        type: 'POST',
        data: {
            user_id: currentUserId,
            amount: amount,
            transaction_type: transaction_type
        },
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                Swal.fire('Success!', response.message, 'success').then(() => {
                    location.reload();
                });
            } else {
                Swal.fire('Error!', response.message, 'error');
            }
        }
    });
}
</script>