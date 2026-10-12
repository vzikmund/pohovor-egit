<?php

declare(strict_types=1);

namespace App\Model\Partner;

use Nette\Database\Table\ActiveRow;


final readonly class Partner
{
	public function __construct(
		public int $id,
		public string $name,
	) {
	}


	public static function fromRow(ActiveRow $row): self
	{
		return new self(
			id: $row->id,
			name: $row->name,
		);
	}
}
