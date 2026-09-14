<?php

declare(strict_types=1);

namespace App;

use Nette;
use Nette\Bootstrap\Configurator;


final class Bootstrap
{
	private readonly Configurator $configurator;
	private readonly string $rootDir;


	public function __construct()
	{
		$this->rootDir = dirname(__DIR__);
		$this->configurator = new Configurator;
		$this->configurator->setTempDirectory($this->rootDir . '/temp');
	}


	public function bootWebApplication(): Nette\DI\Container
	{
		$this->configurator->setDebugMode((bool) getenv('APP_DEBUG'));
		$this->configurator->enableTracy($this->rootDir . '/log');
		return $this->createContainer('local');
	}


	public function bootForCli(): Nette\DI\Container
	{
		$this->configurator->setDebugMode(true);
		return $this->createContainer('local');
	}


	public function bootForTests(): Nette\DI\Container
	{
		$this->configurator->setDebugMode(true);
		return $this->createContainer('test');
	}


	private function createContainer(string $environment): Nette\DI\Container
	{
		$configDir = $this->rootDir . '/config';
		$this->configurator->addConfig($configDir . '/common.neon');
		$this->configurator->addConfig($configDir . '/services.neon');
		$this->configurator->addConfig($configDir . "/$environment.neon");
		return $this->configurator->createContainer();
	}
}
