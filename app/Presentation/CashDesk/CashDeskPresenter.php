<?php

namespace App\Presentation\CashDesk;

use App\Model\Voucher\VoucherCode;
use Nette;
use Tracy\Debugger;


final class CashDeskPresenter extends Nette\Application\UI\Presenter
{
	public function __construct(
		private Nette\Database\Explorer $db,
	) {
	}


	public function actionRedeem(string $code, float $amount): void
	{
		$code = VoucherCode::normalize($code);

		$voucher = $this->db
			->query("SELECT * FROM voucher WHERE code = '$code'")
			->fetch();

		if (!$voucher) {
			$this->error('Poukaz nenalezen');
		}

		if ($voucher->balance >= $amount) {
			$this->db->query(
				'UPDATE voucher SET balance = ? WHERE id = ?',
				$voucher->balance - $amount,
				$voucher->id,
			);
			$this->db->table('redemption')->insert([
				'voucher_id' => $voucher->id,
				'amount' => $amount,
				'user_id' => $this->getUser()->getId(),
			]);
			Debugger::log("Uplatnen poukaz $code, castka $amount", 'redeem');
			$this->sendJson(['ok' => true, 'balance' => $voucher->balance - $amount]);
		}

		$this->sendJson(['ok' => false]);
	}
}
