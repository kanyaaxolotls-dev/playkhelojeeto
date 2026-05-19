<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
          <a class="btn text-white btn-sm btn-primary" href="<?= base_url('backend/admin') ?>">Dashboard</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
        <form class="row" action="<?= base_url('backend/games/amount_setting') ?>" method="post" enctype="multipart/form-data">
        <div class="form-group col-6">
            <label for="exampleInputEmail1">Minimum Deposit</label>
            <input type="text" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Minimum Deposit" value="<?= $detail->min_deposit ?>" name="min_deposit" required>
            <input type="hidden" value="<?= $detail->id ?>" name="id">
        </div>
        <div class="form-group col-6">
            <label for="exampleInputEmail2">Maximum Deposit</label>
            <input type="text" class="form-control" id="exampleInputEmail2" aria-describedby="emailHelp" placeholder="Enter Maximum Deposit" value="<?= $detail->max_deposit ?>" name="max_deposit" required>
        </div>
        <div class="form-group col-6">
            <label for="exampleInputEmail3">Minimum Withdraw</label>
            <input type="text" class="form-control" id="exampleInputEmail3" aria-describedby="emailHelp" placeholder="Enter Minimum Withdraw" value="<?= $detail->min_withdraw ?>" name="min_withdraw" required>
        </div>
        <div class="form-group col-6">
            <label for="exampleInputEmail4">Maximum Withdraw</label>
            <input type="text" class="form-control" id="exampleInputEmail4" aria-describedby="emailHelp" placeholder="Enter Maximum Withdraw" value="<?= $detail->max_withdraw ?>" name="max_withdraw" required>
        </div>
        <div class="form-group col-6">
            <label for="exampleInputEmail5">Withdraw Charges <span class="text-danger"><b>( in % )</b></span></label>
            <input type="text" class="form-control" id="exampleInputEmail5" aria-describedby="emailHelp" placeholder="Enter Maximum Withdraw" value="<?= $detail->with_charges ?>" name="with_charges" required>
        </div>
        <div class="form-group col-6">
            <label for="exampleInputEmail6">Level Income Seperated by comma. <span class="text-danger"><b>( in % ) example [ level_1,level_2,level3 ]</b></span></label>
            <input type="text" class="form-control" id="exampleInputEmail6" aria-describedby="emailHelp" placeholder="Enter Level Income" value="<?= $detail->level_inc ?>" name="level_inc" required>
        </div>
        <div class="form-group col-6">
          <button type="submit" class="btn btn-primary">Update</button>
        </div>
      </div>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer2');?>
