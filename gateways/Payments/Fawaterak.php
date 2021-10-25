<?php
namespace Phpanonymous\Gateways\Payments;
use Illuminate\Support\Facades\Http;

class Fawaterak {
	// Local Framework Laravel (ar,en etc..)
	private $locale;

	//version
	private $version = 'api/v2';

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

	// with Headers
	private $headers;

	// save response as array
	private $response;

	// available payment methods and cancel and refund api
	protected $methods = [
		'INVOICE',
		'METHODS',
		'PAY',
	];

	protected $currency = ['USD', 'EGP', 'SR', 'AED', 'KWD', 'QAR', 'BHD'];

	public function __construct() {
		$this->config = (object) config('gateways.payments.fawaterk');

		$this->locale = app()->getLocale() == 'ar' ? 'AR' : 'EN';

		//set Headers
		$this->headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $this->config->api_key,
		];

		// prepare links
		$this->link = $this->config->mode == 'live' ? 'https://fawaterk.com' : 'https://fawaterkstage.com';

		// Links gateway Methods
		$this->links = [
			"INVOICE" => $this->link . "/" . $this->version . "/createInvoiceLink",
			"METHODS" => $this->link . "/" . $this->version . "/getPaymentmethods",
			"PAY" => $this->link . "/" . $this->version . "/invoiceInitPay",

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
					//dd($param . !in_array($param, array_keys($this->params), false));
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
		if ($this->payment_method == 'INVOICE' || $this->payment_method == 'PAY') {
			$params = ['currency', 'cartTotal', 'customer', 'cartItems'];
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
	 * create invoice
	 * @param null
	 * @return array
	 */
	public function create() {
		if (!in_array($this->params['currency'], $this->currency)) {
			throw new \Exception('currency accepted ' . implode(',', $this->currency));
		}
		unset($this->params['method']);
		$this->response = $this->response(
			Http::withHeaders($this->headers)
				->post($this->links[$this->payment_method], $this->params)
		);

		if ($this->response['collect']['status'] == 422) {
			return $this->response;
		} elseif (isset($this->params['redirect']) && $this->response['collect']['status'] == 'success') {
			//payment_data
			if ($this->payment_method == 'PAY') {
				return redirect($this->response['collect']['data']['payment_data']['redirectTo']);
			} else {

				return redirect($this->response['collect']['data']['url']);
			}
		} else {
			return $this->response['collect'];
		}
	}

	/**
	 * get Payment methods
	 * @param null
	 * @return array
	 */
	public function all() {
		$this->response = $this->response(
			Http::withHeaders($this->headers)
				->get($this->links['METHODS'])
		);
		return $this->response['collect'];
	}

}