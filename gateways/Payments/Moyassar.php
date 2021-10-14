<?php
namespace Phpanonymous\Gateways\Payments;
use Illuminate\Support\Facades\Http;

class Moyassar {
	// Local Framework Laravel (ar,en etc..)
	private $locale;

	// Sandbox or Live Link
	private $link;

	/**
	 * links method to collect and prepare all link with moyassar
	 * @return array
	 */
	private $links = [];

	// Params binding to Guzzel POST & GET
	private $params = [];

	// payment_method
	private $payment_method;

	// available payment methods and cancel and refund api
	protected $methods = ['CREDITCARD', 'APPLEPAY', 'STCPAY', 'SADAD', 'MADA'];

	public function __construct() {
		$this->config = (object) config('gateways.payments.moyassar');
		$this->locale = app()->getLocale() == 'ar' ? 'ar' : 'en';

		// Set live & sandbox keys publishable_key | secret_key
		$this->params['secret_key'] = $this->config->mode == 'live' ? $this->config->live_secret_key : $this->config->test_secret_key;

		$this->params['publishable_api_key'] = $this->config->mode == 'live' ? $this->config->live_publishable_key : $this->config->test_publishable_key;

		//$this->params['language'] = $this->locale == 'ar' ? 'ar' : 'en';

		// prepare links
		$this->link = $this->config->mode == 'sandbox' ? 'https://api.moyasar.com' : 'https://api.moyasar.com';

		$this->links = [
			"CREDITCARD" => $this->link . "/v1/payments",
			"APPLEPAY" => $this->link . "/v1/payments",
			"STCPAY" => $this->link . "/v1/payments",
			"SADAD" => $this->link . "/v1/payments",
			"MADA" => $this->link . "/v1/payments",
		];

	}

	/**
	 * @function checkPrams to check param exists
	 * @return Exception
	 */
	protected function checkParams($params = []) {
		foreach ($params as $param) {
			if (!isset($this->params[$param])) {
				throw new \Exception($param . ' parameter not found');
			}
		}
	}

	/**
	 * perpare param hash
	 * @return $hash params
	 */
	protected function getParams($params = []) {
		$params_list = '?source[type]=' . strtolower($this->params['source[type]']) . '&';
		foreach ($params as $param) {
			$params_list .= $param . '=' . $this->params[$param] . '&';
		}
		return rtrim($params_list, '&');
	}

	/**
	 * function signature
	 * to generate signature code for fawry
	 * @return hash sha256
	 */
	protected function signature() {
		if ($this->payment_method == 'CREDITCARD') {
			$params = ['callback_url', 'amount', 'currency', 'description', 'source[3ds]', 'source[name]', 'source[number]', 'source[cvc]', 'source[month]', 'source[year]'];
		} elseif ($this->payment_method == 'APPLEPAY') {
			$params = ['source[type]', 'source[3ds]', 'source[token]', 'amount'];
		} elseif ($this->payment_method == 'STCPAY') {
			$params = ['source[type]', 'source[mobile]', 'source[branch]', 'source[cashier]', 'source[3ds]'];
		}

		if (isset($params)) {
			$this->checkParams($params);
			return $this->getParams($params);
		}
	}

	/**
	 * set the payment_method to binding in params
	 * @return method chaining or Exception
	 */
	public function method($method): self {
		if (in_array($method, $this->methods)) {
			$this->params['source[type]'] = strtolower($method);
			$this->payment_method = $method;

			return $this;
		} else {
			// throw exception if not set payment method from array
			throw new \Exception('set payment Method ' . implode(',', $this->methods));
		}
	}

	/**
	 * query method
	 * to binding params
	 * @return method chaining
	 */
	public function query($query = []): self{
		$this->params = array_merge($query, $this->params);
		$this->signature();
		return $this;
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

	/**
	 * auto redirect to transaction url
	 * @return method chaining
	 */
	public function redirect() {
		$this->params['redirect'] = true;
		return $this;
	}

	/**
	 * fetch data
	 * @return array
	 */
	public function fetch($str_id = null) {
		return $this->response(
			Http::get($this->links['CREDITCARD'] . '/' . $str_id . '?publishable_api_key=' . $this->params['secret_key'])
		);
	}

	/**
	 * capture data payment
	 * @return array
	 */
	public function capture($str_id = null) {
		$params = isset($this->params['amount']) ? ['amount' => $this->params['amount']] : [];
		return $this->response(Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])->post($this->links['CREDITCARD'] . '/' . $str_id . '/capture?source[type]=' . strtolower($this->payment_method) . '&publishable_api_key=' . $this->params['secret_key'], $params)
		);
	}

	/**
	 * unPaid data payment
	 * @return array
	 */
	public function unPaid($str_id = null) {
		return $this->response(Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])->post($this->links['CREDITCARD'] . '/' . $str_id . '/void?source[type]=' . strtolower($this->payment_method) . '&publishable_api_key=' . $this->params['secret_key'])
		);
	}

	/**
	 * refund data payment
	 * @return array
	 */
	public function refund($str_id = null) {
		$params = isset($this->params['amount']) ? ['amount' => $this->params['amount']] : [];

		return $this->response(Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])->post($this->links['CREDITCARD'] . '/' . $str_id . '/refund?source[type]=' . strtolower($this->payment_method) . '&publishable_api_key=' . $this->params['secret_key'], $params)
		);
	}

	/**
	 * update data payment
	 * @return array
	 */
	public function update($str_id = null) {
		$params = isset($this->params['description']) ? ['description' => $this->params['description']] : [];

		return $this->response(Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])->put($this->links['CREDITCARD'] . '/' . $str_id . '?source[type]=' . strtolower($this->payment_method) . '&publishable_api_key=' . $this->params['secret_key'], $params)
		);
	}

	/**
	 * List payments
	 * @return array
	 */
	public function get() {
		unset($this->params['publishable_api_key']);
		$params = '?publishable_api_key=' . $this->params['secret_key'] . '&';
		foreach ($this->params as $key => $val) {
			$params .= $key . '=' . $val . '&';
		}
		$params = rtrim($params, '&');
		$response = $this->response(
			Http::get($this->links['CREDITCARD'] . $params)
		);
		unset($response['json'], $response['body']);
		return $response;
	}

	/**
	 * purchase to pay with multi methods
	 * @param null
	 * @return array
	 */
	public function purchase() {
		$response = $this->response(
			Http::withHeaders([
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			])->post($this->links[$this->payment_method] . $this->signature(), $this->params)
		);
		if (isset($this->params['redirect'])) {
			if ($response['status'] == 201 && $response['collect']['status'] == 'initiated') {
				return redirect($response['collect']['source']['transaction_url']);
			} else {
				return $response;
			}
		} else {
			return $response;
		}
	}

}