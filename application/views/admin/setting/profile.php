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
        <form action="<?= base_url('backend/admin/profile') ?>" method="post" enctype="multipart/form-data">
        <div class="row">
        <div class="form-group col-6">
            <label for="uname">Username</label>
            <input type="text" class="form-control" id="uname" placeholder="Enter Username" name="uname" value="<?= $data->username ?>" readonly>
        </div>
        <div class="form-group col-6">
            <label for="name">Name</label>
            <input type="text" class="form-control" id="name" placeholder="Enter name" name="name" value="<?= $data->name ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="phone">Phone</label>
            <input type="text" class="form-control" id="phone" placeholder="Enter phone number" name="phone" value="<?= $data->phone ?>" readonly>
        </div>
        <div class="form-group col-6">
            <label for="email">Email</label>
            <input type="text" class="form-control" id="email" placeholder="Enter email" name="email" value="<?= $data->email ?>" readonly>
        </div>
        <div class="form-group col-6">
            <label for="state">State</label>
            <input type="text" class="form-control" id="state" placeholder="Enter state name" name="state" value="<?= $data->state ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="city">City</label>
            <input type="text" class="form-control" id="city" placeholder="Enter city" name="city" value="<?= $data->city ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="address">Address</label>
            <input type="text" class="form-control" id="address" placeholder="Enter full address" name="address" value="<?= $data->address ?>" required>
        </div>
        <div class="form-group col-6">
            <label for="upi">Upi</label>
            <input type="text" class="form-control" id="upi" placeholder="Enter upi" name="upi" value="<?= $data->upi ?>" required>
        </div>
        <div class="form-group col-12">
            <label for="exampleInputPassword1">Set Profile Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
            <label class="custom-file-label" for="customFile">Choose file</label>
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
