<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/admin') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/admin/setting') ?>" method="post" enctype="multipart/form-data">
        <div class="row">
        <div class="form-group col-6">
            <label for="name">Company Name</label>
            <input type="text" class="form-control" id="name" placeholder="Enter company name" name="name" value="<?= $data->name ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="exampleInputPassword1">Set logo</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
            <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
        </div>
        <div class="form-group col-6">
            <label for="upi">Upi Id</label>
            <input type="text" class="form-control" id="upi" placeholder="Enter Upi Id" name="upi" value="<?= $data->upi_id ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="exampleInputPassword1">Qr Code For Manual</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile2" name="qr" accept="image/*">
            <label class="custom-file-label" for="customFile2">Choose file</label>
            </div>
        </div>
        <div class="form-group col-12">
            <button type="submit" class="btn btn-primary">Update</button>
        </div>
        </div>
      </form>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
