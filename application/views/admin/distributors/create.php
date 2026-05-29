<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create New Distributor</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('backend/admin') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('backend/distributors') ?>">Distributors</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Distributor Information</h3>
            </div>
            <form action="<?= site_url('backend/distributors/create') ?>" method="post">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" placeholder="Enter distributor name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Phone Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="phone" placeholder="Enter phone number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" name="password" placeholder="Enter password" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Commission Rate (%)</label>
                                <input type="number" step="0.01" class="form-control" name="commission_rate" value="0.50">
                                <small class="text-muted">Default: 0.50%</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Initial Wallet Balance (₹)</label>
                                <input type="number" step="0.01" class="form-control" name="initial_wallet" value="0">
                                <small class="text-muted">Admin Wallet Balance: ₹ <?= number_format($admin_wallet, 2) ?></small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Distributor Role <span class="text-danger">*</span></label>
                                <select name="role_id" class="form-control" required>
                                    <?php foreach ($distributor_roles as $r): ?>
                                    <option value="<?= (int)$r->id ?>" <?= ($r->slug ?? '') === 'distributor-full' ? 'selected' : '' ?>><?= html_escape($r->name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Manage roles at Backend → Manage Roles (Panel: Distributor)</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Create Distributor</button>
                    <a href="<?= site_url('backend/distributors') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </section>
</div>