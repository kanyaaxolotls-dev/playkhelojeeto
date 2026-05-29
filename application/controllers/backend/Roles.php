<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Roles extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
        if ($this->session->admin_id == null) {
            redirect(site_url('backend/login'));
        }
        $this->load->model('rbac_model');
        $this->load->helper('rbac');
        $this->rbac_model->ensure_schema();
        if (!rbac_can_manage_roles_module()) {
            rbac_require('manage_roles');
        }
    }

    private function _form_data($role = null)
    {
        $tree = $this->rbac_model->get_menu_tree_for_forms();
        $panel = $role ? $role->panel : 'admin';
        return [
            'all_permissions' => $this->rbac_model->get_all_permissions(),
            'menu_parents' => $tree['parents'],
            'menu_children' => $tree['children'],
            'selected_tasks' => $role ? $this->rbac_model->get_task_ids_for_role($role->id) : [],
            'selected_permissions' => $role ? $this->rbac_model->get_permission_ids_for_role($role->id) : [],
            'role_panel' => $panel,
            'role_name' => $role ? $role->name : '',
        ];
    }

    public function create_rol()
    {
        $name = trim($this->input->post('name'));
        $panel = $this->input->post('panel') ?: 'admin';
        if (!in_array($panel, ['admin', 'distributor', 'dealer'], true)) {
            $panel = 'admin';
        }

        if ($this->db_model->count_all('tbl_roles', ['name' => $name, 'panel' => $panel]) > 0) {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Role already exists for this panel.</div>');
            redirect(site_url('backend/roles/manage_role'));
        }

        $tasks = $this->input->post('role');
        $tasks = is_array($tasks) ? implode(',', array_map('intval', $tasks)) : '';
        $permissions = $this->input->post('permissions');
        $permissions = is_array($permissions) ? $permissions : [];

        $dashboard_map = [
            'admin' => 'backend/admin',
            'distributor' => 'distributor/dashboard',
            'dealer' => 'dealer/dashboard',
        ];

        $this->db->insert('tbl_roles', [
            'name' => $name,
            'slug' => url_title($name, '-', true),
            'panel' => $panel,
            'dashboard_url' => $dashboard_map[$panel],
            'tasks' => $tasks,
            'editable' => '',
            'deletable' => '',
            'status' => 1,
            'is_system' => 0,
            'Date' => date('Y-m-d H:i:s'),
        ]);

        $role_id = (int) $this->db->insert_id();
        $this->rbac_model->save_role_permissions($role_id, $permissions);

        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Role added. Only selected permissions and menus are enabled.</div>');
        redirect(site_url('backend/roles/manage_role'));
    }

    public function manage_role()
    {
        $data['roles'] = $this->db_model->get_all_data('tbl_roles', 'status = 1');
        $data['title'] = 'Manage Roles';
        $data = array_merge($data, $this->_form_data());
        $this->load->view('admin/roles/create_role', $data);
    }

    public function edit_role($id = 0)
    {
        $id = (int) $id;
        $role = $this->rbac_model->get_role_by_id($id);
        if (!$role) {
            show_404();
        }

        $data['title'] = 'Edit Role';
        $data['role'] = $role;
        $data['is_edit'] = true;
        $data = array_merge($data, $this->_form_data($role));
        $this->load->view('admin/roles/edit_role_manage', $data);
    }

    public function update_role()
    {
        $id = (int) $this->input->post('id');
        $role = $this->rbac_model->get_role_by_id($id);
        if (!$role) {
            show_404();
        }

        $name = trim($this->input->post('name'));
        $dup = $this->db_model->count_all('tbl_roles', ['name' => $name, 'panel' => $role->panel, 'id !=' => $id]);
        if ($dup > 0) {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Role name already exists.</div>');
            redirect(site_url('backend/roles/edit_role/' . $id));
        }

        $tasks = $this->input->post('role');
        $tasks = is_array($tasks) ? implode(',', array_map('intval', $tasks)) : '';
        $permissions = $this->input->post('permissions');
        $permissions = is_array($permissions) ? $permissions : [];

        $this->db->where('id', $id)->update('tbl_roles', [
            'name' => $name,
            'slug' => url_title($name, '-', true),
            'tasks' => $tasks,
        ]);

        $this->rbac_model->save_role_permissions($id, $permissions);

        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Role updated successfully.</div>');
        redirect(site_url('backend/roles/edit_role/' . $id));
    }

    public function delete_role($id)
    {
        $id = (int) $id;
        $role = $this->db->get_where('tbl_roles', ['id' => $id])->row();
        if (!$role || (int) $role->is_system === 1) {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-warning">System role cannot be deleted.</div>');
            redirect(site_url('backend/roles/manage_role'));
        }

        $this->db_model->update(['status' => 0], 'tbl_roles', 'id = ' . $id);
        $this->db->where('role_id', $id)->delete('tbl_role_permissions');
        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Role deleted.</div>');
        redirect(site_url('backend/roles/manage_role'));
    }

    public function update_wallet()
    {
        rbac_require('manage_wallet');
        $user_id = $this->input->post('user_id');
        $action = $this->input->post('action');
        $amount = $this->input->post('amount');
        $remarks = $this->input->post('remarks');
        $user = $this->db->get_where('tbl_admin', ['id' => $user_id])->row();
        $current_wallet = $user->wallet;

        if ($action === 'add') {
            rbac_require('wallet_credit');
            $new_balance = $current_wallet + $amount;
        } else {
            rbac_require('wallet_debit');
            if ($amount > $current_wallet) {
                $this->session->set_flashdata('error', 'Deduction amount cannot be greater than current wallet balance');
                redirect('backend/roles/assign_role');
            }
            $new_balance = $current_wallet - $amount;
        }

        $this->db->where('id', $user_id);
        $this->db->update('tbl_admin', ['wallet' => $new_balance]);

        $this->db->insert('wallet_transactions', [
            'user_id' => $user_id,
            'action' => $action,
            'amount' => $amount,
            'previous_balance' => $current_wallet,
            'new_balance' => $new_balance,
            'remarks' => $remarks,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->session->userdata('admin_id'),
        ]);

        $this->session->set_flashdata('success', 'Wallet updated successfully');
        redirect('backend/roles/assign_role');
    }

    public function assign_role()
    {
        $data['roles'] = $this->db_model->get_all_data('tbl_admin', 'status = 1');
        $data['role_options'] = $this->rbac_model->get_roles_by_panel('admin');
        $data['title'] = 'Assign Role (Subadmin)';
        $this->load->view('admin/roles/assign_role', $data);
    }

    public function create_users()
    {
        $uname = $this->input->post('uname');
        $role_id = (int) $this->input->post('role_id');
        $phone = $this->input->post('phone');
        $pass = $this->input->post('pass');
        $cpass = $this->input->post('cpass');

        $role = $this->rbac_model->get_role_by_id($role_id);
        if (!$role || $role->panel !== 'admin') {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid admin role selected.</div>');
            redirect(site_url('backend/roles/assign_role'));
        }

        if ($this->db_model->count_all('tbl_admin', ['username' => $uname]) > 0) {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Username already exists.</div>');
            redirect(site_url('backend/roles/assign_role'));
        }

        if ($pass != $cpass) {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Password mismatch.</div>');
            redirect(site_url('backend/roles/assign_role'));
        }

        $this->db->insert('tbl_admin', [
            'username' => $uname,
            'role' => $role->name,
            'role_id' => $role->id,
            'phone' => $phone,
            'password' => $cpass,
            'status' => 1,
        ]);

        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Subadmin created with role.</div>');
        redirect(site_url('backend/roles/assign_role'));
    }

    public function delete_user_role($id)
    {
        if ($id != 1) {
            $this->db_model->update(['status' => 0], 'tbl_admin', 'id = ' . (int) $id);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Subadmin deleted.</div>');
        } else {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-warning">This user cannot be deleted.</div>');
        }
        redirect(site_url('backend/roles/assign_role'));
    }

    public function edit_user_role($id)
    {
        $data['title'] = 'Edit Subadmin';
        $data['detail'] = $this->db_model->select_multi('*', 'tbl_admin', ['id' => $id]);
        $data['role_options'] = $this->rbac_model->get_roles_by_panel('admin');
        $data['id'] = $id;
        $this->load->view('admin/roles/edit_role', $data);
    }

    public function update_subadmin()
    {
        $uname = $this->input->post('uname');
        $pass = $this->input->post('pass');
        $phone = $this->input->post('phone');
        $id = (int) $this->input->post('id');
        $role_id = (int) $this->input->post('role_id');

        $role = $this->rbac_model->get_role_by_id($role_id);
        if (!$role || $role->panel !== 'admin') {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Invalid admin role.</div>');
            redirect(site_url('backend/roles/edit_user_role/' . $id));
        }

        if ($this->db_model->count_all('tbl_admin', ['username' => $uname, 'id !=' => $id]) > 0) {
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Username already exists.</div>');
            redirect(site_url('backend/roles/edit_user_role/' . $id));
        }

        $this->db_model->update([
            'username' => $uname,
            'role' => $role->name,
            'role_id' => $role->id,
            'phone' => $phone,
            'password' => $pass,
        ], 'tbl_admin', 'id = ' . $id);

        $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Subadmin updated.</div>');
        redirect(site_url('backend/roles/edit_user_role/' . $id));
    }
}
