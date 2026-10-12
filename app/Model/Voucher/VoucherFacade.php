<?php

declare(strict_types=1);

namespace App\Model\Voucher;

use App\Model\Clock;
use Nette\Database\UniqueConstraintViolationException;
use Nette\Utils\Validators;


final class VoucherFacade
{
	public function __construct(
		private readonly VoucherRepository $voucherRepository,
		private readonly VoucherCodeGenerator $codeGenerator,
		private readonly Clock $clock,
		private readonly string $apiValidity,
		private readonly int $minAmount,
		private readonly int $maxAmount,
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
		$this->assertAmount($amount);
		$this->assertCustomerEmail($customerEmail);

		return $this->insertNew($amount, $expiresAt, $customerEmail, $note, null, null);
	}


	/**
	 * Vydá poukaz přes API partnera, stejné transactionId u partnera vrátí existující voucher
	 * @param int $amount hodnota v haléřích
	 */
	public function issueForPartner(
		int $partnerId,
		string $transactionId,
		int $amount,
		?string $customerEmail,
	): Voucher
	{
		$transactionId = trim($transactionId);
		$this->assertTransactionId($transactionId);
		$this->assertAmount($amount);
		$this->assertCustomerEmail($customerEmail);

		$existing = $this->voucherRepository->findByPartnerTransaction($partnerId, $transactionId);
		if ($existing) {
			return $existing;
		}

		$expiresAt = $this->clock->now()->modify($this->apiValidity);

		try {
			return $this->insertNew($amount, $expiresAt, $customerEmail, null, $partnerId, $transactionId);
		} catch (UniqueConstraintViolationException) {
			// Paralelní požadavek se stejným transactionId — vrať ten, co se stihl uložit.
			$existing = $this->voucherRepository->findByPartnerTransaction($partnerId, $transactionId);
			if ($existing) {
				return $existing;
			}
			throw new \RuntimeException('Konflikt při ukládání poukazu, záznam se nepodařilo dohledat.');
		}
	}


	private function insertNew(
		int $amount,
		\DateTimeImmutable $expiresAt,
		?string $customerEmail,
		?string $note,
		?int $partnerId,
		?string $transactionId,
	): Voucher
	{
		do {
			$code = $this->codeGenerator->generate();
		} while ($this->voucherRepository->codeExists($code));

		return $this->voucherRepository->insert(
			$code,
			$amount,
			$expiresAt,
			$customerEmail,
			$note,
			$partnerId,
			$transactionId,
		);
	}


	private function assertAmount(int $amount): void
	{
		if ($amount < $this->minAmount || $amount > $this->maxAmount) {
			$minKc = $this->minAmount / 100;
			$maxKc = $this->maxAmount / 100;
			throw new VoucherValidationException(
				'invalid_amount',
				"Hodnota musí být mezi {$minKc} a {$maxKc} Kč.",
			);
		}
	}


	private function assertTransactionId(string $transactionId): void
	{
		if ($transactionId === '') {
			throw new VoucherValidationException(
				'missing_transaction_id',
				'Chybí transactionId.',
			);
		}

		if (strlen($transactionId) > 64) {
			throw new VoucherValidationException(
				'invalid_transaction_id',
				'transactionId smí mít max 64 znaků',
			);
		}
	}


	private function assertCustomerEmail(?string $customerEmail): void
	{
		if ($customerEmail === null || $customerEmail === '') {
			return;
		}

		if (!Validators::isEmail($customerEmail)) {
			throw new VoucherValidationException(
				'invalid_email',
				'customerEmail nemá formát e-mailu.',
			);
		}
	}
}
