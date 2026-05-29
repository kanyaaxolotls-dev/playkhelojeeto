<?php $this->load->view('admin/header'); ?>
<div class="content-wrapper">
    <section class="content-header">
        <h1>Create Dealer <small>Add a new dealer account</small></h1>
    </section>

    <section class="content">
        <?php echo $this->session->flashdata('site_flash'); ?>
        <div class="box box-primary">
            <div class="box-body">
                <form action="<?php echo site_url('backend/dealers/create'); ?>" method="post">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" class="form-control" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="form-group">
                        <label>Distributor</label>
                        <select name="distributor_id" class="form-control" required>
                            <option value="">Select Distributor</option>
                            <?php foreach ($distributors as $distributor): ?>
                                <option value="<?php echo $distributor->id; ?>"><?php echo htmlspecialchars($distributor->name . ' (' . $distributor->phone . ')'); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Commission Rate (%)</label>
                        <input type="number" step="0.01" class="form-control" name="commission_rate" value="2.00" required>
                    </div>
                    <div class="form-group">
                        <label>Dealer Role</label>
                        <select name="role_id" class="form-control" required>
                            <?php foreach ($dealer_roles as $r): ?>
                            <option value="<?= (int)$r->id ?>" <?= ($r->slug ?? '') === 'dealer-full' ? 'selected' : '' ?>><?= html_escape($r->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Create custom dealer roles under Manage Roles (Panel: Dealer)</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Create Dealer</button>
                    <a href="<?php echo site_url('backend/dealers'); ?>" class="btn btn-default">Cancel</a>
                </form>
            </div>
        </div>
    </section>
</div>
<?php $this->load->view('admin/footer'); ?>