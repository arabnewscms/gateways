<?php
namespace Phpanonymous\Geteways;
use Exception;
use Illuminate\Support\ServiceProvider;
use Phpanonymous\Geteways\Payments\Fawry;
use Phpanonymous\Geteways\Payments\Moyassar;

// use Phpanonymous\Geteways\Payments\CashU;
// use Phpanonymous\Geteways\Payments\Fawaterk;
// use Phpanonymous\Geteways\Payments\MyFatoorah;
// use Phpanonymous\Geteways\Payments\Payfort;
// use Phpanonymous\Geteways\Payments\Paymob;
// use Phpanonymous\Geteways\Payments\Paypal;
// use Phpanonymous\Geteways\Payments\Tap;
// use Phpanonymous\Geteways\Payments\TwoCheckout;
// use Phpanonymous\Geteways\SMS\InfoBIP;
// use Phpanonymous\Geteways\SMS\Mobily;
// use Phpanonymous\Geteways\SMS\SmsEG;
// use Phpanonymous\Geteways\SMS\Yamamah;

class Geteway extends ServiceProvider {

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