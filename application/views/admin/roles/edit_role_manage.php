<?php $this->load->view('admin/header'); ?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
        <?= $title ?> — <?= html_escape($role->name) ?>
        <span class="tools pull-right">
          <a class="btn btn-sm btn-secondary" href="<?= base_url('backend/roles/manage_role') ?>">Back</a>
        </span>
      </header>
      <div class="card-body">
        <?php echo $this->session->flashdata('site_flash') ?>
        <form action="<?= base_url('backend/roles/update_role') ?>" method="post">
          <input type="hidden" name="id" value="<?= (int) $role->id ?>">
          <?php
            $is_edit = true;
            $this->load->view('admin/roles/role_form_fields');
          ?>
          <button type="submit" class="btn btn-primary">Update Role</button>
        </form>
      </div>
    </section>
  </div>
</div>

<?php $this->load->view('admin/footer'); ?>
