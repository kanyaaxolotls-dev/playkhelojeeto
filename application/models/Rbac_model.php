<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Rbac_model extends CI_Model {

    public function ensure_schema()
    {
        if (!$this->db->table_exists('tbl_roles')) {
            return false;
        }

        if (!$this->db->field_exists('slug', 'tbl_roles')) {
            $this->db->query("ALTER TABLE `tbl_roles` ADD COLUMN `slug` VARCHAR(50) NULL AFTER `name`");
        }
        if (!$this->db->field_exists('panel', 'tbl_roles')) {
            $this->db->query("ALTER TABLE `tbl_roles` ADD COLUMN `panel` ENUM('admin','distributor','dealer') NOT NULL DEFAULT 'admin' AFTER `slug`");
        }
        if (!$this->db->field_exists('dashboard_url', 'tbl_roles')) {
            $this->db->query("ALTER TABLE `tbl_roles` ADD COLUMN `dashboard_url` VARCHAR(255) NULL AFTER `panel`");
        }
        if (!$this->db->field_exists('is_system', 'tbl_roles')) {
            $this->db->query("ALTER TABLE `tbl_roles` ADD COLUMN `is_system` TINYINT(1) NOT NULL DEFAULT 0");
        }

        if (!$this->db->table_exists('tbl_permissions')) {
            $this->db->query("CREATE TABLE `tbl_permissions` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `slug` VARCHAR(80) NOT NULL,
                `name` VARCHAR(120) NOT NULL,
                `panels` VARCHAR(120) NOT NULL DEFAULT 'admin',
                `sort_order` INT NOT NULL DEFAULT 0,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                PRIMARY KEY (`id`),
                UNIQUE KEY `slug` (`slug`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->db->table_exists('tbl_role_permissions')) {
            $this->db->query("CREATE TABLE `tbl_role_permissions` (
                `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `role_id` INT UNSIGNED NOT NULL,
                `permission_id` INT UNSIGNED NOT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `role_permission` (`role_id`, `permission_id`),
                KEY `role_id` (`role_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        }

        if (!$this->db->field_exists('panel', 'tbl_task_manager')) {
            $this->db->query("ALTER TABLE `tbl_task_manager`
                ADD COLUMN `panel` ENUM('admin','distributor','dealer') NOT NULL DEFAULT 'admin' AFTER `url`,
                ADD COLUMN `route_key` VARCHAR(255) NULL AFTER `panel`,
                ADD COLUMN `permission_slug` VARCHAR(80) NULL AFTER `route_key`");
        } elseif (!$this->db->field_exists('permission_slug', 'tbl_task_manager')) {
            $this->db->query("ALTER TABLE `tbl_task_manager` ADD COLUMN `permission_slug` VARCHAR(80) NULL AFTER `route_key`");
        }

        if ($this->db->table_exists('tbl_admin') && !$this->db->field_exists('role_id', 'tbl_admin')) {
            $this->db->query("ALTER TABLE `tbl_admin` ADD COLUMN `role_id` INT UNSIGNED NULL AFTER `role`, ADD INDEX (`role_id`)");
        }
        if ($this->db->table_exists('tbl_distributors') && !$this->db->field_exists('role_id', 'tbl_distributors')) {
            $this->db->query("ALTER TABLE `tbl_distributors` ADD COLUMN `role_id` INT UNSIGNED NULL AFTER `status`, ADD INDEX (`role_id`)");
        }
        if ($this->db->table_exists('tbl_dealers') && !$this->db->field_exists('role_id', 'tbl_dealers')) {
            $this->db->query("ALTER TABLE `tbl_dealers` ADD COLUMN `role_id` INT UNSIGNED NULL AFTER `status`, ADD INDEX (`role_id`)");
        }

        $this->_seed_system_roles();
        $this->_seed_permissions();
        $this->_cleanup_duplicate_menus();
        $this->_seed_panel_menus();
        $this->_backfill_role_ids();

        return true;
    }

    private function _seed_permissions()
    {
        $list = [
            ['slug' => 'create_distributor', 'name' => 'Create Distributor', 'panels' => 'admin', 'sort_order' => 10],
            ['slug' => 'create_dealer', 'name' => 'Create Dealer', 'panels' => 'admin,distributor', 'sort_order' => 20],
            ['slug' => 'create_user', 'name' => 'Create User', 'panels' => 'admin,dealer', 'sort_order' => 30],
            ['slug' => 'edit_user', 'name' => 'Edit User', 'panels' => 'admin,distributor,dealer', 'sort_order' => 40],
            ['slug' => 'delete_user', 'name' => 'Delete User', 'panels' => 'admin,distributor,dealer', 'sort_order' => 50],
            ['slug' => 'wallet_credit', 'name' => 'Wallet Credit', 'panels' => 'admin,distributor,dealer', 'sort_order' => 60],
            ['slug' => 'wallet_debit', 'name' => 'Wallet Debit', 'panels' => 'admin,distributor,dealer', 'sort_order' => 70],
            ['slug' => 'view_transaction_history', 'name' => 'View Transaction History', 'panels' => 'admin,distributor,dealer', 'sort_order' => 80],
            ['slug' => 'view_reports', 'name' => 'View Reports', 'panels' => 'admin,distributor', 'sort_order' => 90],
            ['slug' => 'manage_wallet', 'name' => 'Manage Wallet', 'panels' => 'admin', 'sort_order' => 100],
            ['slug' => 'manage_roles', 'name' => 'Manage Roles', 'panels' => 'admin', 'sort_order' => 110],
        ];

        foreach ($list as $p) {
            $row = $this->db->get_where('tbl_permissions', ['slug' => $p['slug']])->row();
            if ($row) {
                $this->db->where('id', $row->id)->update('tbl_permissions', [
                    'name' => $p['name'],
                    'panels' => $p['panels'],
                    'sort_order' => $p['sort_order'],
                ]);
            } else {
                $this->db->insert('tbl_permissions', array_merge($p, ['status' => 1]));
            }
        }
    }

    private function _seed_system_roles()
    {
        $defaults = [
            ['id' => 1, 'name' => 'Admin', 'slug' => 'admin', 'panel' => 'admin', 'dashboard_url' => 'backend/admin', 'is_system' => 1],
            ['id' => 2, 'name' => 'Distributor Full', 'slug' => 'distributor-full', 'panel' => 'distributor', 'dashboard_url' => 'distributor/dashboard', 'is_system' => 1],
            ['id' => 3, 'name' => 'Dealer Full', 'slug' => 'dealer-full', 'panel' => 'dealer', 'dashboard_url' => 'dealer/dashboard', 'is_system' => 1],
        ];

        foreach ($defaults as $row) {
            $exists = $this->db->get_where('tbl_roles', ['id' => $row['id']])->row();
            if ($exists) {
                $this->db->where('id', $row['id'])->update('tbl_roles', [
                    'slug' => $row['slug'],
                    'panel' => $row['panel'],
                    'dashboard_url' => $row['dashboard_url'],
                    'is_system' => $row['is_system'],
                ]);
            } else {
                $this->db->insert('tbl_roles', array_merge($row, [
                    'tasks' => '',
                    'editable' => '',
                    'deletable' => '',
                    'status' => 1,
                    'Date' => date('Y-m-d H:i:s'),
                ]));
            }
        }
    }

    private function _cleanup_duplicate_menus()
    {
        if (!$this->db->field_exists('route_key', 'tbl_task_manager')) {
            return;
        }

        foreach (['distributor', 'dealer', 'admin'] as $panel) {
            $this->db->select('route_key, MIN(id) AS keep_id', false);
            $this->db->where('panel', $panel);
            $this->db->where('status', 1);
            $this->db->where('route_key IS NOT NULL', null, false);
            $this->db->where('route_key !=', '');
            $this->db->group_by('route_key');
            $groups = $this->db->get('tbl_task_manager')->result();

            foreach ($groups as $g) {
                if (empty($g->route_key) || empty($g->keep_id)) {
                    continue;
                }
                $this->db->where('panel', $panel);
                $this->db->where('route_key', $g->route_key);
                $this->db->where('id !=', (int) $g->keep_id);
                $this->db->update('tbl_task_manager', ['status' => 0]);
            }
        }
    }

    private function _seed_panel_menus()
    {
        $pos = (int) $this->db->select_max('position')->get('tbl_task_manager')->row()->position;
        $pos = $pos ?: 0;

        $dist_menus = [
            ['name' => 'Dashboard', 'url' => 'distributor/dashboard', 'img' => 'fas fa-tachometer-alt', 'route_key' => 'distributor/dashboard', 'permission_slug' => null],
            ['name' => 'Manage Dealers', 'url' => 'distributor/dashboard/dealers', 'img' => 'fas fa-users', 'route_key' => 'distributor/dashboard/dealers', 'permission_slug' => 'create_dealer'],
            ['name' => 'View Users', 'url' => 'distributor/dashboard/users', 'img' => 'fas fa-user', 'route_key' => 'distributor/dashboard/users', 'permission_slug' => 'edit_user'],
            ['name' => 'Commission', 'url' => 'distributor/dashboard/commission', 'img' => 'fas fa-coins', 'route_key' => 'distributor/dashboard/commission', 'permission_slug' => 'view_transaction_history'],
            ['name' => 'Reports', 'url' => 'distributor/dashboard/reports', 'img' => 'fas fa-chart-bar', 'route_key' => 'distributor/dashboard/reports', 'permission_slug' => 'view_reports'],
        ];

        foreach ($dist_menus as $m) {
            $this->_upsert_panel_menu($m, 'distributor', $pos);
        }

        $dealer_menus = [
            ['name' => 'Dashboard', 'url' => 'dealer/dashboard', 'img' => 'fas fa-tachometer-alt', 'route_key' => 'dealer/dashboard', 'permission_slug' => null],
            ['name' => 'Manage Users', 'url' => 'dealer/dashboard/users', 'img' => 'fas fa-users', 'route_key' => 'dealer/dashboard/users', 'permission_slug' => 'create_user'],
            ['name' => 'Commission', 'url' => 'dealer/dashboard/commission', 'img' => 'fas fa-coins', 'route_key' => 'dealer/dashboard/commission', 'permission_slug' => 'view_transaction_history'],
        ];

        foreach ($dealer_menus as $m) {
            $this->_upsert_panel_menu($m, 'dealer', $pos);
        }

        if ($this->db->field_exists('panel', 'tbl_task_manager')) {
            $this->db->query("UPDATE `tbl_task_manager` SET `panel` = 'admin'
                WHERE (`panel` IS NULL OR `panel` = '') AND (`url` LIKE 'backend/%' OR `route_key` LIKE 'backend/%')");
        }
    }

    private function _upsert_panel_menu(array $m, $panel, &$pos)
    {
        $existing = $this->db->get_where('tbl_task_manager', [
            'panel' => $panel,
            'route_key' => $m['route_key'],
            'status' => 1,
        ])->row();

        $payload = [
            'name' => $m['name'],
            'url' => $m['url'],
            'img' => $m['img'],
            'permission_slug' => $m['permission_slug'],
        ];

        if ($existing) {
            $this->db->where('id', $existing->id)->update('tbl_task_manager', $payload);
            return;
        }

        $this->db->insert('tbl_task_manager', array_merge($payload, [
            'route_key' => $m['route_key'],
            'panel' => $panel,
            'child_of' => 0,
            'position' => ++$pos,
            'status' => 1,
        ]));
    }

    private function _backfill_role_ids()
    {
        if ($this->db->field_exists('role_id', 'tbl_admin')) {
            $this->db->query("UPDATE `tbl_admin` a
                INNER JOIN `tbl_roles` r ON r.name = a.role AND r.panel = 'admin'
                SET a.role_id = r.id
                WHERE a.role_id IS NULL AND a.role IS NOT NULL AND a.role != ''");
            $this->db->query("UPDATE `tbl_admin` SET role_id = 1 WHERE (role = 'Admin' OR role IS NULL) AND role_id IS NULL");
        }
        if ($this->db->field_exists('role_id', 'tbl_distributors')) {
            $this->db->query("UPDATE `tbl_distributors` SET role_id = 2 WHERE role_id IS NULL");
        }
        if ($this->db->field_exists('role_id', 'tbl_dealers')) {
            $this->db->query("UPDATE `tbl_dealers` SET role_id = 3 WHERE role_id IS NULL");
        }
    }

    public function get_role_by_id($role_id)
    {
        return $this->db->get_where('tbl_roles', ['id' => (int) $role_id, 'status' => 1])->row();
    }

    public function get_roles_by_panel($panel)
    {
        $this->db->where('status', 1);
        $this->db->where('panel', $panel);
        $this->db->order_by('name', 'ASC');
        return $this->db->get('tbl_roles')->result();
    }

    public function get_all_permissions($panel = null)
    {
        $this->db->where('status', 1);
        $this->db->order_by('sort_order', 'ASC');
        $rows = $this->db->get('tbl_permissions')->result();
        if (!$panel) {
            return $rows;
        }
        return array_values(array_filter($rows, function ($r) use ($panel) {
            $panels = array_map('trim', explode(',', $r->panels));
            return in_array($panel, $panels, true);
        }));
    }

    public function get_permission_ids_for_role($role_id)
    {
        $this->db->select('permission_id');
        $this->db->where('role_id', (int) $role_id);
        $rows = $this->db->get('tbl_role_permissions')->result();
        return array_map(function ($r) { return (int) $r->permission_id; }, $rows);
    }

    public function get_permission_slugs_for_role($role_id)
    {
        $ids = $this->get_permission_ids_for_role($role_id);
        if (empty($ids)) {
            return [];
        }
        $this->db->where_in('id', $ids);
        $this->db->where('status', 1);
        $rows = $this->db->get('tbl_permissions')->result();
        return array_map(function ($r) { return $r->slug; }, $rows);
    }

    public function save_role_permissions($role_id, array $permission_ids)
    {
        $role_id = (int) $role_id;
        $this->db->where('role_id', $role_id)->delete('tbl_role_permissions');

        $permission_ids = array_unique(array_filter(array_map('intval', $permission_ids)));
        foreach ($permission_ids as $pid) {
            $this->db->insert('tbl_role_permissions', [
                'role_id' => $role_id,
                'permission_id' => $pid,
            ]);
        }
    }

    public function sync_permissions_to_session($role_id)
    {
        $slugs = $this->get_permission_slugs_for_role($role_id);
        $CI =& get_instance();
        $CI->session->set_userdata('permission_slugs', $slugs);
        return $slugs;
    }

    public function has_permission($role_id, $slug)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return false;
        }
        $slugs = $this->get_permission_slugs_for_role($role_id);
        return in_array($slug, $slugs, true);
    }

    public function get_task_ids_for_role($role_id)
    {
        $role = $this->get_role_by_id($role_id);
        if (!$role || empty($role->tasks)) {
            return [];
        }
        return array_values(array_filter(array_map('intval', explode(',', $role->tasks))));
    }
public function get_menu_tree_for_forms($panel = null)
{
    $this->db->where('status', 1);
    $this->db->order_by('position', 'ASC');
    
    if ($panel) {
        $this->db->where('panel', $panel);
    }
    
    $menus = $this->db->get('tbl_task_manager')->result();
    
    $parents = [];
    $children = [];
    
    foreach ($menus as $menu) {
        if ((int)$menu->child_of === 0) {
            $parents[] = $menu;
        } else {
            if (!isset($children[$menu->child_of])) {
                $children[$menu->child_of] = [];
            }
            $children[$menu->child_of][] = $menu;
        }
    }
    
    // Sort children by position
    foreach ($children as $parent_id => $child_list) {
        usort($children[$parent_id], function($a, $b) {
            return (int)$a->position <=> (int)$b->position;
        });
    }
    
    return ['parents' => $parents, 'children' => $children];
}

    public function get_menus_for_role($role_id, $panel = null)
    {
        $role_id = (int) $role_id;
        $task_ids = $this->get_task_ids_for_role($role_id);

        if (empty($task_ids)) {
            return ['parents' => [], 'children' => []];
        }

        $this->db->where_in('id', $task_ids);
        $this->db->where('status', 1);
        if ($panel) {
            $this->db->where('panel', $panel);
        }
        $this->db->order_by('position', 'ASC');
        $rows = $this->db->get('tbl_task_manager')->result();

        $parents = [];
        $children = [];
        foreach ($rows as $row) {
            if (!empty($row->permission_slug) && !$this->has_permission($role_id, $row->permission_slug)) {
                continue;
            }
            if ((int) $row->child_of === 0 && $row->url !== '#') {
                $parents[] = $row;
            } elseif ((int) $row->child_of > 0) {
                $children[$row->child_of][] = $row;
            }
        }

        foreach ($rows as $row) {
            if ((int) $row->child_of === 0 && $row->url === '#') {
                $child_list = $children[$row->id] ?? [];
                if (!empty($child_list)) {
                    $parents[] = $row;
                }
            }
        }

        usort($parents, function ($a, $b) {
            return (int) $a->position <=> (int) $b->position;
        });

        $parents = $this->_dedupe_menus_by_route($parents);

        return ['parents' => $parents, 'children' => $children];
    }

    private function _dedupe_menus_by_route(array $menus)
    {
        $seen = [];
        $out = [];
        foreach ($menus as $menu) {
            $key = strtolower(trim($menu->route_key ?: $menu->url, '/'));
            if ($key === '' || $key === '#') {
                $out[] = $menu;
                continue;
            }
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $out[] = $menu;
        }
        return $out;
    }

    public function can_access_route($role_id, $route_key, $panel = null)
    {
        $role_id = (int) $role_id;
        $route_key = strtolower(trim((string) $route_key, '/'));

        if ($role_id <= 0 || $route_key === '') {
            return false;
        }

        $CI =& get_instance();
        if (strpos($route_key, 'backend/roles/') === 0 && (int) $CI->session->userdata('admin_id') === 1) {
            return true;
        }
        $CI->config->load('rbac_permissions', true);
        $route_perms = $CI->config->item('rbac_route_permissions', 'rbac_permissions');
        if (is_array($route_perms) && isset($route_perms[$route_key])) {
            $need = $route_perms[$route_key];
            if ($need) {
                return $this->has_permission($role_id, $need);
            }
        }

        if ($route_key === 'dealer/dashboard/update_user_wallet' || $route_key === 'distributor/dashboard/update_dealer_wallet') {
            $type = $CI->input->post('transaction_type');
            if ($type === 'debit') {
                return $this->has_permission($role_id, 'wallet_debit');
            }
            return $this->has_permission($role_id, 'wallet_credit');
        }

        $task_ids = $this->get_task_ids_for_role($role_id);
        if (empty($task_ids)) {
            return false;
        }

        $this->db->where_in('id', $task_ids);
        $this->db->where('status', 1);
        if ($panel) {
            $this->db->where('panel', $panel);
        }
        $tasks = $this->db->get('tbl_task_manager')->result();

        foreach ($tasks as $task) {
            if (!empty($task->permission_slug) && !$this->has_permission($role_id, $task->permission_slug)) {
                continue;
            }
            $key = strtolower(trim($task->route_key ?: $task->url, '/'));
            if ($key === '' || $key === '#') {
                continue;
            }
            if ($route_key === $key) {
                return true;
            }
            if (strpos($route_key, $key . '/') === 0) {
                return true;
            }
        }

        return false;
    }

    public function default_role_id_for_panel($panel)
    {
        $map = ['admin' => 1, 'distributor' => 2, 'dealer' => 3];
        return isset($map[$panel]) ? $map[$panel] : 1;
    }
}
