<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Web extends CI_Controller {

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
		$this->load->css('assets/vendor/theme/css/animate.css');
		$this->load->css('assets/vendor/theme/css/preloader.css');
		$this->load->css('assets/vendor/theme/css/slick.css');
		$this->load->css('assets/vendor/theme/css/meanmenu.css');
		$this->load->css('assets/vendor/theme/css/style.css');
		$this->load->css('assets/vendor/theme/css/responsive.css');
		$this->load->css('assets/vendor/fontawesome-free-5.6.3-web/css/all.min.css');
		$this->load->css('assets/css/custom-css.css');
		
		$this->load->js('assets/vendor/jquery/jquery.min.js');
		$this->load->js('assets/vendor/bootstrap-4.4.1-dist/js/bootstrap.min.js');
		$this->load->js('assets/vendor/theme/js/popper.min.js');
		$this->load->js('assets/vendor/theme/js/jquery.magnific-popup.min.js');
		$this->load->js('assets/vendor/theme/js/owl.carousel.min.js');
		$this->load->js('assets/vendor/theme/js/owl.carousel.js');
		$this->load->js('assets/vendor/theme/js/slick.min.js');
		$this->load->js('assets/vendor/theme/js/jquery.meanmenu.min.js');
		$this->load->js('assets/vendor/theme/js/custom.js');
	}

	public function index(){
		$this->output->set_title('Insta App');
		$now =  date('Y-m-d H:i:s');
		
		$url =  base_url('/');
		$title =  'Insta App';

		$data['url'] = $url;
		$data['title'] = $title;
		$data['date'] = $now;

		$this->db->select("app_post.*, app_user.username as username");
		$this->db->from('app_post');
		$this->db->join('app_user', 'app_post.user_id = app_user.id', 'left');
		$query = $this->db->get();
		$posts = $query->result_array();
		$this->db->reset_query();

		if (!empty($posts)) {
			foreach ($posts as $key => $post) {
				$this->db->select("*");
				$this->db->from('app_like');
				$this->db->where('post_id', $post['id']);
				$query = $this->db->get();
				$like = $query->result_array();
				$this->db->reset_query();
				$isLiked = false;

				if (!empty($this->session->userdata('id'))) {
					foreach ($like as $value) {
						if ($value['user_id'] == $this->session->userdata('id')) {
							$isLiked = true;
						}
					}
				}
				
				$this->db->select("app_comment.*, app_user.username as username");
				$this->db->from('app_comment');
				$this->db->join('app_user', 'app_comment.user_id = app_user.id', 'left');
				$this->db->where('post_id', $post['id']);
				$query = $this->db->get();
				$comment = $query->result_array();
				$this->db->reset_query();

				$posts[$key]['like'] = $like;
				$posts[$key]['count_like'] = count($like);
				$posts[$key]['is_liked'] = $isLiked;
				$posts[$key]['comment'] = $comment;
				$posts[$key]['count_comment'] = count($comment);
			}
		}
		
		$data['posts'] = $posts;

		$this->load->view('pages/web/front-page', $data);	
	}

	public function like($post_id = ""){
		if (empty($post_id)) {
			redirect('/', 'refresh');
		}

		$this->db->select("*");
		$this->db->from('app_like');
		$this->db->where('user_id', $this->session->userdata('id'));
		$this->db->where('post_id', $post_id);
		$query = $this->db->get();
		$isAny = $query->result_array();
		$this->db->reset_query();

		if (empty($isAny)) {
			$insert_data = array(
				'id' => '',
				'user_id' => $this->session->userdata('id'),
				'post_id' => $post_id
			);
			
			$this->db->insert('app_like',$insert_data);
		}
		
		redirect('/', 'refresh');
	}

	public function unlike($post_id = ""){
		if (empty($post_id)) {
			redirect('/', 'refresh');
		}

		$this->db->where('user_id', $this->session->userdata('id'));
		$this->db->delete('app_like');
		$this->db->reset_query();
		
		redirect('/', 'refresh');
	}

	public function addComment(){
		$comment = $this->input->post('comment');

		if ($comment != "") {
			$insert_data = array(
				'id' => '',
				'user_id' => $this->input->post('user-id'),
				'post_id' => $this->input->post('post-id'),
				'comment' => $comment
			);
			
			$this->db->insert('app_comment',$insert_data);
		}
		
		redirect('/', 'refresh');
	}
}