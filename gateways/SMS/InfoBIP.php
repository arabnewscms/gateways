<?php
namespace Phpanonymous\Gateways\SMS;

class InfoBIP {
	// Sandbox or Live Link
	private $link;

	/**
	 * links method to collect and prepare all link with fawry
	 * @return array
	 */
	private $links = [];

	// Params  binding to Guzzel POST & GET
	private $params = [];

	// with Headers
	private $headers;

	// Methods
	private $methods = ['SEND'];

	public function __construct() {
		$this->config = (object) config('gateways.sms.infobip');

		$this->locale = app()->getLocale();

		$this->link = $this->config->base_url;

		$this->params['from'] = $this->config->sender;

		//set Headers
		$this->headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => $this->config->public_api_key,
		];

		// Links gateway Methods
		$this->links = [
			'SEND' => $this->link . '/sms/2/text/advanced',
		];
	}

	/**
	 * function signature
	 * to generate signature code for fawry
	 * @return void
	 */
	protected function signature(): void {
		if ($this->method == 'SEND') {
			$params = ['destinations', 'text'];
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
			return $this;
		} else {
			// throw exception if not set payment method from array
			throw new \Exception('Set payment Method ' . implode(',', $this->methods));
		}

	}
}