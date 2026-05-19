<?php include('header.php') ?>
<!--state overview start-->
<?php if($this->session->role == 'Admin'): ?>
<div class="row state-overview">
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/users/') ?>">
            <section class="card">
                <div class="symbol terques">
                    <i class="fa fa-user"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('status', 1)->get('tbl_users')->num_rows(); ?>
                    </h1>
                    <p>Total Players</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/roles/assign_role') ?>">
            <section class="card">
                <div class="symbol red">
                    <i class="fa fa-tags"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('status', 1)->get('tbl_admin')->num_rows(); ?>  
                    </h1>
                    <p>Total Subadmins</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/roles/manage_role') ?>">
            <section class="card">
                <div class="symbol yellow">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('status', 1)->get('tbl_roles')->num_rows(); ?>
                    </h1>
                    <p>Total Roles</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="#">
            <section class="card">
                <div class="symbol blue">
                    <i class="fa fa-bar-chart-o"></i>
                </div>
                <div class="value">
                    <h1 class="">
                        <?= 0; ?>
                    </h1>
                    <p>Total Profit</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/Wallet/fund_requests_p') ?>">
            <section class="card">
                <div class="symbol terques">
                    <i class="fa fa-rupee"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->get('tbl_deposit')->num_rows(); ?>
                    </h1>
                    <p>Total Recharge</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/Wallet/withdraw_requests_p') ?>">
            <section class="card">
                <div class="symbol red">
                    <i class="fa fa-money"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->get('tbl_withdraw')->num_rows(); ?>  
                    </h1>
                    <p>Total Withdrawls</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('data/todays_recharge') ?>">
            <section class="card">
                <div class="symbol yellow">
                    <i class="fa fa-rupee"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('DATE(date)', date('Y-m-d'))->get('tbl_deposit')->num_rows(); ?>
                    </h1>
                    <p>Today's Recharge</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('data/todays_withdraw') ?>">
            <section class="card">
                <div class="symbol blue">
                    <i class="fa fa-money"></i>
                </div>
                <div class="value">
                    <h1 class="">
                        <?= $this->db->where('DATE(date)', date('Y-m-d'))->get('tbl_withdraw')->num_rows(); ?>
                    </h1>
                    <p>Today's Withdrawls</p>
                </div>
            </section>
        </a>
    </div>
</div>
<!--state overview end-->

<div class="row">
    <div class="col-lg-4">
        <section class="card">
            <div class="card-body text-center">
                <div class="task-thumb-details" style="margin-top:-0.3em">
                    <h1><a href="#"><?= $this->db_model->select('name', 'tbl_games', array('id' => 7)); ?></a></h1>
                    <p>Most Played Game</p>
                </div>
            </div>
            <table class="table table-hover personal-task">
                <tbody>
                    <tr>
                        <td><i class="fa fa-tasks"></i></td>
                        <td>Game Name</td>
                        <td><?= $this->db_model->select('name', 'tbl_games', array('id' => 7)); ?></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-exclamation-triangle"></i></td>
                        <td>Game Status</td>
                        <td class='text-success'>Active</td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-envelope"></i></td>
                        <td>Added At</td>
                        <td><?= $this->db_model->select('date', 'tbl_games', array('id' => 7)); ?></td>
                    </tr>
                    <tr>
                        <td><i class="fa fa-bell-o"></i></td>
                        <td>Total Rounds</td>
                        <td><?= $this->db->get('tbl_funtarget_results')->num_rows(); ?></td>
                    </tr>
                </tbody>
            </table>
        </section>
    </div>
    <div class="col-lg-8">
        <section class="card">
            <div class="card-body progress-card">
                <div class="task-progress">
                    <h1>Latest Players</h1>
                    <p>New Player | <?= $this->db_model->select('name', 'tbl_users'); ?></p>
                </div>
                <div class="task-option">
                    <a href="<?= base_url('backend/users/') ?>" class="btn btn-sm btn-primary">All Players</a>
                </div>
            </div>
            <table class="table table-hover personal-task">
                <tbody>
                    <?php $i = 1; foreach($games as $game): ?>
                    <tr>
                        <td><?= $i++; ?></td>
                        <td><?= $game->name ?></td>
                        <td><?= $game->phone ?></td>
                        <td><?= $game->date ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
    <div class="col-12">
        <section class="card" style="height:600px">
            <header class="card-header">
                Recharge And Withdraw Stats
            </header>
            <div class="card-body">
                <canvas id="chart"></canvas>
            </div>
        </section>
    </div>
</div>

