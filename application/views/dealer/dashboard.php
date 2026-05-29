<!-- Stats Cards Row - Tiles with Colors -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;"><?= $total_users ?? 0 ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total Users</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($dealer->wallet ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">My Wallet</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-wallet fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($total_user_wallet ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Users Total Wallet</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($total_commission ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total Commission</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-chart-line fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Today's Commission and Rate Row -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="custom-card">
            <div class="card-header">
                <i class="fas fa-calendar-alt"></i> Today's Commission
            </div>
            <div class="card-body text-center py-4">
                <h2 class="text-success mb-2" style="font-size: 36px; font-weight: 700;">₹ <?= number_format($today_commission ?? 0, 2) ?></h2>
                <small class="text-muted">Earned today from user bets</small>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="custom-card">
            <div class="card-header">
                <i class="fas fa-percent"></i> Commission Rate
            </div>
            <div class="card-body text-center py-4">
                <h2 class="text-primary mb-2" style="font-size: 36px; font-weight: 700;"><?= $dealer->commission_rate ?? 0 ?>%</h2>
                <small class="text-muted">Commission on every user bet</small>
            </div>
        </div>
    </div>
</div>

<!-- My Users Table -->
<div class="custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-users"></i> My Users
        </div>
        <?php if (rbac_has('create_user')) { ?>
        <a href="<?= site_url('dealer/dashboard/create_user') ?>" class="btn btn-primary btn-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
            <i class="fas fa-plus"></i> Add User
        </a>
        <?php } ?>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable" style="width: 100%;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Wallet (₹)</th>
                        <th>Winning Wallet</th>
                        <th>Status</th>
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
                            <td class="text-success fw-bold">₹ <?= number_format($user->wallet, 2) ?></td>
                            <td>₹ <?= number_format($user->winning_wallet ?? 0, 2) ?></td>
                            <td>
                                <?php if($user->status == 1): ?>
                                    <span class="badge" style="background: #28a745; color: white; padding: 5px 12px; border-radius: 20px;">● Active</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #dc3545; color: white; padding: 5px 12px; border-radius: 20px;">● Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (rbac_has('wallet_credit') || rbac_has('wallet_debit')) { ?>
                                <button class="btn btn-sm btn-success me-1" onclick="updateWallet(<?= $user->id ?>, <?= $user->wallet ?>)" title="Update Wallet" style="background: #28a745; border: none;">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                                <?php } ?>
                                <a href="<?= site_url('dealer/dashboard/view_user/'.$user->id) ?>" class="btn btn-sm btn-info" title="View Details" style="background: #17a2b8; border: none;">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-info-circle text-muted"></i> 
                                <span class="text-muted">No users found. Click "Add User" to create one.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Update Wallet Modal -->
<div class="modal fade" id="walletModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; border: none;">
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i> Update User Wallet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="user_id">
                <div class="mb-4 text-center">
                    <label class="text-muted mb-2">Current Balance</label>
                    <h3 id="current_balance" class="text-success fw-bold" style="color: #28a745 !important;">₹ 0.00</h3>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Transaction Type</label>
                    <select id="transaction_type" class="form-select">
                        <option value="credit">💰 Credit (+ Add Money)</option>
                        <option value="debit">💸 Debit (- Deduct Money)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Amount (₹)</label>
                    <input type="number" step="0.01" id="amount" class="form-control" placeholder="Enter amount">
                </div>
                <div class="alert alert-info mt-3" style="background: #e7f3ff; border: none;">
                    <i class="fas fa-info-circle me-2"></i>
                    <small>Your wallet balance: <strong>₹ <?= number_format($dealer->wallet ?? 0, 2) ?></strong></small>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="submitWalletUpdate()" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border: none;">Update Wallet</button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Additional styles for dealer dashboard */
    .stat-card {
        border-radius: 15px;
        padding: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .custom-card {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .custom-card .card-header {
        background: white;
        border-bottom: 1px solid #eef2f6;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .custom-card .card-header i {
        margin-right: 10px;
        color: #667eea;
    }
    
    .table th {
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        padding: 12px 15px;
    }
    
    .table td {
        padding: 12px 15px;
        vertical-align: middle;
        font-size: 13px;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .btn-sm {
        padding: 5px 12px;
        font-size: 12px;
        border-radius: 8px;
    }
    
    .form-select, .form-control {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        padding: 10px 12px;
    }
    
    .form-select:focus, .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25);
    }
</style>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let currentUserId = 0;

function updateWallet(id, currentWallet) {
    currentUserId = id;

    document.getElementById('user_id').value = id;
    document.getElementById('current_balance').innerHTML =
        '₹ ' + parseFloat(currentWallet).toFixed(2);

    document.getElementById('transaction_type').value = 'credit';
    document.getElementById('amount').value = '';

    $('#walletModal').modal('show');
}

function submitWalletUpdate() {

    let amount = document.getElementById('amount').value;
    let transaction_type = document.getElementById('transaction_type').value;

    if (!amount || amount <= 0) {
        Swal.fire('Error!', 'Please enter valid amount', 'error');
        return;
    }

    Swal.fire({
        title: 'Processing...',
        text: 'Please wait',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

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

            Swal.close();

            if (response.success) {

                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    confirmButtonColor: '#28a745'
                }).then(() => {
                    location.reload();
                });

            } else {

                Swal.fire({
                    title: 'Error!',
                    text: response.message,
                    icon: 'error',
                    confirmButtonColor: '#dc3545'
                });

            }
        },

        error: function() {

            Swal.close();

            Swal.fire({
                title: 'Error!',
                text: 'Something went wrong',
                icon: 'error'
            });

        }
    });
}

$(document).ready(function () {

    if ($.fn.DataTable && $('#usersTable tbody tr').length > 0) {

        var hasData = false;

        $('#usersTable tbody tr').each(function () {

            if ($(this).find('td').length > 0 &&
                $(this).find('td').attr('colspan') != 7) {

                hasData = true;
            }
        });

        if (hasData) {

            $('#usersTable').DataTable({
                order: [[0, "desc"]],
                pageLength: 10,
                language: {
                    search: "🔍 Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    emptyTable: "No users found"
                }
            });

        }
    }
});
</script>





