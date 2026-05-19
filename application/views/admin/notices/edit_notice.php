<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/Notices/') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/notices/update_notice') ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="exampleInputEmail1">Title</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter name" value="<?= $detail->title ?>" name="name" required>
            <input type="hidden" value="<?= $detail->id ?>" name="id">
        </div>
        <div class="form-group">
            <label for="exampleInputgame">Select Notice Category</label>
            <select name="cat_name" class="form-control" required>
                <option value="" selected disabled>Select Notice Category</option>
                <option value="<?= $detail->category ?>" selected><?= $detail->category ?></option>
                <option value="Hots">Hots</option>
                <option value="Notice">Notice</option>
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Browse Notice Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*" required>
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
