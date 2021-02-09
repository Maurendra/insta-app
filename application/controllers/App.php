<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Apps extends CI_Controller {

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
		$this->load->css('assets/vendor/bootstrap-select-1.13.0-dev/dist/css/bootstrap-select.min.css');
		$this->load->css('assets/vendor/keen/css/style.bundle.css');
		
		$this->load->js('assets/vendor/jquery/jquery.min.js');
		$this->load->js('assets/js/utils.js');
		$this->load->js('assets/vendor/bootstrap-4.4.1-dist/js/bootstrap.bundle.js');
		$this->load->js('assets/vendor/chart.js-2.9.3/Chart.bundle.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/bootstrap-select.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/i18n/defaults-id_ID.min.js');
		$this->load->js('assets/js/Chart.roundedBarCharts.min.js');
		$this->load->js('assets/js/dashboard.js');
	}

	public function index(){
		$this->output->set_title('Attendance - Dashboard');
		$this->load->view('pages/apps/dashboard/dashboard');	
	}

	public function api($params = ''){
		$this->output->unset_template('dashboard');
		$now =  date('Y-m-d H:i:s');
		if ($params == 'today') {
			$companyId = $this->session->userdata('id');

			$in = $this->input->post('in');
			$out = $this->input->post('out');

			$this->db->select("MAX(attendance.at_timestamp) as last_update");
			$this->db->select("(select COUNT(DISTINCT a.em_id) as at_in from attendance as a where a.co_id = attendance.co_id and a.at_status = 'In' and DATE_FORMAT(a.at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')) as at_in");
			$this->db->select("(select COUNT(DISTINCT a.em_id) as at_out from attendance as a where a.co_id = attendance.co_id and a.at_status = 'Out' and DATE_FORMAT(a.at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')) as at_out");
			$this->db->from('attendance');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			$query = $this->db->get();
			$attendance = $query->row_array();
			$this->db->reset_query();

			$this->db->select("MAX(attendance_gps.atg_timestamp) as last_update");
			$this->db->select("(select COUNT(DISTINCT a.em_id) as atg_in from attendance_gps as a where a.co_id = attendance_gps.co_id and a.atg_status = 'In' and DATE_FORMAT(a.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')) as atg_in");
			$this->db->select("(select COUNT(DISTINCT a.em_id) as atg_out from attendance_gps as a where a.co_id = attendance_gps.co_id and a.atg_status = 'Out' and DATE_FORMAT(a.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')) as atg_out");
			$this->db->from('attendance_gps');
			$this->db->where('co_id', $companyId);
			$this->db->where("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT('$now', '%Y-%m-%d')");
			$query = $this->db->get();
			$attendanceGps = $query->row_array();
			$this->db->reset_query();

			$totalIn = intval($attendance['at_in']) + intval($attendanceGps['atg_in']);
			$totalOut = intval($attendance['at_out']) + intval($attendanceGps['atg_out']);

			$isUpdated = (($totalIn != $in) || ($totalOut != $out)) ? true : false;

			$data = array();
			$data['in'] = $totalIn;
			$data['out'] = $totalOut;
			$data['updated'] = $isUpdated;

			echo json_encode($data);
		} elseif ($params == 'employee') {
			$companyId = $this->session->userdata('id');
			
			$total = $this->input->post('total');

			$this->db->select("count(attendance.em_id) as employee_attendance");
			$this->db->from('attendance');
			$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('employee.co_id', 0);
			$this->db->group_by("attendance.em_id");
			$query = $this->db->get();
			$attendance = $query->row_array();
			$this->db->reset_query();

			$this->db->select("COUNT(em_id) as employee_gps");
			$this->db->from('employee');
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$attendanceGps = $query->row_array();
			$this->db->reset_query();

			$totalEmployee = intval($attendance['employee_attendance']) + intval($attendanceGps['employee_gps']);
			
			$isUpdated = ($total != $totalEmployee) ? true : false;

			$data = array();
			$data['employee'] = $totalEmployee;
			$data['updated'] = $isUpdated;

			echo json_encode($data);
		} elseif ($params == 'gender') {
			$companyId = $this->session->userdata('id');
			
			$female = $this->input->post('female');
			$male = $this->input->post('male');

			$this->db->select("MAX(attendance.at_timestamp) as last_update");
			$this->db->select("(select count(a.em_id) as employee_male from attendance as a left join employee as b on a.em_id = b.em_id where a.co_id = attendance.co_id and b.em_gender = 'Male' and b.co_id = employee.co_id group by b.em_gender, a.em_id) as employee_male");
			$this->db->select("(select count(a.em_id) as employee_male from attendance as a left join employee as b on a.em_id = b.em_id where a.co_id = attendance.co_id and b.em_gender = 'Female' and b.co_id = employee.co_id group by b.em_gender, a.em_id) as employee_female");
			$this->db->from('attendance');
			$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('employee.co_id', 0);
			$query = $this->db->get();
			$attendance = $query->row_array();
			$this->db->reset_query();

			$this->db->select("MAX(employee.em_lastupdate) as last_update");
			$this->db->select("(select COUNT(a.em_id) as employee_gps_male from employee as a where a.co_id = employee.co_id and a.em_gender = 'Male' limit 1) as employee_gps_male");
			$this->db->select("(select COUNT(a.em_id) as employee_gps_female from employee as a where a.co_id = employee.co_id and a.em_gender = 'Female' limit 1) as employee_gps_female");
			$this->db->from('employee');
			$this->db->where('co_id', $companyId);
			$query = $this->db->get();
			$attendanceGps = $query->row_array();
			$this->db->reset_query();

			$totalMale = intval($attendance['employee_male']) + intval($attendanceGps['employee_gps_male']);
			$totalFemale = intval($attendance['employee_female']) + intval($attendanceGps['employee_gps_female']);

			$isUpdated = (($totalMale != $male) || ($totalFemale != $female)) ? true : false;

			$data = array();
			$data['Female'] = $totalFemale;
			$data['Male'] = $totalMale;
			$data['updated'] = $isUpdated;

			echo json_encode($data);
		} elseif ($params == 'gate') {
			$companyId = $this->session->userdata('id');

			$gateName = $this->input->post('gateName');
			$gateData = $this->input->post('gateData');

			$this->db->select("gate.gt_id, gate.gt_name, count(*) as count");
			$this->db->from('attendance');
			$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') >= DATE_FORMAT('$now' - INTERVAL 14 DAY, '%Y-%m-%d')");
			$this->db->group_by("gate.gt_id");
			$query = $this->db->get();
			$gates = $query->result_array();
			$this->db->reset_query();

			$isUpdated = false;
			$gtName = array();
			$gtData = array();
			foreach ($gates as $gate) {
				array_push($gtName, $gate['gt_name']);
				array_push($gtData, $gate['count']);
			}

			if (($gateName != $gtName) || ($gateData != $gtData)) {
				$isUpdated = true;
			}

			$data = array();
			$data['gate'] = $gates;
			$data['updated'] = $isUpdated;

			echo json_encode($data);
		} elseif ($params == 'week') {
			$companyId = $this->session->userdata('id');

			$weeklyLabels = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', "Friday", 'Saturday', 'Sunday');
			$inData = array(0, 0, 0, 0, 0, 0, 0);
			$outData = array(0, 0, 0, 0, 0, 0, 0);

			$in = $this->input->post('in');
			$out = $this->input->post('out');

			$this->db->select("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') as date, DAYNAME(attendance.at_timestamp) as day_name");
			$this->db->select("(select count(Distinct a.em_id) as in_count from attendance as a where a.co_id = attendance.co_id and a.at_status = 'In' and DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(a.at_timestamp, '%Y-%m-%d') group by DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') limit 1) as in_count");
			$this->db->select("(select count(Distinct a.em_id) as out_count from attendance as a where a.co_id = attendance.co_id and a.at_status = 'Out' and DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') = DATE_FORMAT(a.at_timestamp, '%Y-%m-%d') group by DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d') limit 1) as out_count");
			$this->db->from('attendance');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where("attendance.at_timestamp > curdate() - INTERVAL DAYOFWEEK(curdate())- 2 DAY");
			$this->db->group_by("DATE_FORMAT(attendance.at_timestamp, '%Y-%m-%d')");
			$query_1 = $this->db->get_compiled_select();

			$this->db->select("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') as date, DAYNAME(attendance_gps.atg_timestamp) as day_name");
			$this->db->select("(select count(Distinct a.em_id) as in_count from attendance_gps as a where a.co_id = attendance_gps.co_id and a.atg_status = 'In' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(a.atg_timestamp, '%Y-%m-%d') group by DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') limit 1) as in_count");
			$this->db->select("(select count(Distinct a.em_id) as out_count from attendance_gps as a where a.co_id = attendance_gps.co_id and a.atg_status = 'Out' and DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') = DATE_FORMAT(a.atg_timestamp, '%Y-%m-%d') group by DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d') limit 1) as out_count");
			$this->db->from('attendance_gps');
			$this->db->where('attendance_gps.co_id', $companyId);
			$this->db->where("attendance_gps.atg_timestamp > curdate() - INTERVAL DAYOFWEEK(curdate())- 2 DAY");
			$this->db->group_by("DATE_FORMAT(attendance_gps.atg_timestamp, '%Y-%m-%d')");
			$query_2 = $this->db->get_compiled_select();
			
			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2 . ' ORDER BY date');
			$attendance = $final_query->result_array();
			$this->db->reset_query();
			
			
			foreach ($weeklyLabels as $key => $value) {
				foreach ($attendance as $key2 => $value2) {
					if ($value2['day_name'] == $value) {
						$inData[$key] += intval($value2['in_count']);
						$outData[$key] += intval($value2['out_count']);
					}
				}
			}
			

			$isUpdated = false;

			if (($inData != $in) || ($outData != $out)) {
				$isUpdated = true;
			}

			$data = array();
			$data['weeklyLabels'] = $weeklyLabels;
			$data['inData'] = $inData;
			$data['outData'] = $outData;
			$data['updated'] = $isUpdated;
			
			$data['data'] = $attendance;

			echo json_encode($data);
		} elseif ($params == 'latest') {
			$companyId = $this->session->userdata('id');

			$this->db->distinct();
			$this->db->select("employee.em_name as em_name, attendance.at_status as status, attendance.at_timestamp as date");
			$this->db->from('attendance');
			$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
			$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
			$this->db->where('attendance.co_id', $companyId);
			$query_1 = $this->db->get_compiled_select();

			$this->db->distinct();
			$this->db->select("employee.em_name as em_name, attendance_gps.atg_status as status, attendance_gps.atg_timestamp as date");
			$this->db->from('attendance_gps');
			$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
			$this->db->where('attendance_gps.co_id', $companyId);
			$query_2 = $this->db->get_compiled_select();

			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2 . ' ORDER BY date desc limit 5');
			$attendance = $final_query->result_array();
			$this->db->reset_query();

			$this->db->select("employee.em_name as em_name, attendance.at_status as status, attendance.at_timestamp as date");
			$this->db->from('attendance');
			$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
			$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('attendance.at_status', 'In');
			$query_1 = $this->db->get_compiled_select();

			$this->db->select("employee.em_name as em_name, attendance_gps.atg_status as status, attendance_gps.atg_timestamp as date");
			$this->db->from('attendance_gps');
			$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
			$this->db->where('attendance_gps.co_id', $companyId);
			$this->db->where('attendance_gps.atg_status', 'In');
			$query_2 = $this->db->get_compiled_select();

			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2 . ' ORDER BY date desc limit 5');
			$attendanceIn = $final_query->result_array();
			$this->db->reset_query();

			$this->db->select("employee.em_name as em_name, attendance.at_status as status, attendance.at_timestamp as date");
			$this->db->from('attendance');
			$this->db->join('gate', 'attendance.gt_id = gate.gt_id', 'left');
			$this->db->join('employee', 'attendance.em_id = employee.em_id', 'left');
			$this->db->where('attendance.co_id', $companyId);
			$this->db->where('attendance.at_status', 'Out');
			$query_1 = $this->db->get_compiled_select();

			$this->db->select("employee.em_name as em_name, attendance_gps.atg_status as status, attendance_gps.atg_timestamp as date");
			$this->db->from('attendance_gps');
			$this->db->join('employee', 'attendance_gps.em_id = employee.em_id', 'left');
			$this->db->where('attendance_gps.co_id', $companyId);
			$this->db->where('attendance_gps.atg_status', 'Out');
			$query_2 = $this->db->get_compiled_select();

			$final_query = $this->db->query($query_1 . ' UNION ALL ' . $query_2 . ' ORDER BY date desc limit 5');
			$attendanceOut = $final_query->result_array();
			$this->db->reset_query();

			$isUpdated = true;
			
			$data = array();
			$data['all'] = $attendance;
			$data['in'] = $attendanceIn;
			$data['out'] = $attendanceOut;
			$data['updated'] = $isUpdated;

			echo json_encode($data);
		}
	}
}