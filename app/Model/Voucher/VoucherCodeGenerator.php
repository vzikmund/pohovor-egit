<?php

declare(strict_types=1);

namespace App\Model\Voucher;

use Nette\Utils\Random;


final class VoucherCodeGenerator
{
	/** Bez znaků, které se při přepisování pletou (0/O, 1/I). */
	private const Alphabet = 'A-HJ-NP-Z2-9';


	public function generate(): string
	{
		return sprintf(
			'HK-%s-%s',
			Random::generate(4, self::Alphabet),
			Random::generate(4, self::Alphabet),
		);
	}
}
