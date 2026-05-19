<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <?php if($this->uri->segment(2) == 'Wallet'){  ?>
          <a href="<?php echo site_url('backend/Wallet/withdraw_requests_p') ?>" class="btn btn-sm text-white btn-warning ml-2"><span>Pending</span></a>
          <a href="<?php echo site_url('backend/Wallet/withdraw_requests_r') ?>" class="btn btn-sm text-white btn-danger ml-2"><span>Rejected</span></a>
          <a href="<?php echo site_url('backend/Wallet/withdraw_requests_h') ?>" class="btn btn-sm text-white btn-danger ml-2"><span>Hold</span></a>
          <a href="<?php echo site_url('backend/Wallet/withdraw_requests_a') ?>" class="btn btn-sm text-white btn-success ml-2"><span>Approved</span></a>
          <?php } ?>
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table">
          <table class="display table table-bordered" id="hidden-table-info">
          <thead>
    <tr>
        <th>SN</th>
        <th>Mobile</th>
        <th>Name</th>
        <th>Amount</th>
        <th>Tax</th>
        <th>Net Payable</th>
        <th>Status</th>
        <th>Date</th>
        <th>
          <?php
          if($this->uri->segment(3)=='A'){
            echo "Paid Date";
          }
          else{
            echo "Action";
          }
          ?>
        </th>
    </tr>
</thead>
    <tbody>
        <?php
          $i = 1;
          foreach($data as $e){
            $added_by = $this->db_model->select('added_by', 'tbl_users', array('id' => $e->userid));
	        if($this->session->role != 'Admin' and $added_by != $this->session->admin_id){
			    continue;
	        } 
        ?>
        <tr class="gradeX">
            <td><?php echo $i++; ?></td>
            <td><?php echo $this->db_model->select('phone', 'tbl_users', array('id' => $e->userid)); ?></td>
            <td><?php echo $this->db_model->select('name', 'tbl_users', array('id' => $e->userid)); ?></td>
            <td><?php echo $e->amount ?></td>
            <td><?php echo $e->charges ?></td>
            <td><?php echo $e->amount - $e->charges ?></td>
            <td>
               <?php 
                  if($e->staus == 'Proccessing'){
                    echo "<b><span class=text-warning>Pending</span></b>";
                   }
                  elseif($e->staus == 'Paid'){
                    echo "<b><span class=text-success>Approved</span></b>";
                   }
                  elseif($e->staus == 'Rejected'){
                    echo "<b><span class=text-danger>Rejected</span></b>";
                   }
                  else{
                    echo "<b><span class=text-danger>Hold</span></b>";
                   }
                ?>
            </td>
            <td><?php echo $e->date ?></td>
            <td>
            <a class="btn btn-sm btn-info text-white" href="<?= base_url('data/view_user/'.$e->userid) ?>">Profile</a>
            <?php if($e->staus != 'Paid' and $e->staus != 'Rejected'){ ?>
                <a href="<?php echo site_url('backend/wallet/action_withdraw_requests/' . $e->id).'/Paid' ?>" class="btn btn-sm btn-success text-white">Approve</a>
                <a href="<?php echo site_url('backend/wallet/action_withdraw_requests/' . $e->id).'/Rejected' ?>" class="btn btn-sm btn-danger text-white">Reject</a>
                <?php if($e->staus != 'Hold'){ ?>
                <a href="<?php echo site_url('backend/wallet/action_withdraw_requests/' . $e->id.'/Hold') ?>" class="btn btn-sm btn-warning text-white">Hold</a>
            <?php }}else{ ?>
              <?php echo '<br>'.$e->paid_date ?>
              <?php } ?>
            </td>
        </tr>
        <?php
          }
        ?>
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
        <h5 class="modal-title" id="exampleModalLabel">Add New Game</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('backend/games') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Game Name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter name" name="name" required>
        </div>
        <div class="form-group">
            <label for="exampleInputgame">Select Game Category</label>
            <select name="cat_id" class="form-control" required>
                <option value="" selected disabled>Select Game Category</option>
                <?php $i = 1; foreach ($game_cat as $cat) { ?>
                <option value="<?= $cat->id ?>"><?= $cat->name ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Browse Game Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
            <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
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
