<?php

declare(strict_types=1);

namespace App\Router;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;


final class RouterFactory
{
	use Nette\StaticClass;

	public static function createRouter(): RouteList
	{
		$router = new RouteList;

		$router[] = $adminRouter = new RouteList('Admin');
		$adminRouter[] = new Route('admin/<presenter>/<action>[/<id>]', 'Homepage:default');


		$router[] = $mainRouter = new RouteList('Main');
		$mainRouter[] = new Route('[<lang (cs|en)>/]shop/terms-of-trade', 'Shop:termsOfTrade');
		$mainRouter[] = new Route('[<lang (cs|en)>/]shop/shipping', 'Shop:shipping');
		$mainRouter[] = new Route('[<lang (cs|en)>/]article/<id>', 'Article:default');
		$mainRouter[] = new Route('[<lang (cs|en)>/]shop/<category>/<name>', 'Shop:product');
		$mainRouter[] = new Route('[<lang (cs|en)>/]shop/<category>', 'Shop:list');
		$mainRouter[] = new Route('[<lang (cs|en)>/]shop', 'Shop:default');
		$mainRouter[] = new Route('[<lang (cs|en)>/]onas', 'Homepage:about');
		$mainRouter[] = new Route('[<lang (cs|en)>/]logo', 'Homepage:logo');
		$mainRouter[] = new Route('[<lang (cs|en)>/]distribuce', 'Homepage:distribution');
		$mainRouter[] = new Route('[<lang (cs|en)>/]objednavka/<id>', 'Shop:showOrder');
		$mainRouter[] = new Route('[<lang (cs|en)>/]seznam-objednavek', 'Shop:listOfOrders');
		$mainRouter[] = new Route('[<lang (cs|en)>/]tym', 'Homepage:team');
		$mainRouter[] = new Route('[<lang (cs|en)>/]kontakt', 'Homepage:contact');
		$mainRouter[] = new Route('[<lang (cs|en)>/]registration', 'Homepage:registration');
		$mainRouter[] = new Route('[<lang (cs|en)>/]edit-profile', 'Homepage:editProfile');
		$mainRouter[] = new Route('[<lang (cs|en)>/]<presenter>/<action>[/<id>]', 'Homepage:default');

		return $router;
	}
}
