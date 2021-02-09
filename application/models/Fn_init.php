<?php
class fn_init extends CI_Model {
	public function __construct()
	{
		parent::__construct();
	}

	public function getDomain($data=''){
		return 'https://attendance.excelsoft.com/';
	}
	
	public function getFolderCollection($num=''){
		return ceil($num/100) * 100;
	}
	
	public function post($data=''){
		$post = $this->fn_init->escape_str($this->input->post($data, true));
		return !empty($post) ? $post : '';
	}
	
	/* Escape From SQL Injection */
	function escape_str($str, $like = FALSE){
		$db = get_instance()->db->conn_id;
		if (is_array($str))
		{
			foreach ($str as $key => $val)
			{
				$str[$key] = $this->escape_str($val, $like);
			}

			return $str;
		}

		if (function_exists('mysqli_real_escape_string') AND is_object($db))
		{
			$str = mysqli_real_escape_string($db, $str);
		}else{
			$str = addslashes($str);
		}

		// escape LIKE condition wildcards
		if ($like === TRUE){
			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}

		return $str;
	}
	
	/* Respon Code Message */
	function responcode_message($code=''){
		switch ($code) {
			case 404:
				$en = 'No direct script access allowed';
				$id = 'Tidak ada akses skrip langsung yang diizinkan';
				break;
			case 302:
				$en = 'Your email address or password is incorrect';
				$id = 'Alamat email atau kata sandi Anda salah';
				break;
			case 303:
				$en = 'Please enter all required input';
				$id = 'Masukkan semua masukan yang diperlukan';
				break;
			case 304:
				$en = 'Password did not match';
				$id = 'Kata sandi tidak cocok';
				break;
			case 305:
				$en = 'Email already use';
				$id = 'Email sudah digunakan';
				break;
			case 306:
				$en = 'Email is not valid';
				$id = 'Email tidak valid';
				break;
			case 307:
				$en = 'Password must be at least 8 characters';
				$id = 'Kata sandi setidaknya mempunyai minimal 8 karakter';
				break;
			case 308:
				$en = 'Email not found';
				$id = 'Email tidak ditemukan';
				break;
			case 309:
				$en = 'Company code not found';
				$id = 'Kode Perusahaan tidak ditemukan';
				break;
			case 310:
				$en = 'Data is not valid';
				$id = 'Data tidak valid';
				break;
			case 311:
				$en = 'Unknown timezone';
				$id = 'Timezone tidak diketahui';
				break;
			case 314:
				$en = 'Custom message';
				$id = 'Pesan kustom';
				break;
			case 320:
				$en = 'Upload failed, please make sure extension file are image';
				$id = 'Pengunggahan gagal, harap pastikan file ekstensi adalah gambar';
				break;
			case 202:
				$en = 'Success';
				$id = 'Sukses';
				break;
			case 203:
				$en = 'Link reset password already send to your email';
				$id = 'Link reset password telah dikirim ke email';
				break;
			default:
				$en = '';
				$id = '';
		}
		
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		$Lang=!empty($obj['Lang']) ? $obj['Lang'] : '';
		if(empty($Lang) || $Lang=='en'){
			return $en;
		}elseif($Lang=='ind'){
			return $id;
		}
		return $en;
	}
	
