<?php

/**
 * RESTful request basic class
 * 
 * @category giftingapp
 * @package KountApi
 * @copyright Copyright (c) 2012 InComm Canada (http://incomm-canada.com)
 * @author Waltz.of.Pearls <rma@incomm.com, rollie@groupcard.com, rollie.ma@gmail.com>
 * @version 0.1.0
 */

namespace KountApi;

class RestCurlException extends \Exception {}
class RestJsonDecodeException extends \Exception {}
class RestResponseFailureException extends \Exception {}
class RestUnknowStatusException extends \Exception {}

abstract class Rest {

	const METHOD_GET = 'GET';
	const METHOD_PUT = 'PUT';
	const METHOD_POST = 'POST';
	const METHOD_DELETE = 'DELETE';

	const REQUEST_REGULAR = 1;
	const REQUEST_CUSTOM = 2;

	const RESPONSE_SUCCESS = 'ok';
	const RESPONSE_FAILURE = 'failure';

	protected $_endpoint = '';
	protected $_options = array(
		'timeout' => 10,
		'sslKey' => '',
		'sslCertificate' => '',
		'sslPassphrase' => '',
		// Custom or regular
		// - Custom: use curl option CURLOPT_CUSTOMREQUEST
		// - Regular: use curl option CURLOPT_POST or append request fields
		//   to the end of request url (CURLOPT_URL)
		'requestType' => self::REQUEST_REGULAR
	);

	public function __construct($endpoint, array $options = array()) {
		$this->_endpoint = $endpoint;
		foreach ($options as $optName => $optValue) {
			$this->_options[$optName] = $optValue;
		}
	}

	protected function _get(array $data) {
		return $this->_request(self::METHOD_GET, $data);
	}

	protected function _post(array $data) {
		return $this->_request(self::METHOD_POST, $data);
	}

	protected function _request($method, array $data) {
		$url = $this->_endpoint;
		$query = http_build_query($data);
		// Create a new cURL session
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLINFO_SSL_VERIFYRESULT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_options['timeout']);
		// Custom request (needs remote server support)
		if ($this->_options['requestType'] == self::REQUEST_CUSTOM) {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		}
		// Otherwise, regular request
		else {
			// Http post
			if ($method == self::METHOD_POST) {
				curl_setopt($ch, CURLOPT_POST, $method);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
			}
			// Otherwise, http get
			else {
				$url .= '?' . $query;
			}
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		// SSL related fields
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
		curl_setopt($ch, CURLOPT_SSLCERT, $this->_options['sslCertificate']);
		curl_setopt($ch, CURLOPT_SSLKEY, $this->_options['sslKey']);
		curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->_options['sslPassphrase']);
		// Execute cURL request and get response/curl_info
		$output = curl_exec($ch);
		$info = curl_getinfo($ch);
		// Handle cURL errors
		if ($output === FALSE || curl_errno($ch)) {
			$error = curl_error($ch);
			throw new RestCurlException("\nIncomplete Kount REST request.\n"
				. "- URL: {$url}\n"
				. "- Query: {$query}\n"
				. "- Curl error: {$error}\n", 0);
		}
		if ($info['http_code'] != 200) {
			throw new RestCurlException("\nComplete Kount REST request with error.\n"
				. "- URL: {$url}\n"
				. "- Query: {$query}\n"
				. "- Response: {$output}\n", $info['http_code']);
		}
		// Close the cURL session
		curl_close($ch);

		return $this->_response($output);
	}

	protected function _response($output) {
		$response = json_decode($output);
		if (is_null($response) || !($response instanceof \stdClass)) {
			throw new RestJsonDecodeException("\nKount REST response JSON decode error.\n"
				. "- Raw output: {$output}\n");
		}
		switch ($response->status) {
			case self::RESPONSE_SUCCESS:
				return $response;
			case self::RESPONSE_FAILURE:
				throw new RestResponseFailureException("\nKount REST response failure.\n"
					. "- Status: {$response->status}\n"
					. "- Raw output: {$output}\n"
					. "- JSON decoded response:\n"
					. print_r($response, true) . "\n");
				break;
			default:
				throw new RestUnknowStatusException("\nUnknown Kount REST response status.\n"
					. "- Status: {$response->status}\n"
					. "- Raw output: {$output}\n"
					. "- JSON decoded response:\n"
					. print_r($response, true) . "\n");
				break;
		}
	}

}
