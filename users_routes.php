<?php

use \Site\Page;
use \Site\PageAdmin;
use \Site\Models\User;

$app->get('/admin/users', function () {

	User::verifyLogin();

	$page = new PageAdmin();

	$users = User::listAll();

	$page->setTpl('users', compact(['users']));

});

$app->get('/admin/users/create', function () {

	User::verifyLogin();

	$page = new PageAdmin();

	$page->setTpl('users-create');

});

$app->get('/admin/users/:iduser/delete', function ($iduser) {

	User::verifyLogin();

	$user = new User();
	
	$user->delete($iduser);

	header('Location: /admin/users');
	exit;
});

$app->get('/admin/users/:iduser', function ($iduser) {

	User::verifyLogin();

	$page = new PageAdmin();

	$user = new User();

	$user = $user->get($iduser);
	
	$page->setTpl('users-update', compact('user'));

});

$app->post('/admin/users/create', function () {

	User::verifyLogin();

	$user = new User();
	
	$_POST['inadmin'] = isset($_POST['inadmin']) ? 1 : 0;

	$user->setData($_POST);

	$user->save();

	header('Location: /admin/users');
	exit;
});

$app->post('/admin/users/:iduser', function ($iduser) {

	User::verifyLogin();

	$user = new User();

	$_POST['inadmin'] = isset($_POST['inadmin']) ? 1 : 0;

	$user->setData($_POST);

	$user->update();

	header('Location: /admin/users');
	exit;	
});