<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();
Tester\Environment::setupFunctions();
date_default_timezone_set('Europe/Prague');

const TempDir = __DIR__ . '/../temp/tests';
@mkdir(TempDir, 0777, true);


/**
 * DI kontejner napojený na testovací databázi (config/test.neon).
 * Testy, které zapisují do databáze, volají Tester\Environment::lock('database', TempDir).
 */
function createContainer(): Nette\DI\Container
{
	return (new App\Bootstrap)->bootForTests();
}
