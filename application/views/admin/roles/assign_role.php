<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Assign Role</button>
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
                <!--<th>Name</th>-->
                <th>Username</th>
                <th>Password</th>
                <th>Wallet</th>
                <th>Role</th>
                <th>Added At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($roles as $role) { ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <!--<td><?= $role->name; ?></td>-->
                <td><?= $role->username; ?></td>
                <td><?= $role->password; ?></td>
                <td><?= $role->wallet; ?></td>
                <td><?= $role->role; ?></td>
                <td><?= $role->created_at; ?></td>
                <td>
                    <?php if($role->id != 1){ ?>
                  <a class="btn btn-sm btn-primary" href="<?= base_url('backend/roles/edit_user_role/'.$role->id) ?>">Edit</a>
                  <a class="btn btn-sm btn-danger delete-btn" href="<?= base_url('backend/roles/delete_user_role/'.$role->id) ?>">Delete</a>
                  <button class="btn btn-sm btn-info update-wallet-btn" data-id="<?= $role->id ?>" data-wallet="<?= $role->wallet ?>" data-toggle="modal" data-target="#walletModal">Update Wallet</button>
                  <?php } ?>
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

<!-- Assign Role Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Assign Role</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('backend/roles/create_users') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
            <label for="name">User Name</label>
            <input type="text" class="form-control" id="full-uname" required name="uname" placeholder="Enter Username">
        </div>
        <div class="form-group">
            <label for="name">Mobile Number</label>
            <input type="number" class="form-control" id="full-phone" required name="phone" placeholder="Enter Mobile Number">
        </div>
        <div class="form-group">
            <label for="name">Password</label>
            <input type="password" class="form-control" id="full-pass" required name="pass" placeholder="Enter Password">
        </div>
        <div class="form-group">
            <label for="name">Confirm Password</label>
            <input type="password" class="form-control" id="full-cpass" required name="cpass" placeholder="Confirm Password">
        </div>
        <div class="form-group">
            <label for="name">Select Role</label>
            <select class="form-control" name="role" required>
                <option value="" selected disabled>Select Role</option>
                <?php 
                  $this->db->select('*')->from('tbl_roles')->where('status', 1);
                  $query = $this->db->get()->result_array();
                  foreach($query as $optn){
                ?>
                <option value="<?php echo $optn['name'] ?>"><?php echo $optn['name'] ?> </option>
                <?php } ?>
            </select>
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

<!-- Update Wallet Modal -->
<div class="modal fade" id="walletModal" tabindex="-1" role="dialog" aria-labelledby="walletModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="walletModalLabel">Update Wallet</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form id="walletForm" action="<?= base_url('backend/roles/update_wallet') ?>" method="post">
      <div class="modal-body">
        <input type="hidden" id="user_id" name="user_id">
        <div class="form-group">
            <label for="current_wallet">Current Wallet Balance</label>
            <input type="text" class="form-control" id="current_wallet" readonly>
        </div>
        <div class="form-group">
            <label for="action">Action</label>
            <select class="form-control" id="action" name="action" required>
                <option value="add">Add Amount</option>
                <option value="deduct">Deduct Amount</option>
            </select>
        </div>
        <div class="form-group">
            <label for="amount">Amount</label>
            <input type="number" class="form-control" id="amount" name="amount" required min="0" step="0.01" placeholder="Enter Amount">
        </div>
        <div class="form-group">
            <label for="remarks">Remarks</label>
            <textarea class="form-control" id="remarks" name="remarks" placeholder="Enter Remarks (Optional)"></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Update Wallet</button>
    </div>
    </form>
    </div>
  </div>
</div>

<?php $this->load->view('admin/footer');?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script>
$(document).ready(function() {
    // Set up wallet modal with user data when button is clicked
    $('.update-wallet-btn').click(function() {
        var userId = $(this).data('id');
        var walletAmount = $(this).data('wallet');
        
        $('#user_id').val(userId);
        $('#current_wallet').val(walletAmount);
    });
    
    // Delete confirmation with SweetAlert
    $('.delete-btn').click(function(e) {
        e.preventDefault();
        var deleteUrl = $(this).attr('href');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = deleteUrl;
            }
        });
    });
    
    // Wallet form submission with SweetAlert
    $('#walletForm').submit(function(e) {
        e.preventDefault();
        var form = this;
        
        Swal.fire({
            title: 'Confirm Wallet Update',
            text: "Are you sure you want to update this wallet?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, update it!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    
    // Show success message if there's a flash message
    <?php if($this->session->flashdata('success')): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo $this->session->flashdata("success"); ?>',
            timer: 3000
        });
    <?php endif; ?>
    
    // Show error message if there's a flash message
    <?php if($this->session->flashdata('error')): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo $this->session->flashdata("error"); ?>',
            timer: 3000
        });
    <?php endif; ?>
});
</script>