<?php

declare(strict_types=1);

namespace App\Model\Voucher;


enum VoucherStatus: string
{
	case Active = 'active';
	case Blocked = 'blocked';
}
