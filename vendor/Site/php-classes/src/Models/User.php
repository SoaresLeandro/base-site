<?php

namespace site\Models;
use Site\DB\Sql;
use Site\Model;
use Site\Mailer;

class User extends Model {

	const SESSION = "User";
	const SECRET = "NextTISecretKey!";

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

	public static function listAll()
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM users;");

		return $results;
	}

	public function save()
	{
		$date = date('Y-m-d h:i:s');

		$password = $this->password_encrypt($this->getpassword());

		$sql = new Sql();

		$sql->query("INSERT INTO users(name, email, password, inadmin, created, modified) 
			values(:name, :email, :password, :inadmin, :created, :modified) ", array(
				':name' => $this->getname(),
				':email' => $this->getemail(),
				':password' => $password,
				':inadmin' => $this->getinadmin(),
				':created' => $date,
				':modified' => $date
			));
	}

	public function get($iduser)
	{
		$sql = new Sql();

		$results = $sql->select("SELECT * FROM users WHERE id = :iduser", [
			':iduser' => $iduser
		]);

		return $results[0];
	}

	public function update()
	{
		$date = Date('Y-m-d H:i:s');
		
		$sql = new Sql();

		$sql->query("UPDATE users SET name = :name, email = :email, inadmin = :inadmin, modified = :modified WHERE id = :id", [
			":name" => $this->getname(),
			":email" => $this->getemail(),
			":inadmin" => $this->getinadmin(),
			":modified" => $date,
			":id" => $this->getid()
		]);
	}

	public function delete($iduser)
	{

		$sql = new Sql();

		$sql->query("DELETE FROM users WHERE id = :iduser", [
			':iduser' => (int)$iduser
		]);
	}

	public static function getForgot($email)
	{
		$ivlen = openssl_cipher_iv_length("AES-128-CBC");
		$iv = openssl_random_pseudo_bytes($ivlen);

		$date = Date('Y-m-d H:i:s');

		$sql = new Sql();

		$results = $sql->select("SELECT * FROM users WHERE email = :email", [
			':email' => $email
		]);

		if (count($results) == 0) {
			throw new \Exception('Não foi possível recuperar a senha', 1);
			return;
		}

		$data = $results[0];
		
		$sql->query("INSERT INTO password_recovery (id_user, email, date_solicitation) 
					VALUES (:id_user, :email, :date_solicitation);", [
						':id_user' => $data['id'],
						':email' => $data['email'],
						':date_solicitation' => $date
					]);

		$results2 = $sql->select("SELECT * FROM password_recovery WHERE id = LAST_INSERT_ID()");

		if (!count($results2)) {
			throw new \Exception('Não foi possível recuperar a senha', 1);
			return;
		}

		$dataRecovery = $results2[0];

		// $code = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, User::SECRET, $dataRecovery['id'], MYCRYPT_MODE_ECB));
		$code = base64_encode(openssl_encrypt($dataRecovery['id'], "AES-128-CBC", User::SECRET, 0, $iv));

		$link = "http://www.site.com/admin/forgot/reset?code=$code";

		$mailer = new Mailer($data['email'], $data['name'], "Redefinir de senha de acesso à Next TI", 'forgot', ['name' => $data['name'], 'link' => $link]);

		$mailer->send();

		return $data;
	}

	public static function validForgotDecrypt($code)
	{
		$idRecovery = openssl_decrypt(base64_decode($code), "AES-128-CBC", User::SECRET, 0, $iv);

		$sql = new Sql();

		$results = $sql->query("SELECT * FROM password_recovery WHERE id = :id AND date_recovery is null AND DATE_ADD(date_solicitation, INTERVAL 1 HOUR) >= NOW()", [
			':id' => $idRecovery
		]);

		if (!count($results)) {
			throw new \Exception("Não foi possível recuperar a senha.");
		}

		$results2 = $sql->select("SELECT * FROM users WHERE email = :email", [
			':email' => $results[0]['email']
		]);

		return $results2[0];
	}

	public static function setForgotUsed($id)
	{
		$sql = new Sql();

		$sql->query('UPDATE password_recovery SET date_recovery = NOW() WHERE id = :id', [
			':id' => $id
		]);
	}

	public function setPassword($password)
	{
		// $password = $this->password_encrypt($password);
		
		// $sql = new Sql();

		// $sql->query("UPDATE users SET password = :password WHERE id = :id", [
		// 	':password' => $password,
		// 	':id' => $this->getid()
		// ]);
	}

	private function password_encrypt($password)
	{
		$password = password_hash($password, PASSWORD_BCRYPT);

		return $password;
	}

}