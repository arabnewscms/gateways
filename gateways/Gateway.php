<?php
namespace Phpanonymous\Gateways;
use Exception;
use Illuminate\Support\ServiceProvider;
use Phpanonymous\Gateways\Payments\Fawry;
use Phpanonymous\Gateways\Payments\Moyassar;

// use Phpanonymous\Gateways\Payments\CashU;
// use Phpanonymous\Gateways\Payments\Fawaterk;
// use Phpanonymous\Gateways\Payments\MyFatoorah;
// use Phpanonymous\Gateways\Payments\Payfort;
// use Phpanonymous\Gateways\Payments\Paymob;
// use Phpanonymous\Gateways\Payments\Paypal;
// use Phpanonymous\Gateways\Payments\Tap;
// use Phpanonymous\Gateways\Payments\TwoCheckout;
// use Phpanonymous\Gateways\SMS\InfoBIP;
// use Phpanonymous\Gateways\SMS\Mobily;
// use Phpanonymous\Gateways\SMS\SmsEG;
// use Phpanonymous\Gateways\SMS\Yamamah;

class Gateway extends ServiceProvider {

	// Gataway Payments
	use Fawry {
		Fawry::__construct as private fawry;
	}

	// use MyFatoorah {
	// 	MyFatoorah::__construct as private myfatoorah;
	// }

	// use Fawaterk {
	// 	Fawaterk::__construct as private fawaterk;
	// }

	// use Paypal {
	// 	Paypal::__construct as private paypal;
	// }

	// use Payfort {
	// 	Payfort::__construct as private payfort;
	// }

	// use TwoCheckout {
	// 	TwoCheckout::__construct as private towcheckout;
	// }

	use Moyassar {
		Moyassar::__construct as private moyassar;
	}

	// use CashU {
	// 	CashU::__construct as private cashu;
	// }

	// use Tap {
	// 	Tap::__construct as private tap;
	// }

	// use Paymob {
	// 	Paymob::__construct as private paymob;
	// }

	// // Getaway SMS Providers
	// use Mobily {
	// 	Mobily::__construct as private mobily;
	// }

	// use Yamamah {
	// 	Yamamah::__construct as private yamamah;
	// }

	// use SmsEG {
	// 	SmsEG::__construct as private smseg;
	// }

	// use InfoBIP {
	// 	InfoBIP::__construct as private infobip;
	// }

	public function __construct($provider = null) {
		if (method_exists($this, $provider)) {
			return $this->{$provider}();
		} else {
			throw new Exception('Please choose your Provider Payments or SMS');
		}
	}

}