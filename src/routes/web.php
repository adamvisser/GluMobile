<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

// Default URL so we know Docker and Lumen are happy
$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Provided request type/url to retrieve the task with the highest priority
$router->get('task', 'JobController@grab');

// Provided request type/url to submit a new task with the highest priority
$router->post('task', 'JobController@put');

// Request type/url for listing all tasks
$router->get('task/list', 'JobController@list');

// Provided request type/url for getting info about a task
$router->get('task/{jobID}', 'JobController@show');

// Request type/url for updating a task
$router->post('task/{jobID}', 'JobController@update');

