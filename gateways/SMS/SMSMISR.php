<?php
namespace Phpanonymous\Gateways\SMS;
use Illuminate\Support\Facades\Http;

class SMSMISR {
	// Sandbox or Live Link
	private $link = 'https://smsmisr.com/api';

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
		$this->config = (object) config('gateways.sms.smsmisr');

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

	/**
	 * set the payment_method to binding in params
	 * @return method chaining or Exception
	 */
	public function method($method): self {
		if (in_array($method, $this->methods)) {
			$this->params['method'] = $method;
			$this->signature();

			return $this;
		} else {
			// throw exception if not set payment method from array
			throw new \Exception('Set payment Method ' . implode(',', $this->methods));
		}
	}

	/**
	 * @param $response
	 * @return array
	 */
	public function response($response): array{
		return [
			'status' => $response->status(),
			'json' => $response->json(),
			'body' => $response->body(),
			'collect' => $response->collect(),
			'ok' => $response->ok(),
			'successful' => $response->successful(),
			'failed' => $response->failed(),
			'serverError' => $response->serverError(),
			'clientError' => $response->clientError(),
			'headers' => $response->headers(),
		];
	}

	public function mobile($mobile): self{
		$this->params['mobile'] = is_array($mobile) ? implode(',', $mobile) : $mobile;
		return $this;
	}

	public function language($language): self{
		$this->params['language'] = $language;
		return $this;
	}

	public function sender($sender): self{
		$this->params['sender'] = $sender;
		return $this;
	}

	public function message($message): self{
		$this->params['message'] = $message;
		return $this;
	}

	public function DelayUntil($DelayUntil): self{
		$this->params['DelayUntil'] = $DelayUntil;
		return $this;
	}
	public function request($status): self{
		$this->params['request'] = $status;
		return $this;
	}

	public function smsid($SMSID): self{
		$this->params['smsid'] = $SMSID;
		return $this;
	}

	public function send() {
		unset($this->params['method']);
		$send = $this->response(
			Http::withHeaders($this->headers)
				->post($this->links['SEND'], $this->params)
		);
		return $send;
		if ($send['status'] == 500) {
			return [
				'status' => $send['status'],
				'code' => 500,
				'message' => $send['body'],
			];
		} else {
			return [
				'status' => $send['status'],
				'code' => $send['collect']['code'],
				'message' => $this->code($send['collect']['code']),
				'data' => $send['collect'],
			];
		}
	}

	public function balance() {
		unset($this->params['method'], $this->params['language'], $this->params['sender']);
		$balance = $this->response(
			Http::
				post($this->links['BALANCE'] .
				'?username=' . $this->params['username'] . '&'
				. 'password=' . $this->params['password'] . '&'
				. 'smsid=' . $this->params['smsid'] . '&'
				. 'request=' . $this->params['request'] . '&'
			)
		);

		return [
			'status' => $balance['status'],
			'data' => $balance['collect'],
			'body' => $balance['body'],
		];
	}

	public function code($code) {
		$msg = [];
		$msg['1901'] = 'Success, Message Submitted Successfully';
		$msg['1902'] = 'Invalid URL , This means that one of the parameters was not provided';
		$msg['1200'] = 'You sent a lot of requests at the same time , please make a delay at least 1 sec';
		$msg['1903'] = 'Invalid value in username or password field';
		$msg['1904'] = 'Invalid value in "sender" field';
		$msg['1905'] = 'Invalid value in "mobile" field';
		$msg['1906'] = 'Insufficient Credit.';
		$msg['1907'] = 'Server under updating';
		$msg['1908'] = 'Invalid Date & Time format in “DelayUntil=” parameter';
		$msg['1909'] = 'Error In Message';
		$msg['8001'] = 'Mobile IS Null';
		$msg['8002'] = 'Message IS Null';
		$msg['8003'] = 'Language IS Null';
		$msg['8004'] = 'Sender IS Null';
		$msg['8005'] = 'Username IS Null';
		$msg['8006'] = 'Password IS Null';
		$msg['8006'] = 'Password IS Null';
		$msg['6000'] = 'Success, Request Submitted Successfully';
		$msg['Error'] = 'Invalid URL , This means that one of the parameters was not provided or wrong information';
		return isset($msg[$code]) ? $msg[$code] : '';
	}
}