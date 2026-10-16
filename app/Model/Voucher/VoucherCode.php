<?php

declare(strict_types=1);

namespace App\Model\Voucher;


final class VoucherCode
{
	/**
	 * Sjednotí kód zadaný na pokladně (mezery, malá písmena).
	 */
	public static function normalize(string $code): string
	{
		return strtoupper(trim($code));
	}
}
