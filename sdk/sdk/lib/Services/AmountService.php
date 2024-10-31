<?php

namespace B2P\Services;

class AmountService {
	/**
	 * @throws \Exception
	 */
	public static function centifyAmount($value): int {
		if(!\is_numeric($value) || \str_contains($value * 100, '.'))
			throw new \Exception('Incorrect amount format - maximum two decimal places');

		return intval(round($value * 100, 2));
	}
}