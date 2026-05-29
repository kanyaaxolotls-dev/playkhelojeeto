<?php $this->load->view('admin/header');?>

<div class="row">
  <div class="col-sm-12">
    <section class="card">
      <header class="card-header">
      <?= $title ?>
        <span class="tools pull-right">
          <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#exampleModal">Add New Role</button>
        </span>
      </header>
      <div class="card-body">
      <?php echo validation_errors('<div class="alert alert-danger">', '</div>') ?>
      <?php echo $this->session->flashdata('site_flash') ?>
        <div class="adv-table">
          <table class="display table table-bordered" id="hidden-table-info">
            <thead>
              <tr>
                <th>Sr No</th>
                <th>Name</th>
                <th>Panel</th>
                <th>Menus</th>
                <th>Permissions</th>
                <th>Added At</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1; foreach ($roles as $role) {
                $perm_count = count($this->rbac_model->get_permission_ids_for_role($role->id));
              ?>
              <tr class="gradeX">
                <td><?= $i++; ?></td>
                <td><?= html_escape($role->name); ?></td>
                <td><span class="badge badge-info"><?= html_escape($role->panel ?? 'admin'); ?></span></td>
                <td><small><?= html_escape($role->tasks ?: '—'); ?></small></td>
                <td><span class="badge badge-secondary"><?= (int)$perm_count ?> enabled</span></td>
                <td><?= $role->Date; ?></td>
                <td>
                  <a class="btn btn-sm btn-warning" href="<?= base_url('backend/roles/edit_role/'.$role->id) ?>">Edit</a>
                  <?php if (empty($role->is_system)) { ?>
                  <a class="btn btn-sm btn-danger" href="<?= base_url('backend/roles/delete_role/'.$role->id) ?>">Delete</a>
                  <?php } ?>
                </td>
              </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </section>
  </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Role</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <form action="<?= base_url('backend/roles/create_rol') ?>" method="post">
      <div class="modal-body">
        <?php
          $is_edit = false;
          $this->load->view('admin/roles/role_form_fields');
        ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save Role</button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php $this->load->view('admin/footer');?>
