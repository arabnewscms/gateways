<?php
return [
	'default' => [

		/**
		 * SMS Providers and Configurations multi providers
		 *
		 * enable Multiple getaway to use it in your project
		 * fawry,myfatoorah,moyassar,fawaterk
		 */
		'enable_payments' => [
			'fawry',
			'moyassar',
			'myfatoorah',
			'fawaterk',
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
		'myfatoorah' => [
			"mode" => "sandbox", // sandbox OR demo , live
			"secret_key" => "",
			"token" => "",
		],
		'fawaterk' => [
			'mode' => 'sandbox', // sandbox , live
			'api_key' => '',
		],

	],

	/**
	 * SMS Providers and Configurations multi providers
	 * default smsmisr
	 */
	'sms' => [
		'smsmisr' => [
			'username' => '',
			'password' => '',
			'sender' => '', // default sender name
			'language' => 1, // 1 For English , 2 For Arabic , 3 For Unicode
		],
		'4jawaly' => [
			'username' => '',
			'password' => '',
			'sender' => '', 
		]
	],

];