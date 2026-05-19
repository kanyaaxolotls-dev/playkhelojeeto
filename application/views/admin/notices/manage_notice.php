<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <!-- <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Add New Notice</button> -->
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
                <th>Title</th>
                <th>Category</th>
                <th>Image</th>
                <th>Status</th>
                <th>Added At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($data as $tr) { ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td><?= $tr->title; ?></td>
                <td><?= $tr->category; ?></td>
                <td class="text-center">
                    <a href="<?= ($tr->img !== NULL) ? base_url('axxests/notice_img/'.$tr->img) : base_url('axxests/404/2.png') ?>" target="_"> 
                        <img src="<?= ($tr->img !== NULL) ? base_url('axxests/notice_img/'.$tr->img) : base_url('axxests/404/2.png') ?> " height="60px" class="mx-auto" />
                    </a>
                </td>
                <td><?= $tr->status; ?></td>
                <td><?= $tr->date; ?></td>
                <td>
                  <a class="btn btn-sm btn-primary" href="<?= base_url('backend/Notices/edit_notice/'.$tr->id) ?>">Edit</a>
                  <!-- <a class="btn btn-sm btn-danger" href="<?= base_url('backend/Notices/delete_notice/'.$tr->id) ?>">Delete</a> -->
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
        <h5 class="modal-title" id="exampleModalLabel">Add New Notice</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('backend/Notices') ?>" method="post" enctype="multipart/form-data">
      <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Title</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter name" name="name" required>
        </div>
        <div class="form-group">
            <label for="exampleInputgame">Select Notice Category</label>
            <select name="cat_name" class="form-control" required>
                <option value="" selected disabled>Select Notice Category</option>
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
