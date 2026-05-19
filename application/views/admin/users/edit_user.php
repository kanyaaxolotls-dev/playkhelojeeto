<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card p-3">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
      <form action="<?= base_url('backend/users/edit_user/'.$user->id) ?>" method="post" enctype="multipart/form-data">
        <div class="modal-body">
          <h4 class="text-danger"><?= $title ?></h4>
          <div class="form-group mt-3">
              <label for="exampleInputEmail1">Name</label>
              <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Name" value="<?= $user->name ?>" name="name" required>
          </div>
          <div class="form-group">
              <label for="exampleInputEmail2">Phone</label>
              <input type="number" class="form-control" id="exampleInputEmail2" aria-describedby="emailHelp" placeholder="Enter Phone" value="<?= $user->phone ?>" name="phone" readonly required>
          </div>
          <div class="form-group">
              <label for="exampleInputEmail3">Password</label>
              <input type="text" class="form-control" id="exampleInputEmail3" aria-describedby="emailHelp" placeholder="Enter Password" name="password" value="<?= $user->password ?>" required>
          </div>
          <div class="form-group">
              <label for="exampleInputEmail4">Email</label>
              <input type="email" class="form-control" id="exampleInputEmail4" aria-describedby="emailHelp" placeholder="Enter Email" value="<?= $user->email ?>" name="email">
          </div>
          <!-- <div class="form-group">
              <label for="exampleInputPassword1">Browse User Image</label>
              <div class="custom-file">
              <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
              <label class="custom-file-label" for="customFile">Choose file</label>
              </div>
          </div> -->
      </div>
      <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
      </div>
      </form>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
