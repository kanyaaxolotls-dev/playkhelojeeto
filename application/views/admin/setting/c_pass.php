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
        <form action="<?= base_url('backend/admin/change_pass') ?>" method="post">
        <div class="row">
        <div class="form-group col-12">
            <label for="uname">Old Password</label>
            <input type="text" class="form-control" id="opass" placeholder="Enter old Password" name="opass" required>
        </div>
        <div class="form-group col-6">
            <label for="name">Create Password</label>
            <input type="text" class="form-control" id="pass" placeholder="Create Password" name="pass" pattern=".{5,}" title="Minimum 5 characters" required>
        </div>
        <div class="form-group col-6">
            <label for="phone">New Password</label>
            <input type="text" class="form-control" id="cpass" placeholder="Confirm Password" name="cpass" pattern=".{5,}" title="Minimum 5 characters" required>
        </div>
        <div class="form-group col-12">
            <button type="submit" class="btn btn-primary">Change Password</button>
        </div>
        </div>
      </form>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
