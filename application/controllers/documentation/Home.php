<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {
    
	public function index()
	{
	    $this->db->select('*');
		$this->db->from('cards');
		$query         = $this->db->get();
		$data['cards'] = $query->result();
		$this->load->view('documentation/index',$data);
	}
    
	public function card2()
	{
	    $this->db->select('*');
		$this->db->from('cards');
		$query         = $this->db->get();
		$data['cards'] = $query->result();
		$this->load->view('documentation/card2',$data);
	}
    
	public function index2()
	{
	    $this->db->select('*');
		$this->db->from('cards');
		$query         = $this->db->get();
		$data['cards'] = $query->result();
		$this->load->view('documentation/index_new',$data);
	}
}


