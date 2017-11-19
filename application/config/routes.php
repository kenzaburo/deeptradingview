<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/user_guide/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'welcome';
$route['404_override'] = '';
$route['translate_uri_dashes'] = TRUE;

/*
 * users resource routes
 */
$route['api/users/delete/(:num)'] = 'api/users/user_delete/id/$1';
$route['api/users/update'] = 'api/users/update/';
$route['api/users/role'] = 'api/users/role/';
$route['api/users/'] = 'api/users/index/';
$route['api/users/login'] = 'api/users/login/';
$route['api/users/login/sso'] = 'api/users/login/sso/1';
$route['api/users/(:num)'] = 'api/users/index/id/$1';
$route['api/users/(:num)/building'] = 'api/users/index/id/$1/building/1';
$route['api/users/building'] = 'api/users/building/';

/**
 * Building resource routes
 */
$route['api/buildings/'] = 'api/buildings/index/';
$route['api/buildings/(:num)'] = 'api/buildings/index/id/$1';
$route['api/buildings/(:num)/level'] = 'api/buildings/level/id/$1';
$route['api/buildings/level/'] = 'api/buildings/level/';
$route['api/buildings/level/section'] = 'api/buildings/section/';
$route['api/buildings/(:num)/modules'] = 'api/buildings/index/id/$1/modules/1';
$route['api/buildings/delete/modules'] = 'api/buildings/module_delete/';
$route['api/buildings/modules'] = 'api/buildings/module/';


/**
 * Module resource routes
 */
$route['api/modules/'] = 'api/modules/index/';
$route['api/modules/(:num)'] = 'api/modules/index/id/$1';


/**
 * Data resource route
 */
$route['api/data/bitfinex/trade/([a-zA-Z0-9_-]+)/([a-zA-Z0-9_-]+)/(:num)'] = 'api/bitfinex/trade/left/$1/right/$2/type/$3/';



$route['api/data/bitfinex/trade_5m'] = 'api/bitfinex/trade_5m/';
$route['api/data/bitfinex/trade_15m'] = 'api/bitfinex/trade_15m/';
$route['api/data/bitfinex/trade_30m'] = 'api/bitfinex/trade_30m/';
$route['api/data/bitfinex/trade_60m'] = 'api/bitfinex/trade_60m/';

/*
*/
$route['api/data/volume/btc'] = 'api/data/volume24h_trend/';





$route['api/data/daily_trend'] = 'api/data/daily_trend/';
$route['api/data/hourly_trend'] = 'api/data/hourly_trend/';
$route['api/data/occupancy'] = 'api/data/occupancy/';
$route['api/data/transition'] = 'api/data/transition/';
$route['api/data/visit_freq'] = 'api/data/visit_freq/';
$route['api/data/dwell_time'] = 'api/data/dwell_time/';
$route['api/data/student_analytics'] = 'api/data/student_analytics/';
$route['api/data/heatmap'] = 'api/data/heatmap/';


/**
 * Point location route
 */
$route['api/data/point_location'] = 'api/data/point_location';


/*
| -------------------------------------------------------------------------
| Sample REST API Routes
| -------------------------------------------------------------------------
*/
$route['api/example/users/(:num)'] = 'api/example/users/id/$1'; // Example 4
$route['api/example/users/(:num)(\.)([a-zA-Z0-9_-]+)(.*)'] = 'api/example/users/id/$1/format/$3$4'; // Example 8






