<?php
namespace Phpanonymous\Gateways\Payments;
use Illuminate\Support\Facades\Http;

class MyFatoorah {
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

	// version
	private $version;

	//token
	private $token;

	// save to response data
	private $response;

	// with Headers
	private $headers;

	// with countries
	private $countries;

	// with cities
	private $cities;

	// available payment methods and cancel and refund api
	protected $methods = [
		'CANCELTOKEN',
		'RECURRING',
		'CANCELRECURRING',
		'INVOICE',
		'INVOICE_SHIPPING',
	];

	protected $NotificationOption = [
		'EML', // send the invoice link by email only. You should provide the CustomerEmail parameter as well.
		'SMS', // send the invoice link by SMS only. You should provide the CustomerMobile and MobileCountryCode as well.
		'LNK', // returns only the invoice URL through the response
		'ALL', // send the invoice link by both email and SMS, also the invoice link will be in the response body. You have to provide all the needed parameters
	];

	public function __construct() {
		$this->config = (object) config('gateways.payments.myfatoorah');

		$this->locale = app()->getLocale() == 'ar' ? 'AR' : 'EN';

		// Set live & sandbox keys secret_key
		$this->params['secret_key'] = $this->config->secret_key;
		$this->token = $this->config->token;

		// set api version
		$this->version = 'v' . $this->config->version;

		//set Headers
		$this->headers = [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => 'Bearer ' . $this->token,
		];

		// Set Language
		$this->params['language'] = $this->locale;

		// prepare links
		$this->link = $this->config->mode == 'live' ? 'https://api.myfatoorah.com' : 'https://apitest.myfatoorah.com';

		// Links gateway Methods
		$this->links = [
			"INVOICE" => $this->link . "/" . $this->version . "/SendPayment",
			"INITIAT" => $this->link . "/" . $this->version . "/InitiatePayment",
			"EXECUTE" => $this->link . "/" . $this->version . "/ExecutePayment",
			"CANCELTOKEN" => $this->link . "/" . $this->version . "/CancelToken",
			"GetRECURRING" => $this->link . "/" . $this->version . "/GetRecurringPayment",
			"CANCELRECURRING" => $this->link . "/" . $this->version . "/CancelRecurringPayment",
			"PAYMENTSTATUS" => $this->link . "/" . $this->version . "/getPaymentStatus",
			"REFUND" => $this->link . "/" . $this->version . "/MakeRefund",
			"GETCOUNTRIES" => $this->link . "/" . $this->version . "/GetCountries",
			"GETCITIES" => $this->link . "/" . $this->version . "/Getcities",
			"CALCULATE" => $this->link . "/" . $this->version . "/CalculateShippingCharge",
			"CREATESUPPLIER" => $this->link . "/" . $this->version . "/CreateSupplier",
			"EDITSUPPLIER" => $this->link . "/" . $this->version . "/EditSupplier",
			"GETSUPPLIER" => $this->link . "/" . $this->version . "/GetSuppliers",
			"GETSUPPLIERDEPOSITS" => $this->link . "/" . $this->version . "/GetSupplierDeposits",
			"GETSUPPLIERDOCUMENTS" => $this->link . "/" . $this->version . "/GetSupplierDocuments",
			"GETSUPPLIERDASHBOARD" => $this->link . "/" . $this->version . "/GetSupplierDashboard",
			"UPLOADSUPPLIERDOCUMENT" => $this->link . "/" . $this->version . "/UploadSupplierDocument",
			"MAKESUPPLIERREFUND" => $this->link . "/" . $this->version . "/MakeSupplierRefund",
			"TRANSFERBALANCE" => $this->link . "/" . $this->version . "/TransferBalance",
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
		if ($this->payment_method == 'INVOICE') {
			$params = ['NotificationOption', 'InvoiceValue', 'CustomerName'];
		} elseif ($this->payment_method == 'INVOICE_SHIPPING') {
			$params = ['NotificationOption', 'InvoiceValue', 'CustomerName', 'InvoiceItems', 'ShippingConsignee', 'ShippingMethod'];
		} elseif ($this->payment_method == 'CANCELTOKEN') {
			$params = ['token'];
		} elseif ($this->payment_method == 'RECURRING') {
			$params = ['InvoiceValue', 'CallBackUrl', 'ErrorUrl', 'RecurringModel'];
		} elseif ($this->payment_method == 'CANCELRECURRING') {
			$params = ['recurringId'];
		} else {
			$params = ['InvoiceValue', 'CallBackUrl', 'ErrorUrl'];
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
		if (!in_array($this->params['NotificationOption'], $this->NotificationOption)) {
			throw new \Exception('NotificationOption accepted ' . implode(',', $this->NotificationOption));
		}
		$this->response = $this->response(
			Http::withHeaders($this->headers)
				->post($this->links['INVOICE'], $this->params)
		);
		if (isset($this->params['redirect']) && $this->response['status'] == '200' && $this->response['collect']['IsSuccess']) {
			return redirect($this->response['collect']['Data']['InvoiceURL']);
		} else {
			return $this->response;
		}
	}

	/**
	 * INITIAT method to create payment link
	 * @param null
	 * @return array
	 */
	public function init() {
		// check if available params
		$this->checkParams(['InvoiceAmount', 'CurrencyIso']);

		$this->response = $this->response(Http::withHeaders($this->headers)
				->post($this->links['INITIAT'], $this->params));
		// return all methods gateways
		return [
			'status' => $this->response['status'],
			'data' => $this->response['collect'],
		];
	}

	/**
	 * execute payment
	 * @param null
	 * @return method chaining
	 */
	public function card($query) {
		$this->params['card_info'] = [
			'secret_key' => $this->params['secret_key'],
			'language' => $this->params['language'],
		];

		$this->params['card_info'] = array_merge($query, $this->params['card_info']);

		if (isset($query['token']) && !empty($query['token'])) {
			// check if available directPayment Params
			$this->checkParams(['PaymentType', 'Card'], $this->params['card_info']);
			// check if available params for CARD Info
			$this->checkParams(['SecurityCode'], $this->params['card_info']['Card']);
		} else {
			// check if available directPayment Params
			$this->checkParams(['PaymentType', 'Bypass3DS', 'Card'], $this->params['card_info']);
			// check if available params for CARD Info
			$this->checkParams(['Number', 'ExpiryMonth', 'ExpiryYear', 'SecurityCode', 'CardHolderName'], $this->params['card_info']['Card']);
		}
		return $this;
	}

	/**
	 * execute payment
	 * @param null
	 * @return array
	 */
	public function exec($id) {
		// binding Card Info To Push direct payment link
		$card_info = isset($this->params['card_info']) ? $this->params['card_info'] : null;
		unset($this->params['method'], $this->params['card_info']);

		// set paymentMethodId
		$this->params['paymentMethodId'] = $id;
		// check if available params
		$this->checkParams(['InvoiceAmount', 'CurrencyIso']);

		$this->response = $this->response(Http::withHeaders($this->headers)
				->post($this->links['EXECUTE'], $this->params));

		// Direct Payment statement
		if (!empty($card_info) && $this->response['status'] == '200' && $this->response['collect']['IsSuccess']) {

			$directPayment = $this->response(Http::withHeaders($this->headers)
					->post($this->response['collect']['Data']['PaymentURL'], $card_info));
			if (isset($card_info['SaveToken']) && $card_info['SaveToken'] && $directPayment['status'] == 200) {
				session()->put('myfatoorah_token_id', $directPayment['collect']['Data']['Token']);
			}
			// Redirect Statment of Direct Payment
			return isset($this->params['redirect']) && $this->params['redirect']
			&& $directPayment['status'] == '200'
			&& $directPayment['collect']['IsSuccess'] ?
			redirect($directPayment['collect']['Data']['PaymentURL'])
			: $directPayment;

		} elseif (isset($this->params['redirect']) && $this->params['redirect'] && $this->response['status'] == '200' && $this->response['collect']['IsSuccess']) {
			return redirect($this->response['collect']['Data']['PaymentURL']);
		} else {
			return $this->response;
		}
	}

	/**
	 * execute payment
	 * @param null
	 * @return array
	 */
	public function cancelToken() {
		unset($this->params['method']);
		return $this->response(Http::withHeaders($this->headers)
				->post($this->links['CANCELTOKEN'], $this->params));
	}

	/**
	 * Get Recurring Payment
	 * @param null
	 * @return array
	 */
	public function getRecurringList() {
		$requrring = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GetRECURRING']));
		return $requrring['status'] == 200 ? $requrring['collect'] : $requrring;
	}

	/**
	 * Cancel Recurring Payment
	 * @param null
	 * @return array
	 */
	public function cancelRecurring() {
		return $this->response(Http::withHeaders($this->headers)
				->post($this->links['CANCELRECURRING'], $this->params));
	}

	/**
	 * PAYMENTSTATUS Payment
	 * @param null
	 * @return array
	 */
	public function status($query) {
		$this->checkParams(['Key', 'KeyType'], $query);
		$this->params = array_merge($query, $this->params);

		$status = $this->response(Http::withHeaders($this->headers)
				->post($this->links['PAYMENTSTATUS'], $this->params));
		return $status['status'] == 200 ? $status['collect'] : $status;
	}

	/**
	 * refund Payment
	 * @param null
	 * @return array
	 */
	public function refund($query) {
		$this->checkParams(['Key', 'KeyType', 'RefundChargeOnCustomer', 'ServiceChargeOnCustomer', 'Amount', 'Comment'], $query);
		$this->params = array_merge($query, $this->params);

		$refund = $this->response(Http::withHeaders($this->headers)
				->post($this->links['REFUND'], $this->params));
		return $refund['status'] == 200 ? $refund['collect'] : $refund;
	}

	/**
	 * GetCountries Payment
	 * @param null
	 * @return array
	 */
	public function getCountries($find = null) {
		$this->countries = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GETCOUNTRIES']));
		if (!is_null($find) && !empty($find) && $this->countries['status'] == 200) {
			foreach ($this->countries['collect']['Data'] as $country) {
				if ($country['CountryName'] == $find) {
					return $country;
				}
			}
			return [];
		} else {

			return $this->countries['status'] == 200 ? $this->countries['collect']['Data'] : $this->countries;
		}
	}

	/**
	 * GetCities Payment
	 * @param null
	 * @return array
	 */
	public function getCities($query) {
		$this->checkParams(['shippingMethod', 'countryCode'], $query);
		$this->params = array_merge($query, $this->params);
		// remove parameters
		unset($this->params['secret_key'], $this->params['language']);

		$this->cities = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GETCITIES'], $this->params));
		return $this->cities['status'] == 200 ? $this->cities['collect']['Data'] : $this->cities;
	}

	/**
	 * Calculate Shipping Charge
	 * @param null
	 * @return array
	 */
	public function calculate($query) {
		// Check public Parameters
		$this->checkParams(['shippingMethod', 'CityName', 'CountryCode', 'PostalCode', 'Items', 'Items'], $query);

		// Check Items with offset 0 Items[0] Parameters
		$this->checkParams(['ProductName', 'Description', 'Quantity', 'UnitPrice', 'Weight', 'Width', 'Height', 'Depth'], $query['Items'][0]);

		$this->params = array_merge($query, $this->params);
		// remove parameters
		unset($this->params['secret_key'], $this->params['language']);
		$calculate = $this->response(Http::withHeaders($this->headers)
				->post($this->links['CALCULATE'], $this->params));
		return $calculate['status'] == 200 ? $calculate['collect']['Data'] : $calculate;
	}

	/**
	 * CreateSupplier
	 * @param null
	 * @return array
	 */
	public function createSupplier($query) {
		$this->checkParams(['SupplierName', 'Mobile', 'Email'], $query);
		$this->params = array_merge($query, $this->params);
		return $this->response(Http::withHeaders($this->headers)
				->post($this->links['CREATESUPPLIER'], $this->params));
	}

	/**
	 * editSupplier
	 * @param null
	 * @return array
	 */
	public function editSupplier($query) {
		$this->checkParams(['SupplierCode', 'SupplierName', 'Mobile', 'Email'], $query);
		$this->params = array_merge($query, $this->params);
		return $this->response(Http::withHeaders($this->headers)
				->post($this->links['EDITSUPPLIER'], $this->params));
	}

	/**
	 * GETSupplier
	 * @param null
	 * @return array
	 */
	public function GetSuppliers() {
		$GETSUPPLIER = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GETSUPPLIER']));
		return $GETSUPPLIER['status'] == 200 ? $GETSUPPLIER['collect'] : $GETSUPPLIER;
	}

	/**
	 * GetSupplierDeposits
	 * @param null
	 * @return array
	 */
	public function GetSupplierDeposits($query) {
		$this->checkParams(['SupplierCode'], $query);
		$this->params = array_merge($query, $this->params);

		unset($this->params['secret_key'], $this->params['language']);

		$SupplierDeposits = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GETSUPPLIERDEPOSITS'], $this->params));
		return $SupplierDeposits['status'] == 200 ? $SupplierDeposits['collect'] : $SupplierDeposits;
	}

	/**
	 * GetSupplierDocuments
	 * @param null
	 * @return array
	 */
	public function GetSupplierDocuments($query) {
		$this->checkParams(['SupplierCode'], $query);
		$this->params = array_merge($query, $this->params);

		unset($this->params['secret_key'], $this->params['language']);

		$SupplierDocuments = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GETSUPPLIERDOCUMENTS'], $this->params));
		return $SupplierDocuments['status'] == 200 ? $SupplierDocuments['collect'] : $SupplierDocuments;
	}

	/**
	 * MakeSupplierRefund
	 * @param null
	 * @return array
	 */
	public function MakeSupplierRefund($query) {
		$this->checkParams(['Key', 'KeyType', 'Comment'], $query);
		$this->params = array_merge($query, $this->params);

		unset($this->params['secret_key'], $this->params['language']);

		$SupplierRefund = $this->response(Http::withHeaders($this->headers)
				->post($this->links['MAKESUPPLIERREFUND'], $this->params));
		return $SupplierRefund['status'] == 200 ? $SupplierRefund['collect'] : $SupplierRefund;
	}

	/**
	 * TransferBalance
	 * @param null
	 * @return array
	 */
	public function TransferBalance($query) {
		$this->checkParams(['SupplierCode', 'TransferAmount', 'TransferType'], $query);
		$this->params = array_merge($query, $this->params);

		unset($this->params['secret_key'], $this->params['language']);

		$SupplierRefund = $this->response(Http::withHeaders($this->headers)
				->post($this->links['TRANSFERBALANCE'], $this->params));
		return $SupplierRefund['status'] == 200 ? $SupplierRefund['collect'] : $SupplierRefund;
	}

	/**
	 * GetSupplierDashboard
	 * @param null
	 * @return array
	 */
	public function GetSupplierDashboard($query) {
		$this->checkParams(['SupplierCode'], $query);
		$this->params = array_merge($query, $this->params);

		unset($this->params['secret_key'], $this->params['language']);

		$SupplierDashboard = $this->response(Http::withHeaders($this->headers)
				->get($this->links['GETSUPPLIERDASHBOARD'], $this->params));
		return $SupplierDashboard['status'] == 200 ? $SupplierDashboard['collect'] : $SupplierDashboard;
	}

	/**
	 * UploadSupplierDocument
	 * @param null
	 * @return array
	 */
	public function UploadSupplierDocument($query) {
		$this->checkParams(['SupplierCode', 'FileUpload', 'FileType'], $query);
		$file = $this->uploadFile($query['FileUpload']);
		$default_path = config('filesystems.disks.' . config('filesystems.default') . '.root');
		$file = isset($file['path']) ? $default_path . '/' . $file['path'] : '';
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://apitest.myfatoorah.com/v2/UploadSupplierDocument',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'PUT',
			CURLOPT_POSTFIELDS => array(
				'FileUpload' => new
				\CURLFILE($file),
				'SupplierCode' => $query['SupplierCode'],
				'FileType' => $query['FileType'],
			),
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $this->token,
				'Cookie: ApplicationGatewayAffinity=3ef0c0508ad415fb05a4ff3f87fb97da; ApplicationGatewayAffinityCORS=3ef0c0508ad415fb05a4ff3f87fb97da',
			),
		));

		$response = curl_exec($curl);
		\Storage::deleteDirectory('myfatoorah_temp_file_system');
		curl_close($curl);
		if (!empty($response)) {
			return array_merge(['status' => 200], (array) json_decode($response));
		} else {
			return [
				'status' => 400,
				'message' => 'file empty or not sent',
			];
		}
	}

	/**
	 *  upload and prepare file
	 * @return array
	 */
	protected function uploadFile($FileUpload) {
		if (is_string($FileUpload) && request()->hasFile($FileUpload)) {
			$file = request()->file($FileUpload);
		} elseif (!is_string($FileUpload)) {
			$file = $FileUpload;
		}

		if (!empty($file)) {
			return [
				'path' => $file->store('myfatoorah_temp_file_system'),
				'name' => $file->getClientOriginalName(),
				'ext' => $file->extension(),
				'mime' => $file->getMimeType(),
			];
		}
	}

}