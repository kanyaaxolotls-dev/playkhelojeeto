<?php $this->load->view('admin/header'); ?>
<style>
/* Same styling as distributors page */
.dealer-card {
    transition: all 0.3s ease;
    border-radius: 10px;
}
.dealer-stats {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}
.badge-commission {
    background: #ffc107;
    color: #000;
    padding: 5px 10px;
    border-radius: 20px;
}
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="fa fa-users"></i> Dealers 
                        <small>Manage dealer accounts</small>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('backend/admin') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Dealers</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <?php if($this->session->flashdata('site_flash')): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fa fa-check-circle"></i> <?php echo $this->session->flashdata('site_flash'); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3><?php echo count($dealers); ?></h3>
                        <p>Total Dealers</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-users"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3><?php 
                            $totalUsers = 0;
                            foreach($dealers as $d) {
                                $totalUsers += count($this->Hierarchy_model->get_my_users_for_dealer($d->id));
                            }
                            echo $totalUsers;
                        ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-user"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>₹ <?php 
                            $totalWallet = 0;
                            foreach($dealers as $d) {
                                $totalWallet += $d->wallet;
                            }
                            echo number_format($totalWallet, 2);
                        ?></h3>
                        <p>Total Wallet Balance</p>
                    </div>
                    <div class="icon">
                        <i class="fa fa-money"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="card dealer-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa fa-list"></i> Dealer List
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#createDealerModal">
                        <i class="fa fa-plus"></i> Add New Dealer
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="15%">Name</th>
                            <th width="12%">Phone</th>
                            <th width="15%">Distributor</th>
                            <th width="10%">Wallet (₹)</th>
                            <th width="10%">Commission</th>
                            <th width="8%">Users</th>
                            <th width="20%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($dealers)): ?>
                            <?php foreach ($dealers as $index => $dealer): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($dealer->name); ?></strong></td>
                                    <td><i class="fa fa-phone"></i> <?php echo htmlspecialchars($dealer->phone); ?></td>
                                    <td>
                                        <?php 
                                        $distributor = $this->Hierarchy_model->get_distributor($dealer->distributor_id);
                                        echo $distributor ? htmlspecialchars($distributor->name) : 'N/A';
                                        ?>
                                    </td>
                                    <td><span class="badge badge-success">₹ <?php echo number_format($dealer->wallet, 2); ?></span></td>
                                    <td><span class="badge badge-info"><?php echo number_format($dealer->commission_rate, 2); ?>%</span></td>
                                    <td><span class="badge badge-primary"><?php echo count($this->Hierarchy_model->get_my_users_for_dealer($dealer->id)); ?></span></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo site_url('backend/dealers/view/'.$dealer->id); ?>" class="btn btn-info" title="View Dashboard">
                                                <i class="fa fa-dashboard"></i>
                                            </a>
                                            <button type="button" class="btn btn-success" title="Update Wallet" onclick="updateDealerWallet(<?php echo $dealer->id; ?>, <?php echo $dealer->wallet; ?>)">
                                                <i class="fa fa-money"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger" title="Delete" onclick="deleteDealer(<?php echo $dealer->id; ?>, '<?php echo htmlspecialchars($dealer->name); ?>')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center">
                                    <div class="alert alert-info m-0">
                                        <i class="fa fa-info-circle"></i> No dealers found. Click "Add New Dealer" to create one.
                                    </div>
                                 </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Create Dealer Modal -->
<div class="modal fade" id="createDealerModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-plus-circle"></i> Create New Dealer
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?php echo site_url('backend/dealers/create'); ?>" method="post" id="createDealerForm">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fa fa-user"></i> Full Name *</label>
                        <input type="text" class="form-control" name="name" placeholder="Enter dealer name" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-phone"></i> Phone Number *</label>
                        <input type="text" class="form-control" name="phone" placeholder="Enter phone number" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-lock"></i> Password *</label>
                        <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-building"></i> Select Distributor *</label>
                        <select class="form-control" name="distributor_id" required>
                            <option value="">-- Select Distributor --</option>
                            <?php 
                            $distributors = $this->Hierarchy_model->get_all_distributors();
                            foreach($distributors as $dist): ?>
                                <option value="<?php echo $dist->id; ?>">
                                    <?php echo htmlspecialchars($dist->name); ?> (<?php echo $dist->phone; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fa fa-percent"></i> Commission Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" name="commission_rate" value="2.00" placeholder="Commission rate">
                        <small class="text-muted">Default: 2.00%</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Dealer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Dealer Wallet Modal -->
<div class="modal fade" id="dealerWalletModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">
                    <i class="fa fa-money"></i> Update Dealer Wallet
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="updateDealerWalletForm">
                <div class="modal-body">
                    <input type="hidden" name="dealer_id" id="dealer_id">
                    <div class="form-group">
                        <label>Current Wallet Balance</label>
                        <h4 class="text-success" id="current_dealer_wallet">₹ 0.00</h4>
                    </div>
                    <div class="form-group">
                        <label>Transaction Type</label>
                        <select class="form-control" name="transaction_type" id="dealer_transaction_type" required>
                            <option value="credit">Credit (+ Add Money)</option>
                            <option value="debit">Debit (- Deduct Money)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Amount (₹)</label>
                        <input type="number" step="0.01" class="form-control" name="amount" id="dealer_amount" placeholder="Enter amount" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-update"></i> Update Wallet
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function updateDealerWallet(id, currentWallet) {
    $('#dealer_id').val(id);
    $('#current_dealer_wallet').text('₹ ' + parseFloat(currentWallet).toFixed(2));
    $('#dealerWalletModal').modal('show');
}

$('#updateDealerWalletForm').on('submit', function(e) {
    e.preventDefault();
    var formData = $(this).serialize();
    
    $.ajax({
        url: '<?php echo site_url("backend/dealers/update_wallet"); ?>',
        type: 'POST',
        data: formData,
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
});

function deleteDealer(id, name) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You want to delete dealer: " + name,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '<?php echo site_url("backend/dealers/delete/"); ?>' + id,
                type: 'POST',
                dataType: 'json',
                success: function(response) {
                    if(response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                }
            });
        }
    });
}
</script>

<?php $this->load->view('admin/footer'); ?>