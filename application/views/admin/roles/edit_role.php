<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/roles/assign_role') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/roles/update_subadmin') ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="name">User Name</label>
            <input type="text" class="form-control" id="full-uname" required name="uname" value="<?= $detail->username ?>" placeholder="Enter Username">
            <input type="hidden" name="id" value="<?= $detail->id ?>">
        </div>
        <div class="form-group">
            <label for="name">Mobile Number</label>
            <input type="number" class="form-control" id="full-phone" required name="phone" value="<?= $detail->phone ?>" placeholder="Enter Mobile Number">
        </div>
        <div class="form-group">
            <label for="name">Change Password</label>
            <input type="text" class="form-control" id="full-pass" required name="pass" value="<?= $detail->password ?>" placeholder="Enter Password">
        </div>
        <div class="form-group">
            <label for="name">Select Role</label>
            <select class="form-control" name="role" required>
                <option value="" selected disabled>Select Role</option>
                <?php 
                  $this->db->select('*')->from('tbl_roles')->where('status', 1);
                  $query = $this->db->get()->result_array();
                  foreach($query as $optn){
                    if($optn['name'] == $detail->role){
                ?>
                <option value="<?php echo $optn['name'] ?>" selected><?php echo $optn['name'] ?> </option>
                <?php }else{ ?>
                  <option value="<?php echo $optn['name'] ?>"><?php echo $optn['name'] ?> </option>
                <?php }} ?>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Update</button>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
