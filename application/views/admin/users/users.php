<?php $this->load->view('admin/header');?>
<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Add User</button>
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table table-responsive">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <th>Sr No</th>
                <th>User ID</th>
                <th>phone</th>
                <th>password</th>
                <th>Name</th>
                <th>Wallet</th>
                <th>Winning wallet</th>
                <!-- <th>User code</th> -->
                <th>Referral code</th>
                <!-- <th>Profile</th> -->
                <th>Added At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($users as $user) { ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td class="tb-sub user-id"><?= $user->id; ?></td>
                <td><?= $user->phone; ?></td>
                <td><?= $user->password; ?></td>
                <td class="title" ><?= $user->name; ?></td>
                <td class="text-success tb-lead wallet">₹ <?= $user->wallet; ?></td>
                <td class="text-success">₹ <?= $user->winning_wallet; ?></td>
                <!-- <td><?= $user->usercode; ?></td> -->
                <td><?= 'NG'.$user->id; ?></td>
                <!-- <td class="text-center">
                    <a href="<?= ($user->img !== NULL) ? base_url('axxests/user_img/'.$user->img) : base_url('axxests/404/2.png') ?>" target="_"> 
                        <img src="<?= ($user->img !== NULL) ? base_url('axxests/user_img/'.$user->img) : base_url('axxests/404/2.png') ?> " height="60px" class="mx-auto" />
                    </a>
                </td> -->
                <td><?= $user->date; ?></td>
                <td>
                  <a class="btn btn-sm btn-primary" href="<?= base_url('backend/users/edit_user/'.$user->id) ?>">Edit</a>
                  <?php if($user->status == 1){ ?>
                  <a class="btn btn-sm btn-danger" href="<?= base_url('backend/users/delete_user/'.$user->id.'/0') ?>">Deactivate</a>
                  <a class="btn btn-sm btn-success text-white update-wallet">Update Wallet</a>
                  <?php }else{ ?>
                  <a class="btn btn-sm btn-success" href="<?= base_url('backend/users/delete_user/'.$user->id.'/1') ?>">Activate</a>
                  <?php } ?>
                  <a class="btn btn-sm btn-info text-white" href="<?= base_url('data/view_user/'.$user->id) ?>">Profile</a>
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

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script>
$(document).ready(function () {
    $('.update-wallet').on('click', function () {
        var $row       = $(this).closest('tr');
        var userId     = $row.find('.tb-sub.user-id').text();
        var userName   = $row.find('.title').text();
        var userWallet = $row.find('.tb-lead.wallet').text().replace('₹', '').trim();
        Swal.fire({
            title: 'Update Wallet',
            html: 
                '<p><strong>Name :</strong> <span class="text-danger">' + userName + '</span></p>' +
                '<p><strong>Current Wallet :</strong><span class="text-success"> ₹' + userWallet + '</span></p>' +
                '<div class="form-group" style="text-align: left;">' +
                '  <label>Enter Amount : </label>' +
                '  <input type="text" class="form-control mt-2" id="walletAmount" name="walletAmount" placeholder="">' +
                '</div>' +
                '<div class="form-group mt-2" style="text-align: left;">' +
                '  <label>Select Wallet : </label>' +
                '  <select class="form-control mt-2" id="wallet_type" name="wallet_type">' +
                '    <option value="wallet">Wallet</option>' +
                '    <option value="winning_wallet">Winning Wallet</option>' +
                '  </select>' +
                '</div>' +
                '<div class="form-group mt-2" style="text-align: left;">' +
                '  <label>Transaction Type : </label>' +
                '  <select class="form-control mt-2" id="transactionType" name="transactionType">' +
                '    <option value="credit">Credit</option>' +
                '    <option value="debit">Debit</option>' +
                '  </select>' +
                '</div>',
            showCancelButton: true,
            confirmButtonText: 'Update',
        }).then((result) => {
            if (result.isConfirmed) {
                var walletAmount = $('[name="walletAmount"]').val();
                var wallet_type  = $('[name="wallet_type"]').val();
                var transaction  = $('[name="transactionType"]').val();
$.ajax({
    type: 'POST',
    url: "<?php echo base_url('backend/users/process_transaction') ?>", 
    data: {
        user_id: userId,
        amount: walletAmount,
        transaction: transaction,
        wallet_type: wallet_type 
    },
    dataType: 'json',
    success: function(response) {
        if (response.success) {
            Swal.fire('Success', 'Wallet updated successfully', 'success');
            // Don't update the UI directly with the input amount - use the returned balance
            $row.find('.tb-lead.wallet').text('₹ ' + response.balance);
            // Consider if you really need to reload the page
            setTimeout(function() {
                location.reload(); 
            }, 1500);
        } else {
            Swal.fire('Error', response.message || 'Wallet update failed', 'error');
        }
    },
    error: function(xhr, status, error) {
        // Try to parse the response if it's HTML to find the error
        let errorMsg = error;
        if (xhr.responseText && xhr.responseText.startsWith('<')) {
            // Extract error from HTML response if possible
            const doc = new DOMParser().parseFromString(xhr.responseText, 'text/html');
            const errorElement = doc.querySelector('.error, .alert, body');
            errorMsg = errorElement ? errorElement.textContent.substring(0, 100) : 'Server returned HTML instead of JSON';
        }
        Swal.fire('Error', 'An error occurred: ' + errorMsg, 'error');
    }
});

            }
        });
    });
});

</script>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Add New User</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('backend/users/') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Name" name="name" required>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail2">Phone</label>
            <input type="number" class="form-control" id="exampleInputEmail2" aria-describedby="emailHelp" placeholder="Enter Phone" name="phone" required>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail3">Password</label>
            <input type="text" class="form-control" id="exampleInputEmail3" aria-describedby="emailHelp" placeholder="Enter Password" name="password" required>
        </div>
        <div class="form-group">
            <label for="exampleInputEmail4">Invite Code</label>
            <input type="text" class="form-control" id="exampleInputEmail4" aria-describedby="emailHelp" placeholder="Enter Invite Code" name="invite_code">
        </div>
        <!-- <div class="form-group">
            <label for="exampleInputPassword1">Browse User Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*" required>
            <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
        </div> -->
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
