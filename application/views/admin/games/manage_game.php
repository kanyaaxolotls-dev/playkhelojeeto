<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Add New Game</button>
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <!--<th>Sr No</th>-->
                <th>Game ID</th>
                <th>Game Name</th>
                <th>Game Category</th>
                <th>Game Image</th>
                <th>Added At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; 
                foreach ($games as $game) { 
                  if($game->status == 1){
                    $status = 'checked';
                    $pass_v = 0;
                  }else{
                    $status = '';
                    $pass_v = 1;
                  }
              ?>
              <tr class="gradeX">
                <!--<td><?= $i++; ?></td>-->
                <td><?= $game->id; ?></td>
                <td><?= $game->name; ?></td>
                <td><?= $this->db_model->select('name', 'tbl_game_category', array('id' => $game->cat_id)); ?></td>
                <td class="text-center">
                    <a href="<?= ($game->img !== NULL) ? base_url('axxests/game_img/'.$game->img) : base_url('axxests/404/2.png') ?>" target="_"> 
                        <img src="<?= ($game->img !== NULL) ? base_url('axxests/game_img/'.$game->img) : base_url('axxests/404/2.png') ?> " height="60px" class="mx-auto" />
                    </a>
                </td>
                <td><?= $game->date; ?></td>
                <td class="d-flex justify-content-between">
                  <a class="btn btn-sm btn-primary" href="<?= base_url('backend/games/edit_game/'.$game->id) ?>">Edit</a>
                  <a  href="<?= base_url('backend/games/delete_game/'.$game->id.'/'.$pass_v) ?>">
                    <div class="make-switch" data-on="warning" data-off="danger">
                        <input type="checkbox" <?= $status ?>>
                    </div>
                  </a>
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
