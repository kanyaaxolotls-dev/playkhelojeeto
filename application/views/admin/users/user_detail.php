<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a href="<?= base_url('backend/users/') ?>" class="btn btn-sm btn-primary text-white">Go back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <div class="card-body">
          <div class="row mb-3">
            <div class="col-md-4 mb-2">
              <div class="card border">
                <div class="card-body">
                  <h3 class="card-title">Wallet Balance</h3><br>
                  <p class="card-text">₹ <?= $this->db->select('wallet')->where('id', $detail->id)->get('tbl_users')->row()->wallet ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="card border">
                <div class="card-body">
                  <h3 class="card-title">Total Withdraw</h3><br>
                  <p class="card-text">₹ <?= $this->db->select('COALESCE(SUM(amount), 0) as total_amount')->where('userid', $detail->id)->get('tbl_withdraw')->row()->total_amount; ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="card border">
                <div class="card-body">
                  <h3 class="card-title">Total Deposit</h3><br>
                  <p class="card-text">₹ <?= $this->db->select('COALESCE(SUM(amount), 0) as total_amount')->where('userid', $detail->id)->get('tbl_deposit')->row()->total_amount; ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="card border">
                <div class="card-body">
                  <h3 class="card-title">Total win</h3><br>
                  <p class="card-text">₹ <?= $this->db_model->sum('amount','tbl_transactions',array('userid' => $detail->id,'status' => 'Won')) + 0 ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="card border">
                <div class="card-body">
                  <h3 class="card-title">Total Loss</h3><br>
                  <p class="card-text">₹ <?= $this->db_model->sum('amount','tbl_bet_dragon',array('userid' => $detail->id,'status' => 'Loss')) + $this->db_model->sum('amount','tbl_aviator_bet',array('userid' => $detail->id,'status' => 'loss')) + 0 ?></p>
                </div>
              </div>
            </div>
            <div class="col-md-4 mb-2">
              <div class="card border">
                <div class="card-body">
                  <h3 class="card-title">Direct Team</h3><br>
                  <p class="card-text"><?= $this->db_model->count_all('tbl_users',array('referral_code' => $detail->id)) ?></p>
                </div>
              </div>
            </div>
          </div>
          <h5 class="text-primary h5">Personal Information</h5><br>
          <div class="row">
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">Full Name</p>
              <?php echo isset($detail->name) ? '<p class="text-info font-weight-bolder h6">' . $detail->name . '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">Mobile Number</p>
              <?php echo isset($detail->phone) ? '<p class="text-info font-weight-bolder h6">' . $detail->phone . '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">Email</p>
              <?php echo isset($detail->phone) ? '<p class="text-info font-weight-bolder h6">' . $detail->email . '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">UPI</p>
              <?php echo isset($detail->upi) ? '<p class="text-info font-weight-bolder h6">' . $detail->upi. '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">Bank Name</p>
              <?php echo isset($detail->bank_name) ? '<p class="text-info font-weight-bolder h6">' . $detail->bank_name. '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">Ifsc Code</p>
              <?php echo isset($detail->bank_ifsc) ? '<p class="text-info font-weight-bolder h6">' . $detail->bank_ifsc. '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
            <div class="col-md-6 d-flex justify-content-between py-2">
              <p class="text-muted h6 font-weight-bolder">Account Number</p>
              <?php echo isset($detail->bank_acc) ? '<p class="text-info font-weight-bolder h6">' . $detail->bank_acc. '</p>' : '<p class="text-muted h6">No data available</p>'; ?>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="card">
      <header class="card-header">
        <?= $title2 ?>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
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
              </tr>
            </thead>
            <tbody>
              <?php
                $i = 1;
                if($data){
                foreach($data as $e){
              ?>
              <tr class="gradeX">
                <td><?php echo $i++; ?></td>
                <td><?php echo $this->db_model->select('phone', 'tbl_users', array('id' => $e->userid)); ?></td>
                <td><?php echo $this->db_model->select('name', 'tbl_users', array('id' => $e->userid)); ?></td>
                <td><?php echo $e->amount ?></td>
                <td><?php echo $e->tid ?></td>
                <td><?php echo $e->payment_type ?></td>
                <td>
                  <?php 
                    if($e->status == 'Failed'){
                      echo "<span class=text-danger>Rejected</span>";
                    }
                    elseif($e->status == 'Paid'){
                      echo "<span class=text-success>Approved</span>";
                    }
                    else{
                      echo "<span class=text-warning>Proccessing</span>";
                    }
                  ?>
                </td>
                <td><?php echo $e->date ?></td>
              </tr>
              <?php
                }
                }else{
                    echo '<tr><td colspan="8" class="text-center text-danger">No Data Available!</td></tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

    <section class="card">
      <header class="card-header">
        <?= $title3 ?>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <th>SN</th>
                <th>Mobile</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Tax</th>
                <th>Net Payable</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php
                $i = 1;
                if($data2){
                foreach($data2 as $e){
              ?>
              <tr class="gradeX">
                <td><?php echo $i++; ?></td>
                <td><?php echo $this->db_model->select('phone', 'tbl_users', array('id' => $e->userid)); ?></td>
                <td><?php echo $this->db_model->select('name', 'tbl_users', array('id' => $e->userid)); ?></td>
                <td><?php echo $e->amount ?></td>
                <td><?php echo $e->charges ?></td>
                <td><?php echo $e->amount - $e->charges ?></td>
                <td>
                  <?php 
                    if($e->staus == 'Proccessing'){
                      echo "<b><span class=text-warning>Pending</span></b>";
                    }
                    elseif($e->staus == 'Paid'){
                      echo "<b><span class=text-success>Approved</span></b>";
                    }
                    else{
                      echo "<b><span class=text-danger>Hold</span></b>";
                    }
                  ?>
                </td>
                <td><?php echo $e->date ?></td>
              </tr>
              <?php
                }
                }else{
                    echo '<tr><td colspan="8" class="text-center text-danger">No Data Available!</td></tr>';
                }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>

  </div>
</div>

<?php $this->load->view('admin/footer2');?>
