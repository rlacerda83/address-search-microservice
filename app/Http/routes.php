<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['middleware' => 'api.auth', 'namespace' => 'App\Http\Controllers\V1'], function ($api) {

    //Countries
    $api->get('countries', 'CountriesController@index');

    $api->get('countries/{code}', 'CountriesController@get');

    // search address
    $api->get('services-search', 'ServicesSearchController@index');

    $api->post('services-search/search', 'ServicesSearchController@search');

    $api->get('services-search/{id}', 'ServicesSearchController@get');

    $api->post('services-search', 'ServicesSearchController@create');

    $api->put('services-search/{id}', 'ServicesSearchController@update');

    $api->delete('services-search/{id}', 'ServicesSearchController@delete');

    // address
    $api->get('address', 'AddressController@index');

    $api->get('address/{id}', 'AddressController@get');

    $api->post('address', 'AddressController@create');

    $api->put('address/{id}', 'AddressController@update');

    $api->delete('address/{id}', 'AddressController@delete');

});
