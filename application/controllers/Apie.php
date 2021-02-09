<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Apie extends CI_Controller {

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
			$sql = "SELECT 	em_id, em_nik, em_name, em_photo
					FROM  	employee
					WHERE 	em_email='".strtolower($obj['email'])."' AND em_pass='".md5($obj['password'])."' AND em_status='Active'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			if(!empty($user)){
				/* Login Success */
				$em_name = !empty($user['em_name']) ? $user['em_name'] : '';
				$em_id = !empty($user['em_id']) ? $user['em_id'] : '';
				$em_nik = !empty($user['em_nik']) ? $user['em_nik'] : '';
				$em_photo = !empty($user['em_photo']) ? $user['em_photo'] : '';
				if(!empty($em_photo) && file_exists("./photos/user_profile/100/$em_id/$em_photo")){
					$em_photo = "https://attendance.excelsoft.com/photos/user_profile/100/$em_id/$em_photo";
				}else{
					$em_photo = "https://attendance.excelsoft.com/img/user.jpg";
				}
				
				/* Generate Token */
				$arr = array('em_id'=>$user['em_id'],'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 5 years"));
				$token = $this->jwt->encode($arr);
				
				/* Return Success Login Data */
				$response = array(
								"id_token"=>$token,
								"em_name"=>$em_name,
								"em_id"=>$em_id,
								"em_nik"=>$em_nik,
								"em_photo"=>$em_photo,
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
		if(!empty($obj['em_email']) && !empty($obj['em_pass']) && !empty($obj['em_rpass']) && !empty($obj['em_name'])){
			/* Check Password Length */
			if(($obj['em_pass'] == $obj['em_rpass']) && strlen($obj['em_pass']) >= 8){
				/* Check email already use */
				$sql = "SELECT em_id FROM employee WHERE em_email='".strtolower($obj['em_email'])."'";
				$query = $this->db->query($sql);
				$user = $query->row_array();
				$query->free_result();
				if(empty($user)){
					/* Validate Email Type */
					if($this->fn_init->validateEmail($obj['em_email'])){
						$em_email=!empty($obj['em_email']) ? strtolower($obj['em_email']) : '';
						$em_pass=!empty($obj['em_pass']) ? md5($obj['em_pass']) : '';
						$em_name=!empty($obj['em_name']) ? $obj['em_name'] : '';
						
						/* Insert into Databse */
						$sql = "INSERT INTO employee 
									(em_email, em_pass, em_name) 
								VALUES 
									('$em_email', '$em_pass', '$em_name')";
						$this->db->query($sql);
						
						/* START - Generate Auto User Login Information */
						$em_id = $this->db->insert_id();
						$sql = "SELECT 	em_id, em_nik, em_name, em_photo, em_email
								FROM  	employee
								WHERE 	em_id='".$em_id."'";
						$query = $this->db->query($sql);
						$user = $query->row_array();
						$query->free_result();
						
						$em_name = !empty($user['em_name']) ? $user['em_name'] : '';
						$em_id = !empty($user['em_id']) ? $user['em_id'] : '';
						$em_nik = !empty($user['em_nik']) ? $user['em_nik'] : '';
						$em_email = !empty($user['em_email']) ? $user['em_email'] : '';
						$em_photo = "https://attendance.excelsoft.com/img/user.jpg";
						
						/* Generate Token */
						$arr = array('em_id'=>$user['em_id'],'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 5 years"));
						$token = $this->jwt->encode($arr);
						
						/* Response */
						$response = array();
						$response = array(
								"id_token"=>$token,
								"em_name"=>$em_name,
								"em_id"=>$em_id,
								"em_nik"=>$em_nik,
								"em_photo"=>$em_photo,
								'message'=>$this->fn_init->responcode_message(202),
								'responcode'=>202
								);
						
						/* Send email */
						/* Sending reset password url to email */
						$arr = array('em_id'=>$em_id, 'em_email'=>$em_email, 'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 30 days"));
						$token_email = $this->fn_init->base64url_encode($this->jwt->encode($arr));
						$link_activation = 'https://attendance.excelsoft.com/apie/VerificationEmail/'.$token_email;
						$subject ='Attendance Team';
						$message = "<h3>Welcome to account registration Attendance! Please confirm!</h3>
									<p>You have registered Attendance account with $em_email. Please click '<strong>Activate Now</strong>' to confirm your account.</p>
									<a class='button' href='$link_activation' style='background-color: #4CAF50;border: none;border-radius: 8px;color: white;padding: 12px 25px;text-align: center;text-decoration: none;display: inline-block;font-size: 14px;font-weight: 400;margin: 4px 2px;cursor: pointer;'>Activate Now</a>
									<p>If the button does not respond, please open the link below :</p>
									<a href='$link_activation'>$link_activation</a>
									<p>For security, the link will only be active for 24 hours. After 24 hours, you need to request more email activation on the account page. Thank you for supporting Attendance.</p>
									<br><br>
									<p>If you are not owned this account please contact us back at att-support@excelsoft.com.</p>";
						$this->sendMail($em_email,$subject,$message);
					}else{
						$response = array('message'=>$this->fn_init->responcode_message(306),'responcode'=>306);
					}
				}else{
					$response = array('message'=>$this->fn_init->responcode_message(305),'responcode'=>305);
				}
			} else {
				if($obj['em_pass'] !== $obj['em_rpass']) {
					$response = array('message'=>$this->fn_init->responcode_message(304),'responcode'=>304);
				} else if(strlen($obj['em_pass']) < 8) {
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
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			$em_nik=!empty($obj['em_nik']) ? $obj['em_nik'] : '';
			$em_gender=!empty($obj['em_gender']) ? $obj['em_gender']: '';
			$em_dob=!empty($obj['em_dob']) ? date('Y-m-d H:i:s', strtotime($obj['em_dob'])) : '';
			$em_position=!empty($obj['em_position']) ? $obj['em_position'] : '';
			$em_phone=!empty($obj['em_phone']) ? $obj['em_phone'] : '';
			
			$sql = "UPDATE employee SET em_nik = '$em_nik',
										em_gender = '$em_gender',
										em_dob = '$em_dob',
										em_position = '$em_position',
										em_phone = '$em_phone'
						WHERE em_id='$em_id'";
			$this->db->query($sql);
			
			$response = array();
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function reqEmailVerifyLink(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			$sql = "SELECT 	em_id, em_email
					FROM  	employee
					WHERE 	em_id='".$em_id."' AND em_email_verify='0'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			
			if(!empty($user)) {
				$em_id = !empty($user['em_id']) ? $user['em_id'] : '';
				$em_email = !empty($user['em_email']) ? $user['em_email'] : '';
				
				/* Send email */
				/* Sending reset password url to email */
				$arr = array('em_id'=>$em_id, 'em_email'=>$em_email, 'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 30 days"));
				$token_email = $this->fn_init->base64url_encode($this->jwt->encode($arr));
				$link_activation = 'https://attendance.excelsoft.com/apie/VerificationEmail/'.$token_email;
				$subject ='Attendance Team';
				$message = "<h3>Welcome to account registration Attendance! Please confirm!</h3>
							<p>You have registered Attendance account with $em_email. Please click '<strong>Activate Now</strong>' to confirm your account.</p>
							<a class='button' href='$link_activation' style='background-color: #4CAF50;border: none;border-radius: 8px;color: white;padding: 12px 25px;text-align: center;text-decoration: none;display: inline-block;font-size: 14px;font-weight: 400;margin: 4px 2px;cursor: pointer;'>Activate Now</a>
							<p>If the button does not respond, please open the link below :</p>
							<a href='$link_activation'>$link_activation</a>
							<p>For security, the link will only be active for 24 hours. After 24 hours, you need to request more email activation on the account page. Thank you for supporting Attendance.</p>
							<br><br>
							<p>If you are not owned this account please contact us back at att-support@excelsoft.com.</p>";
				$this->sendMail($em_email,$subject,$message);
			}
			
			$response = array();
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function editProfile(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			$em_name=!empty($obj['em_name']) ? $obj['em_name'] : '';
			$em_nik=!empty($obj['em_nik']) ? $obj['em_nik']: '';
			$em_gender=!empty($obj['em_gender']) ? $obj['em_gender']: '';
			$em_dob=!empty($obj['em_dob']) ? date('Y-m-d H:i:s', strtotime($obj['em_dob'])) : '';
			$em_position=!empty($obj['em_position']) ? $obj['em_position'] : '';
			$em_phone=!empty($obj['em_phone']) ? $obj['em_phone'] : '';
			$em_photo=!empty($obj['em_photo']) ? $obj['em_photo'] : '';
			
			$sql = "UPDATE employee SET em_name = '$em_name',
										em_nik = '$em_nik',
										em_gender = '$em_gender',
										em_dob = '$em_dob',
										em_position = '$em_position',
										em_phone = '$em_phone'
						WHERE em_id='$em_id'";
			$this->db->query($sql);
			
			/* Upload Photo Profile */
			if(!empty($em_photo)) {
				$check_image_type = $this->fn_init->CheckImageExtensionBase64($em_photo);
				$filename = $this->fn_init->randomPassword().'-'.date('Ymd-Hi').'.'.$check_image_type['extenstion'];
				$arr = array(
					'name'=>$filename,
					'base64'=>base64_decode($em_photo),
					'ext'=>'.'.$check_image_type['extenstion'],
					'dir'=>'./photos/profile/'.$this->fn_init->getFolderCollection($em_id).'/'.$em_id.'/'
				);
				$upload = $this->fn_init->UploadImageBase64NoWM($arr);
				
				if(!empty($upload['berhasil']) && $upload['berhasil']==true){
					/* Delete file photo on local */
					$sql = "SELECT 	em_photo
							FROM employee 
							WHERE em_id='$em_id'";
					$query = $this->db->query($sql);
					$user = $query->row_array();
					$query->free_result();
					$em_photo=!empty($user['em_photo']) ? $user['em_photo'] : '';
					$file = './photos/profile/'.$this->fn_init->getFolderCollection($em_id).'/'.$em_id.'/'.$em_photo;
					if(file_exists($file))
						unlink($file);
					
					$uploaded_em_photo = $upload['file_name'];
					
					/* Update into Databse */
					$sql = "UPDATE employee 
							SET em_photo = '$uploaded_em_photo'
							WHERE em_id='$em_id'";
					$this->db->query($sql);
					
					$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202);
				} else {
					$response = array("message"=>$this->fn_init->responcode_message(320),'responcode'=>320);
				}
			} else {
				$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202);
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function getDataProfile(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			$sql = "SELECT 	em_name, em_nik, em_gender, em_dob, em_photo,
							em_position, em_phone, em_email, em_email_verify
					FROM employee 
					WHERE em_id='$em_id'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();

			$response = array();
			$response['em_name'] = !empty($user['em_name']) ? $user['em_name'] : '';
			$response['em_nik'] = !empty($user['em_nik']) ? $user['em_nik'] : '';
			$response['em_gender'] = !empty($user['em_gender']) ? $user['em_gender'] : '';
			$response['em_dob'] = !empty($user['em_dob']) ? $user['em_dob'] : '';
			$response['em_position'] = !empty($user['em_position']) ? $user['em_position'] : '';
			$response['em_phone'] = !empty($user['em_phone']) ? $user['em_phone'] : '';
			$response['em_email'] = !empty($user['em_email']) ? $user['em_email'] : '';
			$response['em_email_verify'] = !empty($user['em_email_verify']) ? $user['em_email_verify'] : '';
			$url_photo = $this->fn_init->getDomain().'photos/profile/'.$this->fn_init->getFolderCollection($em_id).'/'.$em_id.'/';
			$response['em_photo'] = !empty($user['em_photo']) ? $url_photo.$user['em_photo'] : '';
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		}else{
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}

	public function getDataAbsentGPS(){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			$sql = "SELECT 	em.co_id, em.em_nik, co.co_name
					FROM employee AS em
						LEFT JOIN company AS co ON co.co_id=em.co_id
					WHERE em.em_id='$em_id'
					GROUP BY em.em_id";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			
			$response = array();
			$response['co_id'] = !empty($user['co_id']) ? $user['co_id'] : '';
			$response['co_name'] = !empty($user['co_name']) ? $user['co_name'] : '';
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		}else{
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}

	public function getHistoryAttList($page=1,$limit=20){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			/* Get All Message */
			$page = ($page-1)*$limit;
			$sql = "SELECT  at.at_id, at.at_status, co.co_name, at.at_timestamp, gt.gt_name
					FROM  attendance AS at
						LEFT JOIN company AS co ON co.co_id=at.co_id
						LEFT JOIN gate AS gt ON gt.gt_id=at.gt_id
					WHERE at.em_id='$em_id'
					GROUP BY at.at_id
					ORDER BY at.at_timestamp DESC, at.at_id DESC LIMIT $limit OFFSET $page";
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
	
	public function getHistoryAttGpsList($page=1,$limit=20){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			/* Get All Message */
			$page = ($page-1)*$limit;
			$sql = "SELECT  atg.atg_id, atg.atg_status, co.co_name, 
							atg.atg_timestamp, atg.atg_lat, atg.atg_lng,
							atg.atg_notes, atg.cl_name, atg.atg_photo
					FROM  attendance_gps AS atg
						LEFT JOIN company AS co ON co.co_id=atg.co_id
					WHERE atg.em_id='$em_id'
					GROUP BY atg.atg_id
					ORDER BY atg.atg_timestamp DESC, atg.atg_id DESC LIMIT $limit OFFSET $page";
			$query = $this->db->query($sql);
			$att = $query->result_array();
			$query->free_result();
			
			/* Get Photo url */
			if(!empty($att) && is_array($att)){
				foreach($att as $k=>$dm1) {
					$atg_timestamp = !empty($dm1['atg_timestamp']) ? $dm1['atg_timestamp'] : '';
					$atg_photo = !empty($dm1['atg_photo']) ? $dm1['atg_photo'] : '';
					$year = date('Y', strtotime($atg_timestamp));
					$month = date('m', strtotime($atg_timestamp));
					$day = date('d', strtotime($atg_timestamp));
					$att[$k]['atg_photo'] = $this->fn_init->getDomain().'photos/att-gps/'.
											$this->fn_init->getFolderCollection($em_id).'/'.$em_id.'/'.
											'/'.$year.'/'.$month.'/'.$day.'/'.$atg_photo;
				}
			}
			
			$response = array();
			$response['data'] = $att;
			$response['message'] = $this->fn_init->responcode_message(202);
			$response['responcode'] = 202;
		}else{
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function registerEmCo($page=1,$limit=20){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			$token_co=!empty($obj['token_co']) ? $obj['token_co'] : '';
			if($this->jwt->validate($token_co)){
				$payload = $this->jwt->getPayload($token_co);
				$company_code=!empty($payload['company_code']) ? $payload['company_code'] : '';
				
				/* Check company available on database */
				$sql = "SELECT 	co_id, co_name
						FROM  	company
						WHERE 	co_id='$company_code' AND co_status='Active'";
				$query = $this->db->query($sql);
				$company = $query->row_array();
				$query->free_result();
				
				if (!empty($company)) {
					/* Update database user with this company id */
					$sql = "UPDATE employee 
							SET co_id = '$company_code'
							WHERE em_id='$em_id'";
					$this->db->query($sql);
			
					$response = array();
					$response['message'] = $this->fn_init->responcode_message(202);
					$response['responcode'] = 202;
				} else {
					$response = array();
					$response['co_name'] = !empty($company['co_name']) ? $company['co_name'] : '';
					$response['message'] = $this->fn_init->responcode_message(309);
					$response['responcode'] = 309;
				}
			} else {
				$response = array();
				$response['message'] = $this->fn_init->responcode_message(310);
				$response['responcode'] = 310;
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function saveAttGPS($page=1,$limit=20){
		$jwt_token = $this->jwt->getBearerToken();
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			
			/* Get data company id */
			$sql = "SELECT 	co_id, em_nik
					FROM  	employee
					WHERE 	em_id='$em_id' AND em_status='Active'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			
			if(!empty($user['co_id']) && !empty($obj['atg_timezone'])) {
				/* Save photo to local */
				$co_id=!empty($user['co_id']) ? $user['co_id'] : '';
				$em_nik=!empty($user['em_nik']) ? $user['em_nik'] : '';
				$atg_timezone=!empty($obj['atg_timezone']) ? $obj['atg_timezone'] : '';
				$atg_photo=!empty($obj['atg_photo']) ? $obj['atg_photo'] : '';
				
				$check_image_type = $this->fn_init->CheckImageExtensionBase64($atg_photo);
				$date = new DateTime("now", new DateTimeZone($atg_timezone) );
				$filename = $this->fn_init->randomPassword().'-'.$em_nik.'-'.$date->format('Ymd-Hi').'.'.$check_image_type['extenstion'];
				$arr = array(
					'name'=>$filename,
					'base64'=>base64_decode($atg_photo),
					'ext'=>'.'.$check_image_type['extenstion'],
					'dir'=>'./photos/att-gps/100/'.$em_id.'/'.$date->format('Y').'/'.$date->format('m').'/'.$date->format('d').'/'
				);
				$upload = $this->fn_init->UploadImageBase64NoWM($arr);
				
				if(!empty($upload['berhasil']) && $upload['berhasil']==true){
					$uploaded_atg_photo = $upload['file_name'];
					$cl_name=!empty($obj['cl_name']) ? $obj['cl_name'] : '';
					$atg_lat=!empty($obj['atg_lat']) ? $obj['atg_lat'] : '';
					$atg_lng=!empty($obj['atg_lng']) ? $obj['atg_lng'] : '';
					$atg_notes=!empty($obj['atg_notes']) ? $obj['atg_notes'] : '';
					$atg_status=!empty($obj['atg_status']) ? $obj['atg_status'] : '';
					$atg_timestamp=!empty($obj['atg_timestamp']) ? $obj['atg_timestamp'] : '';
					
					/* Insert into Databse */
					$query = $this->db->query("SET time_zone = '$atg_timezone'");
					$sql = "INSERT INTO attendance_gps 
								(co_id, em_id, em_nik, cl_name, 
								atg_lat, atg_lng, atg_notes, 
								atg_status, atg_timestamp, atg_photo) 
							VALUES 
								('$co_id', '$em_id', '$em_nik', '$cl_name',
								'$atg_lat', '$atg_lng', '$atg_notes',
								'$atg_status', NOW(), '$uploaded_atg_photo')";
					$this->db->query($sql);
					
					$response = array("message"=>$this->fn_init->responcode_message(202),'responcode'=>202);
				} else {
					$response = array("message"=>$this->fn_init->responcode_message(320),'responcode'=>320);
				}
			} else {
				if(empty($user['co_id'])) {
					$response = array("message"=>$this->fn_init->responcode_message(309),'responcode'=>309);
				} else if (empty($obj['atg_timezone'])){
					$response = array("message"=>$this->fn_init->responcode_message(311),'responcode'=>311);
				}
			}
		} else {
			$response = array("message"=>$this->fn_init->responcode_message(404),'responcode'=>404);
		}
		
		$this->jwt->response($response);
	}
	
	public function requestResetPassword() {
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		$obj = $this->fn_init->escape_str($obj);
		if(!empty($obj['em_email'])){
			/* Select User From Prefix thats found */
			$sql = "SELECT 	em_id, em_email
					FROM  	employee
					WHERE 	em_email='".strtolower($obj['em_email'])."' AND em_status='Active'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			if(!empty($user)){
				$em_id = !empty($user['em_id']) ? $user['em_id'] : '';
				$em_email = !empty($user['em_email']) ? $user['em_email'] : '';
				
				/* Send email */
				/* Sending reset password url to email */
				$arr = array('em_id'=>$em_id, 'em_email'=>$em_email, 'iat'=>strtotime(date("Y-m-d H:i:s")),'exp'=>strtotime(date("Y-m-d H:i:s") ." + 1 hour"));
				$token_email = $this->fn_init->base64url_encode($this->jwt->encode($arr));
				$link_reset = 'https://attendance.excelsoft.com/apie/ResetPassword/'.$token_email;
				$subject ='Request Change Password';
				$message = "<h3>REQUEST CHANGE PASSWORD</h3>
							<p>This email sent because you want to reset your password. To reset your password please click link below :</p>
							<h4><a href='$link_reset'>Click here to change your password</a></h4>
							<p>This link active just for a hour.</p>
							<p>If you are not owned this account and property please contact us back at att-support@excelsoft.com.</p>";
				$this->sendMail($em_email,$subject,$message);
				
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
	
	public function CheckUpdate($version = '0.0.1',$os = 'Android'){
		$sql = "SELECT 	ap_needupdate, ap_forceupdate 
				FROM app_version
				WHERE ap_version='$version' AND ap_os='$os'";
		$query = $this->db->query($sql);
		$app_version = $query->row_array();
		$query->free_result();
		
		if(!empty($app_version)){
			$response = array();
			$response['update'] = $app_version['ap_needupdate'];
			$response['force'] =  $app_version['ap_forceupdate'];
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
		$response['updateUrlAndroid'] = 'com.excelsoft.biox_employee';
		$response['updateUrlIos'] = 'id1525166390';
		$this->jwt->response($response);
	}
	
	public function ResetPassword($token=''){
		header('Content-type: text/html');
		$jwt_token = $this->fn_init->base64url_decode($token);
		if($this->jwt->validate($jwt_token)){
			$payload = $this->jwt->getPayload($jwt_token);
			$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
			$em_email=!empty($payload['em_email']) ? $payload['em_email'] : '';
			
			$sql = "SELECT em_id FROM employee WHERE em_email='$em_email' AND em_id='$em_id'";
			$query = $this->db->query($sql);
			$user = $query->row_array();
			$query->free_result();
			if(!empty($em_email) && !empty($em_id)){
				if(!empty($_POST)){
					$password = $this->fn_init->post('password');
					$cpassword = $this->fn_init->post('cpassword');
					if(!empty($password) && !empty($cpassword)){
						if($password==$cpassword){
							if(strlen($password) >= 8){
								/* set to database */
								$password = md5($password);
								$sql = "UPDATE employee SET em_pass='$password' WHERE em_email='$em_email'";
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
				$em_id=!empty($payload['em_id']) ? $payload['em_id'] : '';
				$em_email=!empty($payload['em_email']) ? $payload['em_email'] : '';

				$sql = "SELECT em_id, em_email_verify FROM employee WHERE em_id='$em_id' AND em_email='$em_email'";
				$query = $this->db->query($sql);
				$user = $query->row_array();
				$em_id = !empty($user['em_id']) ? $user['em_id'] : '';
				$em_email_verify = !empty($user['em_email_verify']) ? $user['em_email_verify'] : '';
				$query->free_result();
				
				if(!empty($em_id)){
					if($em_email_verify == 1){
						$data =array('message'=>'You already verify this email','status'=>'success');
					}else{
						/* Set true Email Verifiation */
						$sql = "UPDATE employee SET em_email_verify='1' WHERE em_id='$em_id'";
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
	
}