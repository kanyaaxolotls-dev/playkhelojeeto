<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Get menus for admin panel
$menus = rbac_menus('admin');
$parents = $menus['parents'];
$children = $menus['children'];

// Get current URI segments
$seg1 = $this->uri->segment(1); // 'backend'
$seg2 = $this->uri->segment(2); // 'distributors', 'dealers', 'users', etc.
$seg3 = $this->uri->segment(3); // 'create', 'edit', 'view', etc.
?>

<?php foreach ($parents as $menu): 
    $menu_children = isset($children[$menu->id]) ? $children[$menu->id] : [];
    
    // Check if this menu or any child is active
    $is_active = false;
    $menu_url = trim($menu->url, '/');
    $menu_parts = explode('/', $menu_url);
    
    if (empty($menu_children)) {
        // Single menu item
        if ($menu_url !== '#') {
            $is_active = ($seg1 == ($menu_parts[0] ?? '') && $seg2 == ($menu_parts[1] ?? ''));
        }
    } else {
        // Parent menu - check if any child is active
        foreach ($menu_children as $child) {
            $child_url = trim($child->url, '/');
            $child_parts = explode('/', $child_url);
            if ($seg1 == ($child_parts[0] ?? '') && $seg2 == ($child_parts[1] ?? '')) {
                $is_active = true;
                break;
            }
        }
    }
?>

<?php if (empty($menu_children) && $menu_url !== '#'): ?>
    <!-- Single Menu Item -->
    <li class="<?= $is_active ? 'active' : '' ?>">
        <a href="<?= site_url($menu->url) ?>">
            <i class="<?= $menu->img ?: 'fa fa-circle-o' ?>"></i>
            <span><?= htmlspecialchars($menu->name) ?></span>
        </a>
    </li>

<?php elseif (!empty($menu_children)): ?>
    <!-- Parent Menu with Children -->
    <li class="sub-menu <?= $is_active ? 'active' : '' ?>">
        <a href="javascript:void(0);">
            <i class="<?= $menu->img ?: 'fa fa-folder' ?>"></i>
            <span><?= htmlspecialchars($menu->name) ?></span>
            <span class="menu-arrow"></span>
        </a>
        <ul class="sub">
            <?php foreach ($menu_children as $child): 
                $child_url = trim($child->url, '/');
                $child_parts = explode('/', $child_url);
                $is_child_active = ($seg1 == ($child_parts[0] ?? '') && $seg2 == ($child_parts[1] ?? ''));
            ?>
            <li class="<?= $is_child_active ? 'active' : '' ?>">
                <a href="<?= site_url($child->url) ?>">
                    <i class="<?= $child->img ?: 'fa fa-circle-o' ?>"></i>
                    <?= htmlspecialchars($child->name) ?>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?php endif; ?>

<?php endforeach; ?>