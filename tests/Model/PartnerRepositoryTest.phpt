<?php

declare(strict_types=1);

use App\Model\Partner\PartnerRepository;
use Tester\Assert;
use Tester\Environment;

require __DIR__ . '/../bootstrap.php';

Environment::lock('database', TempDir);

$container = createContainer();
$repository = $container->getByType(PartnerRepository::class);


test('platný API=partner', function () use ($repository) {
	$partner = $repository->findByApiKey('alfa-test-key-123');

	Assert::notNull($partner);
	Assert::same(1, $partner->id);
	Assert::same('Trafika Alfa', $partner->name);
});


test('neplatný API klíč=null', function () use ($repository) {
	Assert::null($repository->findByApiKey('neplatny-klic'));
});
