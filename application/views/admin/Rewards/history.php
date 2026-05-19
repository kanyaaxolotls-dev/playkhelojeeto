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
                        <th>SN</th>
                        <th>Userid</th>
                        <th>Phone</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1; foreach($data as $e){ ?>
                        <tr class="gradeX">
                            <td><?= $i++; ?></td>
                            <td><?= $e->userid; ?></td>
                            <td>+91 <?= $this->db_model->select('phone', 'tbl_users', array('id' => $e->userid)); ?></td>
                            <td class="text-success">₹ <?= $e->amount ?></td>
                            <td class="text-danger"><?= $e->type ?></td>
                            <td><?= $e->date ?></td>
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