	public function validateEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}
	
	function base64url_encode($data) { 
	  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
	} 
	
	function base64url_decode($data) { 
	  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
	}
	
	function UploadImageBase64NoWM($arr=''){
		if(!empty($arr['name']) && !empty($arr['base64']) && !empty($arr['dir']) && !empty($arr['ext'])){
			/* Create and Check Folder */
			$dir = $arr['dir'];
			if (!file_exists($dir) && !is_dir($dir)) {
				mkdir($dir, 0777, true);         
			}
			
			$file = $dir.$arr['name'];
			if (file_put_contents($file, $arr['base64']) === FALSE){
				$data = array('error' => 'File can\'t be created');
				$data['berhasil'] = false;
				return $data;
			}else{
				$data = array('file_name' => $arr['name']);
				$data['berhasil'] = true;
				
				/* Resize Image */
				$config['image_library']    = 'gd2';
				$config['source_image']     = $file;
				$config['create_thumb'] 	= FALSE;
				$config['maintain_ratio'] 	= TRUE;
				$config['width']         	= 300;
				$config['height']       	= 300;

				$this->load->library('image_lib');
				$this->image_lib->initialize($config);
				
				if (!$this->image_lib->resize()) {
					$data = $this->image_lib->display_errors();
					$data['berhasil'] = false;
				}
				
				return $data;
			}
		}
		$data = array('berhasil'=>false,'error'=>'Nama file dan data tidak lengkap');
		return $data;
	}
	
	function CheckImageExtensionBase64($File){
		$imgdata = base64_decode($File);
		$f = finfo_open();
		$mime_type = finfo_buffer($f, $imgdata, FILEINFO_MIME_TYPE);
		
		if($mime_type=='image/jpeg')
			return array('image'=>true,'extenstion'=>'jpeg');
		
		if($mime_type=='image/jpg')
			return array('image'=>true,'extenstion'=>'jpg');
		
		if($mime_type=='image/png')
			return array('image'=>true,'extenstion'=>'png');
		
		if($mime_type=='image/gif')
			return array('image'=>true,'extenstion'=>'gif');
		
		if($mime_type=='image/bmp')
			return array('image'=>true,'extenstion'=>'bmp');
		
		return array('image'=>false,'extenstion'=>$mime_type);
	}
	
	function randomPassword() {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
		$pass = array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 8; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	
	function templateEmail($to,$subject='',$message=''){
		$templated = '
		<!doctype html>
		<html>
		  <head>
		    <meta name="viewport" content="width=device-width">
		    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		    <title>'.$subject.'</title>
		    <style>
		    /* -------------------------------------
		        INLINED WITH htmlemail.io/inline
		    ------------------------------------- */
		    /* -------------------------------------
		        RESPONSIVE AND MOBILE FRIENDLY STYLES
		    ------------------------------------- */
		    @media only screen and (max-width: 620px) {
		      table[class=body] h1 {
		        font-size: 28px !important;
		        margin-bottom: 10px !important;
		      }
		      table[class=body] p,
		            table[class=body] ul,
		            table[class=body] ol,
		            table[class=body] td,
		            table[class=body] span,
		            table[class=body] a {
		        font-size: 16px !important;
		      }
		      table[class=body] .wrapper,
		            table[class=body] .article {
		        padding: 10px !important;
		      }
		      table[class=body] .content {
		        padding: 0 !important;
		      }
		      table[class=body] .container {
		        padding: 0 !important;
		        width: 100% !important;
		      }
		      table[class=body] .main {
		        border-left-width: 0 !important;
		        border-radius: 0 !important;
		        border-right-width: 0 !important;
		      }
		      table[class=body] .btn table {
		        width: 100% !important;
		      }
		      table[class=body] .btn a {
		        width: 100% !important;
		      }
		      table[class=body] .img-responsive {
		        height: auto !important;
		        max-width: 100% !important;
		        width: auto !important;
		      }
		    }

		    /* -------------------------------------
		        PRESERVE THESE STYLES IN THE HEAD
		    ------------------------------------- */
		    @media all {
		      .ExternalClass {
		        width: 100%;
		      }
		      .ExternalClass,
		            .ExternalClass p,
		            .ExternalClass span,
		            .ExternalClass font,
		            .ExternalClass td,
		            .ExternalClass div {
		        line-height: 100%;
		      }
		      .apple-link a {
		        color: inherit !important;
		        font-family: inherit !important;
		        font-size: inherit !important;
		        font-weight: inherit !important;
		        line-height: inherit !important;
		        text-decoration: none !important;
		      }
		      #MessageViewBody a {
		        color: inherit;
		        text-decoration: none;
		        font-size: inherit;
		        font-family: inherit;
		        font-weight: inherit;
		        line-height: inherit;
		      }
		      .btn-primary table td:hover {
		        background-color: #34495e !important;
		      }
		      .btn-primary a:hover {
		        background-color: #34495e !important;
		        border-color: #34495e !important;
		      }
		    }
		    </style>
		  </head>
		  <body class="" style="background-color: #fff; font-family: sans-serif; -webkit-font-smoothing: antialiased; font-size: 14px; line-height: 1.4; margin: 0; padding: 0; -ms-text-size-adjust: 100%; -webkit-text-size-adjust: 100%;">
		    <table border="0" cellpadding="0" cellspacing="0" class="body" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background-color: #fff;">
		      <tr>
		        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
		        <td class="container" style="font-family: sans-serif; font-size: 14px; vertical-align: top; display: block; Margin: 0 auto; max-width: 516px; padding: 10px; width: 516px;">
		          <div class="content" style="box-sizing: border-box; display: block; Margin: 0 auto; max-width: 580px; padding: 10px;">

		            <!-- START CENTERED WHITE CONTAINER -->
		            <table class="main" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%; background: #ffffff; border-radius: 8px;border-color: #dadce0;border-style: solid;border-width: thin;padding: 15px 20px;">

		              <!-- START MAIN CONTENT AREA -->
		              <tr>
		                <td class="wrapper" style="font-family: sans-serif; font-size: 14px; vertical-align: top; box-sizing: border-box;">
		                  <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
		                  	<tr>
		                      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top; text-align: center;">
		                        <img width="65" height="65" src="https://attendance.excelsoft.com/assets/logo/attendance.png" style="width:65px;height:65px;margin-bottom:16px" class="CToWUd">
		                      </td>
		                    </tr>
		                    <tr>
		                      <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">
		                        '.$message.'
		                      </td>
		                    </tr>
		                  </table>
		                </td>
		              </tr>

		            <!-- END MAIN CONTENT AREA -->
		            </table>

		            <!-- START FOOTER -->
		            <div class="footer" style="clear: both; Margin-top: 12px; text-align: center; width: 100%;">
		              <table border="0" cellpadding="0" cellspacing="0" style="border-collapse: separate; mso-table-lspace: 0pt; mso-table-rspace: 0pt; width: 100%;">
		                <tr>
		                  <td class="content-block" style="font-family: sans-serif; vertical-align: top; font-size: 11px; color: #999999; text-align: center;">
							If you are not owned this account please contact us back at <a href="mailto:att-support@excelsoft.com" style="color: #999999; font-size: 11px; text-align: center; text-decoration: none;">att-support@excelsoft.com</a>.
		                  </td>
		                </tr>
		                <tr>
		                  <td class="content-block powered-by" style="font-family: sans-serif; vertical-align: top; font-size: 11px; color: #999999; text-align: center;">
		                    <span class="apple-link" style="color: #999999; font-size: 11px; text-align: center;">Attendance Application, Puri Niaga Building III Lot M8 Unit 32 F - G, Jakarta West 11610, Indonesia</span>
		                  </td>
		                </tr>
		              </table>
		            </div>
		            <!-- END FOOTER -->

		          <!-- END CENTERED WHITE CONTAINER -->
		          </div>
		        </td>
		        <td style="font-family: sans-serif; font-size: 14px; vertical-align: top;">&nbsp;</td>
		      </tr>
		    </table>
		  </body>
		</html>
		';

		return $templated;
	}
	
	public function config_pagination($data=array()){
		$config = array();
		$config['full_tag_open'] = "<ul class='pagination'>";
		$config['full_tag_close'] ="</ul>";
		$config['num_tag_open'] = '<li class="page-item page-link">';
		$config['num_tag_close'] = '</li>';
		$config['cur_tag_open'] = '<li class="page-item active"><a class="page-link" href="#">';
		$config['cur_tag_close'] = "<span class='sr-only'></span></a></li>";
		$config['next_link'] = 'Selanjutnya &rarr;';
		$config['next_tag_open'] = '<li class="page-item page-link">';
		$config['next_tagl_close'] = "</li>";
		$config['prev_link'] = '&larr; Sebelumnya';
		$config['prev_tag_open'] = '<li class="page-item page-link">';
		$config['prev_tagl_close'] = "</li>";
		$config['first_tag_open'] = '<li class="page-item page-link">';
		$config['first_tagl_close'] = "</li>";
		$config['last_tag_open'] = '<li class="page-item page-link">';
		$config['last_tagl_close'] = "</li>";
		$config['uri_segment'] = (!empty($data['uri_segment']) ? $data['uri_segment'] :0);
		$config['base_url'] = (!empty($data['url']) ? $data['url'] : base_url());
		$config['total_rows'] = (!empty($data['total_row']) ? $data['total_row'] : 0);
		$config['per_page'] = (!empty($data['per_page']) ? $data['per_page'] : 10);
		$config['reuse_query_string'] = TRUE;
		
		return $config;
	}
	
}
?>