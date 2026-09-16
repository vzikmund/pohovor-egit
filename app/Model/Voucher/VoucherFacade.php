<?php

declare(strict_types=1);

namespace App\Model\Voucher;


final class VoucherFacade
{
	public function __construct(
		private readonly VoucherRepository $voucherRepository,
		private readonly VoucherCodeGenerator $codeGenerator,
	) {
	}


	/**
	 * Vydá nový poukaz.
	 * @param int $amount hodnota v haléřích
	 */
	public function issue(
		int $amount,
		\DateTimeImmutable $expiresAt,
		?string $customerEmail,
		?string $note,
	): Voucher
	{
		do {
			$code = $this->codeGenerator->generate();
		} while ($this->voucherRepository->codeExists($code));

		return $this->voucherRepository->insert($code, $amount, $expiresAt, $customerEmail, $note);
	}
}
