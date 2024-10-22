<?php

$router = new Router();

$router->layout('default'); 



$router->get('/', function() use ($router) {
    $router->render('public/home');
});

$router->get('/about', function() use ($router) {
    $router->render('public/about');
});

$router->setNotFound(function() use ($router) {
    $router->render('error');
});

