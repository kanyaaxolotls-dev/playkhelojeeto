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
                <th>Color</th>
                <th>Number</th>
                <th>Datetime</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $i = 1; foreach ($data as $tr) { 
              ?>
                <tr class="gradeX">
                    <td><?= $i++; ?></td>
                    <td><?= $tr->period; ?></td>
                    <td style="color:<?= $tr->clo ?>"><b><?= strtoupper($tr->clo); ?></b></td>
                    <td style="color:<?= $tr->clo ?>"><b><?= $tr->num; ?></b></td>
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
