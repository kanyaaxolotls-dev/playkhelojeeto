<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * URI route (segment1/segment2/segment3) => permission slug
 * null value = menu route only (checked via tbl_roles.tasks)
 */
$config['rbac_route_permissions'] = [
    // Admin
    'backend/distributors/create' => 'create_distributor',
    'backend/dealers/create' => 'create_dealer',
    'backend/roles/manage_role' => 'manage_roles',
    'backend/roles/assign_role' => 'manage_roles',
    'backend/roles/create_rol' => 'manage_roles',
    'backend/roles/edit_role' => 'manage_roles',
    'backend/roles/update_role' => 'manage_roles',
    'backend/roles/delete_role' => 'manage_roles',
    'backend/roles/update_wallet' => 'manage_wallet',
    'backend/distributors/update_wallet' => 'wallet_credit',
    'backend/distributors/commission' => 'view_reports',

    // Distributor
    'distributor/dashboard/create_dealer' => 'create_dealer',
    'distributor/dashboard/update_dealer_wallet' => 'wallet_credit',
    'distributor/dashboard/delete_dealer' => 'delete_user',
    'distributor/dashboard/reports' => 'view_reports',
    'distributor/dashboard/commission' => 'view_transaction_history',

    // Dealer
    'dealer/dashboard/create_user' => 'create_user',
    'dealer/dashboard/update_user_wallet' => 'wallet_credit',
    'dealer/dashboard/commission' => 'view_transaction_history',
];

$config['rbac_menu_permission'] = [
    'distributor/dashboard/dealers' => 'create_dealer',
    'distributor/dashboard/users' => 'edit_user',
    'dealer/dashboard/users' => 'create_user',
    'backend/roles/manage_role' => 'manage_roles',
];
