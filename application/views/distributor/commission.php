<!-- Stats Cards Row -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($total_commission ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Total Commission</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-chart-line fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($today_commission ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">Today's Commission</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-calendar-day fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="stat-number" style="font-size: 32px; font-weight: 700;">₹ <?= number_format($monthly_commission ?? 0, 2) ?></div>
                    <div class="stat-label" style="color: rgba(255,255,255,0.8);">This Month</div>
                </div>
                <div class="stat-icon">
                    <i class="fas fa-calendar-alt fa-3x" style="opacity: 0.5;"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Commission Rate Card -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="custom-card">
            <div class="card-header">
                <i class="fas fa-percent"></i> My Commission Rate
            </div>
            <div class="card-body text-center py-4">
                <h2 class="text-primary mb-2" style="font-size: 48px; font-weight: 700;"><?= $distributor->commission_rate ?? 0 ?>%</h2>
                <small class="text-muted">Commission earned on every user bet under your dealers</small>
            </div>
        </div>
    </div>
</div>

<!-- Commission History Table -->
<div class="custom-card">
    <div class="card-header">
        <i class="fas fa-history"></i> Commission History
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover" id="commissionTable" style="width: 100%;">
                <thead style="background: #f8f9fa;">
                    <tr>
                        <th>Date & Time</th>
                        <th>User Name</th>
                        <th>Dealer Name</th>
                        <th>Amount (₹)</th>
                        <th>Rate</th>
                        <th>Game Type</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($commissions) && count($commissions) > 0): ?>
                        <?php foreach($commissions as $comm): ?>
                        <tr>
                            <td><?= date('d-m-Y H:i:s', strtotime($comm->created_at)) ?></td>
                            <td><strong><?= $comm->user_name ?? 'N/A' ?></strong></td>
                            <td><?= $comm->dealer_name ?? 'N/A' ?></td>
                            <td class="text-success fw-bold">₹ <?= number_format($comm->amount, 2) ?></td>
                            <td><span class="badge" style="background: #17a2b8; color: white;"><?= $comm->rate ?>%</span></td>
                            <td><?= ucfirst($comm->game_name ?? 'N/A') ?></td>
                            <td>
                                <span class="badge" style="background: #28a745; color: white;">Completed</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-info-circle text-muted"></i> 
                                <span class="text-muted">No commission records found</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .stat-card {
        border-radius: 15px;
        padding: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
    
    .custom-card {
        background: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    
    .custom-card .card-header {
        background: white;
        border-bottom: 1px solid #eef2f6;
        padding: 15px 20px;
        font-weight: 600;
        font-size: 16px;
    }
    
    .custom-card .card-header i {
        margin-right: 10px;
        color: #667eea;
    }
    
    .table th {
        font-weight: 600;
        font-size: 13px;
        color: #495057;
        padding: 12px 15px;
    }
    
    .table td {
        padding: 12px 15px;
        vertical-align: middle;
        font-size: 13px;
    }
    
    .table tbody tr:hover {
        background: #f8f9fa;
    }
    
    .badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 11px;
    }
</style>

<script>
$(document).ready(function() {
    if($.fn.DataTable && $('#commissionTable tbody tr').length > 0) {
        var hasData = false;
        $('#commissionTable tbody tr').each(function() {
            if($(this).find('td').length > 0 && $(this).find('td').attr('colspan') != 7) {
                hasData = true;
            }
        });
        
        if(hasData) {
            $('#commissionTable').DataTable({
                "order": [[0, "desc"]],
                "pageLength": 25,
                "language": {
                    "search": "🔍 Search:",
                    "lengthMenu": "Show _MENU_ entries",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "emptyTable": "No commission records found"
                }
            });
        }
    }
});
</script>