<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Add New Role</button>
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <th>Sr No</th>
                <th>Name</th>
                <th>Assign Tasks</th>
                <th>Added At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($roles as $role) { ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td><?= $role->name; ?></td>
                <td><?= $role->tasks; ?></td>
                <td><?= $role->Date; ?></td>
                <td>
                  <a class="btn btn-sm btn-danger" href="<?= base_url('backend/roles/delete_role/'.$role->id) ?>">Delete</a>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Create New Role</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('backend/roles/create_rol') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
      <div class="form-group">
            <label for="name">Role Name</label>
            <input type="text" class="form-control" id="name" aria-describedby="emailHelp" placeholder="Enter Role name" name="name" required>
        </div>
        <div class="form-group">
        <table class="table table-borderless">
            <thead>
             <tr>
               <th scope="col">#</th>
               <th scope="col">Menus</th>
               <th scope="col">Child Menu</th>
               <!-- <th scope="col">Edit</th>
               <th scope="col">Delete</th>
             </tr> -->
            </thead>
            <tbody>
              <?php 
                $this->db->select("*");
                $this->db->where('child_of',0);
                $this->db->where('status',1);
                $this->db->from("tbl_task_manager");
                $query = $this->db->get();        
                $optins = $query->result();
                $i = 1;
                foreach($optins as $menu){
              ?>
            <tr>
              <th scope="row"><?php echo $i++;; ?></th>
              <td><input type="checkbox" name="role[]"   value="<?php echo $menu->id; ?>" ><span style="margin-left:2px"><?php echo $menu->name; ?></span></td>
              <td>
                  <?php 
                     $this->db->select("*");
                     $this->db->where('child_of',$menu->id);
                     $this->db->where('status',1);
                     $query = $this->db->get('tbl_task_manager');     
                     $optins2 = $query->result();
                     foreach($optins2 as $menu2){
                  ?>
                  <input type="checkbox" name="role[]"   value="<?php echo $menu2->id; ?>"><?php echo $menu2->name; ?><br>
                  <?php } ?>
              </td>
              <!-- <td><input type="checkbox" name="edit[]"   value="<?php echo $menu->id; ?>"></td>
              <td><input type="checkbox" name="delete[]" value="<?php echo $menu->id; ?>"></td> -->
            </tr>  
              <?php } ?>
              </tbody>
            </table>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
    </div>
    </form>
    </div>
  </div>
</div>
<?php $this->load->view('admin/footer');?>
