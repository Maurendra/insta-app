<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Attendancegps extends CI_Controller {

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
		$this->output->set_title('Attendance - Attendance GPS List');
		
		$companyId = $this->session->userdata('id');

		$this->db->select("MAX(atg_id) as last_id");
		$this->db->from('attendance_gps');
		$this->db->where('co_id', $companyId);
		$this->db->limit(1);
		$query = $this->db->get();
		$attendances = $query->row_array();
		$this->db->reset_query();		

		$data = array();
		$data['attendance'] = $attendances;

		$this->load->view('pages/apps/attendance_gps/list', $data);	
	}

	public function delete($id = 0){
		$companyId = $this->session->userdata('id');
    if (empty($id) || empty($companyId)) {
			$session_data = array(
				'message'   => 'Id not found',
				'message_status'   => 'info'
			);
			$this->session->set_userdata($session_data);
			redirect('attendancegps');
		} else {
			$update_data = array(
				'co_id' => 0
      );
			
			// Update Data
			$this->db->where('co_id', $companyId);
			$this->db->where('atg_id', $id);
			$this->db->update('attendance_gps',$update_data);

			$session_data = array(
				'message'   => 'Data successfully updated',
				'message_status'   => 'success'
			);
			$this->session->set_userdata($session_data);

			redirect('attendancegps');
		}
  }

	public function api($params){
		$this->output->unset_template('dashboard');
		if ($params == 'getAllAttendances') {
			$this->getAllAttendances();
		} elseif ($params == 'getQRCode') {
      $companyId = $this->session->userdata('id');
    
      $this->load->library('ciqrcode');

      /* Generate Token */
      $arr = array('company_code'=>$companyId,'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 1 hours"));
      $token = $this->jwt->encode($arr);

      $params = array();
      $params['data'] = $token;
      $params['level'] = 'H';
      $params['size'] = 5;
      $params['savename'] = 'assets/images/tempQRCode.png';
      $this->ciqrcode->generate($params);

      $data = array();
      $data['QRCode'] = 'assets/images/tempQRCode.png';

      echo json_encode($data);
    } elseif ($params == 'getNewData') {
			$this->getNewData();
		}
	}

	private function query_attendance(){
		$companyId = $this->session->userdata('id');

		// Initialize needed variable
		$column_search = array('employee.em_name', 'attendance_gps.em_nik', 'attendance_gps.atg_lat', 'attendance_gps.atg_lng', 'attendance_gps.cl_name', 'attendance_gps.atg_status', 'attendance_gps.atg_timestamp', 'attendance_gps.atg_photo');
		$column_order = array(null, 'employee.em_name', 'attendance_gps.em_nik', 'attendance_gps.atg_lat', 'attendance_gps.atg_lng', 'attendance_gps.cl_name', 'attendance_gps.atg_status', 'attendance_gps.atg_timestamp', 'attendance_gps.atg_photo', null);
		$order = array('attendance_gps.atg_id' => 'desc');

		// Get Query
		$this->db->select("attendance_gps.*, employee.em_name as em_name");
		$this->db->select("CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(attendance_gps.em_id/100)*100), '/', attendance_gps.em_id, '/', YEAR(attendance_gps.atg_timestamp), '/', IF(MONTH(attendance_gps.atg_timestamp) > 9, MONTH(attendance_gps.atg_timestamp), CONCAT(0, MONTH(attendance_gps.atg_timestamp))), '/', DAY(attendance_gps.atg_timestamp), '/', attendance_gps.atg_photo) as photo");
		$this->db->from('attendance_gps');
		$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
		$this->db->where('attendance_gps.co_id', $companyId);

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
				$row[] = $field->atg_lat;
				$row[] = $field->atg_lng;
				$row[] = $field->cl_name;
				$row[] = $field->atg_status;
				$row[] = $field->atg_timestamp;
				if (!empty($field->atg_photo)) {
					$row[] = "<img class='mr-2 d-inline-block align-top' style='height:auto;max-height:60px;width:50px;margin-right: 10px;margin-top: 5px;' src = '$field->photo' />";
				} else {
					$row[] = "No photo found";
				}
				$row[] = "<a class='btn btn-danger btn-sm modal-details mr-2' role='button'  
				href='".base_url('attendancegps/delete/'.$field->atg_id)."' onclick=\"return confirm('Anda yakin menghapus data ini?')\">Delete</a>";

				$data[] = $row;
		}

		$this->db->reset_query();

		// Count all data
		$this->db->select("attendance_gps.atg_id");
		$this->db->from('attendance_gps');
		$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
		$this->db->where('attendance_gps.co_id', $companyId);
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
		$periodType = $this->input->get('ex-period');
		$startDate = $this->input->get('ex-startdate');
		$endDate = $this->input->get('ex-enddate');
    $submitType = $this->input->get('submit-type');
    
    if ($page == 1) {
      $page = 0;
    }

		if (!empty($type)) {
			if ($type == 'overtime') {
				$this->previewOvertime($type, $periodType, $startDate, $endDate, $submitType, $page, $limit);
			} elseif ($type == 'fifo') {
				$this->previewFifo($type, $periodType, $startDate, $endDate, $submitType, $page, $limit);
			} else {
				$this->previewAll($type, $periodType, $startDate, $endDate, $submitType, $page, $limit);
			}
		} else {
			redirect('attendancegps');
		}
	}

	private function previewAll($type, $periodType, $startDate, $endDate, $submitType, $page, $limit = 10){
		$companyId = $this->session->userdata('id');
		$now =  date('Y-m-d H:i:s');

		// Start of the query you want to re-use
		$this->db->start_cache();
		$this->db->select("attendance_gps.*, employee.em_name as em_name");
		$this->db->select("IF(attendance_gps.atg_status = 'In', 
                        (select b.atg_timestamp from attendance_gps as b where b.em_id = attendance_gps.em_id and b.atg_status = 'In' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') ORDER by b.atg_timestamp asc limit 1)
                        , IF(attendance_gps.atg_status = 'Out', 
                          (select b.atg_timestamp from attendance_gps as b where b.em_id = attendance_gps.em_id and b.atg_status = 'Out' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') ORDER by b.atg_timestamp desc limit 1)
                          , IF(attendance_gps.atg_status = 'Overtime',
                            (select b.atg_timestamp from attendance_gps as b where b.em_id = attendance_gps.em_id and b.atg_status = 'Overtime' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') ORDER by b.atg_timestamp desc limit 1)
                            ,attendance_gps.atg_timestamp)
                          ) 
                      ) as atg_timestamp");
		$this->db->select("IF(attendance_gps.atg_status = 'In', 
												(select IF(b.atg_photo != '', (CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(b.em_id/100)*100), '/', b.em_id,'/', YEAR(b.atg_timestamp), '/', IF(MONTH(b.atg_timestamp) > 9, MONTH(b.atg_timestamp), CONCAT(0, MONTH(b.atg_timestamp))), '/', DAY(b.atg_timestamp), '/', b.atg_photo)), '' ) from attendance_gps as b where b.em_id = attendance_gps.em_id and b.atg_status = 'In' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') ORDER by b.atg_timestamp asc limit 1)
												, IF(attendance_gps.atg_status = 'Out', 
													(select IF(b.atg_photo != '', (CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(b.em_id/100)*100), '/', b.em_id,'/', YEAR(b.atg_timestamp), '/', IF(MONTH(b.atg_timestamp) > 9, MONTH(b.atg_timestamp), CONCAT(0, MONTH(b.atg_timestamp))), '/', DAY(b.atg_timestamp), '/', b.atg_photo)), '' ) from attendance_gps as b where b.em_id = attendance_gps.em_id and b.atg_status = 'Out' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') ORDER by b.atg_timestamp desc limit 1)
													, IF(attendance_gps.atg_status = 'Overtime',
														(select IF(b.atg_photo != '', (CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(b.em_id/100)*100), '/', b.em_id,'/', YEAR(b.atg_timestamp), '/', IF(MONTH(b.atg_timestamp) > 9, MONTH(b.atg_timestamp), CONCAT(0, MONTH(b.atg_timestamp))), '/', DAY(b.atg_timestamp), '/', b.atg_photo)), '' ) from attendance_gps as b where b.em_id = attendance_gps.em_id and b.atg_status = 'Overtime' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') ORDER by b.atg_timestamp desc limit 1)
														,IF(attendance_gps.atg_photo, (CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(attendance_gps.em_id/100)*100), '/', attendance_gps.em_id, '/', YEAR(attendance_gps.atg_timestamp), '/', MONTH(attendance_gps.atg_timestamp), '/', DAY(attendance_gps.atg_timestamp), '/', attendance_gps.atg_photo)), '' ))
													) 
											) as photo");
    $this->db->from('attendance_gps');
		$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
		$this->db->where('attendance_gps.co_id', $companyId);

		if (!empty($periodType)) {
			if ($periodType == 'range') {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') >= ", date("Y-m-d",strtotime($startDate)));
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') <= ", date("Y-m-d",strtotime($endDate)));
			} elseif($periodType == 'today') {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			} else {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now' - INTERVAL 1 DAY, '%Y-%m-%d')");
			}
		}
		
		$this->db->group_by(array("attendance_gps.em_id", "attendance_gps.atg_status", "DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d')"));

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
		$data['type'] = $type;
		$data['periodType'] = $periodType;
		$data['startDate'] = $startDate;
		$data['endDate'] = $endDate;
		$data['attendances'] = $attendances;
		$data['pureAttendances'] = $pureAttendances;
		$data['jsonAttendances'] = json_encode($attendances);
		$data['config_pagination'] = $this->fn_init->config_pagination(
			array('total_row'=>$totalRow,'url'=>base_url('attendancegps/preview/'),
																			'uri_segment'=>3,'per_page'=>$limit)
		);
		$data['num'] = $page+1;

		if (!empty($submitType) && $submitType == 'export') {
			
			$this->load->library("excel");
			$spreadsheet = new PHPExcel();

			// Set document properties
			$spreadsheet->getProperties()->setCreator('Excelsoft')
			->setLastModifiedBy('Excelsoft')
			->setTitle('Office 2007 XLSX Test Document')
			->setSubject('Office 2007 XLSX Test Document')
			->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
			->setKeywords('office 2007 openxml php')
			->setCategory('Attendance Data');

			// Add some data
			$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A1', '#')
			->setCellValue('B1', 'Date time')
			->setCellValue('C1', 'NIK')
			->setCellValue('D1', 'Name')
			->setCellValue('E1', 'Status')
			->setCellValue('F1', 'Client Name')
			->setCellValue('G1', 'Notes')
			->setCellValue('H1', 'Latitude')
			->setCellValue('I1', 'Longitude')
			->setCellValue('J1', 'Longitude');

			// Miscellaneous glyphs, UTF-8
			$i=2;
			$counting = 1;
			foreach($data['pureAttendances'] as $attendance) {
				$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $counting++)
				->setCellValue('B'.$i, $attendance['atg_timestamp'] ? $attendance['atg_timestamp'] : '-')
				->setCellValue('C'.$i, $attendance['em_nik'] ? $attendance['em_nik'] : '-')
				->setCellValue('D'.$i, $attendance['em_name'] ? $attendance['em_name'] : '-')
				->setCellValue('E'.$i, $attendance['atg_status'] ? $attendance['atg_status'] : '-')
				->setCellValue('F'.$i, $attendance['cl_name'] ? $attendance['cl_name'] : '-')
				->setCellValue('G'.$i, $attendance['atg_notes'] ? $attendance['atg_notes'] : '-')
				->setCellValue('H'.$i, $attendance['atg_lat'] ? $attendance['atg_lat'] : '-')
				->setCellValue('I'.$i, $attendance['atg_lng'] ? $attendance['atg_lng'] : '-')
				->setCellValue('J'.$i, $attendance['photo'] ? $attendance['photo'] : '-');
				$i++;
			}

			// Rename worksheet
			$spreadsheet->getActiveSheet()->setTitle('Attendance GPS '.date('d-m-Y H'));

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$spreadsheet->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');

			date_default_timezone_set('Asia/Jakarta');
			$dateNow = date('d/m/Y');
			$fileName = "Attendance_GPS_All_".$dateNow.".xls";
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'"');
			header('Cache-Control: max-age=0');

			$writer->save('php://output');
		} else {
			$this->load->view('pages/apps/attendance_gps/export/preview/all', $data);	
		}
	}

	private function previewFifo($type, $periodType, $startDate, $endDate, $submitType, $page, $limit = 10){
		$companyId = $this->session->userdata('id');
		$now =  date('Y-m-d H:i:s');

		// Start of the query you want to re-use
		$this->db->start_cache();
		$this->db->select("attendance_gps.*, employee.em_name as em_name, DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') as atg_timestamp");
		$this->db->select("(select IF(b.atg_photo != '', (CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(b.em_id/100)*100), '/', b.em_id,'/', YEAR(b.atg_timestamp), '/', IF(MONTH(b.atg_timestamp) > 9, MONTH(b.atg_timestamp), CONCAT(0, MONTH(b.atg_timestamp))), '/', DAY(b.atg_timestamp), '/', b.atg_photo)), '' ) from attendance_gps as b where DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') and b.atg_status = 'In' and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp asc limit 1) as photo");
		$this->db->select("(select b.atg_timestamp from attendance_gps as b where DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') and b.atg_status = 'In' and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp asc limit 1) as time_in");
		$this->db->select("(select b.atg_timestamp from attendance_gps as b where DATE_FORMAT(b.atg_timestamp, '%Y-%m-%dT%H:%i') > DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%dT%H:%i') 
												and DATE_FORMAT(b.atg_timestamp, '%Y-%m-%dT%H:%i') < DATE_FORMAT(attendance_gps.atg_timestamp + INTERVAL 24 HOUR, '%Y-%m-%dT%H:%i') 
												and b.atg_status = 'Out' and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp desc limit 1) as time_out");
		$this->db->select("(select TIMEDIFF(
												(select b.atg_timestamp from attendance_gps as b where DATE_FORMAT(b.atg_timestamp, '%Y-%m-%dT%H:%i') > DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%dT%H:%i') 
												and DATE_FORMAT(b.atg_timestamp, '%Y-%m-%dT%H:%i') < DATE_FORMAT(attendance_gps.atg_timestamp + INTERVAL 24 HOUR, '%Y-%m-%dT%H:%i') 
												and b.atg_status = 'Out' and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp desc limit 1),
												(select b.atg_timestamp from attendance_gps as b where DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') and b.atg_status = 'In' and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp asc limit 1)
												)) as total_hour");
		$this->db->from('attendance_gps');
		$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
		$this->db->where('attendance_gps.co_id', $companyId);

		if (!empty($periodType)) {
			if ($periodType == 'range') {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') >= ", date("Y-m-d",strtotime($startDate)));
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') <= ", date("Y-m-d",strtotime($endDate)));
			} elseif($periodType == 'today') {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			} else {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now' - INTERVAL 1 DAY, '%Y-%m-%d')");
			}
		}
		
		$this->db->group_by(array("attendance_gps.em_id", "DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d')")); 

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
		$data['type'] = $type;
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
			$spreadsheet->getProperties()->setCreator('Excelsoft')
			->setLastModifiedBy('Excelsoft')
			->setTitle('Office 2007 XLSX Test Document')
			->setSubject('Office 2007 XLSX Test Document')
			->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
			->setKeywords('office 2007 openxml php')
			->setCategory('Attendance Data');

			// Add some data
			$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A1', '#')
			->setCellValue('B1', 'Date')
			->setCellValue('C1', 'Name')
			->setCellValue('D1', 'NIK')
			->setCellValue('E1', 'Time In')
			->setCellValue('F1', 'Time Out')
			->setCellValue('G1', 'Total Hours')
			->setCellValue('H1', 'Client Name')
			->setCellValue('I1', 'Notes')
			->setCellValue('J1', 'Latitude')
			->setCellValue('K1', 'Longitude')
			->setCellValue('L1', 'Photo');

			// Miscellaneous glyphs, UTF-8
			$i=2;
			$counting = 1;
			foreach($data['pureAttendances'] as $attendance) {
				$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $counting++)
				->setCellValue('B'.$i, $attendance['atg_timestamp'] ? $attendance['atg_timestamp'] : '-')
				->setCellValue('C'.$i, $attendance['em_name'] ? $attendance['em_name'] : '-')
				->setCellValue('D'.$i, $attendance['em_nik'] ? $attendance['em_nik'] : '-')
				->setCellValue('E'.$i, $attendance['time_in'] ? $attendance['time_in'] : '-')
				->setCellValue('F'.$i, $attendance['time_out'] ? $attendance['time_out'] : '-')
				->setCellValue('G'.$i, $attendance['total_hour'] ? $attendance['total_hour'] : '-')
				->setCellValue('H'.$i, $attendance['cl_name'] ? $attendance['cl_name'] : '-')
				->setCellValue('I'.$i, $attendance['atg_notes'] ? $attendance['atg_notes'] : '-')
				->setCellValue('J'.$i, $attendance['atg_lat'] ? $attendance['atg_lat'] : '-')
				->setCellValue('K'.$i, $attendance['atg_lng'] ? $attendance['atg_lng'] : '-')
				->setCellValue('L'.$i, $attendance['photo'] ? $attendance['photo'] : '-');
				$i++;
			}

			// Rename worksheet
			$spreadsheet->getActiveSheet()->setTitle('Attendance GPS '.date('d-m-Y H'));

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$spreadsheet->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');
			
			date_default_timezone_set('Asia/Jakarta');
			$dateNow = date('d/m/Y');
			$fileName = "Attendance_GPS_FIFO_".$dateNow.".xls";
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'"');
			header('Cache-Control: max-age=0');

			$writer->save('php://output');
			
			// $this->load->view('pages/apps/attendance/export/excel/fifo', $data);
		} else {
			$this->load->view('pages/apps/attendance_gps/export/preview/fifo', $data);
		}
	}

	private function previewOvertime($type, $periodType, $startDate, $endDate, $submitType, $page, $limit = 10){
		$companyId = $this->session->userdata('id');
		$now =  date('Y-m-d H:i:s');

		// Start of the query you want to re-use
		$this->db->start_cache();
		$this->db->select("attendance_gps.*, employee.em_name as em_name");
		$this->db->select("(select atg_timestamp from attendance_gps as b where DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') and b.atg_status = attendance_gps.atg_status and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp desc limit 1) as atg_timestamp");
		$this->db->select("(select IF(b.atg_photo != '', (CONCAT('https://attendance.excelsoft.com/photos/att-gps/', (CEIL(b.em_id/100)*100), '/', b.em_id,'/', YEAR(b.atg_timestamp), '/', IF(MONTH(b.atg_timestamp) > 9, MONTH(b.atg_timestamp), CONCAT(0, MONTH(b.atg_timestamp))), '/', DAY(b.atg_timestamp), '/', b.atg_photo)), '' ) from attendance_gps as b where DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(b.atg_timestamp, '%Y-%m-%d') and b.atg_status = attendance_gps.atg_status and b.em_id = attendance_gps.em_id ORDER by b.atg_timestamp desc limit 1) as photo");
		$this->db->from('attendance_gps');
		$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
		$this->db->where('attendance_gps.co_id', $companyId);
		$this->db->where('attendance_gps.atg_status', 'Overtime');

		if (!empty($periodType)) {
			if ($periodType == 'range') {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') >= ", date("Y-m-d",strtotime($startDate)));
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') <= ", date("Y-m-d",strtotime($endDate)));
			} elseif($periodType == 'today') {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			} else {
				$this->db->where("DATE_FORMAT(atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now' - INTERVAL 1 DAY, '%Y-%m-%d')");
			}
		}
		
		$this->db->group_by(array("attendance_gps.em_id", "attendance_gps.atg_status", "DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d')")); 

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
		$data['type'] = $type;
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
			$spreadsheet->getProperties()->setCreator('Excelsoft')
			->setLastModifiedBy('Excelsoft')
			->setTitle('Office 2007 XLSX Test Document')
			->setSubject('Office 2007 XLSX Test Document')
			->setDescription('Test document for Office 2007 XLSX, generated using PHP classes.')
			->setKeywords('office 2007 openxml php')
			->setCategory('Attendance Data');

			// Add some data
			$spreadsheet->setActiveSheetIndex(0)
			->setCellValue('A1', '#')
			->setCellValue('B1', 'NIK')
			->setCellValue('C1', 'Name')
			->setCellValue('D1', 'Date time')
			->setCellValue('E1', 'Client Name')
			->setCellValue('F1', 'Notes')
			->setCellValue('G1', 'Latitude')
			->setCellValue('H1', 'Longitude')
			->setCellValue('I1', 'photo');

			// Miscellaneous glyphs, UTF-8
			$i=2;
			$counting = 1;
			foreach($data['pureAttendances'] as $attendance) {
				$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $counting++)
				->setCellValue('B'.$i, $attendance['em_nik'] ? $attendance['em_nik'] : '-')
				->setCellValue('C'.$i, $attendance['em_name'] ? $attendance['em_name'] : '-')
				->setCellValue('D'.$i, $attendance['atg_timestamp'] ? $attendance['atg_timestamp'] : '-')
				->setCellValue('E'.$i, $attendance['cl_name'] ? $attendance['cl_name'] : '-')
				->setCellValue('F'.$i, $attendance['atg_notes'] ? $attendance['atg_notes'] : '-')
				->setCellValue('G'.$i, $attendance['atg_lat'] ? $attendance['atg_lat'] : '-')
				->setCellValue('H'.$i, $attendance['atg_lng'] ? $attendance['atg_lng'] : '-')
				->setCellValue('I'.$i, $attendance['photo'] ? $attendance['photo'] : '-');
				$i++;
			}

			// Rename worksheet
			$spreadsheet->getActiveSheet()->setTitle('Attendance GPS '.date('d-m-Y H'));

			// Set active sheet index to the first sheet, so Excel opens this as the first sheet
			$spreadsheet->setActiveSheetIndex(0);

			$writer = PHPExcel_IOFactory::createWriter($spreadsheet, 'Excel5');
			
			date_default_timezone_set('Asia/Jakarta');
			$dateNow = date('d/m/Y');
			$fileName = "Attendance_GPS_OVERTIME_".$dateNow.".xls";
			header('Content-Type: application/vnd.ms-excel');
			header('Content-Disposition: attachment;filename="'.$fileName.'"');
			header('Cache-Control: max-age=0');

			$writer->save('php://output');
			// $this->load->view('pages/apps/attendance/export/excel/overtime', $data);	
		} else {
			$this->load->view('pages/apps/attendance_gps/export/preview/overtime', $data);
		}
  }
  
  public function generateQR(){
    $companyId = $this->session->userdata('id');
    
    $this->load->library('ciqrcode');

    /* Generate Token */
    $arr = array('company_code'=>$companyId,'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 1 hours"));
    $token = $this->jwt->encode($arr);

    $params['data'] = 'This is a text to encode become QR Code';
    $params['level'] = 'H';
    $params['size'] = 10;
    $params['savename'] = 'assets/images/tempQRCode.png';
    $this->ciqrcode->generate($params);
	}
	
	private function getNewData(){
		$companyId = $this->session->userdata('id');
		$attendance = array();

		$this->db->select("COUNT(atg_id) as new, MAX(atg_id) as last_id");
		$this->db->from('attendance_gps');
		$this->db->where('co_id', $companyId);
		$this->db->where('atg_id > ', $this->input->post('last_id'));
		$this->db->limit(1);
		$query = $this->db->get();
		$attendance = $query->row_array();
		$this->db->reset_query();	

		echo json_encode($attendance);
	}
}