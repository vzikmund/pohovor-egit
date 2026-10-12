<?php

declare(strict_types=1);

namespace App\Core;

use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	public static function createRouter(): RouteList
	{
		$router = new RouteList;
		$router->addRoute('api/v1/ping', 'Api:ping');
		$router->addRoute('api/v1/vouchers/<code>', 'Api:getVoucher');
		$router->addRoute('api/v1/vouchers', 'Api:createVoucher');
		$router->addRoute('<presenter>/<action>[/<id>]', 'Home:default');
		return $router;
	}
}
