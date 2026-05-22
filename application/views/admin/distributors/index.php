<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Distributors</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('backend/admin') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Distributors</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <?php if($this->session->flashdata('site_flash')): ?>
            <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= $this->session->flashdata('site_flash') ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Distributor List</h3>
                <div class="card-tools">
                    <a href="<?= site_url('backend/distributors/create') ?>" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New Distributor
                    </a>
                </div>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-hover" id="distributorsTable">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Wallet (₹)</th>
                            <th>Commission Rate</th>
                            <th>Dealers</th>
                            <th>Users</th>
                            <th>Created Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($distributors as $index => $d): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($d->name) ?></strong></td>
                            <td><?= $d->phone ?></td>
                            <td class="text-success">₹ <?= number_format($d->wallet, 2) ?></td>
                            <td><?= $d->commission_rate ?>%</td>
                            <td><?= count($this->Hierarchy_model->get_my_dealers($d->id)) ?></td>
                            <td><?= count($this->Hierarchy_model->get_my_users_for_distributor($d->id)) ?></td>
                            <td><?= date('d-m-Y', strtotime($d->created_at)) ?></td>
                            <td>
                                <div class="btn-group">
                                    <a href="<?= site_url('backend/distributors/view/'.$d->id) ?>" class="btn btn-info btn-sm" title="View">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <button type="button" class="btn btn-success btn-sm" title="Update Wallet" onclick="updateWallet(<?= $d->id ?>, <?= $d->wallet ?>)">
                                        <i class="fa fa-money"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" title="Delete" onclick="deleteDistributor(<?= $d->id ?>, '<?= addslashes($d->name) ?>')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Update Wallet Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h4 class="modal-title"><i class="fa fa-money"></i> Update Distributor Wallet</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
<form id="updateWalletForm" method="post" onsubmit="return false;">
                <div class="modal-body">
                    <input type="hidden" name="distributor_id" id="distributor_id">
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
                        <input type="number" step="0.01" name="amount" id="amount" class="form-control" placeholder="Enter amount" required>
                    </div>
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="2" placeholder="Optional remarks"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="submitWalletBtn" class="btn btn-success">
    Update Wallet
</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
// Global variable for debugging
var debugInfo = {};

function updateWallet(id, currentWallet) {
    console.log('========================================');
    console.log('🔵 updateWallet() called');
    console.log('📌 Distributor ID:', id);
    console.log('📌 Current Wallet:', currentWallet);
    
    debugInfo.distributor_id = id;
    debugInfo.current_wallet = currentWallet;
    
    $('#distributor_id').val(id);
    $('#current_balance').text('₹ ' + parseFloat(currentWallet).toFixed(2));
    $('#transaction_type').val('credit');
    $('#amount').val('');
    $('#remarks').val('');
    $('#walletModal').modal('show');
    
    console.log('✅ Modal opened successfully');
}
$(document).ready(function() {

    console.log('Page Loaded');

    $('#submitWalletBtn').click(function () {

        console.log('Button clicked');

        var distributor_id = $('#distributor_id').val();
        var transaction_type = $('#transaction_type').val();
        var amount = $('#amount').val();
        var remarks = $('#remarks').val();

        console.log(distributor_id, transaction_type, amount);

        if(!amount || amount <= 0) {

            Swal.fire({
                title: 'Error!',
                text: 'Please enter valid amount',
                icon: 'error'
            });

            return;
        }

        $.ajax({

            url: '<?= site_url("backend/distributors/update_wallet") ?>',
            type: 'POST',

            data: {
                distributor_id: distributor_id,
                transaction_type: transaction_type,
                amount: amount,
                remarks: remarks
            },

            dataType: 'json',

            success: function(response) {

                console.log(response);

                if(response.success) {

                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success'
                    }).then(() => {
                        location.reload();
                    });

                } else {

                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error'
                    });
                }
            },

            error: function(xhr) {

                console.log(xhr.responseText);

                Swal.fire({
                    title: 'Error!',
                    text: 'AJAX Failed',
                    icon: 'error'
                });
            }

        });

    });

});

function deleteDistributor(id, name) {
    console.log('🔴 Delete distributor called:', id, name);
    
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete distributor: " + name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            console.log('🗑️ Deleting distributor:', id);
            
            Swal.fire({ 
                title: 'Processing...', 
                text: 'Please wait', 
                allowOutsideClick: false, 
                didOpen: () => { Swal.showLoading(); } 
            });
            
            $.ajax({
                url: '<?= site_url("backend/distributors/delete/") ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    Swal.close();
                    console.log('Delete Response:', response);
                    
                    if(response.success) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#3085d6'
                        }).then(() => { 
                            location.reload(); 
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.close();
                    console.error('Delete Error:', error);
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

$(document).ready(function() {
    console.log('========================================');
    console.log('🚀 Page Loaded - Distributors Management');
    console.log('========================================');
    
    if($.fn.DataTable) {
        $('#distributorsTable').DataTable({
            "order": [[0, "desc"]],
            "pageLength": 25,
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries"
            }
        });
        console.log('✅ DataTable initialized');
    }
});
</script>