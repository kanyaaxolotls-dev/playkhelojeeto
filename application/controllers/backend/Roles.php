<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Roles extends CI_Controller {

	public function __construct() 
    {
        parent::__construct();
		if ($this->session->admin_id == NULL)
        {
            redirect(site_url('backend/login'));
        }
    }

    public function create_rol()
	{
		$name   = $this->input->post('name');
		$u_chec = $this->db_model->count_all('tbl_roles', array('name' => $name));
		if($u_chec > 0){
			$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Role Already Exist.</div>');
			redirect(site_url('backend/roles/manage_role'));
		}
		else{
			$tasks  = implode(",",$this->input->post('role'));
			$edit   = implode(",",$this->input->post('edit'));
			$delete = implode(",",$this->input->post('delete'));
			$data   = array(
					   'name'       => $name,
					   'tasks'      => $tasks,
					   'editable'   => $edit,
					   'deletable'  => $delete
			);
			$this->db->insert('tbl_roles', $data);
			$this->session->set_flashdata('site_flash', '<div class="alert alert-success">Role Added.</div>');
			redirect(site_url('backend/roles/manage_role'));
		}
	}

    public function manage_role()
	{
        $where_condition  = "status = 1";
        $data['roles']    = $this->db_model->get_all_data('tbl_roles',$where_condition);
        $data['title']    = 'Manage Roles';;
		$this->load->view('admin/roles/create_role',$data);
	}

    public function delete_role($id)
	{
        if($id != 1){
            $where_condition  = "id = ".$id;
            $array            =  ['status'   => 0];
            $result           = $this->db_model->update($array,'tbl_roles',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Role Deleted.</div>');
            redirect(site_url('backend/roles/manage_role'));
        }
        else{
            $this->session->set_flashdata('site_flash', '<div class="alert alert-warning">Admin Role Is Not Deletable.</div>');
            redirect(site_url('backend/roles/manage_role'));
        }
    }

    public function update_wallet() {
        $user_id = $this->input->post('user_id');
        $action  = $this->input->post('action');
        $amount  = $this->input->post('amount');
        $remarks = $this->input->post('remarks');
        $user    = $this->db->get_where('tbl_admin', ['id' => $user_id])->row();
        $current_wallet = $user->wallet;
        
        if ($action == 'add') {
            $new_balance = $current_wallet + $amount;
        } else {
            if ($amount > $current_wallet) {
                $this->session->set_flashdata('error', 'Deduction amount cannot be greater than current wallet balance');
                redirect('backend/roles/manage_users');
            }
            $new_balance = $current_wallet - $amount;
        }
        
        $this->db->where('id', $user_id);
        $this->db->update('tbl_admin', ['wallet' => $new_balance]);
        
        $transaction_data = [
            'user_id'          => $user_id,
            'action'           => $action,
            'amount'           => $amount,
            'previous_balance' => $current_wallet,
            'new_balance'      => $new_balance,
            'remarks'          => $remarks,
            'created_at'       => date('Y-m-d H:i:s'),
            'created_by'       => $this->session->userdata('admin_id')
        ];
        $this->db->insert('wallet_transactions', $transaction_data);
        
        $this->session->set_flashdata('success', 'Wallet updated successfully');
        redirect('backend/roles/assign_role');
    }

    public function assign_role()
	{
        $where_condition  = "status = 1";
        $data['roles']    = $this->db_model->get_all_data('tbl_admin',$where_condition);
        $data['title']    = 'Manage Roles';;
		$this->load->view('admin/roles/assign_role',$data);
	}

    public function create_users()
	{
		$uname  = $this->input->post('uname');
		$role   = $this->input->post('role');
		$phone  = $this->input->post('phone');
		$pass   = $this->input->post('pass');
		$cpass  = $this->input->post('cpass');
		$u_chec = $this->db_model->count_all('tbl_admin', array('username' => $uname));
		if($u_chec > 0){
			$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Username Already Exist.</div>');
			redirect(site_url('backend/roles/assign_role'));
		}
        elseif($pass == $cpass){
			$data   = array(
				'username'   => $uname,
				'role'       => $role,
				'role'       => $role,
				'phone'      => $phone,
				'password'   => $cpass,
				'status'     => 1,
			);
			$this->db->insert('tbl_admin', $data);
	
			$this->session->set_flashdata('site_flash', '<div class="alert alert-success">Role Assigned.</div>');
			redirect(site_url('backend/roles/assign_role'));
		}
		else{
			$this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Password Missmatch.</div>');
			redirect(site_url('backend/roles/assign_role'));
		}
	}

    public function delete_user_role($id)
	{
        if($id != 1){
            $where_condition  = "id = ".$id;
            $array            =  ['status'   => 0];
            $result           = $this->db_model->update($array,'tbl_admin',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Subadmin Deleted.</div>');
            redirect(site_url('backend/roles/assign_role'));
        }
        else{
            $this->session->set_flashdata('site_flash', '<div class="alert alert-warning">This User  Is Not Deletable.</div>');
            redirect(site_url('backend/roles/assign_role'));
        }
    }

	public function edit_user_role($id)
	{
		$data['title']    = 'Edit Subadmin';
		$where_condition  = "status = 1";
		$data['detail']   = $this->db_model->select_multi('*', 'tbl_admin', array('id' => $id));
		$data['id']       = $id;
		$this->load->view('admin/roles/edit_role',$data);
	}

	public function update_subadmin()
	{
	    $uname        = $this->input->post('uname');
	    $pass         = $this->input->post('pass');
	    $phone        = $this->input->post('phone');
	    $id           = $this->input->post('id');
	    $role         = $this->input->post('role');
	    $cat_chec     = $this->db_model->count_all('tbl_admin', array('username' => $uname,'id !=' => $id));
	    if($cat_chec == 0){
			$array = array(
				'username'   => $uname,
				'role'       => $role,
				'phone'      => $phone,
				'password'   => $pass,
			);
			$where_condition  = "id = ".$id;
            $this->db_model->update($array,'tbl_admin',$where_condition);
            $this->session->set_flashdata('site_flash', '<div class="alert alert-success">Subadmin Updated.</div>');
            redirect(site_url('backend/roles/edit_user_role/'.$id));
	    }
	    else{
	        $this->session->set_flashdata('site_flash', '<div class="alert alert-danger">Username Already Exist.</div>');
            redirect(site_url('backend/roles/edit_user_role/'.$id));
	    }
	}

}