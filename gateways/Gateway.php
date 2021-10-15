<?php
namespace Phpanonymous\Gateways;
use Exception;
use Illuminate\Support\ServiceProvider;

class Gateway extends ServiceProvider {
	protected $provider;

	public function __construct($provider = null) {
		$this->provider = $provider;
	}

	public function getaway() {
		if ($this->provider == 'fawry') {
			return 'Phpanonymous\Gateways\Payments\Fawry';
		} elseif ($this->provider == 'moyassar') {
			return 'Phpanonymous\Gateways\Payments\Moyassar';
		} elseif ($this->provider == 'paymob') {
			return 'Phpanonymous\Gateways\Payments\Paymob';
		} else {
			throw new Exception('Please choose your Provider Payments or SMS');
		}
	}

}