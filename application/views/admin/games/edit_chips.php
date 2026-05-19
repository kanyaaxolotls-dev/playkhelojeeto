<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/games/manage_chips') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/games/update_chips') ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="exampleInputEmail1">Chips Amount</label>
            <input type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Chips Amount" value="<?= $detail->amount ?>" name="amount" required>
            <input type="hidden" value="<?= $detail->id ?>" name="id">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">Bonus</label>
            <input type="number" class="form-control" id="exampleInputEmail2" aria-describedby="emailHelp" placeholder="Enter Bonus" value="<?= $detail->principle ?>" name="p_amount" required>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Browse Chip Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
            <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Update</button>
    </div>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
