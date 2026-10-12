<?php

declare(strict_types=1);

use App\Model\Voucher\Voucher;
use App\Model\Voucher\VoucherStatus;
use Tester\Assert;

require __DIR__ . '/../bootstrap.php';


function createVoucher(
	string $expiresAt,
	int $balance = 50_000,
	VoucherStatus $status = VoucherStatus::Active,
): Voucher
{
	return new Voucher(
		id: 1,
		code: 'HK-TEST-2345',
		initialAmount: 50_000,
		balance: $balance,
		expiresAt: new DateTimeImmutable($expiresAt),
		status: $status,
		customerEmail: null,
		note: null,
		partnerId: null,
		partnerName: null,
		transactionId: null,
		createdAt: new DateTimeImmutable('2026-01-05 10:00'),
	);
}


test('poukaz po datu platnosti je prpadlý', function () {
	$voucher = createVoucher('2026-03-31');
	Assert::true($voucher->isExpired(new DateTimeImmutable('2026-04-15 10:00')));
});


test('poukaz před datem platnosti není prpadlý', function () {
	$voucher = createVoucher('2026-03-31');
	Assert::false($voucher->isExpired(new DateTimeImmutable('2026-02-10 10:00')));
});


test('platný poukaz se zůstatkem lze uplatnit', function () {
	$voucher = createVoucher('2026-03-31');
	Assert::true($voucher->isRedeemable(new DateTimeImmutable('2026-02-10 10:00')));
});


test('zablokovaný poukaz nelze uplatnit', function () {
	$voucher = createVoucher('2026-03-31', status: VoucherStatus::Blocked);
	Assert::false($voucher->isRedeemable(new DateTimeImmutable('2026-02-10 10:00')));
});


test('vyčerpaný poukaz nelze uplatnit', function () {
	$voucher = createVoucher('2026-03-31', balance: 0);
	Assert::false($voucher->isRedeemable(new DateTimeImmutable('2026-02-10 10:00')));
});
