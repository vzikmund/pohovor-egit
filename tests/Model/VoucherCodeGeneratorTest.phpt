<?php

declare(strict_types=1);

use App\Model\Voucher\VoucherCodeGenerator;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


test('kód má tvar HK-XXXX-XXXX bez zaměnitelných znaků', function () {
	$generator = new VoucherCodeGenerator;
	for ($i = 0; $i < 200; $i++) {
		Assert::match('~^HK-[A-HJ-NP-Z2-9]{4}-[A-HJ-NP-Z2-9]{4}$~', $generator->generate());
	}
});
