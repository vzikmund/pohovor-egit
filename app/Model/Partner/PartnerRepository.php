<?php

declare(strict_types=1);

namespace App\Model\Partner;

use Nette\Database\Explorer;


final class PartnerRepository
{
	public function __construct(
		private readonly Explorer $database,
	) {
	}


	public function getById(int $id): ?Partner
	{
		$row = $this->database->table('partner')->get($id);
		return $row ? Partner::fromRow($row) : null;
	}


	/** Najde partnera podle bearer tokenu */
	public function findByApiKey(string $plainKey): ?Partner
	{
		$hash = hash('sha256', $plainKey);
		$row = $this->database->table('partner')->where('api_key_hash', $hash)->fetch();
		return $row ? Partner::fromRow($row) : null;
	}
}
