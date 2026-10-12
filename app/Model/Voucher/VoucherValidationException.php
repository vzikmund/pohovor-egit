<?php

declare(strict_types=1);

namespace App\Model\Voucher;


/** Neplatný požadavek na vydání poukazu (mapuje se na HTTP 400). */
final class VoucherValidationException extends \InvalidArgumentException
{
	public function __construct(
		public readonly string $error,
		string $message,
	) {
		parent::__construct($message);
	}
}
