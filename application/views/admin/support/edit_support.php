<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/support/index') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/support/update_support') ?>" method="post" enctype="multipart/form-data">
        <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter name" name="name" value="<?= $detail->name ?>" required>
            <input type="hidden"  name="id" value="<?= $id ?>">
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">Link</label>
            <input type="text" class="form-control" id="exampleInputEmail2" aria-describedby="emailHelp2" placeholder="Enter link" name="link" value="<?= $detail->link ?>" required>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Change Support Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
            <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
        </div>
          <button type="submit" class="btn btn-primary">Update</button>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
