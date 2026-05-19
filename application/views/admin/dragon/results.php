<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          
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
                <th>Game Period</th>
                <th>Dragon Card</th>
                <th>Tiger Card</th>
                <th>Winner</th>
                <th>Datetime</th>
              </tr>
            </thead>
            <tbody>
              <?php 
                $i = 1; foreach ($data as $tr) { 
                  if($tr->win_card == 'Tiger'){
                    $img = 'https://cdn-icons-png.flaticon.com/128/12658/12658833.png';
                  }
                  elseif($tr->win_card == 'Dragon'){
                    $img = 'https://cdn-icons-png.flaticon.com/128/1016/1016741.png';
                  }
                  else{
                    $img = 'https://cdn-icons-png.flaticon.com/128/10480/10480159.png';
                  }
              ?>
                <tr class="gradeX">
                    <td><?= $i++; ?></td>
                    <td><?= $tr->period_id; ?></td>
                    <td class="text-center">
                        <img src="<?= base_url('Assets/cards/'.$tr->dragon_card_id.'.png'); ?>" height="80px">
                    </td>
                    <td class="text-center">
                        <img src="<?= base_url('Assets/cards/'.$tr->tiger_card_id.'.png'); ?>" height="80px">
                    </td>
                    <td class="text-center">
                        <img src="<?= $img ?>" height="60px">
                    </td>
                    <td><?= $tr->date; ?></td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer');?>
