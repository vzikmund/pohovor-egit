<?php

declare(strict_types=1);

namespace App\Model;


interface Clock
{
	public function now(): \DateTimeImmutable;
}
