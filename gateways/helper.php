<?php

if (!function_exists('gateway')) {
	function gateway($provider = null) {
		if (class_exists('Phpanonymous\Gateways\Gateway')) {
			return new Phpanonymous\Gateways\Gateway($provider);
		}
	}
}
