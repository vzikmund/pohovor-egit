<?php

declare(strict_types=1);

namespace App\Model\Redemption;

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;


final class RedemptionRepository
{
	public function __construct(
		private Explorer $db,
	) {
	}


	public function findByPartner(int $partnerId): array
	{
		return $this->db->table('redemption')
			->where('partner_id', $partnerId)
			->order('created_at DESC')
			->fetchAll();
	}


	public function get(int $id): ?ActiveRow
	{
		return $this->db->table('redemption')->get($id);
	}
}
