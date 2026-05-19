<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <?php if($this->uri->segment(2) == 'Wallet'){  ?>
          <a href="<?php echo site_url('backend/Wallet/fund_requests_a') ?>" class="btn btn-sm btn-success text-white ml-2"><span>Approved</span></a>
          <a href="<?php echo site_url('backend/Wallet/fund_requests_p') ?>" class="btn btn-sm btn-warning text-white ml-2"><span>Pending</span></a>
          <a href="<?php echo site_url('backend/Wallet/fund_requests_r') ?>" class="btn btn-sm btn-danger text-white ml-2"><span>Rejected</span></a>
          <?php } ?>
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
                <tr class="gradeX">
                    <th>SN</th>
                    <th>Mobile</th>
                    <th>Name</th>
                    <th>Amount</th>
                    <th>UTR</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Action</th>
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
                    <td><?php echo $e->tid ?></td>
                    <td><?php echo $e->	payment_type ?></td>
                    <td>
                       <?php 
                          if($e->status == 'Failed'){
                            echo "<span class=text-danger>Rejected</span>";
                           }
                          elseif($e->status == 'Paid'){
                            echo "<span class=text-success>Approved</span>";
                           }
                          else{
                            echo "<span class=text-warning>Proccessing</span>";
                           }
                        ?>
                    </td>
                    <td><?php echo $e->date ?></td>
                    <td>
                    <a class="btn btn-sm btn-info text-white" href="<?= base_url('data/view_user/'.$e->userid) ?>">Profile</a>
                    <?php 
                        if($e->status == 'Pending'){ ?>
                        <a href="<?php echo site_url('backend/wallet/approve_fund_request/' . $e->id) ?>" class="btn btn-sm text-white btn-success">Approve</a>
                        <a href="<?php echo site_url('backend/wallet/reject_fund_request/' . $e->id) ?>" class="btn btn-sm  text-white btn-danger">Reject</a>
                    <?php }else{ echo "<span class='btn btn-sm btn-light'>Approved</span>";} ?>
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
