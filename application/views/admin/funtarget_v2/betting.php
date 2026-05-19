<style>
    .bet-box {
        border: 2px solid #ddd;
        border-radius: 10px;
        padding: 25px;
        text-align: center;
        font-weight: bold;
        background-color: #f8f9fa;
        transition: 0.3s;
        font-size: 20px;
        min-width: 100%;
    }

    .bet-box .number {
        font-size: 26px;
        margin-bottom: 5px;
    }

    .highlight {
        background-color: #ffe58f !important;
        border-color: #ffc107;
        animation: blink 1s infinite;
    }

    @keyframes blink {
        0%, 100% { box-shadow: 0 0 10px #ffc107; }
        50% { box-shadow: 0 0 20px #ff9800; }
    }
</style>

<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<?php $this->load->view('admin/header'); ?>

<!-- PASS SERVER TIME TO JAVASCRIPT -->
<script>
    // Server timestamp from PHP at the moment page loads
    const serverTimeAtLoad = <?= time() ?>;

    // Local JS timestamp at page load
    const localTimeAtLoad = Math.floor(Date.now() / 1000);

    // Offset = server time - device time
    let serverOffset = serverTimeAtLoad - localTimeAtLoad;
</script>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
            <button id="manualBtn" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Set Manual Result</button>
        </span> 
      </header>

      <div class="card-body">

        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-danger">
                <?= $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <?= $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>

        <h2>
            Period ID: <span id="period_id">Loading...</span>
            <span id="timer" style="margin-left:20px; font-size:22px; color:#dc3545; font-weight:bold;">60s</span>
        </h2>

        <div class="row justify-content-center" id="liveBetsContainer"></div>

        <div class="adv-table table-responsive">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <th>Sr No</th>
                <th>Userid</th>
                <th>Name</th>
                <th>Amount</th>
                <th>Bet on</th>
                <th>Game Period</th>
                <th>Status</th>
                <th>Date</th>
              </tr>
            </thead>

            <tbody>
              <?php 
                $i = 1; 
                foreach ($data as $tr) { 
                  if($tr->status == 'Won') $clr = 'success';
                  elseif($tr->status == 'Loss' or $tr->status == 'cancelled') $clr = 'danger';
                  else $clr = 'warning';
              ?>

              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td><?= $tr->userid; ?></td>
                <td><?= $this->db_model->select('name', 'tbl_users', array('id' => $tr->userid)); ?></td>
                <td class="text-<?= $clr ?>">₹ <?= $tr->amount; ?></td>
                <td class="text-primary"><?= $tr->bet; ?></td>
                <td><?= $tr->period_id; ?></td>
                <td class="text-<?= $clr ?> text-center">
                  <?php if($tr->status == 'Waiting'){ ?>
                    <div class="spinner-border" role="status">
                      <span class="sr-only">Loading...</span>
                    </div>
                  <?php } else { echo $tr->status; } ?>
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


<!-- Manual Result Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Set Manual Result</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>

      <form action="<?= base_url('backend/Funtarget_v2/update_secret') ?>" method="post">
      <div class="modal-body">

        <div class="form-group">
            <label>Enter Number From 0-9</label>
            <input type="number" class="form-control" required name="secret" placeholder="Enter Number From 0-9">
            <input type="hidden" name="period_id" value="<?= $this->db_model->select('period_id', 'tbl_games', array('id' => 7)) ?>">
        </div>

        <div class="form-check form-switch mt-3">
            <input class="form-check-input" type="checkbox" id="isJoker" name="is_joker" value="1">
            <label class="form-check-label" for="isJoker">Is Joker</label>
        </div>
        <input type="hidden" name="is_joker" value="0">

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save changes</button>
      </div>

      </form>
    </div>
  </div>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- FETCH PERIOD ID (unchanged) -->
<script>
function fetchPeriodId() {
    $.ajax({
        url: "<?= base_url('api/Funtarget_v2/period_id') ?>",
        method: "GET",
        dataType: "json",
        success: function(response) {
            if(response.status === 'success') {
                $('#period_id').text(response.period_id);
            }
        }
    });
}
fetchPeriodId();
setInterval(fetchPeriodId, 2000);
</script>

<!-- LIVE BET FETCH (unchanged) -->
<script>
function fetchLiveBets() {
    $.ajax({
        url: '<?= base_url("cron/live_bet_totals") ?>',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.status === 'success') {
                let html = '';
                for (let i = 0; i <= 9; i++) {
                    const amount = response.bets[i] || 0;
                    const highlightClass = (parseInt(response.highlight) === i) ? 'highlight' : '';
                    html += `
                        <div class="col-6 col-md-4 col-lg-2 mb-3 d-flex justify-content-center">
                            <div class="bet-box ${highlightClass}">
                                <div class="number">${i}</div>
                                ₹${amount}
                            </div>
                        </div>
                    `;
                }
                $('#liveBetsContainer').html(html);
            }
        }
    });
}
fetchLiveBets();
setInterval(fetchLiveBets, 2000);
</script>

<!-- REAL SERVER SYNC TIMER (NO API) -->
<script>
let locked = false;

function getRemainingSeconds() {
    const nowLocal = Math.floor(Date.now() / 1000);
    const nowServer = nowLocal + serverOffset;

    return 60 - (nowServer % 60);
}

function startRealtimeTimer() {
    setInterval(() => {

        const remaining = getRemainingSeconds();

        $("#timer").text(remaining + "s");

        if (remaining <= 5) {
            if (!locked) {
                locked = true;

                $("#manualBtn").prop("disabled", true);
                $("#exampleModal button[type='submit']").prop("disabled", true);

                toastr.error("Result in progress… You don't have right to add!", "Access Denied");
            }
        } else {
            locked = false;
            $("#manualBtn").prop("disabled", false);
            $("#exampleModal button[type='submit']").prop("disabled", false);
        }

    }, 1000);
}

startRealtimeTimer();
</script>
<style>
    /* Force the footer to stay at the bottom of the page flow */
    footer, .footer, #footer {
        position: static !important; 
        display: block !important;
        clear: both !important;
        margin-top: 50px !important;
        text-align: center;
    }
</style>

<?php $this->load->view('admin/footer'); ?>
