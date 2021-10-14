<?php

if (!function_exists('gateway')) {
	function gateway($provider = null) {
		if (class_exists('Phpanonymous\Gateways\Gateway')) {
			$data = (new Phpanonymous\Gateways\Gateway($provider))->getaway();
			return new $data;
		}
	}
}
