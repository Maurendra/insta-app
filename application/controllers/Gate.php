<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gate extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->_init();
		$this->load->database();
	}

	private function _init()
	{
		if (!$this->session->userdata('login')) {
			redirect('auth');
		}
		$this->output->set_template('dashboard');
    
		$this->load->css('assets/vendor/bootstrap-4.4.1-dist/css/bootstrap.min.css');
		$this->load->css('assets/vendor/fontawesome-free-5.6.3-web/css/all.min.css');
		$this->load->css('assets/css/custom-css.css');
		$this->load->css('assets/vendor/bootstrap-select-1.13.0-dev/dist/css/bootstrap-select.min.css');
    $this->load->css('assets/plugins/DataTables/datatables.min.css');
		
		$this->load->js('assets/vendor/jquery/jquery.min.js');
		$this->load->js('assets/js/utils.js');
		$this->load->js('assets/vendor/bootstrap-4.4.1-dist/js/bootstrap.bundle.js');
		$this->load->js('assets/vendor/chart.js-2.9.3/Chart.bundle.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/bootstrap-select.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/i18n/defaults-id_ID.min.js');
    $this->load->js('assets/plugins/DataTables/datatables.min.js');
    $this->load->js('assets/plugins/InputMask/dist/jquery.inputmask.js');
	}

	public function index(){
    $this->output->set_title('Attendance - Gate List');
		$this->load->view('pages/apps/gate/list');	
	}

	public function show_add(){
    $this->output->set_title('Attendance - Gate Add');
		$this->load->view('pages/apps/gate/add');	
	}

	public function add(){
		$companyId = $this->session->userdata('id');
		if (!empty($companyId)) {
			// Add Data
			$insert_data = array(
				'co_id' => $companyId,
				'gt_name' => $this->input->post('gt-name') ? $this->input->post('gt-name') : NULL,
				'gt_getphoto' => $this->input->post('gt-getphoto') ? $this->input->post('gt-getphoto') : NULL,
				'gt_getphoto_onlyin' => $this->input->post('gt-getphoto-onlyin') ? $this->input->post('gt-getphoto-onlyin') : NULL
			);
			
			$this->db->insert('gate',$insert_data);

			$session_data = array(
				'message'   => 'Data successfully added',
				'message_status'   => 'success'
			);
			$this->session->set_userdata($session_data);
		}

		redirect('gate');
	}

	public function edit($id = 0){
		$companyId = $this->session->userdata('id');
		if (empty($id)) {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect('gate');
		} else {
			$this->db->select('*');
			$this->db->from('gate');
			$this->db->where('gt_id', $id);
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$data = $query->row_array();

			if (!empty($data)) {
				$this->load->view('pages/apps/gate/edit', $data);	
			} else {
				$session_data = array(
					'message'   => 'Data not found',
					'message_status'   => 'info'
				);
				$this->session->set_userdata($session_data);
				redirect($_SERVER['HTTP_REFERER'], 'refresh');
			}
		}
	}

	public function update($id = 0){
		$companyId = $this->session->userdata('id');
		if (empty($id) || empty($companyId)) {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect('gate');
		} else {
			$update_data = array(
        'gt_name' => $this->input->post('gt-name') ? $this->input->post('gt-name') : NULL,
				'gt_getphoto' => $this->input->post('gt-getphoto') ? $this->input->post('gt-getphoto') : NULL,
				'gt_getphoto_onlyin' => $this->input->post('gt-getphoto-onlyin') ? $this->input->post('gt-getphoto-onlyin') : NULL
      );
			
			// Update Data
			$this->db->where('co_id', $companyId);
			$this->db->where('gt_id', $id);
			$this->db->update('gate',$update_data);

			$session_data = array(
				'message'   => 'Data successfully updated',
				'message_status'   => 'success'
			);
			$this->session->set_userdata($session_data);

			redirect('gate/edit/' .$id);
		}
	}

	public function delete($id = 0){
		$companyId = $this->session->userdata('id');
		if (!empty($id) && !empty($companyId)) {

			$this->db->select("*");
			$this->db->from('attendance');
			$this->db->where('co_id', $companyId);
			$this->db->where('gt_id', $id);
			$query = $this->db->get();
			$attendances = $query->result_array();
			$this->db->reset_query();

			if (empty($attendances)) {
				$this->db->where('gt_id', $id);
				$this->db->where('co_id', $companyId);
				$this->db->delete('gate');
				$this->db->reset_query();

				// Redirect Process
				$session_data = array(
					'message'   => 'Data successfully deleted',
					'message_status'   => 'success'
				);
				$this->session->set_userdata($session_data);
			} else {
				$session_data = array(
					'message'   => "Data failed to delete because there are any attendance's data",
					'message_status'   => 'danger'
				);
				$this->session->set_userdata($session_data);
			}
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		} else {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect('gate');
		}
  }

	public function api($params){
		$this->output->unset_template('dashboard');
		if ($params == 'getAllGates') {
			$this->getAllGates();
		}
	}

	private function query_gate(){
		$companyId = $this->session->userdata('id');

		// Initialize needed variable
		$column_search = array('gt_name','gt_getphoto', 'gt_getphoto_onlyin');
		$column_order = array(null, 'gt_name','gt_getphoto', 'gt_getphoto_onlyin');
		$order = array('gt_id' => 'desc');

		// Get Query
		$this->db->select("*");
		$this->db->from('gate');
		$this->db->where('co_id', $companyId);

		// Get Query additional from datatable
		$i = 0;
	
		foreach ($column_search as $item) // looping awal
		{
			if($_POST['search']['value']) // jika datatable mengirimkan pencarian dengan metode POST
			{
						
				if($i===0) // looping awal
				{
					$this->db->group_start(); 
					$this->db->like($item, $_POST['search']['value']);
				}
				else
				{
					$this->db->or_like($item, $_POST['search']['value']);
				}

				if(count($column_search) - 1 == $i) 
					$this->db->group_end(); 
			}
			$i++;
		}
			
		if(isset($_POST['order'])){
			$this->db->order_by($column_order[$_POST['order']['0']['column']], $_POST['order']['0']['dir']);
		}else if(isset($order)){
			$order = $order;
			$this->db->order_by(key($order), $order[key($order)]);
		}
	}

	private function getAllGates(){
		$companyId = $this->session->userdata('id');
		// Well lets create by ourself
		// Get filtered data
		$this->query_gate();
		$query = $this->db->get();
		$countFiltered = $query->num_rows();

		// Limit created by Datatable
		$this->query_gate();
		if($_POST['length'] != -1)
		$this->db->limit($_POST['length'], $_POST['start']);
		$query = $this->db->get();
		$list = $query->result();

		// Get data from query that had been built
		$data = array();
		$no = $_POST['start'];
		foreach ($list as $field) {
				$no++;
				$row = array();
				$row[] = $no;
				$row[] = $field->gt_name;
				if ($field->gt_getphoto) {
					$row[] = "Yes";
				} else {
					$row[] = "No";
				}
				if ($field->gt_getphoto_onlyin) {
					$row[] = "Yes";
				} else {
					$row[] = "No";
				}
				$row[] = "<a class='btn btn-primary btn-sm modal-details mr-2' role='button' href='" .base_url('gate/edit/'.$field->gt_id) ."' target='_blank'>Edit</a>"
				."<a class='btn btn-danger btn-sm modal-details mr-2' role='button'  
				href='".base_url('gate/delete/'.$field->gt_id)."' onclick=\"return confirm('Anda yakin menghapus data ini?')\">Delete</a>";

				$data[] = $row;
		}

		$this->db->reset_query();

		// Count all data
		$this->db->select("*");
		$this->db->from('gate');
		$this->db->where('co_id', $companyId);
		$query = $this->db->get();
		$countAll = $query->num_rows();
		$this->db->reset_query();

		// Format to send back
		$output = array(
			"draw" => $_POST['draw'],
			"recordsTotal" => $countAll,
			"recordsFiltered" => $countFiltered,
			"data" => $data,
		);
		//output dalam format JSON
		echo json_encode($output);
	}
}