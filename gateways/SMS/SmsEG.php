<?php
namespace Phpanonymous\Gateways\SMS;

class SmsEG {
	// Sandbox or Live Link
	private $link = 'https://smssmartegypt.com/sms/api';

	/**
	 * links method to collect and prepare all link with fawry
	 * @return array
	 */
	private $links = [];

	// Params  binding to Guzzel POST & GET
	private $params = [];

	// with Headers
	private $headers;

	// with method
	private $method;

	// Methods
	private $methods = ['SEND', 'BALANCE'];

	public function __construct() {
		$this->config = (object) config('gateways.sms.smseg');

		$this->locale = app()->getLocale();

		$this->params['language'] = $this->config->language;
		$this->params['sender'] = $this->config->sender;
		$this->params['username'] = $this->config->username;
		$this->params['password'] = $this->config->password;
		//$this->params['SMSID'] = $this->config->sender;

		//set Headers
		$this->headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		];

		// Links gateway Methods
		$this->links = [
			'SEND' => $this->link . '/v2/',
			'BALANCE' => $this->link . '/Request',
		];
	}

	/**
	 * @function checkPrams to check param exists
	 * @return Exception
	 */
	protected function checkParams($params = [], $default = null) {
		foreach ($params as $param) {
			$param = strtolower($param);
			if (is_null($default)) {
				$all_keys = array_keys($this->params);
				$all_keys = array_map('strtolower', $all_keys);

				if (!in_array($param, $all_keys)) {
					dd($param . !in_array($param, array_keys($this->params), false));
					throw new \Exception($param . ' parameter not found');
				}
			} else {
				$all_keys = array_keys($default);
				$all_keys = array_map('strtolower', $all_keys);

				if (!in_array($param, $all_keys, true)) {
					throw new \Exception($param . ' parameter not found');
				}
			}
		}
	}

	/**
	 * function signature
	 * to generate signature code for fawry
	 * @return void
	 */
	protected function signature(): void {
		if ($this->method == 'SEND') {
			$params = ['Username', 'password', 'language', 'sender', 'Mobile', 'message'];
		} elseif ($this->method == 'BALANCE') {
			$params = ['username', 'password', 'smsid'];
		}

		if (isset($params)) {
			$this->checkParams($params);
		}
	}

}