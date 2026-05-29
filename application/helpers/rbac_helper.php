<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('rbac_route_key')) {
    function rbac_route_key()
    {
        $CI =& get_instance();
        $parts = array_filter([
            $CI->uri->segment(1),
            $CI->uri->segment(2),
            $CI->uri->segment(3),
        ]);
        return strtolower(implode('/', $parts));
    }
}

if (!function_exists('rbac_role_id')) {
    function rbac_role_id()
    {
        $CI =& get_instance();
        return (int) $CI->session->userdata('role_id');
    }
}

if (!function_exists('rbac_permission_slugs')) {
    function rbac_permission_slugs()
    {
        $CI =& get_instance();
        $slugs = $CI->session->userdata('permission_slugs');
        if (!is_array($slugs)) {
            $CI->load->model('rbac_model');
            $slugs = $CI->rbac_model->sync_permissions_to_session(rbac_role_id());
        }
        return $slugs;
    }
}

if (!function_exists('rbac_has')) {
    function rbac_has($slug)
    {
        $slug = trim((string) $slug);
        if ($slug === '') {
            return false;
        }
        return in_array($slug, rbac_permission_slugs(), true);
    }
}

/**
 * Who may open Role Management screens (setup + ongoing).
 * Main admin account (session admin_id = 1) may always configure roles.
 */
if (!function_exists('rbac_can_manage_roles_module')) {
    function rbac_can_manage_roles_module()
    {
        if (rbac_has('manage_roles')) {
            return true;
        }
        $CI =& get_instance();
        return (int) $CI->session->userdata('admin_id') === 1;
    }
}

if (!function_exists('rbac_wallet_can')) {
    function rbac_wallet_can($type = 'credit')
    {
        if ($type === 'debit') {
            return rbac_has('wallet_debit');
        }
        return rbac_has('wallet_credit');
    }
}

if (!function_exists('rbac_can')) {
    function rbac_can($route_key = null, $panel = null)
    {
        $CI =& get_instance();
        $CI->load->model('rbac_model');
        $role_id = rbac_role_id();
        if ($role_id <= 0) {
            return false;
        }
        $route_key = $route_key ?: rbac_route_key();
        $panel = $panel ?: $CI->session->userdata('panel');
        return $CI->rbac_model->can_access_route($role_id, $route_key, $panel);
    }
}

if (!function_exists('rbac_menus')) {
    function rbac_menus($panel)
    {
        $CI =& get_instance();
        $CI->load->model('rbac_model');
        $role_id = rbac_role_id();
        if ($role_id <= 0) {
            return ['parents' => [], 'children' => []];
        }
        return $CI->rbac_model->get_menus_for_role($role_id, $panel);
    }
}

if (!function_exists('rbac_require')) {
    function rbac_require($slug)
    {
        if (rbac_has($slug)) {
            return;
        }
        $CI =& get_instance();
        if ($CI->input->is_ajax_request()) {
            $CI->output
                ->set_status_header(403)
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Permission Denied']));
            exit;
        }
        show_error('Permission Denied', 403);
    }
}

if (!function_exists('rbac_set_account_session')) {
    function rbac_set_account_session($account, $role)
    {
        $CI =& get_instance();
        $CI->load->model('rbac_model');
        $CI->session->set_userdata([
            'role_id' => (int) $role->id,
            'role_slug' => $role->slug,
            'panel' => $role->panel,
            'dashboard_url' => $role->dashboard_url,
        ]);
        $CI->rbac_model->sync_permissions_to_session((int) $role->id);
    }
}
