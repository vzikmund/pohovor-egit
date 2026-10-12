<?php

declare(strict_types=1);

namespace App\Model\Voucher;

use Nette\Database\Table\ActiveRow;


/**
 * Dárkový poukaz. Všechny částky jsou v haléřích.
 */
final readonly class Voucher
{
	public function __construct(
		public int $id,
		public string $code,
		public int $initialAmount,
		public int $balance,
		public \DateTimeImmutable $expiresAt,
		public VoucherStatus $status,
		public ?string $customerEmail,
		public ?string $note,
		public \DateTimeImmutable $createdAt,
	) {
	}


	public static function fromRow(ActiveRow $row): self
	{
		return new self(
			id: $row->id,
			code: $row->code,
			initialAmount: $row->initial_amount,
			balance: $row->balance,
			expiresAt: \DateTimeImmutable::createFromInterface($row->expires_at),
			status: VoucherStatus::from($row->status),
			customerEmail: $row->customer_email,
			note: $row->note,
			createdAt: \DateTimeImmutable::createFromInterface($row->created_at),
		);
	}


	public function isExpired(\DateTimeImmutable $now): bool
	{
		return $this->expiresAt->setTime(0, 0) < $now->setTime(0, 0);
	}


	public function isRedeemable(\DateTimeImmutable $now): bool
	{
		return $this->status === VoucherStatus::Active
			&& !$this->isExpired($now)
			&& $this->balance > 0;
	}
}
