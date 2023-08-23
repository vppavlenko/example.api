<?php

use Bitrix\Main\Loader;
use nvs\api\v1\App\Api;

require __DIR__ . "/prolog.php";
require __DIR__ . "/vendor/autoload.php";

$routes = require_once __DIR__ . "/config/routes.php";
$config = require_once __DIR__ . "/config/config.php";

Loader::IncludeModule('iblock');

$api = new Api($routes, $config, new Silex\Application());
$api->run();
