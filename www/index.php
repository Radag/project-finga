<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

define('WWW_DIR', __DIR__);
define('APP_DIR', WWW_DIR . '/../app');

App\Bootstrap::boot()
	->createContainer()
	->getByType(Nette\Application\Application::class)
	->run();
