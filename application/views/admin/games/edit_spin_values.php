<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/games/spin') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/games/update_spin') ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="exampleInputEmail1">Amount</label>
            <input type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Chips Amount" value="<?= $detail->amount ?>" name="amount" required>
            <input type="hidden" value="<?= $detail->id ?>" name="id">
        </div>
          <button type="submit" class="btn btn-primary">Update</button>
    </div>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
