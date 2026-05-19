<?php $this->load->view('admin/header'); ?>
<style>
@media (max-width: 767px) {
    #hidden-table-info {
        overflow-x: auto;
        display: block;
    }
}
</style>
<div class="main_content_iner mb-5">
    <div class="container-fluid p-0 sm_padding_15px">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_header">
                    <div class="box_header m-0">
                        <div class="main-title">
                            <!-- <h3 class="m-0"><?= $title ?></h3> -->
                        </div>
                    </div>
                </div>
                <div class="main-title">
                                        <h4 class="mb-3"><?= $title2 ?></h4>
                                    </div>
                <div class="white_card_body">
                    <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
                    <?php echo $this->session->flashdata('site_flash') ?>
                    <div class="card-body">
                        <div class="white_card card_height_100 mb_30">
                            <div class="white_card_header">
                            </div>
                            <div class="white_card_body">
                                <div class="adv-table table-responsive">
                                    <table class="display table table-bordered" id="hidden-table-info" style="overflow-x: auto;">
                                        <thead>
                                            <tr>
                                                <th>SN</th>
                                                <th>Userid</th>
                                                <th>Phone</th>
                                                <th>Amount</th>
                                                <th>Type</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 1; foreach($data as $e){ ?>
                                                <tr class="gradeX">
                                                    <td><?= $i++; ?></td>
                                                    <td><?= $e->userid; ?></td>
                                                    <td>+91 <?= $this->db_model->select('phone', 'tbl_users', array('id' => $e->userid)); ?></td>
                                                    <td class="text-success">₹ <?= $e->amount ?></td>
                                                    <td class="text-danger"><?= $e->type ?></td>
                                                    <td><?= $e->date ?></td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('admin/footer'); ?>
