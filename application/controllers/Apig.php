<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Apig extends CI_Controller {

	function __construct(){
		parent::__construct();
		/* Set Timezone */
		date_default_timezone_set('Asia/Jakarta');
        
		/* Allow from any origin */
		header('Content-type: application/json');
		header('Access-Control-Allow-Origin: *');
		header('Access-Control-Allow-Credentials: true');
		header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization');
		header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,PATCH,OPTIONS');
		header('Access-Control-Max-Age: 86400');
	}
	
	public function Login(){
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if(!empty($obj['email']) && !empty($obj['password'])){
			/* Select User From Prefix thats found */
			$sql = "SELECT 	co_id, co_name, co_pass
					FROM  	company
					WHERE 	co_email='".strtolower($obj['email'])."' AND co_status='Active'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			$co_pass = !empty($user['co_pass']) ? $user['co_pass'] : '';
			if (password_verify($obj['password'], $co_pass)) {
				/* Login Success */
				$co_name = !empty($user['co_name']) ? $user['co_name'] : '';
				$co_id = !empty($user['co_id']) ? $user['co_id'] : '';
				
				/* Generate Token */
				$arr = array('co_id'=>$user['co_id'],'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 5 years"));
				$token = $this->jwt->encode($arr);
				
				/* Return Success Login Data */
				$response = array(
								"id_token"=>$token,
								"co_name"=>$co_name,
								'message'=>$this->fn_init->responcode_message(202),
								'responcode'=>202
								);
			}else{
				/* Return User Not Found */
				$response = array('message'=>$this->fn_init->responcode_message(302),'responcode'=>302);
			}
		}else{
			$response = array('message'=>$this->fn_init->responcode_message(303),'responcode'=>303);
		}
		$this->jwt->response($response);
	}
	
	public function Register(){
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if(!empty($obj['co_email']) && !empty($obj['co_pass']) && !empty($obj['co_rpass']) && !empty($obj['co_name'])){
			/* Check Password Length */
			if(($obj['co_pass'] == $obj['co_rpass']) && strlen($obj['co_pass']) >= 8){
				/* Check email already use */
				$sql = "SELECT co_id FROM company WHERE co_email='".strtolower($obj['co_email'])."'";
				$query = $this->db->query($sql);
				$company = $query->row_array();
				$query->free_result();
				if(empty($company)){
					/* Validate Email Type */
					if($this->fn_init->validateEmail($obj['co_email'])){
						$co_email=!empty($obj['co_email']) ? strtolower($obj['co_email']) : '';
						$co_pass=!empty($obj['co_pass']) ? $obj['co_pass'] : '';
						$co_name=!empty($obj['co_name']) ? $obj['co_name'] : '';
						$password = password_hash($co_pass, PASSWORD_DEFAULT);
						
						/* Insert into Databse */
						$sql = "INSERT INTO company 
									(co_email, co_pass, co_name) 
								VALUES 
									('$co_email', '$password', '$co_name')";
						$this->db->query($sql);
						
						/* START - Generate Auto Company Login Information */
						$co_id = $this->db->insert_id();
						$sql = "SELECT 	co_id, co_name, co_email
								FROM  	company
								WHERE 	co_id='".$co_id."'";
						$query = $this->db->query($sql);
						$company = $query->row_array();
						$query->free_result();
						
						$co_name = !empty($company['co_name']) ? $company['co_name'] : '';
						$co_id = !empty($company['co_id']) ? $company['co_id'] : '';
						$co_email = !empty($company['co_email']) ? $company['co_email'] : '';
						
						/* Generate Token */
						$arr = array('co_id'=>$company['co_id'],'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 5 years"));
						$token = $this->jwt->encode($arr);
						
						/* Response */
						$response = array();
						$response = array(
								"id_token"=>$token,
								"co_name"=>$co_name,
								"co_id"=>$co_id,
								'message'=>$this->fn_init->responcode_message(202),
								'responcode'=>202
								);
						
						/* Send email */
						/* Sending reset password url to email */
						$arr = array('co_id'=>$co_id, 'co_email'=>$co_email, 'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 30 days"));
						$token_email = $this->fn_init->base64url_encode($this->jwt->encode($arr));
						$link_activation = 'https://attendance.excelsoft.com/apig/VerificationEmail/'.$token_email;
						$subject ='Attendance Team';
						$message = "<h3>Welcome to account registration Attendance! Please confirm!</h3>
									<p>You have registered Attendance account with $co_email. Please click '<strong>Activate Now</strong>' to confirm your account.</p>
									<a class='button' href='$link_activation' style='background-color: #4CAF50;border: none;border-radius: 8px;color: white;padding: 12px 25px;text-align: center;text-decoration: none;display: inline-block;font-size: 14px;font-weight: 400;margin: 4px 2px;cursor: pointer;'>Activate Now</a>
									<p>If the button does not respond, please open the link below :</p>
									<a href='$link_activation'>$link_activation</a>
									<p>For security, the link will only be active for 24 hours. After 24 hours, you need to request more email activation on the account page. Thank you for supporting Attendance.</p>
									<br><br>
									<p>If you are not owned this account please contact us back at att-support@excelsoft.com.</p>";
						$this->sendMail($co_email,$subject,$message);
					}else{
						$response = array('message'=>$this->fn_init->responcode_message(306),'responcode'=>306);
					}
				}else{
					$response = array('message'=>$this->fn_init->responcode_message(305),'responcode'=>305);
				}
			} else {
				if($obj['co_pass'] !== $obj['co_rpass']) {
					$response = array('message'=>$this->fn_init->responcode_message(304),'responcode'=>304);
				} else if(strlen($obj['co_pass']) < 8) {
					$response = array('message'=>$this->fn_init->responcode_message(307),'responcode'=>307);
				}
			}
		}else{
			$response = array('message'=>$this->fn_init->responcode_message(303),'responcode'=>303);
		}
		
		$this->jwt->response($response);
	}
	
	public function saveInfo(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			
			$co_addr1=!empty($obj['co_addr1']) ? $obj['co_addr1'] : '';
			$co_addr2=!empty($obj['co_addr2']) ? $obj['co_addr2']: '';
			$co_country=!empty($obj['co_country']) ? $obj['co_country']: '';
			$co_phone=!empty($obj['co_phone']) ? $obj['co_phone'] : '';
			
			$sql = "UPDATE company SET 	co_addr1 = '$co_addr1',
										co_addr2 = '$co_addr2',
										co_country = '$co_country',
										co_phone = '$co_phone'
						WHERE co_id='$co_id'";
			$this->db->query($sql);
			
			$response = array();
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function getGateList($page=1,$limit=20){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			
			/* Get All Message */
			$page = ($page-1)*$limit;
			$sql = "SELECT  gt_id, gt_name, gt_getphoto, gt_getphoto_onlyin
					FROM  gate
					WHERE co_id='$co_id'
					ORDER BY gt_id ASC LIMIT $limit OFFSET $page";
			$query = $this->db->query($sql);
			$att = $query->result_array();
			$query->free_result();

			// Get license
			$sql = "SELECT  co_license
					FROM  company
					WHERE co_id='$co_id'";
			$query = $this->db->query($sql);
			$license = $query->row_array();
			$query->free_result();
			
			$response = array();
			$response['data'] = $att;
			$response['license'] = $license['co_license'];
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		}else{
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function getAllGateList($page=1,$limit=100){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			
			/* Get All Message */
			$page = ($page-1)*$limit;
			$sql = "SELECT  gt_id, gt_name, gt_getphoto, gt_getphoto_onlyin
					FROM  gate
					WHERE co_id='$co_id'
					ORDER BY gt_id ASC LIMIT $limit OFFSET $page";
			$query = $this->db->query($sql);
			$att = $query->result_array();
			$query->free_result();
			
			$response = array();
			$response['data'] = $att;
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		}else{
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function addGate(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			
			if(!empty($co_id)) {

				/* check max gate */
				$sql = "SELECT a.co_license, COUNT(b.gt_id) AS total_gate 
								FROM company AS a 
								JOIN gate AS b ON a.co_id = b.co_id 
								WHERE a.co_id = $co_id";
				$query = $this->db->query($sql);
				$gate = $query->row_array();
				$query->free_result();

				if (!empty($gate)) {
					if ($gate['co_license'] != 'Paid' && $gate['total_gate'] >= 5) {
						$response = array("message"=>$this->fn_init->responcode_message(321),'responcode'=>321);
					} else {
						$gt_name=!empty($obj['gt_name']) ? $obj['gt_name'] : '';
						$gt_getphoto=!empty($obj['gt_getphoto']) ? $obj['gt_getphoto']: '';
						$gt_getphoto_onlyin=!empty($obj['gt_getphoto_onlyin']) ? $obj['gt_getphoto_onlyin']: '';
						
						$sql = "INSERT INTO gate 
									(co_id, gt_name, gt_getphoto, gt_getphoto_onlyin) 
								VALUES 
									('$co_id', '$gt_name', '$gt_getphoto', '$gt_getphoto_onlyin')";
						$this->db->query($sql);
						
						$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202,'sql'=>$sql);
					}	
				} else {
					$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
				}
			} else {
				$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function getDataGate($gt_id=''){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			
			$sql = "SELECT 	gt_name, gt_getphoto, gt_getphoto_onlyin
					FROM gate 
					WHERE co_id='$co_id' AND gt_id='$gt_id'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();

			$response = array();
			$response['gt_name'] = !empty($user['gt_name']) ? $user['gt_name'] : '';
			$response['gt_getphoto'] = !empty($user['gt_getphoto']) ? $user['gt_getphoto'] : '';
			$response['gt_getphoto_onlyin'] = !empty($user['gt_getphoto_onlyin']) ? $user['gt_getphoto_onlyin'] : '';
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		}else{
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}

	public function aditGate(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			
			if(!empty($co_id)) {
				$gt_name=!empty($obj['gt_name']) ? $obj['gt_name'] : '';
				$gt_getphoto=!empty($obj['gt_getphoto']) ? $obj['gt_getphoto']: '';
				$gt_getphoto_onlyin=!empty($obj['gt_getphoto_onlyin']) ? $obj['gt_getphoto_onlyin']: '';
				$gt_id=!empty($obj['gt_id']) ? $obj['gt_id']: '';
				
				$sql = "UPDATE gate SET 
							gt_name = '$gt_name',
							gt_getphoto = '$gt_getphoto',
							gt_getphoto_onlyin = '$gt_getphoto_onlyin'
						WHERE co_id='$co_id' AND gt_id='$gt_id'";
				$this->db->query($sql);
				
				$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202);
			} else {
				$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function synchAtt(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id = !empty($payload['co_id']) ? $payload['co_id'] : '';
			
			if(!empty($co_id)) {
				$data=!empty($obj['data']) ? $obj['data'] : '';
				if(!empty($data) && is_array($data)) {
					foreach($data as $dm1) {
						$em_id = !empty($dm1['em_id']) ? $dm1['em_id'] : '';
						$em_nik = !empty($dm1['em_nik']) ? $dm1['em_nik'] : '';
						$gt_id = !empty($dm1['gt_id']) ? $dm1['gt_id'] : '';
						$at_shift = !empty($dm1['at_shift']) ? $dm1['at_shift'] : '';
						$at_status = !empty($dm1['at_status']) ? $dm1['at_status'] : '';
						$at_timestamp=!empty($dm1['at_timestamp']) ? date('Y-m-d H:i:s', strtotime($dm1['at_timestamp'])) : '';
						$at_photo = !empty($dm1['at_photo']) ? $dm1['at_photo'] : '';

						// check if data is already in DB
						$sql = "SELECT 	at_id
								FROM attendance
								WHERE em_id						='$em_id' 
										AND em_nik				='$em_nik' 
										AND gt_id 				='$gt_id' 
										AND at_shift			='$at_shift' 
										AND at_status			='$at_status' 
										AND at_timestamp	='$at_timestamp' 
										AND at_photo			='$at_photo'";
						$query = $this->db->query($sql);
						$attData = $query->row_array();
						$query->free_result();

						// only insert when no data duplicate
						if (empty($attData)) {
							$sql = "INSERT INTO attendance 
										(co_id, em_id, em_nik, gt_id,
										at_shift, at_status, at_timestamp, at_photo) 
									VALUES 
										('$co_id', '$em_id', '$em_nik', '$gt_id',
										'$at_shift', '$at_status', '$at_timestamp', '$at_photo')";
							$this->db->query($sql);
						}
					}
				}
				$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202);
			} else {
				$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function CheckUpdate($version = '0.0.1',$os = 'Android'){
		$sql = "SELECT 	apg_needupdate, apg_forceupdate 
				FROM app_version_gate
				WHERE apg_version='$version' AND apg_os='$os'";
		$query = $this->db->query($sql);
		$app_version = $query->row_array();
		$query->free_result();
		
		if(!empty($app_version)){
			$response = array();
			$response['update'] = $app_version['apg_needupdate'];
			$response['force'] =  $app_version['apg_forceupdate'];
		} else {
			if($os == 'Web'){
				$response = array();
				$response['update'] = '0';
				$response['force'] = '0';
			} else {
				$response = array();
				$response['update'] = '1';
				$response['force'] = '1';
			}
		}
		$response['updateUrlAndroid'] = 'com.excelsoft.biox_clockin';
		$response['updateUrlIos'] = 'id1525520855';
		$this->jwt->response($response);
	}
	
	public function requestResetPassword() {
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if(!empty($obj['co_email'])){
			/* Select User From Prefix thats found */
			$sql = "SELECT 	co_id, co_email
					FROM  	company
					WHERE 	co_email='".strtolower($obj['co_email'])."' AND co_status='Active'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			if(!empty($user)){
				$co_id = !empty($user['co_id']) ? $user['co_id'] : '';
				$co_email = !empty($user['co_email']) ? $user['co_email'] : '';
				
				/* Send email */
				/* Sending reset password url to email */
				$arr = array('co_id'=>$co_id, 'co_email'=>$co_email, 'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 1 hour"));
				$token_email = $this->fn_init->base64url_encode($this->jwt->encode($arr));
				$link_reset = 'https://attendance.excelsoft.com/apig/ResetPassword/'.$token_email;
				$subject ='Request Change Password';
				$message = "<h3>REQUEST CHANGE PASSWORD</h3>
							<p>This email sent because you want to reset your password. To reset your password please click link below :</p>
							<h4><a href='$link_reset'>Click here to change your password</a></h4>
							<p>This link active just for a hour.</p>
							<p>If you are not owned this account and property please contact us back at att-support@excelsoft.com.</p>";
				$this->sendMail($co_email,$subject,$message);
				
				$response = array();
				$response['message'] = $this->fn_init->responcode_message(203);
				$response['responcode'] = 203;
			} else {
				$response = array();
				$response['message'] = $this->fn_init->responcode_message(308);
				$response['responcode'] = 308;
			}
		} else {
			$response = array();
			$response['message'] = $this->fn_init->responcode_message(306);
			$response['responcode'] = 306;
		}
		
		$this->jwt->response($response);
	}	
	
	public function ResetPassword($token=''){
		header('Content-type: text/html');
		$jwt_token = $this->fn_init->base64url_decode($token);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
			$co_email=!empty($payload['co_email']) ? $payload['co_email'] : '';
			
			$sql = "SELECT co_id FROM company WHERE co_email='$co_email' AND co_id='$co_id'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			if(!empty($co_email) && !empty($co_id)){
				if(!empty($_POST)){
					$password = $this->fn_init->post('password');
					$cpassword = $this->fn_init->post('cpassword');
					if(!empty($password) && !empty($cpassword)){
						if($password==$cpassword){
							if(strlen($password) >= 8){
								/* set to database */
								$password = password_hash($password, PASSWORD_DEFAULT);
								$sql = "UPDATE company SET co_pass='$password' WHERE co_email='$co_email' AND co_id='$co_id'";
								$this->db->query($sql);
								
								$this->load->view('ChangePassword',array('success'=>true));
							}else{
								$this->load->view('ChangePassword',array('message'=>'Password must be at least 8 characters'));
							}
						}else{
							$this->load->view('ChangePassword',array('message'=>'Password did not match'));
						}
					}else{
						$this->load->view('ChangePassword',array('message'=>'Please fill Password and Repeat Password'));
					}
				}else{
					$this->load->view('ChangePassword');
				}
			}else{
				$this->load->view('ChangePassword',array('invalid'=>true));
			}
		}else{
			$this->load->view('ChangePassword',array('invalid'=>true));
		}
	}

	public function VerificationEmail($token=''){
		header('Content-type: text/html; charset=utf-8');
		if(!empty($token)){
			$jwt_token = $this->fn_init->base64url_decode($token);
			if($this->jwt->validate($jwt_token)){
				$payload = $this->jwt->getPayload($jwt_token);
				$co_id=!empty($payload['co_id']) ? $payload['co_id'] : '';
				$co_email=!empty($payload['co_email']) ? $payload['co_email'] : '';

				$sql = "SELECT co_id, co_email_verify FROM company WHERE co_id='$co_id' AND co_email='$co_email'";
				$query = $this->db->query($sql);
				$user = $query->row_array();
				$co_id = !empty($user['co_id']) ? $user['co_id'] : '';
				$co_email_verify = !empty($user['co_email_verify']) ? $user['co_email_verify'] : '';
				$query->free_result();
				
				if(!empty($co_id)){
					if($co_email_verify == 1){
						$data =array('message'=>'You already verify this email','status'=>'success');
					}else{
						/* Set true Email Verifiation */
						$sql = "UPDATE company SET co_email_verify='1' WHERE co_id='$co_id'";
						$this->db->query($sql);
						
						$data =array('message'=>'Verification email success','status'=>'success');
					}
				}else{
					$data =array('message'=>'Token not found','status'=>'error');
				}
			}else{
				$data =array('message'=>'Token invalid or expired','status'=>'error');
			}
		}else{
			$data =array('message'=>'Token empty','status'=>'error');
		}
		
		$this->load->view('VerificationEmail', $data);
	}
	
	private function sendMail($to='',$subject='',$message='') {
		require APPPATH.'libraries/phpmailer/src/Exception.php';
		require APPPATH.'libraries/phpmailer/src/PHPMailer.php';
		require APPPATH.'libraries/phpmailer/src/SMTP.php';
		
		$message = $this->fn_init->templateEmail($to,$subject,$message);
		// PHPMailer object
		$response = false;
		$mail = new PHPMailer();
	   
		// SMTP configuration
		$mail->isSMTP();
		$mail->Host     = 'smtp.startlogic.com'; //sesuaikan sesuai nama domain hosting/server yang digunakan
		$mail->SMTPAuth = true;
		$mail->Username = 'att-noreply@excelsoft.com'; // user email
		$mail->Password = 'RPrh3MjEtDa4Yh'; // password email
		$mail->SMTPSecure = 'tls';
		$mail->Port     = 587;

		$mail->setFrom('att-noreply@excelsoft.com', ''); // user email
		$mail->addReplyTo('att-sales@excelsoft.com', ''); //user email

		// Add a recipient
		$mail->addAddress($to); //email tujuan pengiriman email

		// Email subject
		$mail->Subject = $subject; //subject email

		// Set email format to HTML
		$mail->isHTML(true);

		// Email body content
		$mailContent = $message; // isi email
		$mail->Body = $mailContent;

		// Send email
		if(!$mail->send()){
			$return =  'Message could not be sent.';
			$return .= 'Mailer Error: ' . $mail->ErrorInfo;
		}else{
			$return = 'Message has been sent';
		}
		
		return $return;
	}

	public function maxAttendanceHistory(){
		// Get data company license
		$sql = "SELECT co_id, co_license FROM company";
		$query = $this->db->query($sql);
		$company = $query->result_array();
		$query->free_result();

		// delete data from last year
		if (!empty($company)) {
			foreach ($company as $item) {
				if ($item['co_license'] != 'Paid') {
					$co_id = $item['co_id'];
					$sql = "DELETE FROM attendance WHERE co_id = $co_id AND at_timestamp <= DATE_ADD(NOW(), INTERVAL -12 MONTH)";
					$query = $this->db->query($sql);
				}
			}	
		}

	}

	public function checkPassword(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$co_id = !empty($payload['co_id']) ? $payload['co_id'] : '';
			
			if(!empty($co_id)) {
				$pass=!empty($obj['Password']) ? $obj['Password'] : '';

				$sql = "SELECT 	co_id, co_name, co_pass
								FROM  	company
								WHERE 	co_id='$co_id'";
				$query = $this->db->query($sql);
				$company = $query->row_array();
				$query->free_result();
				$co_pass = !empty($company['co_pass']) ? $company['co_pass'] : '';
				if (password_verify($pass, $co_pass)) {
					$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202);
				} else {
					// user not found
					$response = array("message"=>$this->fn_init->responcode_message(302),'responcode'=>302);
				}

			} else {
				$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
}