<?php

declare(strict_types=1);

namespace App\Model\Voucher;

use Nette\Database\Explorer;
use Nette\Database\Table\Selection;


final class VoucherRepository
{
	public function __construct(
		private readonly Explorer $database,
	) {
	}


	/** @return list<Voucher> */
	public function findAll(): array
	{
		$vouchers = [];
		foreach ($this->database->table('voucher')->order('created_at DESC, id DESC') as $row) {
			$vouchers[] = Voucher::fromRow($row);
		}

		return $vouchers;
	}


	public function getById(int $id): ?Voucher
	{
		$row = $this->database->table('voucher')->get($id);
		return $row ? Voucher::fromRow($row) : null;
	}


	public function getByCode(string $code): ?Voucher
	{
		$row = $this->database->table('voucher')->where('code', $code)->fetch();
		return $row ? Voucher::fromRow($row) : null;
	}


	public function codeExists(string $code): bool
	{
		return $this->database->table('voucher')->where('code', $code)->count('*') > 0;
	}


	public function findByPartnerTransaction(int $partnerId, string $transactionId): ?Voucher
	{
		$row = $this->database->table('voucher')
			->where('partner_id', $partnerId)
			->where('transaction_id', $transactionId)
			->fetch();
		return $row ? Voucher::fromRow($row) : null;
	}


	public function insert(
		string $code,
		int $amount,
		\DateTimeImmutable $expiresAt,
		?string $customerEmail,
		?string $note,
		?int $partnerId = null,
		?string $transactionId = null,
	): Voucher
	{
		$row = $this->database->table('voucher')->insert([
			'code' => $code,
			'initial_amount' => $amount,
			'balance' => $amount,
			'expires_at' => $expiresAt->format('Y-m-d'),
			'status' => VoucherStatus::Active->value,
			'customer_email' => $customerEmail,
			'note' => $note,
			'partner_id' => $partnerId,
			'transaction_id' => $transactionId,
		]);

		return $this->getById($row->id);
	}


	/** Historie uplatnění poukazu, nejnovější první. */
	public function findRedemptions(int $voucherId): Selection
	{
		return $this->database->table('redemption')
			->where('voucher_id', $voucherId)
			->order('created_at DESC, id DESC');
	}
}
