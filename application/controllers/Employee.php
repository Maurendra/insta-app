<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Employee extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		/* Set Timezone */
		date_default_timezone_set('Asia/Jakarta');
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
    $this->load->css('assets/plugins/DataTables/datatables.min.css');
		
		$this->load->js('assets/vendor/jquery/jquery.min.js');
		$this->load->js('assets/js/utils.js');
		$this->load->js('assets/vendor/bootstrap-4.4.1-dist/js/bootstrap.bundle.js');
		$this->load->js('assets/vendor/chart.js-2.9.3/Chart.bundle.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/bootstrap-select.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/i18n/defaults-id_ID.min.js');
    $this->load->js('assets/plugins/DataTables/datatables.min.js');
	}

	public function index(){
		$this->output->set_title('Attendance - List Registered Employee');

		$this->load->view('pages/apps/employee/list');	
	}

	public function delete($id = 0){
		$companyId = $this->session->userdata('id');
		if (!empty($id) && !empty($companyId)) {

      $update_data = array(
				'co_id' => 0
      );
			
			// Update Data
			$this->db->where('co_id', $companyId);
			$this->db->where('em_id', $id);
      $this->db->update('employee',$update_data);
			$this->db->reset_query();

			// Redirect Process
			$session_data = array(
				'message'   => 'Data successfully deleted',
				'message_status'   => 'success'
			);
			$this->session->set_userdata($session_data);

			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		} else {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}
  }

	public function api($params){
		$this->output->unset_template('dashboard');
		if ($params == 'getAllEmployees') {
			$this->getAllEmployees();
		} elseif ($params == 'getEmployees') {
			$this->getEmployees();
		}
	}

	private function query_employee(){
		$companyId = $this->session->userdata('id');

		// Initialize needed variable
		$column_search = array('em_name','em_nik', 'em_gender', 'em_email', 'em_status', 'em_phone');
		$column_order = array(null, 'em_name','em_nik', 'em_gender', 'em_email', 'em_status', NULL);
		$order = array('em_id' => 'desc');

		// Get Query
		$this->db->select("*");
		$this->db->from('employee');
		$this->db->where('employee.co_id', $companyId);

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

	private function getAllEmployees(){
		$companyId = $this->session->userdata('id');
		// Well lets create by ourself
		// Get filtered data
		$this->query_employee();
		$query = $this->db->get();
		$countFiltered = $query->num_rows();

		// Limit created by Datatable
		$this->query_employee();
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
				$row[] = $field->em_name;
				$row[] = $field->em_nik;
				$row[] = $field->em_gender;
				$row[] = $field->em_email;
				$row[] = $field->em_status;
				$row[] = "<a class='btn btn-danger btn-sm modal-details mr-2' role='button'  
				href='".base_url('employee/delete/'.$field->em_id)."' onclick=\"return confirm('Anda yakin menghapus data ini?')\">Delete</a>";

				$data[] = $row;
		}

		$this->db->reset_query();

		// Count all data
		$this->db->select("em_id");
		$this->db->from('employee');
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

	public function all(){
		$this->output->set_title('Attendance - All Employee');

		$this->load->view('pages/apps/employee/all/list');	
	}

	private function query_all_employee(){
		$companyId = $this->session->userdata('id');

		// Initialize needed variable
		$column_search = array('employee.em_name','employee.em_nik', 'employee.em_gender', 'employee.em_email', 'employee.em_status', 'employee.em_phone');
		$column_order = array(null, 'employee.em_name','employee.em_nik', 'employee.em_gender', 'employee.em_email', 'employee.em_status', NULL);
		$order = array('em_id' => 'desc');

		// Get Query
		$this->db->select("em_id");
		$this->db->from('attendance');
		$this->db->where('attendance.co_id', $companyId);
		$this->db->group_by('em_id');
		$where_clause = $this->db->get_compiled_select();
		$this->db->reset_query();

		$this->db->select("*");
		$this->db->from('employee');
		$this->db->where('employee.co_id', $companyId)->or_where('employee.em_id IN ('.$where_clause.')');

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

	private function getEmployees(){
		$companyId = $this->session->userdata('id');
		// Well lets create by ourself
		// Get filtered data
		$this->query_all_employee();
		$query = $this->db->get();
		$countFiltered = $query->num_rows();

		// Limit created by Datatable
		$this->query_all_employee();
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
				$row[] = $field->em_name;
				$row[] = $field->em_nik;
				$row[] = $field->em_gender;
				$row[] = $field->em_email;
				$row[] = $field->em_status;
				$row[] = "<a class='btn btn-info btn-sm modal-details mr-2' role='button'  
				href='".base_url('employee/detail/'.$field->em_id)."' >Detail</a>";

				$data[] = $row;
		}

		$this->db->reset_query();

		// Count all data
		$this->db->select("em_id");
		$this->db->from('employee');
		$this->db->where('co_id', $companyId)->or_where('co_id', 0);
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

	public function detail($id = ''){
		$this->output->set_title('Attendance - Detail Employee');
		$this->load->css('assets/vendor/creative/css/fullcalendar.css');
		$this->load->css('assets/vendor/creative/css/fullcalendar.print.css');
		
		$this->load->js('assets/vendor/creative/js/jquery-1.10.2.js');
		$this->load->js('assets/vendor/creative/js/jquery-ui.custom.min.js');
		$this->load->js('assets/vendor/creative/js/fullcalendar.js');

		$now =  date('Y-m-d H:i:s');

		$companyId = $this->session->userdata('id');
		if (!empty($id) && !empty($companyId)) {

			// Checking access
			// Get Query
			$this->db->select("em_id");
			$this->db->from('attendance');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->group_by('em_id');
			$where_clause = $this->db->get_compiled_select();
			$this->db->reset_query();

			$this->db->select("*");
			$this->db->from('employee');
			$this->db->where('employee.em_id', $id);
			$this->db->where('employee.co_id', $companyId)->or_where('employee.em_id IN ('.$where_clause.')');
			$query = $this->db->get();
			$accessable = $query->row_array();
			$this->db->reset_query();

			if (empty($accessable)) {
				redirect(base_url('employee/all/'), 'refresh');
			}


			// Get Attendance data
			$this->db->distinct();
			$this->db->select("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') as date, attendance.at_status as status");
			$this->db->select("DAYNAME(attendance.at_timestamp) as day_name, attendance.em_id as em_id");
			$this->db->select("EXTRACT(YEAR FROM attendance.at_timestamp) as year");
			$this->db->select("EXTRACT(MONTH FROM attendance.at_timestamp) as month");
			$this->db->select("EXTRACT(DAY FROM attendance.at_timestamp) as day");
			$this->db->from('attendance');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('attendance.em_id', $id);
			$query_1 = $this->db->get_compiled_select();

			$this->db->distinct();
			$this->db->select("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') as date, attendance_gps.atg_status as status");
			$this->db->select("DAYNAME(attendance_gps.atg_timestamp) as day_name, attendance_gps.em_id as em_id");
			$this->db->select("EXTRACT(YEAR FROM attendance_gps.atg_timestamp) as year");
			$this->db->select("EXTRACT(MONTH FROM attendance_gps.atg_timestamp) as month");
			$this->db->select("EXTRACT(DAY FROM attendance_gps.atg_timestamp) as day");
			$this->db->from('attendance_gps');
			$this->db->where('attendance_gps.co_id', $companyId);
			$this->db->where('attendance_gps.em_id', $id);
			$query_2 = $this->db->get_compiled_select();
			
			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2 . ' ORDER BY date desc');
			$attendance = $final_query->result_array();
			$this->db->reset_query();
			// End Get Attendance data

			// Get Personal data
			$this->db->select("*");
			$this->db->from('employee');
			$this->db->where('em_id', $id);
			$query = $this->db->get();
			$employee = $query->row_array();
			// End Get Personal data

			// Get Attendance data with 'In' Status
			$this->db->distinct();
			$this->db->select("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') as date, attendance.at_status as status");
			$this->db->from('attendance');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('attendance.em_id', $id);
			$this->db->where('attendance.at_status', 'In');
			$this->db->where("DATE_FORMAT(attendance.at_timestamp, '%Y-%m') = DATE_FORMAT('$now', '%Y-%m')");
			$query_1 = $this->db->get_compiled_select();

			$this->db->distinct();
			$this->db->select("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') as date, attendance_gps.atg_status as status");
			$this->db->from('attendance_gps');
			$this->db->where('attendance_gps.co_id', $companyId);
			$this->db->where('attendance_gps.em_id', $id);
			$this->db->where('attendance_gps.atg_status', 'In');
			$this->db->where("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m') = DATE_FORMAT('$now', '%Y-%m')");
			$query_2 = $this->db->get_compiled_select();
			
			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2);
			$inData = $final_query->result_array();
			$this->db->reset_query();
			// End Get Attendance data with 'In' Status

			// Get Attendance data with 'Overtime' Status
			$this->db->distinct();
			$this->db->select("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') as date, attendance.at_status as status");
			$this->db->from('attendance');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('attendance.em_id', $id);
			$this->db->where('attendance.at_status', 'Overtime');
			$this->db->where("DATE_FORMAT(attendance.at_timestamp, '%Y-%m') = DATE_FORMAT('$now', '%Y-%m')");
			$query_1 = $this->db->get_compiled_select();

			$this->db->distinct();
			$this->db->select("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') as date, attendance_gps.atg_status as status");
			$this->db->from('attendance_gps');
			$this->db->where('attendance_gps.co_id', $companyId);
			$this->db->where('attendance_gps.em_id', $id);
			$this->db->where('attendance_gps.atg_status', 'Overtime');
			$this->db->where("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m') = DATE_FORMAT('$now', '%Y-%m')");
			$query_2 = $this->db->get_compiled_select();
			
			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2);
			$overtimeData = $final_query->result_array();
			$this->db->reset_query();
			// End Get Attendance data with 'Overtime' Status

			$data = array();
			$data['attendance'] = json_encode($attendance);
			$data['employee'] = $employee;
			$data['in'] = count($inData);
			$data['overtime'] = count($overtimeData);
			// echo json_encode($attendance);
			$this->load->view('pages/apps/employee/all/detail', $data);
		} else {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect($_SERVER['HTTP_REFERER'], 'refresh');
		}	
	}
}