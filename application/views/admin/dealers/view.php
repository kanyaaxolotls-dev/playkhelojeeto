<?php $this->load->view('admin/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Dealer Dashboard <small><?php echo htmlspecialchars($dealer->name); ?></small></h1>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?php echo number_format($dealer->wallet, 2); ?></h3>
                        <p>Dealer Wallet</p>
                    </div>
                    <div class="icon"><i class="fa fa-wallet"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?php echo number_format($commission_total, 2); ?></h3>
                        <p>Total Dealer Commission</p>
                    </div>
                    <div class="icon"><i class="fa fa-money"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?php echo intval($user_count); ?></h3>
                        <p>Assigned Users</p>
                    </div>
                    <div class="icon"><i class="fa fa-user"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3><?php echo htmlspecialchars($this->Hierarchy_model->get_distributor($dealer->distributor_id)->name ?? 'N/A'); ?></h3>
                        <p>Distributor</p>
                    </div>
                    <div class="icon"><i class="fa fa-user-tie"></i></div>
                </div>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Users Under Dealer</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Wallet</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $index => $user): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($user->name); ?></td>
                                    <td><?php echo htmlspecialchars($user->phone); ?></td>
                                    <td><?php echo number_format($user->wallet, 2); ?></td>
                                    <td><?php echo $user->status == 1 ? 'Active' : 'Inactive'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No users assigned to this dealer.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
<?php $this->load->view('admin/footer'); ?>