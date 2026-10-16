<?php

declare(strict_types=1);

namespace App\Presentation\PartnerRedemption;

use App\Model\Redemption\RedemptionRepository;
use App\Presentation\SecuredPresenter;
use Nette\Database\Explorer;


final class PartnerRedemptionPresenter extends SecuredPresenter
{
	protected ?string $requiredRole = 'partner';

	private RedemptionRepository $redemptions;


	public function __construct(
		private Explorer $db,
	) {
	}


	public function startup(): void
	{
		parent::startup();
		$this->redemptions = new RedemptionRepository($this->db);
		$this->getHttpResponse()->setHeader('Cache-Control', 'no-store, private');
	}


	public function renderDefault(): void
	{
		$partnerId = $this->getUser()->getIdentity()->partner_id;

		$items = [];
		foreach ($this->redemptions->findByPartner($partnerId) as $row) {
			$voucher = $this->db->query('SELECT code FROM voucher WHERE id = ?', $row->voucher_id)->fetch();
			$items[] = [
				'id' => $row->id,
				'code' => $voucher->code,
				'amount' => $row->amount,
				'created_at' => $row->created_at,
			];
		}

		$this->template->items = $items;
	}


	public function renderDetail(int $id): void
	{
		$redemption = $this->redemptions->get($id);
		if (!$redemption) {
			$this->error();
		}

		$this->template->redemption = $redemption;
		$this->template->voucher = $this->db->table('voucher')->get($redemption->voucher_id);
	}
}
