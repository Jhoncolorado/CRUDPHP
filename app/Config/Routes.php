<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Front::index');
$routes->resource('images');
$routes->get('uploads/(:segment)', 'Uploads::show/$1');
