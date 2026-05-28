<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title><?= $title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 20px;
            color: white;
            margin-bottom: 20px;
        }
        .stats-card h1 { font-size: 36px; font-weight: 700; margin: 0; }
        .stats-card p { margin: 0; opacity: 0.9; }
        .custom-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .custom-card .card-header {
            background: white;
            border-bottom: 1px solid #eef2f6;
            padding: 15px 20px;
            font-weight: 600;
            font-size: 16px;
        }
        .custom-card .card-header i { margin-right: 10px; color: #667eea; }
        .table th { font-weight: 600; font-size: 13px; background: #f8f9fa; }
        .badge-success { background: #28a745; color: white; padding: 5px 12px; border-radius: 20px; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h2 class="mt-4 mb-4">
                <i class="fas fa-chart-line text-success"></i> 
                Admin Commission Report
            </h2>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <div class="col-md-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <p>Total Admin Commission</p>
                <h1>₹ <?= number_format($total_admin_commission, 2) ?></h1>
                <p>Total Rounds: <?= $total_rounds ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #17a2b8, #0dcaf0);">
                <p>Average per Round</p>
                <h1>₹ <?= number_format($avg_commission, 2) ?></h1>
                <p>Per Period Average</p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stats-card" style="background: linear-gradient(135deg, #6f42c1, #8b5cf6);">
                <p>Total Bet Amount</p>
                <h1>₹ <?= number_format($total_bet_amount, 2) ?></h1>
                <p>Across All Rounds</p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="custom-card">
        <div class="card-header">
            <i class="fas fa-filter"></i> Filter Reports
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="<?= $from_date ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="<?= $to_date ?? '' ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Game</label>
                    <select name="game_id" class="form-select">
    <option value="all">All Games</option>

    <?php
    $games = $this->db->get('tbl_games')->result();

    foreach($games as $game):
    ?>
        <option value="<?= $game->id ?>"
            <?= ($game_id ?? '') == $game->id ? 'selected' : '' ?>>
            <?= $game->name ?>
        </option>
    <?php endforeach; ?>
</select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="<?= base_url('backend/admin/export_admin_commission?' . $_SERVER['QUERY_STRING']) ?>" class="btn btn-success">
                        <i class="fas fa-download"></i> Export
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Commission Table -->
    <div class="custom-card">
        <div class="card-header">
            <i class="fas fa-table"></i> Admin Commission History (Period-wise)
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="commissionTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Period ID</th>
                            <th>Game</th>
                            <th>Total Bet Amount</th>
                            <th>Admin Commission (20%)</th>
                            <th>Winning Number</th>
                            <th>Date & Time</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($commissions)): ?>
                            <?php foreach($commissions as $comm): ?>
                            <tr>
                                <td><?= $comm->id ?></td>
                                <td><span class="badge bg-primary"><?= $comm->period_id ?></span></td>
                                <td><?= $comm->game_name ?? 'Lucky36' ?></td>
                                <td>₹ <?= number_format($comm->total_bet_amount, 2) ?></td>
                                <td class="text-success fw-bold">₹ <?= number_format($comm->admin_commission, 2) ?> (<small>20%</small>)</td>
                                <td><span class="badge bg-info"><?= $comm->winning_number ?></span></td>
                                <td><?= date('d-m-Y H:i:s', strtotime($comm->created_at)) ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetails(<?= $comm->period_id ?>, <?= $comm->game_id ?>)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <i class="fas fa-info-circle"></i> No admin commission records found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Total:</th>
                            <th>₹ <?= number_format($total_bet_amount, 2) ?></th>
                            <th>₹ <?= number_format($total_admin_commission, 2) ?></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function() {
    $('#commissionTable').DataTable({
        "order": [[0, "desc"]],
        "pageLength": 25,
        "language": {
            "search": "🔍 Search:",
            "lengthMenu": "Show _MENU_ entries",
            "info": "Showing _START_ to _END_ of _TOTAL_ entries",
            "emptyTable": "No admin commission records found"
        }
    });
});

function viewDetails(period_id, game_id) {
    window.location.href = "<?= base_url('backend/admin/admin_commission_details') ?>?period_id=" + period_id + "&game_id=" + game_id;
}
</script>
</body>
</html>