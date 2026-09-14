<?php

/**
 * Spustí SQL migrace ze složky migrations/, které ještě neproběhly.
 *
 *   php bin/migrate.php           lokální databáze (config/local.neon)
 *   php bin/migrate.php --test    testovací databáze (config/test.neon)
 *   php bin/migrate.php --fresh   smaže všechny tabulky a spustí migrace znovu
 */

declare(strict_types=1);

use Nette\Database\Connection;
use Nette\Database\Helpers;

require __DIR__ . '/../vendor/autoload.php';

$test = in_array('--test', $argv, true);
$fresh = in_array('--fresh', $argv, true);

$bootstrap = new App\Bootstrap;
$container = $test ? $bootstrap->bootForTests() : $bootstrap->bootForCli();
$connection = $container->getByType(Connection::class);

if ($fresh) {
	echo "Mažu všechny tabulky…\n";
	$tables = $connection->query('SHOW TABLES')->fetchPairs(null, 0);
	$connection->query('SET FOREIGN_KEY_CHECKS = 0');
	foreach ($tables as $table) {
		$connection->query('DROP TABLE ?name', $table);
	}
	$connection->query('SET FOREIGN_KEY_CHECKS = 1');
}

$connection->query('
	CREATE TABLE IF NOT EXISTS schema_migration (
		name VARCHAR(190) NOT NULL PRIMARY KEY,
		applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
');

$applied = $connection->query('SELECT name FROM schema_migration')->fetchPairs(null, 'name');
$files = glob(__DIR__ . '/../migrations/*.sql') ?: [];
sort($files);

$count = 0;
foreach ($files as $file) {
	$name = basename($file);
	if (in_array($name, $applied, true)) {
		continue;
	}

	echo "Spouštím $name\n";
	Helpers::loadFromFile($connection, $file);
	$connection->query('INSERT INTO schema_migration', ['name' => $name]);
	$count++;
}

echo $count ? "Hotovo, spuštěno migrací: $count\n" : "Databáze je aktuální.\n";
