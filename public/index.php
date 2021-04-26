<?php

declare(strict_types=1);

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;


$vendors = '';
$envs = '';

if (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false
    || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false) {
    $vendors =  dirname(__DIR__).'/vendor/autoload.php';
    $envs = dirname(__DIR__).'/.env';
} else {
    $vendors = dirname( __DIR__ ) . '/salve/current/vendor/autoload.php';
    $envs    = dirname( __DIR__ ) . '/salve/current/.env';
}

require $vendors;

(new Dotenv())->loadEnv($envs);

$env = $_SERVER['APP_ENV'] ?? 'dev';
$debug = (bool) ($_SERVER['APP_DEBUG'] ?? ($env !== 'prod'));

if ($debug) {
    umask(0000);

    Debug::enable();
}

if (! empty($_SERVER['TRUSTED_PROXIES'])) {
    Request::setTrustedProxies(explode(',', $_SERVER['TRUSTED_PROXIES']), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if (! empty($_SERVER['TRUSTED_HOSTS'])) {
    Request::setTrustedHosts(explode(',', $_SERVER['TRUSTED_HOSTS']));
}

$kernel = new Kernel($env, $debug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
