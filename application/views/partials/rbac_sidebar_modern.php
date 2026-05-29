<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$panel = isset($panel) ? $panel : 'distributor';
$menus = rbac_menus($panel);
$current = rbac_route_key();

foreach ($menus['parents'] as $menu) {
    $key = strtolower(trim($menu->route_key ?: $menu->url, '/'));
    $active = ($current === $key || strpos($current, $key . '/') === 0) ? 'active' : '';
    if ($menu->url === '#' || $key === '') {
        continue;
    }
?>
<li class="nav-item">
    <a class="nav-link <?= $active ?>" href="<?= site_url($menu->url) ?>">
        <i class="<?= $menu->img ?>"></i> <?= html_escape($menu->name) ?>
    </a>
</li>
<?php } ?>
