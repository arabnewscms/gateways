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
			'paymob',
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
		'paymob' => [
			"mode" => "sandbox", // sandbox , live
			"api_key" => "",
			// testcredentials
			"test_iframe_card_id" => "",
			"test_integration_card_id" => "",

			"test_iframe_valu_id" => "",
			"test_integration_valu_id" => "",

			"test_iframe_souhoola_id" => "",
			"test_integration_souhoola_id" => "",
			
			"test_iframe_get_go_id" => "",
			"test_integration_get_go_id" => "",
			
			"test_iframe_bank_installment_id" => "",
			"test_integration_bank_installment_id" => "",
			
			"test_iframe_premium_card_id" => "",
			"test_integration_premium_card_id" => "",
			
			"test_integration_wallet_id" => "",
			"test_integration_kiosk_id" => "",

			// live credentials
			"integration_card_id" => "",
			"iframe_card_id" => "",

			"integration_valu_id" => "",
			"iframe_valu_id" => "",
			
			"integration_souhoola_id" => "",
			"iframe_souhoola_id" => "",
			
			"integration_get_go_id" => "",
			"iframe_get_go_id" => "",
			
			"iframe_bank_installment_id" => "",
			"integration_bank_installment_id" => "",
			
			"iframe_premium_card_id" => "",
			"integration_premium_card_id" => "",
			
			"integration_wallet_id" => "",
			
			"integration_kiosk_id" => "",
		],
	],

];