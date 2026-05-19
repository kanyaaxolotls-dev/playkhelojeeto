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
                <th>Game Period</th>
                <th>Crash On</th>
                <th>Total Betting</th>
                <th>Total Cashout</th>
                <th>Profit | Loss</th>
                <th>Datetime</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $i = 1; foreach ($data as $tr) { 
              ?>
                <tr class="gradeX">
                    <td><?= $i++; ?></td>
                    <td><?= $tr->period_id; ?></td>
                    <td class="text-danger"><?= $tr->winning.' X'; ?></td>
                    <td><?= $tr->total_amount; ?></td>
                    <td class="text-success"><?= $tr->total_win; ?></td>
                    <td class="text-info"><?= $tr->total_amount - $tr->total_win; ?></td>
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
