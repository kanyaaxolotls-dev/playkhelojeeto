<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table table-responsive">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <th>Sr No</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Amount</th>
                <th>Bet on</th>
                <th>Game Period</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $i = 1; 
                foreach ($data as $tr) { 
                    $userinfo = $this->db_model->select_multi('*', 'tbl_users', array('phone' => $tr->username));
                    if($tr->res == 'success'){
                      $clr = 'success';
                    }
                    elseif($tr->res == 'fail'){
                      $clr = 'danger';
                    }
                    else{
                      $clr = 'warning';
                    }
              ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td><?= $userinfo->name; ?></td>
                <td><?= '+91 '.$userinfo->phone; ?></td>
                <td class="text-<?= $clr ?>"><b>₹ <?= $tr->amount; ?></b></td>
                <td class="text-info"><b><?= strtoupper($tr->ans); ?></b></td>
                <td><?= $tr->period; ?></td>
                <td class="text-<?= $clr ?> text-center">
                
                <?php if($tr->res == 'Waiting'){ ?>
                  <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <?php } else{ echo strtoupper($tr->res); } ?>
                </td>
                <td><?= $tr->time; ?></td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer');?>
