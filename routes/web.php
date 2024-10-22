<?php

$router = new Router();

$router->layout('default'); 

// Public routes

// run : http://localhost/
$router->get('/', function() use ($router) {
    $router->render('public/home');
});

// run : http://localhost/user/{type-your-id}
$router->get('/user/{id}', function($id) use ($router) {
    $router->render('public/user');
});

// Not Found error with default template
$router->setNotFound(function() use ($router) {
    $router->render('error');
});

// Admin routes
$router->group('/admin', function() use ($router) {
    
    // run : http://localhost/admin/dashboard
    $router->get('/dashboard', function() use ($router) {
        $router->render('admin/dashboard');
    });

    // run : http://localhost/admin/settings
    $router->get('/settings', function() use ($router) {
        $router->render('admin/settings');
    });
    
});

