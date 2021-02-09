<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jwt {
		
	protected $secret_key = "1va6r!E'C3gE4|{8$0,*DEsDPy%RY32'gpLqLw55Lrxqy0W8cpKa3603z50y957v";
	// Note: Only the widely used HTTP status codes are documented

    // Informational

    const HTTP_CONTINUE = 100;
    const HTTP_SWITCHING_PROTOCOLS = 101;
    const HTTP_PROCESSING = 102;            // RFC2518

    // Success

    /**
     * The request has succeeded
     */
    const HTTP_OK = 200;

    /**
     * The server successfully created a new resource
     */
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The server successfully processed the request, though no content is returned
     */
    const HTTP_NO_CONTENT = 204;
    const HTTP_RESET_CONTENT = 205;
    const HTTP_PARTIAL_CONTENT = 206;
    const HTTP_MULTI_STATUS = 207;          // RFC4918
    const HTTP_ALREADY_REPORTED = 208;      // RFC5842
    const HTTP_IM_USED = 226;               // RFC3229

    // Redirection

    const HTTP_MULTIPLE_CHOICES = 300;
    const HTTP_MOVED_PERMANENTLY = 301;
    const HTTP_FOUND = 302;
    const HTTP_SEE_OTHER = 303;

    /**
     * The resource has not been modified since the last request
     */
    const HTTP_NOT_MODIFIED = 304;
    const HTTP_USE_PROXY = 305;
    const HTTP_RESERVED = 306;
    const HTTP_TEMPORARY_REDIRECT = 307;
    const HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238

    // Client Error

    /**
     * The request cannot be fulfilled due to multiple errors
     */
    const HTTP_BAD_REQUEST = 400;

    /**
     * The user is unauthorized to access the requested resource
     */
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_PAYMENT_REQUIRED = 402;

    /**
     * The requested resource is unavailable at this present time
     */
    const HTTP_FORBIDDEN = 403;

    /**
     * The requested resource could not be found
     *
     * Note: This is sometimes used to mask if there was an UNAUTHORIZED (401) or
     * FORBIDDEN (403) error, for security reasons
     */
    const HTTP_NOT_FOUND = 404;

    /**
     * The request method is not supported by the following resource
     */
    const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * The request was not acceptable
     */
    const HTTP_NOT_ACCEPTABLE = 406;
    const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    const HTTP_REQUEST_TIMEOUT = 408;

    /**
     * The request could not be completed due to a conflict with the current state
     * of the resource
     */
    const HTTP_CONFLICT = 409;
    const HTTP_GONE = 410;
    const HTTP_LENGTH_REQUIRED = 411;
    const HTTP_PRECONDITION_FAILED = 412;
    const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    const HTTP_REQUEST_URI_TOO_LONG = 414;
    const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const HTTP_EXPECTATION_FAILED = 417;
    const HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    const HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const HTTP_LOCKED = 423;                                                      // RFC4918
    const HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;   // RFC2817
    const HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    const HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    const HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    const HTTP_LOGIN_TIME_OUT = 440;                             // RFC6585

    // Server Error

    /**
     * The server encountered an unexpected error
     *
     * Note: This is a generic error message when no specific message
     * is suitable
     */
    const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * The server does not recognise the request method
     */
    const HTTP_NOT_IMPLEMENTED = 501;
    const HTTP_BAD_GATEWAY = 502;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    const HTTP_GATEWAY_TIMEOUT = 504;
    const HTTP_VERSION_NOT_SUPPORTED = 505;
    const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    const HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    const HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    const HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

	public function encode($encoded_payload_arr='')
	{
		$encoded_header = base64_encode('{"alg": "HS256","typ": "JWT"}');
		$encoded_payload = base64_encode(json_encode($encoded_payload_arr));

		$header_and_payload_combined = $encoded_header . '.' . $encoded_payload;

		$signature = base64_encode(hash_hmac('sha256', $header_and_payload_combined, $this->secret_key, true));

		$jwt_token = $header_and_payload_combined . '.' . $signature;
		
		return $jwt_token;
	}
	
	public function validate($jwt_token='')
	{
		#recieved_jwt would in real life be populated from a $_POST['values'] but for this example this will work
		$recieved_jwt = $jwt_token;

		$jwt_values = explode('.', $recieved_jwt);
		
		@ $recieved_signature = $jwt_values[2];
		@ $recieved_header_and_payload = $jwt_values[0] . '.' . $jwt_values[1];

		$what_signature_should_be = base64_encode(hash_hmac('sha256', $recieved_header_and_payload, $this->secret_key, true));
		
		if($what_signature_should_be == $recieved_signature) {
			// signature is ok, the payload has not been tampered with
			
			$payload = base64_decode($jwt_values[1]);
			$payload = json_decode($payload);
			if($payload->exp >= strtotime(date("Y-m-d h:i:s"))){
				return true;
			}else{
				$response = array("message"=>'Token Expired','responcode'=>'405');
				$this->response($response);
			}
		}
		return false;
	}
	
	public function getPayload($recieved_jwt='')
	{
		$jwt_values = explode('.', $recieved_jwt);
		
		@ $payload = base64_decode($jwt_values[1]);
		@ $payload = json_decode($payload, true);
		
		return $payload;
	}
	
	public function response($response='', $status_header=jwt::HTTP_OK)
	{
		$ci =& get_instance();
		$ci	->output
				->set_status_header($status_header)
				->set_content_type('application/json', 'utf-8')
				->set_output(json_encode($response))
				->_display();
		exit;
	}
	
	function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
	
	/**
	 * get access token from header
	 * */
	function getBearerToken() {
		/* New Script added because Authorization Header had CORS problem at Ionic View Pro, so add Authorization as POST */
		$json = file_get_contents('php://input');
		$obj = json_decode($json, true);
		if(!empty($obj['Authorization'])){
			return $obj['Authorization'];
		}
		if(!empty($_POST['Authorization'])){
			return $_POST['Authorization'];
		}
		
		/* END Post Authorization -- */
		$headers = $this->getAuthorizationHeader();
		// HEADER: Get the access token from the header
		if (!empty($headers)) {
			if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
				return $matches[1];
			}
		}
		return null;
	}
}