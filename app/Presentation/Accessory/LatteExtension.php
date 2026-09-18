<?php

declare(strict_types=1);

namespace App\Presentation\Accessory;

use Latte\Extension;


final class LatteExtension extends Extension
{
	public function getFilters(): array
	{
		return [
			'money' => self::formatMoney(...),
		];
	}


	/** Částka v haléřích jako „1 234,50 Kč“. */
	public static function formatMoney(int $amount): string
	{
		return number_format($amount / 100, 2, ',', "\u{a0}") . "\u{a0}Kč";
	}
}
