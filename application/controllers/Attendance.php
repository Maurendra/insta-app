<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attendance extends CI_Controller {

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
		$this->output->set_title('Attendance - List');
		
		$companyId = $this->session->userdata('id');

		$this->db->select("*");
		$this->db->from('gate');
		$this->db->where('co_id', $companyId);
		$query = $this->db->get();
		$gates = $query->result_array();
		$this->db->reset_query();

		$this->db->select("MAX(at_id) as last_id");
		$this->db->from('attendance');
		$this->db->where('co_id', $companyId);
		$this->db->limit(1);
		$query = $this->db->get();
		$attendances = $query->row_array();
		$this->db->reset_query();		

		$data = array();
		$data['gates'] = $gates;
		$data['attendance'] = $attendances;

		$this->load->view('pages/apps/attendance/list', $data);	
	}

	public function show_add(){
		$this->output->set_title('Attendance - Add');
		
		$companyId = $this->session->userdata('id');

		$this->db->select("*");
		$this->db->from('gate');
		$this->db->where('co_id', $companyId);
		$query = $this->db->get();
		$gates = $query->result_array();
		$this->db->reset_query();

		$this->db->select("*");
		$this->db->from('employee');
		$this->db->where('co_id', $companyId)->or_where('co_id', 0);
		$query = $this->db->get();
		$employees = $query->result_array();
		$this->db->reset_query();

		$data = array();
		$data['gates'] = $gates;
		$data['employees'] = $employees;

		$this->load->view('pages/apps/attendance/add', $data);	
	}

	public function edit($id = 0){
		$this->output->set_title('Attendance - Edit');
		$companyId = $this->session->userdata('id');
		if (empty($id)) {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect('attendance');
		} else {
			$this->db->select("*, DATE_FORMAT(at_timestamp, '%Y-%m-%dT%H:%i') AS at_timestamp");
			$this->db->from('attendance');
			$this->db->where('at_id', $id);
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$attendance = $query->row_array();

			$this->db->select("*");
			$this->db->from('gate');
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$gates = $query->result_array();
			$this->db->reset_query();

			$this->db->select("*");
			$this->db->from('employee');
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$employees = $query->result_array();
			$this->db->reset_query();

			$data = array();
			$data['gates'] = $gates;
			$data['employees'] = $employees;
			$data['attendance'] = $attendance;

			if (!empty($attendance)) {
				$this->load->view('pages/apps/attendance/edit', $data);
			} else {
				$session_data = array(
					'message'   => 'Data not found',
					'message_status'   => 'info'
				);
				$this->session->set_userdata($session_data);
				redirect('attendance');
			}
		}
	}

	public function add(){
		$companyId = $this->session->userdata('id');

		$this->db->select("em_nik");
		$this->db->from('employee');
		$this->db->where('em_id', $this->input->post('em-id'));
		$query = $this->db->get();
		$employee = $query->row_array();
		$this->db->reset_query();

		if (!empty($companyId) && !empty($employee)) {
			// Add Data
			$insert_data = array(
				'co_id' => $companyId,
				'em_id' => $this->input->post('em-id') ? $this->input->post('em-id') : NULL,
				'em_nik' => $employee['em_nik'] ? $employee['em_nik'] : NULL,
				'gt_id' => $this->input->post('gt-id') ? $this->input->post('gt-id') : NULL,
				'at_shift' => $this->input->post('at-shift') ? $this->input->post('at-shift') : NULL,
				'at_status' => $this->input->post('at-status') ? $this->input->post('at-status') : NULL,
				'at_timestamp' => $this->input->post('at-timestamp') ? date("Y-m-d H:i:s",strtotime($this->input->post('at-timestamp'))) : NULL
			);
			
			print_r($insert_data);
			$this->db->insert('attendance',$insert_data);

			$session_data = array(
				'message'   => 'Data successfully added',
				'message_status'   => 'success'
			);
			$this->session->set_userdata($session_data);
		}

		redirect('attendance');
	}

	public function update($id = 0){
		$companyId = $this->session->userdata('id');

		$this->db->select("em_nik");
		$this->db->from('employee');
		$this->db->where('em_id', $this->input->post('em-id'));
		$query = $this->db->get();
		$employee = $query->row_array();
		$this->db->reset_query();

		if (empty($id) || empty($companyId) || empty($employee)) {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect('attendance');
		} else {
			$update_data = array(
				'em_id' => $this->input->post('em-id') ? $this->input->post('em-id') : NULL,
				'em_nik' => $employee['em_nik'] ? $employee['em_nik'] : NULL,
				'gt_id' => $this->input->post('gt-id') ? $this->input->post('gt-id') : NULL,
				'at_shift' => $this->input->post('at-shift') ? $this->input->post('at-shift') : NULL,
				'at_status' => $this->input->post('at-status') ? $this->input->post('at-status') : NULL,
				'at_timestamp' => $this->input->post('at-timestamp') ? date("Y-m-d H:i:s",strtotime($this->input->post('at-timestamp'))) : NULL
      );
			
			// Update Data
			$this->db->where('co_id', $companyId);
			$this->db->where('at_id', $id);
			$this->db->update('attendance',$update_data);

			$session_data = array(
				'message'   => 'Data successfully updated',
				'message_status'   => 'success'
			);
			$this->session->set_userdata($session_data);

			redirect('attendance/edit/' .$id);
		}
	}

	public function delete($id = 0){
		$companyId = $this->session->userdata('id');
		if (!empty($id) && !empty($companyId)) {

			$this->db->where('at_id', $id);
			$this->db->where('co_id', $companyId);
			$this->db->delete('attendance');
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
		if ($params == 'getAllAttendances') {
			$this->getAllAttendances();
		} elseif ($params == 'getNewData') {
			$this->getNewData();
		}
	}

	private function query_attendance(){
		$companyId = $this->session->userdata('id');

		// Initialize needed variable
		$column_search = array('gate.gt_name','employee.em_name', 'attendance.em_nik', 'attendance.at_status', 'attendance.at_timestamp', 'attendance.at_photo');
		$column_order = array(null, 'employee.em_name', 'attendance.em_nik', 'gate.gt_name', 'attendance.at_shift', 'attendance.at_status', 'attendance.at_timestamp', 'attendance.at_photo');
		$order = array('attendance.at_id' => 'desc');

		// Get Query
		$this->db->select("attendance.*, gate.gt_name as gt_name, employee.em_name as em_name");
		$this->db->from('attendance');
		$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
		$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
		$this->db->where('attendance.co_id', $companyId);

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

	private function getAllAttendances(){
		$companyId = $this->session->userdata('id');
		// Well lets create by ourself
		// Get filtered data
		$this->query_attendance();
		$query = $this->db->get();
		$countFiltered = $query->num_rows();

		// Limit created by Datatable
		$this->query_attendance();
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
				$row[] = $field->gt_name;
				$row[] = $field->at_shift;
				$row[] = $field->at_status;
				$row[] = $field->at_timestamp;
				$row[] = $field->at_photo;
				$row[] = "<a class='btn btn-primary btn-sm modal-details mr-2' role='button' href='" .base_url('attendance/edit/'.$field->at_id) ."' target='_blank'>Edit</a>"
				."<a class='btn btn-danger btn-sm modal-details mr-2' role='button'  
				href='".base_url('attendance/delete/'.$field->at_id)."' onclick=\"return confirm('Anda yakin menghapus data ini?')\">Delete</a>";

				$data[] = $row;
		}

		$this->db->reset_query();

		// Count all data
		$this->db->select("attendance.at_id");
		$this->db->from('attendance');
		$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
		$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
		$this->db->where('attendance.co_id', $companyId);
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

	public function preview($page=0, $limit=10){
		$this->output->set_title('Attendance - Export Preview');
		$companyId = $this->session->userdata('id');

		$type = $this->input->get('ex-type');
		$gate = $this->input->get('gt-id');
		$periodType = $this->input->get('ex-period');
		$startDate = $this->input->get('ex-startdate');
		$endDate = $this->input->get('ex-enddate');
		$submitType = $this->input->get('submit-type');

		if (!empty($type)) {
			$this->db->select("*");
			$this->db->from('gate');
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$gates = $query->result_array();
			$this->db->reset_query();
			
			if ($type == 'overtime') {
				$this->previewOvertime($type, $gate, $periodType, $startDate, $endDate, $gates, $submitType, $page, $limit);
			} elseif ($type == 'fifo') {
				$this->previewFifo($type, $gate, $periodType, $startDate, $endDate, $gates, $submitType, $page, $limit);
			} else {
				$this->previewAll($type, $gate, $periodType, $startDate, $endDate, $gates, $submitType, $page, $limit);
			}
		} else {
			redirect('attendance');
		}
	}

	private function previewAll($type, $gate, $periodType, $startDate, $endDate, $gates, $submitType, $page, $limit = 10){
		$companyId = $this->session->userdata('id');
		$now =  date('Y-m-d H:i:s');

		// Start of the query you want to re-use
		$this->db->start_cache();
		$this->db->select("attendance.*, gate.gt_name as gt_name, employee.em_name as em_name");
		$this->db->select("IF(attendance.at_status = 'In', 
													(select b.at_timestamp from attendance as b where b.em_id = attendance.em_id and b.at_status = 'In' and DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') ORDER by b.at_timestamp asc limit 1)
													, IF(attendance.at_status = 'Out', 
														(select b.at_timestamp from attendance as b where b.em_id = attendance.em_id and b.at_status = 'Out' and DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') ORDER by b.at_timestamp desc limit 1)
														, IF(attendance.at_status = 'Overtime',
															(select b.at_timestamp from attendance as b where b.em_id = attendance.em_id and b.at_status = 'Overtime' and DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') ORDER by b.at_timestamp desc limit 1)
															,attendance.at_timestamp)
														) 
												) as at_timestamp");
		$this->db->from('attendance');
		$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
		$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
		$this->db->where('attendance.co_id', $companyId);

		if (!empty($gate)) {
			$this->db->where('attendance.gt_id', $gate);
		}

		if (!empty($periodType)) {
			if ($periodType == 'range') {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') >= ", date("Y-m-d",strtotime($startDate)));
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') <= ", date("Y-m-d",strtotime($endDate)));
			} elseif($periodType == 'today') {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			} else {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now' - INTERVAL 1 DAY, '%Y-%m-%d')");
			}
		}
		
		$this->db->group_by(array("attendance.em_id", "attendance.at_status", "DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d')"));

		// Keep Query Alive
		$this->db->stop_cache();

		$query = $this->db->get();
		$totalRow = $query->num_rows();
		$pureAttendances = $query->result_array();

		$this->db->limit($limit, $page);
		$query = $this->db->get();
		$attendances = $query->result_array();
		$query->free_result();
		$this->db->reset_query();
		$this->db->flush_cache();

		$data = array();
		$data['gates'] = $gates;
		$data['type'] = $type;
		$data['gate_id'] = $gate;
		$data['periodType'] = $periodType;
		$data['startDate'] = $startDate;
		$data['endDate'] = $endDate;
		$data['attendances'] = $attendances;
		$data['pureAttendances'] = $pureAttendances;
		$data['jsonAttendances'] = json_encode($attendances);
		$data['config_pagination'] = $this->fn_init->config_pagination(
			array('total_row'=>$totalRow,'url'=>base_url('attendance/preview/'),
																			'uri_segment'=>3,'per_page'=>$limit)
		);
		$data['num'] = $page+1;

		if (!empty($submitType) && $submitType == 'export') {
			
			$this->load->library("excel");
			$spreadsheet = new PHPExcel();

			// Set document properties
			$spreadsheet->getProperties()->setCreator('Andoyo - Java Web Media')
			->setLastModifiedBy('Andoyo - Java Web Medi')
			->setTitle('Office 2007 XLSX Test Document')
			->setSubject('Office 2007 XLSX Test Document')
			->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
			->setKeywords('office 2007 openxml php')
			->setCategory('Test result file');

			// Add some data
			$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A1', '#')
			->setCellValue('B1', 'Date time')
			->setCellValue('C1', 'NIK')
			->setCellValue('D1', 'Name')
			->setCellValue('E1', 'Gate Name')
			->setCellValue('F1', 'Status');

			// Miscellaneous glyphs, UTF-8
			$i=2;
			$counting = 1;
			foreach($data['pureAttendances'] as $attendance) {
				$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $counting++)
				->setCellValue('B'.$i, $attendance['at_timestamp'] ? $attendance['at_timestamp'] : '-')
				->setCellValue('C'.$i, $attendance['em_nik'] ? $attendance['em_nik'] : '-')
				->setCellValue('D'.$i, $attendance['em_name'] ? $attendance['em_name'] : '-')
				->setCellValue('E'.$i, $attendance['gt_name'] ? $attendance['gt_name'] : '-')
				->setCellValue('F'.$i, $attendance['at_status'] ? $attendance['at_status'] : '-');
				$i++;
			}

			// Rename worksheet
			$spreadsheet->getActiveSheet()->setTitle('Attendance '.date('d-m-Y H'));

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$spreadsheet->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

			date_default_timezone_set('Asia/Jakarta');
			$dateNow = date('d/m/Y');
			$fileName = "Attendance_All_".$dateNow.".xls";
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'"');
			header('Cache-Control: max-age=0');

			$writer->save('php://output');
		} else {
			$this->load->view('pages/apps/attendance/export/preview/all', $data);	
		}
	}

	private function previewFifo($type, $gate, $periodType, $startDate, $endDate, $gates, $submitType, $page, $limit = 10){
		$companyId = $this->session->userdata('id');
		$now =  date('Y-m-d H:i:s');

		// Start of the query you want to re-use
		$this->db->start_cache();
		$this->db->select("attendance.*, gate.gt_name as gt_name, employee.em_name as em_name, DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') as at_timestamp");
		$this->db->select("(select b.at_timestamp from attendance as b where DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') and b.at_status = 'In' and b.em_id = attendance.em_id ORDER by b.at_timestamp asc limit 1) as time_in");
		$this->db->select("(select b.at_timestamp from attendance as b where DATE_FORMAT(b.at_timestamp, '%Y-%m-%dT%H:%i') > DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%dT%H:%i') 
												and DATE_FORMAT(b.at_timestamp, '%Y-%m-%dT%H:%i') < DATE_FORMAT(attendance.at_timestamp + INTERVAL 24 HOUR, '%Y-%m-%dT%H:%i') 
												and b.at_status = 'Out' and b.em_id = attendance.em_id ORDER by b.at_timestamp desc limit 1) as time_out");
		$this->db->select("(select TIMEDIFF(
												(select b.at_timestamp from attendance as b where DATE_FORMAT(b.at_timestamp, '%Y-%m-%dT%H:%i') > DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%dT%H:%i') 
												and DATE_FORMAT(b.at_timestamp, '%Y-%m-%dT%H:%i') < DATE_FORMAT(attendance.at_timestamp + INTERVAL 24 HOUR, '%Y-%m-%dT%H:%i') 
												and b.at_status = 'Out' and b.em_id = attendance.em_id ORDER by b.at_timestamp desc limit 1),
												(select b.at_timestamp from attendance as b where DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') and b.at_status = 'In' and b.em_id = attendance.em_id ORDER by b.at_timestamp asc limit 1)
												)) as total_hour");
		$this->db->select("(select c.gt_name from attendance as b LEFT JOIN gate as c on b.gt_id = c.gt_id where DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') and b.at_status = 'In' and b.em_id = attendance.em_id ORDER by b.at_timestamp asc limit 1) as gate_in");
		$this->db->select("(select c.gt_name from attendance as b LEFT JOIN gate as c on b.gt_id = c.gt_id where DATE_FORMAT(b.at_timestamp, '%Y-%m-%dT%H:%i') > DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%dT%H:%i') 
												and DATE_FORMAT(b.at_timestamp, '%Y-%m-%dT%H:%i') < DATE_FORMAT(attendance.at_timestamp + INTERVAL 24 HOUR, '%Y-%m-%dT%H:%i') 
												and b.at_status = 'Out' and b.em_id = attendance.em_id ORDER by b.at_timestamp desc limit 1) as gate_out");
		$this->db->from('attendance');
		$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
		$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
		$this->db->where('attendance.co_id', $companyId);

		if (!empty($gate)) {
			$this->db->where('attendance.gt_id', $gate);
		}

		if (!empty($periodType)) {
			if ($periodType == 'range') {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') >= ", date("Y-m-d",strtotime($startDate)));
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') <= ", date("Y-m-d",strtotime($endDate)));
			} elseif($periodType == 'today') {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			} else {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now' - INTERVAL 1 DAY, '%Y-%m-%d')");
			}
		}
		
		$this->db->group_by(array("attendance.em_id", "DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d')")); 

		// Keep Query Alive
		$this->db->stop_cache();

		$query = $this->db->get();
		$totalRow = $query->num_rows();
		$pureAttendances = $query->result_array();

		$this->db->limit($limit, $page);
		$query = $this->db->get();
		$attendances = $query->result_array();
		$query->free_result();
		$this->db->reset_query();
		$this->db->flush_cache();

		$data = array();
		$data['gates'] = $gates;
		$data['type'] = $type;
		$data['gate_id'] = $gate;
		$data['periodType'] = $periodType;
		$data['startDate'] = $startDate;
		$data['endDate'] = $endDate;
		$data['attendances'] = $attendances;
		$data['pureAttendances'] = $pureAttendances;
		$data['jsonAttendances'] = json_encode($attendances);
		$data['config_pagination'] = $this->fn_init->config_pagination(
			array('total_row'=>$totalRow,'url'=>base_url('attendance/preview/'),
																			'uri_segment'=>3,'per_page'=>$limit)
		);
		$data['num'] = $page+1;

		if (!empty($submitType) && $submitType == 'export') {
			$this->load->library("excel");
			$spreadsheet = new PHPExcel();

			// Set document properties
			$spreadsheet->getProperties()->setCreator('Andoyo - Java Web Media')
			->setLastModifiedBy('Andoyo - Java Web Medi')
			->setTitle('Office 2007 XLSX Test Document')
			->setSubject('Office 2007 XLSX Test Document')
			->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
			->setKeywords('office 2007 openxml php')
			->setCategory('Test result file');

			// Add some data
			$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A1', '#')
			->setCellValue('B1', 'Date')
			->setCellValue('C1', 'Name')
			->setCellValue('D1', 'NIK')
			->setCellValue('E1', 'Time In')
			->setCellValue('F1', 'Time Out')
			->setCellValue('G1', 'Gate In')
			->setCellValue('H1', 'Gate Out')
			->setCellValue('I1', 'Total Hours');

			// Miscellaneous glyphs, UTF-8
			$i=2;
			$counting = 1;
			foreach($data['pureAttendances'] as $attendance) {
				$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $counting++)
				->setCellValue('B'.$i, $attendance['at_timestamp'] ? $attendance['at_timestamp'] : '-')
				->setCellValue('C'.$i, $attendance['em_name'] ? $attendance['em_name'] : '-')
				->setCellValue('D'.$i, $attendance['em_nik'] ? $attendance['em_nik'] : '-')
				->setCellValue('E'.$i, $attendance['time_in'] ? $attendance['time_in'] : '-')
				->setCellValue('F'.$i, $attendance['time_out'] ? $attendance['time_out'] : '-')
				->setCellValue('G'.$i, $attendance['gate_in'] ? $attendance['gate_in'] : '-')
				->setCellValue('H'.$i, $attendance['gate_out'] ? $attendance['gate_out'] : '-')
				->setCellValue('I'.$i, $attendance['total_hour'] ? $attendance['total_hour'] : '-');
				$i++;
			}

			// Rename worksheet
			$spreadsheet->getActiveSheet()->setTitle('Attendance '.date('d-m-Y H'));

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$spreadsheet->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');
			
			date_default_timezone_set('Asia/Jakarta');
			$dateNow = date('d/m/Y');
			$fileName = "Attendance_FIFO_".$dateNow.".xls";
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'"');
			header('Cache-Control: max-age=0');

			$writer->save('php://output');
			
			// $this->load->view('pages/apps/attendance/export/excel/fifo', $data);
		} else {
			$this->load->view('pages/apps/attendance/export/preview/fifo', $data);
		}
	}

	private function previewOvertime($type, $gate, $periodType, $startDate, $endDate, $gates, $submitType, $page, $limit = 10){
		$companyId = $this->session->userdata('id');
		$now =  date('Y-m-d H:i:s');

		// Start of the query you want to re-use
		$this->db->start_cache();
		$this->db->select("attendance.*, gate.gt_name as gt_name, employee.em_name as em_name");
		$this->db->select("(select at_timestamp from attendance as b where DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.at_timestamp, '%Y-%m-%d') and b.at_status = attendance.at_status and b.em_id = attendance.em_id ORDER by b.at_timestamp desc limit 1) as at_timestamp");
		$this->db->from('attendance');
		$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
		$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
		$this->db->where('attendance.co_id', $companyId);
		$this->db->where('attendance.at_status', 'Overtime');

		if (!empty($gate)) {
			$this->db->where('attendance.gt_id', $gate);
		}

		if (!empty($periodType)) {
			if ($periodType == 'range') {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') >= ", date("Y-m-d",strtotime($startDate)));
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') <= ", date("Y-m-d",strtotime($endDate)));
			} elseif($periodType == 'today') {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			} else {
				$this->db->where("DATE_FORMAT(at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now' - INTERVAL 1 DAY, '%Y-%m-%d')");
			}
		}
		
		$this->db->group_by(array("attendance.em_id", "attendance.at_status", "DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d')")); 

		// Keep Query Alive
		$this->db->stop_cache();

		$query = $this->db->get();
		$totalRow = $query->num_rows();
		$pureAttendances = $query->result_array();

		$this->db->limit($limit, $page);
		$query = $this->db->get();
		$attendances = $query->result_array();
		$query->free_result();
		$this->db->reset_query();
		$this->db->flush_cache();

		$data = array();
		$data['gates'] = $gates;
		$data['type'] = $type;
		$data['gate_id'] = $gate;
		$data['periodType'] = $periodType;
		$data['startDate'] = $startDate;
		$data['endDate'] = $endDate;
		$data['attendances'] = $attendances;
		$data['pureAttendances'] = $pureAttendances;
		$data['jsonAttendances'] = json_encode($attendances);
		$data['config_pagination'] = $this->fn_init->config_pagination(
			array('total_row'=>$totalRow,'url'=>base_url('attendance/preview/'),
																			'uri_segment'=>3,'per_page'=>$limit)
		);
		$data['num'] = $page+1;

		if (!empty($submitType) && $submitType == 'export') {
			$this->load->library("excel");
			$spreadsheet = new PHPExcel();

			// Set document properties
			$spreadsheet->getProperties()->setCreator('Andoyo - Java Web Media')
			->setLastModifiedBy('Andoyo - Java Web Medi')
			->setTitle('Office 2007 XLSX Test Document')
			->setSubject('Office 2007 XLSX Test Document')
			->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
			->setKeywords('office 2007 openxml php')
			->setCategory('Test result file');

			// Add some data
			$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A1', '#')
			->setCellValue('B1', 'NIK')
			->setCellValue('C1', 'Name')
			->setCellValue('D1', 'Date time');

			// Miscellaneous glyphs, UTF-8
			$i=2;
			$counting = 1;
			foreach($data['pureAttendances'] as $attendance) {
				$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $counting++)
				->setCellValue('B'.$i, $attendance['em_nik'] ? $attendance['em_nik'] : '-')
				->setCellValue('C'.$i, $attendance['em_name'] ? $attendance['em_name'] : '-')
				->setCellValue('D'.$i, $attendance['at_timestamp'] ? $attendance['at_timestamp'] : '-');
				$i++;
			}

			// Rename worksheet
			$spreadsheet->getActiveSheet()->setTitle('Attendance '.date('d-m-Y H'));

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$spreadsheet->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');
			
			date_default_timezone_set('Asia/Jakarta');
			$dateNow = date('d/m/Y');
			$fileName = "Attendance_OVERTIME_".$dateNow.".xls";
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'"');
			header('Cache-Control: max-age=0');

			$writer->save('php://output');
			// $this->load->view('pages/apps/attendance/export/excel/overtime', $data);	
		} else {
			$this->load->view('pages/apps/attendance/export/preview/overtime', $data);
		}
	}

	private function getNewData(){
		$companyId = $this->session->userdata('id');
		$attendance = array();

		$this->db->select("COUNT(at_id) as new, MAX(at_id) as last_id");
		$this->db->from('attendance');
		$this->db->where('co_id', $companyId);
		$this->db->where('at_id > ', $this->input->post('last_id'));
		$this->db->limit(1);
		$query = $this->db->get();
		$attendance = $query->row_array();
		$this->db->reset_query();	

		echo json_encode($attendance);
	}
}