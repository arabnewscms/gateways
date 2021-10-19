<?php

namespace Phpanonymous\Gateways\Payments;

use Illuminate\Support\Facades\Http;

class Paymob {

	// Sandbox or Live Link
	private $link;

	/**
	 * links method to collect and prepare all link with fawry
	 * @return array
	 */
	private $links = [];

	// Params  binding to Guzzel POST & GET
	private $params = [];

	// payment_method
	private $payment_method;

	// available payment methods and cancel and refund api
	protected $methods = ['CARD', 'SOUHOOLA', 'GET_GO', 'VALU', 'BANK_INSTALLMENTS', 'PREMIUM_CARD', 'KIOSK', 'MWALLET','CASHONDELIVERY', 'REFUND', 'CANCEL'];

	public function __construct() {
		$this->config = (object) config('gateways.payments.paymob');

		// Set api_key from config
		$this->params['api_key'] = $this->config->api_key;

		// prepare links
		$this->link = $this->config->mode == 'sandbox' ? 'https://accept.paymob.com' : 'https://accept.paymob.com';

		$this->links = [
			"TOKENS" => $this->link . "/api/auth/tokens",
			"ORDERS" => $this->link . "/api/ecommerce/orders",
			"PAYMENT_KEYS" => $this->link . "/api/acceptance/payment_keys",
			"CARD" => $this->link . "/api/acceptance/iframes/",
			"VALU" => $this->link . "/api/acceptance/iframes/",
			"SOUHOOLA" => $this->link . "/api/acceptance/iframes/",
			"GET_GO" => $this->link . "/api/acceptance/iframes/",
			"BANK_INSTALLMENTS" => $this->link . "/api/acceptance/iframes/",
			"PREMIUM_CARD" => $this->link . "/api/acceptance/iframes/",
			"KIOSK" => $this->link . "/api/acceptance/payments/pay",
			"MWALLET" => $this->link . "/api/acceptance/payments/pay",
			"CASHONDELIVERY" => $this->link . "/api/acceptance/payments/pay",
			"REFUND" => $this->link . "/api/acceptance/void_refund/refund",
			"CANCEL" => $this->link . "/api/acceptance/void_refund/void",
		];
	}

	/**
	 * Authentication request using api_key
	 * @param null
	 * @return array
	 */
	public function autheticateRequest(): string {
		$jsonReponse = Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])
		->post($this->links["TOKENS"], $this->params);
		
		$response = json_decode($jsonReponse, true);

		if ($jsonReponse->status() != 201) {
			if (isset($this->params['redirect'])) {
				return redirect()->back()->with(["error_message" => $response['message']]);	
			}
			return $this->response($jsonReponse);
		}
		// remove api_key from params
		unset($this->params['api_key']);
		return $response['token'];
	}

	/**
	 * Register order in paymob database
	 * use token from authentication step 1
	 * @param string $authToken
	 * @return array
	 */
	public function registerOrder($authToken): string {
		$this->params['auth_token'] = $authToken;
		$jsonReponse = Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])
		->post($this->links["ORDERS"], $this->params);
		$response = json_decode($jsonReponse, true);
		if ($jsonReponse->status() != 201) {
			if (isset($this->params['redirect'])) {
				return redirect()->back()->with(["error_message" => $response['message']]);	
			}
			return $this->response($jsonReponse);
		}
		return $response['id'];
	}

	/**
	 * Obtain payment key token to be used to authenticate your payment request
	 * use order id from register order step 2
	 * @param string $orderId
	 * @return array
	 */
	public function getPaymentKeyToken($orderId): string {
		$this->params['order_id'] = $orderId;
		$jsonReponse = Http::withHeaders([
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
		])
		->post($this->links["PAYMENT_KEYS"], $this->params);
		$response = json_decode($jsonReponse, true);
		if ($jsonReponse->status() != 201) {
			if (isset($this->params['redirect'])) {
				return redirect()->back()->with(["error_message" => $response['message']]);	
			}
			return $this->response($jsonReponse);
		}
		return $response['token'];
	}

	public function setIntegrationId()
	{		
		if ($this->payment_method == "CARD") {
			$this->params['integration_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_integration_card_id : $this->config->integration_card_id);
			$this->params['iframe_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_iframe_card_id : $this->config->iframe_card_id);
		} else if ($this->payment_method == "VALU"){
			$this->params['integration_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_integration_valu_id : $this->config->integration_valu_id);
			$this->params['iframe_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_iframe_valu_id : $this->config->iframe_valu_id);
		} else if ($this->payment_method == "SOUHOOLA"){
			$this->params['integration_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_integration_souhoola_id : $this->config->integration_souhoola_id);
			$this->params['iframe_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_iframe_souhoola_id : $this->config->iframe_souhoola_id);
		} else if ($this->payment_method == "GET_GO"){
			$this->params['integration_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_integration_get_go_id : $this->config->integration_get_go_id);
			$this->params['iframe_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_iframe_get_go_id : $this->config->iframe_get_go_id);
		} else if ($this->payment_method == "PREMIUM_CARD"){
			$this->params['integration_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_integration_premium_card_id : $this->config->integration_premium_card_id);
			$this->params['iframe_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_iframe_premium_card_id : $this->config->iframe_premium_card_id);
		} else if ($this->payment_method == "BANK_INSTALLMENTS"){
			$this->params['integration_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_integration_bank_installment_id : $this->config->integration_bank_installment_id);
			$this->params['iframe_id'] = ($this->config->mode == 'sanbox' ? $this->config->test_iframe_bank_installment_id : $this->config->iframe_bank_installment_id);
		}
	}

	/**
	 * set the payment_method to bind integration_id to params
	 * @return method chaining or Exception
	 */
	public function method($method): self {
		if (in_array($method, $this->methods)) {
			$this->payment_method = $method;
			$this->setIntegrationId();
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
		// $this->params['signature'] = $this->signature();
		return $this;
	}

	/**
	 * auto redirect to iframe url
	 * @return method chaining
	 */
	public function redirect() {
		$this->params['redirect'] = true;
		return $this;
	}

	/**
	 * get iframe url
	 * @return method chaining
	 */
	public function getIframeUrl($paymentKey) {
		return $this->links[$this->payment_method] . $this->params['iframe_id'] . "?payment_token=" . $paymentKey;
	}

	/**
	 * @param $response
	 * @return array
	 */
	public function response($response) {
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
	 * purchase to pay with multi methods
	 * @param null
	 * @return array
	 */
	public function purchase() {
		// step 1: authenticate request
		$authToken = $this->autheticateRequest();
		// step 2: register order in paymob
		$orderId = $this->registerOrder($authToken);
		// step 3: get payment key request
		$paymentKey = $this->getPaymentKeyToken($orderId);
		// step 4: get Iframe url
		$iframeUrl = $this->getIframeUrl($paymentKey);
		
		if (isset($this->params['redirect'])) {
			return redirect($iframeUrl);
		}
		
		$response = response()->json(['iframe_url' => $iframeUrl]);
		return $this->response($response);
	}
}
