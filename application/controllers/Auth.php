<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Auth extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		$this->_init();
		$this->load->database();
	}

	private function _init()
	{
		$this->output->set_template('web');
		

		$this->load->css('assets/vendor/bootstrap-4.4.1-dist/css/bootstrap.min.css');
		$this->load->css('assets/vendor/theme/css/flaticon.css');
		$this->load->css('assets/vendor/theme/css/magnific-popup.css');
		$this->load->css('assets/vendor/theme/css/owl.carousel.css');
		$this->load->css('assets/vendor/theme/css/owl.theme.css');
		$this->load->css('assets/vendor/theme/css/slick.css');
		$this->load->css('assets/vendor/theme/css/meanmenu.css');
		$this->load->css('assets/vendor/theme/css/style.css');
		$this->load->css('assets/vendor/theme/css/responsive.css');
		$this->load->css('assets/vendor/fontawesome-free-5.6.3-web/css/all.min.css');
		$this->load->css('assets/css/custom-css.css');
		
		$this->load->js('assets/vendor/jquery/jquery.min.js');
		$this->load->js('assets/vendor/bootstrap-4.4.1-dist/js/bootstrap.min.js');
	}

	public function index(){
		redirect('auth/login');
	}
	
  public function login(){
		$this->output->set_title('InstaApp - Login');

		if ($this->session->userdata('login')) {
			redirect('apps');
		}
    $username = strtolower($this->input->post('username'));
		$password = $this->input->post('password');

		if (!empty($username) && !empty($password)) {
			$this->db->select('*');
	    $this->db->from('app_user');
	    $this->db->where('LOWER(username)', $username);
			$query = $this->db->get();
			$user = $query->row_array();

			if (!empty($user)) {
				if (password_verify($password, $user['password'])) {
					$session_data = array(
						'login'   => 1,
						'id'   => $user['id'],
						'username'   => $user['username']
					);
					//set session userdata
					$this->session->set_userdata($session_data);
					redirect("apps");
				} else {
					$session_data = array(
						'message'   => 'The password you entered is incorrect',
						'message_status'   => 'info'
					);
					$this->session->set_userdata($session_data);
				}
			} else {
				$session_data = array(
					'message'   => 'Username not found',
					'message_status'   => 'info'
				);
				$this->session->set_userdata($session_data);
			}
		}
		
		$now =  date('Y-m-d H:i:s');

		$url =  base_url('/auth/login');
		$title =  'InstaApp - Login';

		$data['url'] = $url;
		$data['title'] = $title;
		$data['date'] = $now;

		$this->load->view('pages/web/login', $data);
	}

	public function logout(){
		$this->session->sess_destroy();
		redirect('/');
	}

	public function register(){
		$this->output->set_title('InstaApp - Register');

		if ($this->session->userdata('login')) {
			redirect('apps');
		}
    $username = strtolower($this->input->post('username'));

		if (!empty($username)) {
			$pss = strtolower($this->input->post('password'));
			$repss = strtolower($this->input->post('password-re-entered'));

			if ($pss != $repss) {
				$session_data = array(
					'message'   => 'Re entered password incorrect',
					'message_status'   => 'info'
				);
				$this->session->set_userdata($session_data);
				redirect('auth/register');
			}

			/* Get Data */
	    $this->db->select('username');
	    $this->db->from('app_user');
	    $this->db->where('LOWER(username)', $username);
			$query = $this->db->get();
			$isAvailable = $query->row_array();
			
			if (!empty($isAvailable)) {
				$session_data = array(
					'message'   => 'Username already registered',
					'message_status'   => 'info'
				);
				$this->session->set_userdata($session_data);
				redirect('auth/register');
			} else {
				$username = $this->input->post('username');
				$password = password_hash($this->input->post('password'), PASSWORD_DEFAULT);

				if (empty($username) || empty($password)) {
					$session_data = array(
						'message'   => 'Please check again the fields',
						'message_status'   => 'danger'
					);
					$this->session->set_userdata($session_data);
					redirect('auth/register');
				} else {
					// Add Data
					$insert_data = array(
						'username' => $username,
						'password' => $password ? $password : NULL
					);
					
					$this->db->insert('app_user',$insert_data);
					
					$insert_id = $this->db->insert_id();

					$session_data = array(
						'login'   => 1,
						'id'   => $insert_id,
						'username'   => $username
					);
					//set session userdata
					$this->session->set_userdata($session_data);
					redirect("apps");
				}
			}
		}
		
		$now =  date('Y-m-d H:i:s');

		$url =  base_url('/auth/register');
		$title =  'InstaApp - Register';

		$data['url'] = $url;
		$data['title'] = $title;
		$data['date'] = $now;

		$this->load->view('pages/web/register', $data);
	}
}