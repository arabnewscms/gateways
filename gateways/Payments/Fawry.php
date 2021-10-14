<?php
namespace Phpanonymous\Gateways\Payments;
use Illuminate\Support\Facades\Http;

class Fawry {
	// Local Framework Laravel (ar,en etc..)
	private $locale;

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
	protected $methods = ['CARD', 'MWALLET', 'PAYATFAWRY', 'VALU', 'CASHONDELIVERY', 'REFUND', 'CANCEL'];

	public function __construct() {
		$this->config = (object) config('gateways.payments.fawry');
		$this->locale = app()->getLocale() == 'ar' ? 'ar-eg' : 'en-gb';

		// Set merchantCode & secureKey from config
		$this->params['merchantCode'] = $this->config->merchant_code;
		$this->params['secureKey'] = $this->config->security_key;
		$this->params['language'] = $this->locale == 'ar' ? 'ar-eg' : 'en-gb';

		// prepare links
		$this->link = $this->config->mode == 'sandbox' ? 'https://atfawry.fawrystaging.com' : 'https://www.atfawry.com';

		$this->links = [
			"installment_plans" => $this->link . "/ECommerceWeb/api/merchant/installment-plans",
			"CARD" => $this->link . "/ECommerceWeb/Fawry/payments/charge",
			"PAYATFAWRY" => $this->link . "/ECommerceWeb/Fawry/payments/charge",
			"MWALLET" => $this->link . "/ECommerceWeb/api/payments/charge",
			"VALU" => $this->link . "/ECommerceWeb/api/payments/charge",
			"CASHONDELIVERY" => $this->link . "/ECommerceWeb/Fawry/payments/charge",
			"STATUS_V1" => $this->link . "/ECommerceWeb/Fawry/payments/status",
			"STATUS_V2" => $this->link . "/ECommerceWeb/Fawry/payments/status/v2",
			"REFUND" => $this->link . "/ECommerceWeb/Fawry/payments/refund",
			"CANCEL" => $this->link . "/ECommerceWeb/api/orders/cancel-unpaid-order",
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
		$params_list = '';
		foreach ($params as $param) {
			$params_list .= $this->params[$param];
		}
		return $params_list;
	}
	/**
	 * function signature
	 * to generate signature code for fawry
	 * @return hash sha256
	 */
	protected function signature(): string {
		if (isset($this->params['version'])) {
			// authrize and capture
			$params = ['merchantCode', 'merchantRefNum', 'secureKey'];
		} elseif ($this->params['payment_method'] == 'PAYATFAWRY') {
			// PAYATFAWRY
			$params = ['merchantCode', 'merchantRefNum', 'customerProfileId', 'payment_method', 'amount', 'secureKey'];

		} elseif ($this->params['payment_method'] == 'MWALLET') {
			//MWALLET
			if (isset($this->params['customerProfileId'])) {
				$params = ['merchantCode', 'merchantRefNum', 'customerProfileId', 'payment_method', 'amount', 'customerMobile', 'secureKey'];

			} else {
				$params = ['merchantCode', 'merchantRefNum', 'payment_method', 'amount', 'customerMobile', 'secureKey'];
			}

		} elseif ($this->params['payment_method'] == 'VALU') {
			//VALU
			$params = ['merchantCode', 'merchantRefNum', 'customerProfileId', 'payment_method', 'amount', 'valuCustomerCode', 'secureKey'];

		} elseif ($this->params['payment_method'] == 'REFUND') {
			//REFUND
			if (isset($this->params['reason'])) {
				$params = ['merchantCode', 'referenceNumber', 'refundAmount', 'reason', 'secureKey'];
			} else {
				$params = ['merchantCode', 'referenceNumber', 'refundAmount', 'secureKey'];
			}

		} elseif ($this->params['payment_method'] == 'CANCEL') {
			//CANCEL payment
			$params = ['orderRefNo', 'merchantAccount', 'lang', 'secureKey'];

		} else {
			// 3D secure && pay using bank installments
			if (isset($this->params['customerProfileId']) && isset($this->params['installmentPlanId'])) {
				$params = ['merchantCode', 'merchantRefNum', 'customerProfileId', 'payment_method', 'amount', 'cardNumber', 'cardExpiryYear', 'cardExpiryMonth', 'cvv', 'installmentPlanId', 'secureKey'];
			} elseif (isset($this->params['installmentPlanId'])) {

				$params = ['merchantCode', 'merchantRefNum', 'customerProfileId', 'payment_method', 'amount', 'cardNumber', 'cardExpiryYear', 'cardExpiryMonth', 'cvv', 'installmentPlanId', 'secureKey'];

			} elseif (isset($this->params['customerProfileId'])) {

				$params = ['merchantCode', 'merchantRefNum', 'customerProfileId', 'payment_method', 'amount', 'cardNumber', 'cardExpiryYear', 'cardExpiryMonth', 'cvv', 'returnUrl', 'secureKey'];
			} else {
				$params = ['merchantCode', 'merchantRefNum', 'payment_method', 'amount', 'cardNumber', 'cardExpiryYear', 'cardExpiryMonth', 'cvv', 'returnUrl', 'secureKey'];
			}

		}

		$this->checkParams($params);
		return hash('sha256', $this->getParams($params));
	}

	/**
	 * set the payment_method to binding in params and make signature
	 * @return method chaining or Exception
	 */
	public function method($method): self {
		if (in_array($method, $this->methods)) {
			$this->params['payment_method'] = $method;
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
		$this->params['signature'] = $this->signature();
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
	 * get installment by account number to pay by installment installmentPlan
	 * @param $query
	 * @return array
	 */
	public function getInstallment($query = []): array{
		$this->params = array_merge($query, $this->params);
		// check if account number not binding and check if query function played
		if (!isset($this->params['accountNumber']) || isset($this->params['accountNumber']) && empty($this->params['accountNumber'])) {
			throw new \Exception('in installment please add accountNumber parameter');
		}

		// get installment plan info
		return $this->response(Http::get($this->links['installment_plans'], [
			'query' => [
				'accountNumber' => $this->params['accountNumber'],
			],
		]));
	}

	/**
	 * purchase to pay with multi methods
	 * @param null
	 * @return array
	 */
	public function purchase() {

		return $this->response(
			Http::withHeaders([
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			])
				->asJson(json_encode($this->params))
				->post($this->links[$this->payment_method])
		);
	}

	/**
	 * status method to get payment status
	 * @param null
	 * @return array
	 */
	public function status() {
		if (!isset($this->params['version']) || !in_array($this->params['version'], [1, 2])) {
			throw new \Exception('Select version 1 or 2 this is available versions');
		}
		$version = $this->params['version'];
		// payment status unset this params to get correct query
		unset($this->params['language'], $this->params['payment_method'], $this->params['version'], $this->params['secureKey']);
		// standard params on live query {merchantRefNum,merchantCode,signature}
		return $this->response(
			Http::withHeaders([
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			])
				->asJson(json_encode($this->params))
				->post($this->links['STATUS_V' . $version])
		);
	}

	/**
	 * refund method to refund payment
	 * @param null
	 * @return array
	 */
	public function refund() {
		// payment status unset this params to get correct query
		unset($this->params['language'], $this->params['payment_method'], $this->params['secureKey']);
		return $this->response(Http::get($this->links['REFUND'],
			[
				'query' => $this->params,
			]));
	}

	/**
	 * unPaid method to cancel order payment
	 * @param null
	 * @return array
	 */
	public function unPaid() {
		// payment status unset this params to get correct query
		unset($this->params['language'], $this->params['payment_method'], $this->params['secureKey'], $this->params['merchantCode']);
		//return $this->params;
		return $this->response(
			Http::withHeaders([
				'Content-Type' => 'application/json',
				'Accept' => 'application/json',
			])
				->asJson(json_encode($this->params))
				->post($this->links['CANCEL'])
		);
	}

}