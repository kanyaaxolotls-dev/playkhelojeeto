<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Distributor Dashboard: <?= htmlspecialchars($distributor->name) ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('backend/admin') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= site_url('backend/distributors') ?>">Distributors</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="info-box bg-info">
                    <span class="info-box-icon"><i class="fa fa-users"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Dealers</span>
                        <span class="info-box-number"><?= $dealer_count ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-success">
                    <span class="info-box-icon"><i class="fa fa-user"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Users</span>
                        <span class="info-box-number"><?= $user_count ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-warning">
                    <span class="info-box-icon"><i class="fa fa-money"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Wallet Balance</span>
                        <span class="info-box-number">₹ <?= number_format($distributor->wallet, 2) ?></span>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="info-box bg-danger">
                    <span class="info-box-icon"><i class="fa fa-line-chart"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Total Commission</span>
                        <span class="info-box-number">₹ <?= number_format($commission_total, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Dealers Under <?= htmlspecialchars($distributor->name) ?></h3>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr><th>ID</th><th>Name</th><th>Phone</th><th>Wallet</th><th>Commission</th><th>Users</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach($dealers as $dealer): ?>
                        <tr>
                            <td><?= $dealer->id ?></td>
                            <td><?= htmlspecialchars($dealer->name) ?></td>
                            <td><?= $dealer->phone ?></td>
                            <td>₹ <?= number_format($dealer->wallet, 2) ?></td>
                            <td><?= $dealer->commission_rate ?>%</td>
                            <td><?= count($this->Hierarchy_model->get_my_users_for_dealer($dealer->id)) ?></td>
                            <td><?= $dealer->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>