<?php

declare(strict_types=1);

use App\Model\Voucher\VoucherCode;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('normalize', function () {
	$code = VoucherCode::normalize(' hk-plat-2345 ');
	Assert::type('string', $code);
});
