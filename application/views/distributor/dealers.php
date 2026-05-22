<!-- Stats Cards Row -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;"><?= count($dealers) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total Dealers</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-users fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;"><?= $total_users ?? 0 ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total Users</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-user-friends fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($distributor->wallet ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">My Wallet</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-wallet fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dealers Table -->
<div class="custom-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <i class="fas fa-list"></i> My Dealers
        </div>
        <a href="<?= site_url('distributor/dashboard/create_dealer') ?>" class="btn-custom btn-sm">
            <i class="fas fa-plus"></i> Add New Dealer
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="dealersTable" style="width: 100%;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Wallet (₹)</th>
                        <th>Commission Rate</th>
                        <th>Users</th>
                        <th>Status</th>
                        <th>Joined Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($dealers)): ?>
                        <?php foreach($dealers as $dealer): ?>
                        <tr>
                            <td><?= $dealer->id ?></td>
                            <td><strong><?= htmlspecialchars($dealer->name) ?></strong></td>
                            <td><?= $dealer->phone ?></td>
                            <td><?= $dealer->email ?? 'N/A' ?></td>
                            <td class="text-success fw-bold">₹ <?= number_format($dealer->wallet, 2) ?></td>
                            <td><span class="badge" style="background: #17a2b8; color: white;"><?= $dealer->commission_rate ?>%</span></td>
                            <td><span class="badge" style="background: #667eea; color: white;"><?= $this->Hierarchy_model->count_users_under_dealer($dealer->id) ?></span></td>
                            <td>
                                <?php if($dealer->status == 1): ?>
                                    <span class="badge" style="background: #28a745; color: white;">● Active</span>
                                <?php else: ?>
                                    <span class="badge" style="background: #dc3545; color: white;">● Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d-m-Y', strtotime($dealer->created_at)) ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-info btn-sm" onclick="viewDealer(<?= $dealer->id ?>)" title="View Dealer" style="background: #17a2b8; border: none;">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-success btn-sm" onclick="updateWallet(<?= $dealer->id ?>, <?= $dealer->wallet ?>)" title="Update Wallet" style="background: #28a745; border: none;">
                                        <i class="fas fa-money-bill"></i>
                                    </button>
                                    <button class="btn btn-danger btn-sm" onclick="deleteDealer(<?= $dealer->id ?>)" title="Delete" style="background: #dc3545; border: none;">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <i class="fas fa-info-circle text-muted"></i> 
                                <span class="text-muted">No dealers found. Click "Add New Dealer" to create one.</span>
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
                <h5 class="modal-title"><i class="fas fa-money-bill-wave me-2"></i> Update Dealer Wallet</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="dealer_id">
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
                    <small>Your wallet balance: <strong>₹ <?= number_format($distributor->wallet ?? 0, 2) ?></strong></small>
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
    
    .btn-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        padding: 6px 16px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
        font-size: 13px;
    }
    
    .btn-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        color: white;
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
    
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
    }
    
    .btn-group .btn {
        margin: 0 2px;
        border-radius: 8px !important;
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

<script>
function viewDealer(id) {
    window.location.href = '<?= site_url("distributor/dashboard/dealer_view/") ?>' + id;
}

let currentDealerId = 0;

function updateWallet(id, currentWallet) {
    currentDealerId = id;
    $('#dealer_id').val(id);
    $('#current_balance').text('₹ ' + parseFloat(currentWallet).toFixed(2));
    $('#transaction_type').val('credit');
    $('#amount').val('');
    $('#walletModal').modal('show');
}

function submitWalletUpdate() {
    let amount = $('#amount').val();
    let transaction_type = $('#transaction_type').val();
    
    if(!amount || amount <= 0) {
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
        url: '<?= site_url("distributor/dashboard/update_dealer_wallet") ?>',
        type: 'POST',
        data: {
            dealer_id: currentDealerId,
            amount: amount,
            transaction_type: transaction_type
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            if(response.success) {
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

function deleteDealer(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Processing...',
                text: 'Please wait',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            $.ajax({
                url: '<?= site_url("distributor/dashboard/delete_dealer/") ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    if(response.success) {
                        Swal.fire({
                            title: 'Deleted!',
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
    });
}

// Initialize DataTable
$(document).ready(function() {
    if($.fn.DataTable && $('#dealersTable tbody tr').length > 0) {
        var hasData = false;
        $('#dealersTable tbody tr').each(function() {
            if($(this).find('td').length > 0 && $(this).find('td').attr('colspan') != 10) {
                hasData = true;
            }
        });
        
        if(hasData) {
            $('#dealersTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 10,
                "language": {
                    "search": "🔍 Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "emptyTable": "No dealers found"
                }
            });
        }
    }
});
</script>