<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$selected_tasks = isset($selected_tasks) ? $selected_tasks : [];
$selected_permissions = isset($selected_permissions) ? $selected_permissions : [];
$role_panel = isset($role_panel) ? $role_panel : 'admin';
$is_edit = !empty($is_edit);
?>
<div class="form-group">
    <label for="name">Role Name</label>
    <input type="text" class="form-control" name="name" value="<?= isset($role_name) ? html_escape($role_name) : '' ?>" required <?= $is_edit ? '' : '' ?>>
</div>
<?php if (!$is_edit) { ?>
<div class="form-group">
    <label for="panel">Panel Type</label>
    <select class="form-control" name="panel" id="panel" required>
        <option value="admin" <?= $role_panel === 'admin' ? 'selected' : '' ?>>Admin / Subadmin</option>
        <option value="distributor" <?= $role_panel === 'distributor' ? 'selected' : '' ?>>Distributor</option>
        <option value="dealer" <?= $role_panel === 'dealer' ? 'selected' : '' ?>>Dealer</option>
    </select>
</div>
<?php } else { ?>
<input type="hidden" name="panel" id="panel" value="<?= html_escape($role_panel) ?>">
<p><strong>Panel:</strong> <span class="badge badge-info"><?= html_escape($role_panel) ?></span></p>
<?php } ?>

<div class="form-group">
    <label class="d-block font-weight-bold">Action Permissions</label>
    <small class="text-muted d-block mb-2">Only checked permissions are allowed. Nothing is enabled by default.</small>
    <div class="row" id="permissions-list">
        <?php foreach ($all_permissions as $perm) {
            $panels = array_map('trim', explode(',', $perm->panels));
            $checked = in_array((int)$perm->id, $selected_permissions, true) ? 'checked' : '';
        ?>
        <div class="col-md-6 perm-row mb-2" data-panels="<?= html_escape($perm->panels) ?>">
            <label class="d-block border rounded p-2">
                <input type="checkbox" name="permissions[]" value="<?= (int)$perm->id ?>" <?= $checked ?>>
                <?= html_escape($perm->name) ?>
            </label>
        </div>
        <?php } ?>
    </div>
</div>

<div class="form-group">
    <label class="d-block font-weight-bold">Sidebar Menus</label>
    <small class="text-muted d-block mb-2">Menus appear in sidebar only when ticked here.</small>
    <table class="table table-bordered table-sm">
        <thead>
            <tr><th>#</th><th>Menu</th><th>Child Menu</th></tr>
        </thead>
        <tbody id="role-menu-tbody">
        <?php
        $n = 1;
        foreach ($menu_parents as $menu) {
            $panel_attr = isset($menu->panel) ? $menu->panel : 'admin';
            $children = isset($menu_children[$menu->id]) ? $menu_children[$menu->id] : [];
        ?>
        <tr class="menu-row" data-panel="<?= html_escape($panel_attr) ?>">
            <td><?= $n++; ?></td>
            <td>
                <?php if ($menu->url !== '#') {
                    $ck = in_array((int)$menu->id, $selected_tasks, true) ? 'checked' : '';
                ?>
                <label><input type="checkbox" name="role[]" value="<?= (int)$menu->id ?>" <?= $ck ?>> <?= html_escape($menu->name) ?></label>
                <?php } else { ?>
                <strong><?= html_escape($menu->name) ?></strong>
                <?php } ?>
            </td>
            <td>
                <?php foreach ($children as $menu2) {
                    $ck2 = in_array((int)$menu2->id, $selected_tasks, true) ? 'checked' : '';
                ?>
                <label class="d-block"><input type="checkbox" name="role[]" value="<?= (int)$menu2->id ?>" <?= $ck2 ?>> <?= html_escape($menu2->name) ?></label>
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
(function() {
    function filterByPanel() {
        var panel = document.getElementById('panel').value;
        document.querySelectorAll('.menu-row').forEach(function(row) {
            var p = row.getAttribute('data-panel');
            row.style.display = (p === panel) ? '' : 'none';
        });
        document.querySelectorAll('.perm-row').forEach(function(row) {
            var panels = (row.getAttribute('data-panels') || '').split(',').map(function(s) { return s.trim(); });
            row.style.display = panels.indexOf(panel) >= 0 ? '' : 'none';
        });
    }
    var el = document.getElementById('panel');
    if (el) {
        el.addEventListener('change', filterByPanel);
        filterByPanel();
    }
})();
</script>
