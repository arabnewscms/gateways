<?php

if (!function_exists('gateway')) {
	function gateway($provider = null) {
		if (class_exists('Phpanonymous\Geteways\Geteway')) {
			return new Phpanonymous\Geteways\Geteway($provider);
		}
	}
}
