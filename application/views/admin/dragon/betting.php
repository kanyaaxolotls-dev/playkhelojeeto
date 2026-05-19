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
                <th>Userid</th>
                <th>Name</th>
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
                  if($tr->status == 'Won'){
                    $clr = 'success';
                  }
                  elseif($tr->status == 'Loss'){
                    $clr = 'danger';
                  }
                  else{
                    $clr = 'warning';
                  }
              ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td><?= $tr->userid; ?></td>
                <td><?= $this->db_model->select('name', 'tbl_users', array('id' => $tr->userid)); ?></td>
                <td class="text-<?= $clr ?>">₹ <?= $tr->amount; ?></td>
                <td class="text-primary"><?= $tr->betting_on; ?></td>
                <td><?= $tr->period_id; ?></td>
                <td class="text-<?= $clr ?> text-center">
                
                <?php if($tr->status == 'Waiting'){ ?>
                  <div class="spinner-border" role="status">
                    <span class="sr-only">Loading...</span>
                  </div>
                  <?php } else{ echo $tr->status; } ?>
                </td>
                <td><?= $tr->date; ?></td>
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
