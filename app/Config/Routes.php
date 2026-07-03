<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Ocr::index');
$routes->post('/ocr/upload', 'Ocr::upload');
$routes->get('/ocr/job/(:segment)', 'Ocr::job/$1');
