<?php

session_start();

require_once('vendor/autoload.php');

use \Slim\Slim;
use \Site\Page;
use \Site\PageAdmin;
use \Site\Models\User;

$app = new Slim();

$app->config('debug', true);

$app->get('/', function () {
        
	$page = new Page();

	$page->setTpl('index');

});

$app->get('/admin', function () {

	User::verifyLogin();
        
	$page = new PageAdmin();

	$page->setTpl('index');

});

$app->get('/admin/login', function () {

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl('login');

});

$app->post('/admin/login', function () {

	User::login($_POST['email'], $_POST['password']);

	header('Location: /admin');

	exit;

});

$app->get('/admin/logout', function () {

	User::logout();

	header('Location: /admin/login');

	exit;

});

$app->get('/admin/forgot', function () {

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl('forgot');

});

$app->post('/admin/forgot', function () {

	$email = $_POST['email'];

	$user = User::getForgot($email);

	header('Location: /admin/forgot/sent');
	exit;
});

$app->get('/admin/forgot/sent', function () {

	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl('forgot-sent');

});

$app->get('/admin/forgot/reset', function() {

	$user = User::validForgotDecrypt($_GET['code']);
	
	$page = new PageAdmin([
		"header" => false,
		"footer" => false
	]);

	$page->setTpl('forgot-reset');

});

require_once("users_routes.php");

$app->run();

