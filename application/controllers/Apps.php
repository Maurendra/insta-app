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
		$this->output->set_title('Insta App - Profile');
		$now =  date('Y-m-d H:i:s');
		
		$url =  base_url('/dashboard');
		$title =  'Insta App - Profile';

		$this->db->select("*");
		$this->db->from('app_user');
		$this->db->where('id', $this->session->userdata('id'));
		$query = $this->db->get();
		$user = $query->row_array();
		$this->db->reset_query();

		$this->db->select("*");
		$this->db->from('app_post');
		$this->db->where('user_id', $this->session->userdata('id'));
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
				
				$this->db->select("app_comment.*, app_user.username as username");
				$this->db->from('app_comment');
				$this->db->join('app_user', 'app_comment.user_id = app_user.id', 'left');
				$this->db->where('post_id', $post['id']);
				$query = $this->db->get();
				$comment = $query->result_array();
				$this->db->reset_query();

				$posts[$key]['like'] = $like;
				$posts[$key]['count_like'] = count($like);
				$posts[$key]['comment'] = $comment;
				$posts[$key]['count_comment'] = count($comment);
			}
		}

		$data['url'] = $url;
		$data['title'] = $title;
		$data['date'] = $now;

		$data['user'] = $user;
		$data['posts'] = $posts;

		$this->load->view('pages/apps/dashboard/profile', $data);	
	}

	public function addPost(){
    // Begin Transaction
		$this->db->trans_begin();

    // Add Data
    $insert_data = array(
			'id' => '',
			'user_id' => $this->session->userdata('id'),
			'description' => $this->input->post('description'),
			'title' => $this->input->post('title')
		);
    
    $this->db->insert('app_post',$insert_data);
    $insert_id = $this->db->insert_id();

		if ($this->db->trans_status() == 1) {
      // Upload image
      $newNameImage = time();
      $Upload = $this->UploadImage($this->session->userdata('id'), $newNameImage);
      if (!empty($Upload) && ($Upload['statusUpload'] == true)) {
        $newNameImage = $Upload['upload_data']['raw_name'].$Upload['upload_data']['file_ext'];
        $updated_data = array(
					'file_name' => $newNameImage
				);
				
				$this->db->where('id', $insert_id);
				$this->db->update('app_post',$updated_data);
				$this->db->trans_commit();
      } else {
        redirect('/apps/dashboard/', 'refresh');
      }
		} 
		else {
			$this->db->trans_rollback();
    }
   
    // Redirect Process
		redirect('/apps', 'refresh');
  }

	public function like($post_id = ""){
		if (empty($post_id)) {
			redirect('/apps', 'refresh');
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
		
		redirect('/apps', 'refresh');
	}

	public function unlike($post_id = ""){
		if (empty($post_id)) {
			redirect('/apps', 'refresh');
		}

		$this->db->where('user_id', $this->session->userdata('id'));
		$this->db->delete('app_like');
		$this->db->reset_query();
		
		redirect('/apps', 'refresh');
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
		
		redirect('/apps', 'refresh');
	}

	private function uploadImage($id = '', $name = ''){

    if (empty($id) || empty($name)) {
      $data = array();
      $data['statusUpload'] = false;
      return $data;
    }

    /* Create and Check Folder */
    $dir = './photos/post/';
    if (!file_exists($dir) && !is_dir($dir)) {
      mkdir($dir);         
    }
    
    $dir = './photos/post/'.$id.'/';
    if (!file_exists($dir) && !is_dir($dir)) {
      mkdir($dir);         
    }
    
    $config['upload_path']          = $dir;
    $config['allowed_types']        = 'gif|jpg|png|jpeg';
    $config['encrypt_name'] 	    	= TRUE;
    $new_name 						          = $name;
    $config['file_name']			      = $new_name;
    $config['max_size']             = 2048000; 
    $config['max_width']            = 10000000000000; 
    $config['max_height']           = 10000000000000; 
    /*
    Turn off Requested
    $config['min_width']            = 1648; // 1024 old
    $config['min_height']           = 1648; //768 old
    */
    $this->load->library('upload', $config);
    
    if ( ! $this->upload->do_upload('photo')){
      $data = array('error' => $this->upload->display_errors());
      $data['statusUpload'] = false;
      return $data;
    }else{
      $data = array('upload_data' => $this->upload->data());
      $data['statusUpload'] = true;
      
      return $data;
    }
  }
}