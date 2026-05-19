<?php $this->load->view('admin/header'); ?>

<div class="main_content_iner mb-5">
    <div class="container-fluid p-0 sm_padding_15px">
        <div class="col-lg-12">
            <div class="white_card card_height_100 mb_30">
                <div class="white_card_header">
                    <div class="box_header m-0">
                        <div class="main-title">
                            <h4 class="mb-2"><?= $title ?></h4>
                        </div>
                    </div>
                </div>
                <div class="white_card_body">
                    <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
                    <?php echo $this->session->flashdata('site_flash') ?>
                    <div class="card-body">
                        <form action="<?php echo base_url('backend/Rewards/give_reward') ?>" method='post'>
                            <div class="tab-pane active" id="personal">
                                <div class="row gy-4">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="amount">Amount :</label>
                                            <input type="text" class="form-control" id="amount"  placeholder="Enter Reward Amount" name="amount" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="inc_name">Reward Name :</label>
                                            <input type="text" class="form-control" id="inc_name"  placeholder="Enter Reward Name" name="inc_name" value="" required>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-label" for="phone">Enter User's Phone Number :</label>
                                            <input type="text" class="form-control" id="phone"  placeholder="Enter User's Phone Number" name="phone" value="" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 mt-4">
                                <ul class="align-center flex-wrap flex-sm-nowrap gx-4 gy-2">
                                    <li>
                                        <input type='submit' value='Credit Reward' class="btn btn-primary">
                                    </li>
                                </ul>
                            </div>
                        </form>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('admin/footer'); ?>
