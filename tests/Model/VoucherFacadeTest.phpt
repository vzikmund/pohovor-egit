<?php

declare(strict_types=1);

use App\Model\Voucher\VoucherFacade;
use App\Model\Voucher\VoucherRepository;
use App\Model\Voucher\VoucherValidationException;
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
	Assert::same(50_000, $found->initialAmount);
	Assert::same(50_000, $found->balance);
	Assert::null($found->partnerId);
	Assert::null($found->transactionId);
});


test('issueForPartner vytvoří poukaz s partnerem  a platnosti', function () use ($facade) {
	$tx = 'TEST-ISSUE-' . bin2hex(random_bytes(4));
	$issued = $facade->issueForPartner(
		partnerId: 1,
		transactionId: $tx,
		amount: 100_000,
		customerEmail: 'jana@example.com',
	);

	Assert::same(1, $issued->partnerId);
	Assert::same('Trafika Alfa', $issued->partnerName);
	Assert::same($tx, $issued->transactionId);
	Assert::same(100_000, $issued->initialAmount);
	Assert::same(
		(new DateTimeImmutable('today'))->modify('+1 year')->format('Y-m-d'),
		$issued->expiresAt->format('Y-m-d'),
	);
});


test('stejné transactionId != 2. poukaz', function () use ($facade, $repository) {
	$tx = 'TEST-IDEM-' . bin2hex(random_bytes(4));
	$first = $facade->issueForPartner(1, $tx, 100_000, null);
	$second = $facade->issueForPartner(1, $tx, 100_000, null);

	Assert::same($first->id, $second->id);
	Assert::same($first->code, $second->code);

	$found = $repository->findByPartnerTransaction(1, $tx);
	Assert::notNull($found);
	Assert::same($first->id, $found->id);
});


test('neplatná částka vyhodí exception', function () use ($facade) {
	Assert::exception(
		fn () => $facade->issueForPartner(1, 'TEST-AMOUNT', 50, null),
		VoucherValidationException::class,
		'Hodnota musí být mezi 100 a 10000 Kč.',
	);
});


test('chybějící transactionId vyhodí excption', function () use ($facade) {
	Assert::exception(
		fn () => $facade->issueForPartner(1, '', 100_000, null),
		VoucherValidationException::class,
	);
});


test('neplatný e-mail vyhodí exception', function () use ($facade) {
	Assert::exception(
		fn () => $facade->issueForPartner(1, 'TEST-EMAIL', 100_000, 'neplatny'),
		VoucherValidationException::class,
	);
});


test('getForPartner vrátí poukaz partnera', function () use ($facade) {
	$tx = 'TEST-GET-' . bin2hex(random_bytes(4));
	$issued = $facade->issueForPartner(1, $tx, 100_000, null);

	$found = $facade->getForPartner(1, $issued->code);
	Assert::same($issued->id, $found->id);
	Assert::same(100_000, $found->balance);
});


test('getForPartner cizí poukaz je not found', function () use ($facade) {
	$tx = 'TEST-GET-A-' . bin2hex(random_bytes(4));
	$issued = $facade->issueForPartner(1, $tx, 100_000, null);

	$e = Assert::exception(
		fn () => $facade->getForPartner(2, $issued->code),
		VoucherValidationException::class,
	);
	Assert::same('voucher_not_found', $e->error);
});


test('getForPartner neexistující kód je not_found', function () use ($facade) {
	$e = Assert::exception(
		fn () => $facade->getForPartner(1, 'HK-XXXX-YYYY'),
		VoucherValidationException::class,
	);
	Assert::same('voucher_not_found', $e->error);
});