<?php
$recharge_counts = $this->db_model->count_records_by_day('tbl_deposit');
$withdraw_counts = $this->db_model->count_records_by_day('tbl_withdraw');
$deposit_values = array_values($recharge_counts);
$withdraw_values = array_values($withdraw_counts);
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
var chartData = {
    labels: ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
    datasets: [
        {
            label: "Deposits",
            backgroundColor: "rgba(75,192,192,0.4)",
            borderColor: "rgba(75,192,192,1)",
            borderWidth: 1,
            data: <?= json_encode($deposit_values); ?>,
        },
        {
            label: "Withdrawals",
            backgroundColor: "rgba(255,99,132,0.4)",
            borderColor: "rgba(255,99,132,1)",
            borderWidth: 1,
            data: <?= json_encode($withdraw_values); ?>, 
        },
    ],
};

function createChart() {
    var ctx = document.getElementById("chart").getContext("2d");
    var myChart = new Chart(ctx, {
        type: "bar",
        data: chartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: { stacked: false },
                y: { stacked: false },
            },
        },
    });
}
document.addEventListener("DOMContentLoaded", createChart);
</script>

<?php else: ?>

<div class="row state-overview">
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/users/') ?>">
            <section class="card">
                <div class="symbol terques">
                    <i class="fa fa-user"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('added_by', $this->session->admin_id)->get('tbl_users')->num_rows(); ?>
                    </h1>
                    <p>Total Users</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/users/') ?>">
            <section class="card">
                <div class="symbol red">
                    <i class="fa fa-tags"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('status', 1)->where('added_by', $this->session->admin_id)->get('tbl_users')->num_rows(); ?>  
                    </h1>
                    <p>Active Users</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="<?= base_url('backend/users/inactive') ?>">
            <section class="card">
                <div class="symbol yellow">
                    <i class="fa fa-shopping-cart"></i>
                </div>
                <div class="value">
                    <h1 class="count_ak">
                        <?= $this->db->where('status', 0)->where('added_by', $this->session->admin_id)->get('tbl_users')->num_rows(); ?>  
                    </h1>
                    <p>Inactive Users</p>
                </div>
            </section>
        </a>
    </div>
    <div class="col-lg-3 col-sm-6">
        <a href="#">
            <section class="card">
                <div class="symbol blue">
                    <i class="fa fa-bar-chart-o"></i>
                </div>
                <div class="value">
                    <h1 class="">
                        <?= $this->db_model->select('wallet', 'tbl_admin', array('id' => $this->session->admin_id)); ?>
                    </h1>
                    <p>Wallet</p>
                </div>
            </section>
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <section class="card">
            <div class="card-body progress-card">
                <div class="task-progress">
                    <h1>Latest Deposits</h1>
                </div>
                <div class="task-option">
                    <a href="<?= base_url('Wallet/fund_requests_p') ?>" class="btn btn-sm btn-primary">All Deposits</a>
                </div>
            </div>
            <div class="adv-table">
                <table class="display table table-bordered" id="hidden-table-info">
                    <thead>
                        <tr class="gradeX">
                            <th>SN</th>
                            <th>Mobile</th>
                            <th>Name</th>
                            <th>Amount</th>
                            <th>UTR</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        $deposits = $this->db->order_by('id', 'desc')->limit(10)->get('tbl_deposit')->result();
                        foreach($deposits as $e):
                            $added_by = $this->db_model->select('added_by', 'tbl_users', array('id' => $e->userid));
                            if($this->session->role != 'Admin' && $added_by != $this->session->admin_id) {
                                continue;
                            }
                        ?>
                        <tr class="gradeX">
                            <td><?= $i++; ?></td>
                            <td><?= $this->db_model->select('phone', 'tbl_users', array('id' => $e->userid)); ?></td>
                            <td><?= $this->db_model->select('name', 'tbl_users', array('id' => $e->userid)); ?></td>
                            <td><?= $e->amount ?></td>
                            <td><?= $e->tid ?></td>
                            <td><?= $e->payment_type ?></td>
                            <td>
                                <?php if($e->status == 'Failed'): ?>
                                    <span class="text-danger">Rejected</span>
                                <?php elseif($e->status == 'Paid'): ?>
                                    <span class="text-success">Approved</span>
                                <?php else: ?>
                                    <span class="text-warning">Processing</span>
                                <?php endif; ?>
                            </td>
                            <td><?= $e->date ?></td>
                            <td>
                                <a class="btn btn-sm btn-info text-white" href="<?= base_url('data/view_user/'.$e->userid) ?>">Profile</a>
                                <?php if($e->status == 'Pending'): ?>
                                    <a href="<?= site_url('backend/wallet/approve_fund_request/' . $e->id) ?>" class="btn btn-sm text-white btn-success">Approve</a>
                                    <a href="<?= site_url('backend/wallet/reject_fund_request/' . $e->id) ?>" class="btn btn-sm text-white btn-danger">Reject</a>
                                <?php else: ?>
                                    <span class='btn btn-sm btn-light'>Approved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<?php endif; ?>

<?php include('footer.php') ?>