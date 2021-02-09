<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Company extends CI_Controller {

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
    $this->output->set_title('Attendance - Company Setting');
    
		$this->load->css('assets/vendor/bootstrap-4.4.1-dist/css/bootstrap.min.css');
		$this->load->css('assets/vendor/fontawesome-free-5.6.3-web/css/all.min.css');
		$this->load->css('assets/css/custom-css.css');
		$this->load->css('assets/vendor/bootstrap-select-1.13.0-dev/dist/css/bootstrap-select.min.css');
		
		$this->load->js('assets/vendor/jquery/jquery.min.js');
		$this->load->js('assets/js/utils.js');
		$this->load->js('assets/vendor/bootstrap-4.4.1-dist/js/bootstrap.bundle.js');
		$this->load->js('assets/vendor/chart.js-2.9.3/Chart.bundle.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/bootstrap-select.min.js');
		$this->load->js('assets/vendor/bootstrap-select-1.13.0-dev/dist/js/i18n/defaults-id_ID.min.js');
		$this->load->js('assets/js/Chart.roundedBarCharts.min.js');
	}

	public function index(){
    $this->db->select('*');
    $this->db->from('company');
    $this->db->where('co_id', $this->session->userdata('id'));
    $query = $this->db->get();
    $data = $query->row_array();

		$this->load->view('pages/apps/company/setting', $data);	
  }
  
  public function update(){
    $companyId = $this->session->userdata('id');

    $isChangeAccount = $this->input->post('is-change-account');
    $update_data = array();

    if ($isChangeAccount == 0) {
      // No need to change account
      $update_data = array(
        'co_name' => $this->input->post('co-name') ? $this->input->post('co-name') : NULL,
        'co_addr1' => $this->input->post('co-addr1') ? $this->input->post('co-addr1') : NULL,
        'co_addr2' => $this->input->post('co-addr2') ? $this->input->post('co-addr2') : NULL,
        'co_country' => $this->input->post('co-country') ? $this->input->post('co-country') : NULL,
        'co_phone' => $this->input->post('co-phone') ? $this->input->post('co-phone') : NULL
      );
    } else {
      // Need to change account too
      $email = strtolower($this->input->post('co-email'));
      $password = password_hash($this->input->post('co-pass'), PASSWORD_DEFAULT);

      if (empty($email) && empty($password)) {
				$session_data = array(
					'message'   => 'Please fill email and password fields',
					'message_status'   => 'danger'
				);
        $this->session->set_userdata($session_data);
        redirect('company');
      } else {
        $update_data = array(
          'co_name' => $this->input->post('co-name') ? $this->input->post('co-name') : NULL,
          'co_addr1' => $this->input->post('co-addr1') ? $this->input->post('co-addr1') : NULL,
          'co_addr2' => $this->input->post('co-addr2') ? $this->input->post('co-addr2') : NULL,
          'co_country' => $this->input->post('co-country') ? $this->input->post('co-country') : NULL,
          'co_phone' => $this->input->post('co-phone') ? $this->input->post('co-phone') : NULL,
          'co_email' => $email ? $email : NULL,
          'co_pass' => $password ? $password : NULL,
        );
      }
    }
    // Update Data
    $this->db->where('co_id', $companyId);
    $this->db->update('company',$update_data);

    $session_data = array(
      'message'   => 'Data successfully updated',
      'message_status'   => 'success'
    );
    $this->session->set_userdata($session_data);
    redirect('company');
	}
}