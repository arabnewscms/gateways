<?php

namespace Phpanonymous\Gateways\SMS;

use Illuminate\Support\Facades\Http;

//TODO:: implement interface to make easier for developer to add new gateways
//TODO:: handle errors methods

class ForJawaly {

	// Sandbox or Live Link
	private $link = 'http://www.4jawaly.net/api/';

	/**
	 * links method to collect and prepare all link with fawry
	 * @return array
	 */
	private $links = [];

	// Params  binding to Guzzel POST & GET
	private $params = [];

	// with method
	private $method;

	// Methods
	private $methods = ['SEND', 'BALANCE'];

    public function __construct(){
        $this->config = (object) config('gateways.sms.4jawaly');

        $this->params['sender']   = $this->config->sender;
		$this->params['username'] = $this->config->username;
		$this->params['password'] = $this->config->password;
		$this->params['unicode'] = 'E';
		$this->params['return']   = 'json';

        $this->links = [
			'SEND' => $this->link . 'sendsms.php',
			'BALANCE' => $this->link . 'getbalance.php',
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
	 * to generate signature code for gateway
	 * @return void
	 */
	protected function signature(): void {
		if ($this->method == 'SEND') {
			$params = ['sender', 'username', 'password', 'unicode', 'return', 'numbers', 'message'];
		} elseif ($this->method == 'BALANCE') {
			$params = ['sender', 'username', 'password', 'unicode', 'return'];
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
			$this->method = $method;
			return $this;
		} else {
			// throw exception if not set payment method from array
			throw new \Exception('Set payment Method ' . implode(',', $this->methods));
		}
	}

    public function mobile($mobile): self{
		$this->params['numbers'] = is_array($mobile) ? implode(',', $mobile) : $mobile;
		return $this;
	}

	public function message($message): self{
		$this->params['message'] = $message;
		return $this;
	}

    public function send(){
		$this->signature();
		unset($this->params['method']);
        $response = Http::get( $this->links['SEND'] ,$this->params);  
        return $response->json();
    }
    
	public function balance() {
		$this->signature();
		unset($this->params['method'], $this->params['numbers'], $this->params['message']);
        $response = Http::get( $this->links['BALANCE'] ,$this->params);  
        return $response->json();
    }

}  