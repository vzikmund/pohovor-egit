<?php

declare(strict_types=1);

use App\Model\Voucher\VoucherFacade;
use App\Model\Voucher\VoucherRepository;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../bootstrap.php';

Environment::lock('database', TempDir);

$container = createContainer();
$facade = $container->getByType(VoucherFacade::class);
$repository = $container->getByType(VoucherRepository::class);


test('vydaný poukaz má plný zůstatek a jde najít podle kódu', function () use ($facade, $repository) {
	$issued = $facade->issue(
		amount: 50_000,
		expiresAt: new DateTimeImmutable('+1 year'),
		customerEmail: 'test@example.com',
		note: null,
	);

	$found = $repository->getByCode($issued->code);
	Assert::notNull($found);
	Assert::same($issued->id, $found->id);
	Assert::same(50_000, $found->balance);
});
