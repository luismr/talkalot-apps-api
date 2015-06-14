<?php
class HttpServerException extends Exception {
}
class HttpServerException404 extends Exception {
	function __construct($message = 'Not Found') {
		parent::__construct ( $message, 404 );
	}
}
class RestClientException extends Exception {
}
class RestCurlClient {
	public $handle;
	public $http_options;
	public $response_object;
	public $response_info;
	function __construct() {
		$this->http_options = array ();
		$this->http_options [CURLOPT_RETURNTRANSFER] = true;
		$this->http_options [CURLOPT_FOLLOWLOCATION] = false;
		$this->http_options [CURLOPT_USERAGENT] = 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13';
	}
	
	/**
	 * Perform a GET call to server
	 *
	 * Additionaly in $response_object and $response_info are the
	 * response from server and the response info as it is returned
	 * by curl_exec() and curl_getinfo() respectively.
	 *
	 * @param string $url
	 *        	The url to make the call to.
	 * @param array $http_options
	 *        	Extra option to pass to curl handle.
	 * @return string The response from curl if any
	 */
	function get($url, $http_options = array()) {
		$http_options = $http_options + $this->http_options;
		$this->handle = curl_init ( $url );
		
		if (! curl_setopt_array ( $this->handle, $http_options )) {
			throw new RestClientException ( "Error setting cURL request options" );
		}
		
		$this->response_object = curl_exec ( $this->handle );
		$this->http_parse_message ( $this->response_object );
		
		curl_close ( $this->handle );
		return $this->response_object;
	}
	
	/**
	 * Perform a POST call to the server
	 *
	 * Additionaly in $response_object and $response_info are the
	 * response from server and the response info as it is returned
	 * by curl_exec() and curl_getinfo() respectively.
	 *
	 * @param string $url
	 *        	The url to make the call to.
	 * @param
	 *        	string|array The data to post. Pass an array to make a http form post.
	 * @param array $http_options
	 *        	Extra option to pass to curl handle.
	 * @return string The response from curl if any
	 */
	function post($url, $fields = array(), $http_options = array()) {
		$http_options = $this->http_options + $http_options;
		$http_options [CURLOPT_POST] = true;
		$http_options [CURLOPT_POSTFIELDS] = $fields;
		if (is_array ( $fields )) {
			syslog(LOG_INFO, "HTTP Rest Client --> POST --> Fields");
			
			if (is_array($http_options[CURLOPT_HTTPHEADER])) {
				syslog(LOG_INFO, "HTTP Rest Client --> POST --> Fields --> HEADER IS SET");
				
				$options = $http_options[CURLOPT_HTTPHEADER];
				$options [] = 'Content-Type: multipart/form-data';
				
				$http_options[CURLOPT_HTTPHEADER] = $options;
				
				syslog(LOG_INFO, "HTTP Rest Client --> POST --> Fields --> HEADER --> " . print_r($http_options, true));
			} else {
				syslog(LOG_INFO, "HTTP Rest Client --> POST --> Fields --> HEADER IS NOT SET");
				
				$http_options[CURLOPT_HTTPHEADER] = array('Content-Type: multipart/form-data');
				
				syslog(LOG_INFO, "HTTP Rest Client --> POST --> Fields --> HEADER --> " . print_r($http_options, true));
			}
		}
		
		$this->handle = curl_init ( $url );
		
		if (! curl_setopt_array ( $this->handle, $http_options )) {
			throw new RestClientException ( "Error setting cURL request options." );
		}
		
		$this->response_object = curl_exec ( $this->handle );
		$this->http_parse_message ( $this->response_object );
		
		curl_close ( $this->handle );
		return $this->response_object;
	}
	
	/**
	 * Perform a PUT call to the server
	 *
	 * Additionaly in $response_object and $response_info are the
	 * response from server and the response info as it is returned
	 * by curl_exec() and curl_getinfo() respectively.
	 *
	 * @param string $url
	 *        	The url to make the call to.
	 * @param
	 *        	string|array The data to post.
	 * @param array $http_options
	 *        	Extra option to pass to curl handle.
	 * @return string The response from curl if any
	 */
	function put($url, $data = '', $http_options = array()) {
		$http_options = $http_options + $this->http_options;
		$http_options [CURLOPT_CUSTOMREQUEST] = 'PUT';
		$http_options [CURLOPT_POSTFIELDS] = $data;
		$this->handle = curl_init ( $url );
		
		if (! curl_setopt_array ( $this->handle, $http_options )) {
			throw new RestClientException ( "Error setting cURL request options." );
		}
		
		$this->response_object = curl_exec ( $this->handle );
		$this->http_parse_message ( $this->response_object );
		
		curl_close ( $this->handle );
		return $this->response_object;
	}
	
	/**
	 * Perform a DELETE call to server
	 *
	 * Additionaly in $response_object and $response_info are the
	 * response from server and the response info as it is returned
	 * by curl_exec() and curl_getinfo() respectively.
	 *
	 * @param string $url
	 *        	The url to make the call to.
	 * @param array $http_options
	 *        	Extra option to pass to curl handle.
	 * @return string The response from curl if any
	 */
	function delete($url, $http_options = array()) {
		$http_options = $http_options + $this->http_options;
		$http_options [CURLOPT_CUSTOMREQUEST] = 'DELETE';
		$this->handle = curl_init ( $url );
		
		if (! curl_setopt_array ( $this->handle, $http_options )) {
			throw new RestClientException ( "Error setting cURL request options." );
		}
		
		$this->response_object = curl_exec ( $this->handle );
		$this->http_parse_message ( $this->response_object );
		
		curl_close ( $this->handle );
		return $this->response_object;
	}
	private function http_parse_message($res) {
		if (! $res) {
			throw new HttpServerException ( curl_error ( $this->handle ), - 1 );
		}
		
		$this->response_info = curl_getinfo ( $this->handle );
		$code = $this->response_info ['http_code'];
		
		if ($code == 404) {
			throw new HttpServerException404 ( curl_error ( $this->handle ) );
		}
		
		if ($code >= 400 && $code <= 600) {
			throw new HttpServerException ( 'Server response status was: ' . $code . ' with response: [' . $res . ']', $code );
		}
		
		if (! in_array ( $code, range ( 200, 207 ) )) {
			throw new HttpServerException ( 'Server response status was: ' . $code . ' with response: [' . $res . ']', $code );
		}
	}
}
