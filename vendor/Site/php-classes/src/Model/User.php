<?php

namespace site\Model;
use Site\DB\Sql;
use Site\Model;

class User extends Model {

	const SESSION = "User";

	public static function login($email, $password)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM users WHERE email = :email", [
			":email" => $email
		]);

		if (count($results) == 0) {
			throw new \Exception("Usuário ou senha inválidos", 1);
		}
		
		$data = $results[0];
		
		if (password_verify($password, $data["password"])) {

			$user = new User();

			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {
			throw new \Exception("Usuário ou senha inválidos", 1);
		}
	}

	public static function verifyLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			||
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]['id'] > 0
			||
			(bool)$_SESSION[User::SESSION]['inadmin'] !== $inadmin
		){
			header("Location: /admin/login");
			exit;
		}
	}

	public static function logout()
	{
		$_SESSION[User::SESSION] = NULL;
	}

}