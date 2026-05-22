<div class="card">
    <div class="card-header">
        <h5><i class="fa fa-plus"></i> Create New Dealer</h5>
    </div>
    <div class="card-body">
        <form action="<?= site_url('distributor/dashboard/create_dealer') ?>" method="post">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="text" name="phone" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Email (Optional)</label>
                        <input type="email" name="email" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Commission Rate (%)</label>
                        <input type="number" step="0.01" name="commission_rate" class="form-control" value="2.00">
                        <small>Default: 2%</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-save"></i> Create Dealer
            </button>
            <a href="<?= site_url('distributor/dashboard/dealers') ?>" class="btn btn-secondary">
                Cancel
            </a>
        </form>
    </div>
</div>