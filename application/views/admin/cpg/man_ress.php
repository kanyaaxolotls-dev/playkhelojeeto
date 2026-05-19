<?php $this->load->view('admin/header');?>
<style>
    .green {
        background-color: #6edf6e;
    }
    .gold {
        background-color: gold;
    }
    .red {
        background-color: #ff334a;
    }
    .win_clr {
      animation: blink 2s infinite;
    }

    @keyframes blink {
        0% {
            background-color: gold;
        }
        25% {
            background-color: skyblue;
        }
        50% {
            background-color: green;
        }
        75% {
            background-color: red;
        }
        100% {
            background-color: pink;
        }
    }
</style>
<?php 
        $color      = $result['color'];
        $result_num = $result['result_num'];
        $green      = $result['green'];
        $gold       = $result['gold'];
        $red        = $result['red'];
        $zero       = $result['zero'];
        $one        = $result['one'];
        $two        = $result['two'];
        $three      = $result['three'];
        $four       = $result['four'];
        $five       = $result['five'];
        $six        = $result['six'];
        $seven      = $result['seven'];
        $eight      = $result['eight'];
        $nine       = $result['nine'];
?>
<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?>
        <span class="tools pull-right">
            <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Set Manual Result</button>
        </span>
      </header>
      <div class="card-body">
        <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
        <?php echo $this->session->flashdata('site_flash') ?>
            <div class="" style="display: flex; gap: 16px;">
                <div class="card <?php if($color == 'green'){ echo 'win_clr';}else{ echo 'green'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark p-2">Green</h1>
                    <h2 class="h2 text-dark p-2">₹ <?php echo $green ?></h2>
                </div>
                <div class="card <?php if($color == 'violet'){ echo 'win_clr';}else{ echo 'gold'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark p-2">Gold</h1>
                    <h2 class="h2 text-dark p-2">₹ <?php echo $gold ?></h2>
                </div>
                <div class="card <?php if($color == 'red'){ echo 'win_clr';}else{ echo 'red'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark p-2">Red</h1>
                    <h2 class="h2 text-dark p-2">₹ <?php echo $red;?></h2>
                </div>
            </div>
            <div class="" style="display: flex; gap: 16px;">
                <div class="p-2 card <?php if($result_num == '0'){ echo 'win_clr';}else{ echo 'gold'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">0</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $zero ?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '1'){ echo 'win_clr';}else{ echo 'green'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">1</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $one ?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '2'){ echo 'win_clr';}else{ echo 'red'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">2</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $two;?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '3'){ echo 'win_clr';}else{ echo 'green'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">3</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $three;?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '4'){ echo 'win_clr';}else{ echo 'red'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">4</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $four;?></h2>
                </div>
            </div>
            <div class="" style="display: flex; gap: 16px;">
                <div class="p-2 card <?php if($result_num == '5'){ echo 'win_clr';}else{ echo 'gold'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">5</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $five ?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '6'){ echo 'win_clr';}else{ echo 'red'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">6</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $six ?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '7'){ echo 'win_clr';}else{ echo 'green'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">7</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $seven;?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '8'){ echo 'win_clr';}else{ echo 'red'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">8</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $eight;?></h2>
                </div>
                <div class="p-2 card <?php if($result_num == '9'){ echo 'win_clr';}else{ echo 'green'; } ?>" style="flex: 1;">
                    <h1 class="h1 text-dark">9</h1>
                    <h2 class="h2 text-dark">₹ <?php echo $nine;?></h2>
                </div>
            </div>
            
            <div class="adv-table mt-2 table-responsive">
                <table class="display table table-bordered" id="hidden-table-info">
                    <tr>
                        <th>Numers</th>
                        <th>Total Amount</th>
                        <th>Winning Amount</th>
                    </tr>
                    <tr>
                        <td>0</td>
                        <td><?php echo $zero + $gold  ?></td>
                        <td><?php echo $zero*9 + $gold*4.5  ?></td>
                    </tr>
                    <tr>
                        <td>1</td>
                        <td><?php echo $one + $green  ?></td>
                        <td><?php echo $one*9 + $green*2  ?></td>
                    </tr>
                    <tr>
                        <td>2</td>
                        <td><?php echo $two + $red  ?></td>
                        <td><?php echo $two*9 + $red*2  ?></td>
                    </tr>
                    <tr>
                        <td>3</td>
                        <td><?php echo $three + $green  ?></td>
                        <td><?php echo $three*9 + $green*2  ?></td>
                    </tr>
                    <tr>
                        <td>4</td>
                        <td><?php echo $four + $red  ?></td>
                        <td><?php echo $four*9 + $red*2  ?></td>
                    </tr>
                    <tr>
                        <td>5</td>
                        <td><?php echo $five + $gold  ?></td>
                        <td><?php echo $five*9 + $gold*4.5  ?></td>
                    </tr>
                    <tr>
                        <td>6</td>
                        <td><?php echo $six + $red  ?></td>
                        <td><?php echo $six*9 + $red*2  ?></td>
                    </tr>
                    <tr>
                        <td>7</td>
                        <td><?php echo $seven + $green  ?></td>
                        <td><?php echo $seven*9 + $green*2  ?></td>
                    </tr>
                    <tr>
                        <td>8</td>
                        <td><?php echo $eight + $red  ?></td>
                        <td><?php echo $eight*9 + $red*2  ?></td>
                    </tr>
                    <tr>
                        <td>9</td>
                        <td><?php echo $nine + $green   ?></td>
                        <td><?php echo $nine*9 + $green*2  ?></td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <?php
                            $tt   = $result['all_sum'] -  $result['total22'];
                            if($tt >= 0){
                                $cllr = 'green';
                            }
                            else{
                                $cllr = 'red'; 
                            }
                        ?>
                        <td style="background:<?php echo $cllr ?>;color:white;font-size:1.3em">Profit : ₹ <?php echo $tt   ?></td>
                    </tr>
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
        <h5 class="modal-title" id="exampleModalLabel">Set Manual Result</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="<?= base_url('backend/cpg/set_manual') ?>" method="post">
      <div class="modal-body">
        <div class="form-group">
            <label for="exampleInputEmail1">Insert Number From 0-9</label>
            <input type="number" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Enter Number" name="number" required  min="0" max="9" pattern="[0-9]" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
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
