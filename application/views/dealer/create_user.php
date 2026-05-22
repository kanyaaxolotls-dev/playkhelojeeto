<div class="card">
    <div class="card-header">
        <i class="fas fa-plus"></i> Create New User
    </div>
    <div class="card-body">
        <form action="<?= site_url('dealer/dashboard/create_user') ?>" method="post">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create User</button>
            <a href="<?= site_url('dealer/dashboard/users') ?>" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>