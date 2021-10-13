<?php
return [
	'default' => [
		/**
		 * SMS Providers and Configurations multi providers
		 * set one sms provider mobily,yamamah,smseg,infobip
		 */
		'sms' => 'yamamah',

		/**
		 * SMS Providers and Configurations multi providers
		 *
		 * enable Multiple getaway to use it in your project
		 * fawry,myfatoorah,fawaterk,paypal,payfort,2checkout,moyassar,cashu,tab,paymob
		 */
		'enable_payments' => [
			'fawry',
			// 'myfatoorah',
			// 'fawaterk',
			// 'paypal',
			// 'payfort',
			// '2checkout',
			// 'moyassar',
			// 'cashu',
			// 'tab',
			// 'paymob',
		],

	],
	/**
	 * Payments configurations multi getaway
	 * available providers fawry,myfatoorah,fawaterk,paypal,payfort,2checkout,moyassar,cashu,tab,paymob
	 */
	'payments' => [
		'fawry' => [
			"mode" => "sandbox", // sandbox , live
			"merchant_code" => "1tSa6uxz2nRlhbmxHHde5A==",
			"security_key" => "259af31fc2f74453b3a55739b21ae9ef",
		],
		// 'myfatoorah' => [
		// ],
		// 'fawaterk' => [
		// ],
		// 'paypal' => [
		// ],
		// 'payfort' => [
		// ],
		// '2checkout' => [
		// ],
		// 'moyassar' => [
		// ],
		// 'cashu' => [
		// ],
		// 'tab' => [
		// ],
		// 'paymob' => [
		// ],
	],

	/**
	 * SMS Providers and Configurations multi providers
	 * default providers mobily,mobily,yamamah,smseg,infobip
	 */
	'sms' => [
		'yamamah' => [
		],
		// 'mobily' => [
		// ],

		// 'smseg' => [
		// ],
		// 'infobip' => [
		// ],
	],
];