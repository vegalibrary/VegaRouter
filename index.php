<?php

require_once 'core/Router.php';
require_once 'routes/web.php';

// Get the request method and URI
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Dispatch the route
$router->dispatch($method, $path);
