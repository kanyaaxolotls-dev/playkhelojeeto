<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/games') ?>">Go Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/games/update_game') ?>" method="post" enctype="multipart/form-data">
        <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Game Name</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter name" name="name" value="<?= $detail->name ?>" required>
            <input type="hidden"  name="id" value="<?= $id ?>">
        </div>
        <div class="form-group">
            <label for="exampleInputgame">Select Game Category</label>
            <select name="cat_id" class="form-control" required>
                <option value="" selected disabled>Select Game Category</option>
                <?php $i = 1; foreach ($game_cat as $cat) { if($detail->cat_id == $cat->id){ ?>
                <option value="<?= $cat->id ?>" selected><?= $cat->name ?></option>
                <?php }else{ ?>
                <option value="<?= $cat->id ?>"><?= $cat->name ?></option>
                <?php }} ?>
            </select>
        </div>
        <div class="form-group">
            <label for="exampleInputPassword1">Browse Game Image</label>
            <div class="custom-file">
            <input type="file" class="custom-file-input" id="customFile" name="img" accept="image/*">
            <label class="custom-file-label" for="customFile">Choose file</label>
            </div>
        </div>
          <button type="submit" class="btn btn-primary">Update</button>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
