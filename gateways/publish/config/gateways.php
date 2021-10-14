<?php
return [
	'default' => [

		/**
		 * SMS Providers and Configurations multi providers
		 *
		 * enable Multiple getaway to use it in your project
		 * fawry,myfatoorah,fawaterk,paypal,payfort,2checkout,moyassar,cashu,tab,paymob
		 */
		'enable_payments' => [
			'fawry',
			'moyassar',
		],

	],
	/**
	 * Payments configurations multi getaway
	 * available providers fawry,moyassar
	 */
	'payments' => [
		'fawry' => [
			"mode" => "sandbox", // sandbox , live
			"merchant_code" => "",
			"security_key" => "",
		],
		'moyassar' => [
			"mode" => "sandbox", // sandbox , live
			"test_secret_key" => "",
			"test_publishable_key" => "",
			"live_secret_key" => "",
			"live_publishable_key" => "",
		],
	],

];