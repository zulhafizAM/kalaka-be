<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
$request = Request::capture();
$response = $kernel->handle($request);

// Attach CORS headers to every response unconditionally,
// so exceptions that bypass HandleCors still get them.
$origin = getenv('FRONTEND_URL') ?: 'http://localhost:5173';
if ($request->headers->has('Origin')) {
    $response->headers->set('Access-Control-Allow-Origin', $origin);
    $response->headers->set('Access-Control-Allow-Credentials', 'true');
    if ($request->isMethod('OPTIONS')) {
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
    }
}

$response->send();
$kernel->terminate($request, $response);
