<?php
namespace Chat;

class Users {

  public function __construct($pdo)
  {
    $this->db = $pdo;
  }

  public function auth()
  {
    $login = isset($_REQUEST['login']) ? $_REQUEST['login'] : '';
    $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
    $token = md5(time());
    $expires = time() + 3600000;
    if (empty($login) || empty($password))
    {
      return "Invalid login or password";
    }
    if ($login == ADMIN_LOGIN && $password == ADMIN_PASSWORD)
    {
      $statement = $this->db->prepare('INSERT INTO auth_tokens (token, expires, login) VALUES (:token, :expires, :login)');

      $statement->execute([
        'token' => $token,
        'expires' => $expires,
        'login' => $login
      ]);

      return $token;
    }

    $statement = $this->db->prepare('SELECT * FROM chat_users where login = :login');
    $statement->execute( [ 'login' => $login ] );
    $row = $statement->fetch();
    if ($row['login'] == $login && $row['password_hash'] == md5($password))
    {
      $statement = $this->db->prepare('INSERT INTO auth_tokens (token, expires, login) VALUES (:token, :expires, :login)');

      $statement->execute([
        'token' => $token,
        'expires' => $expires,
        'login' => $login
      ]);

      return $token;
    }

    return "Invalid login or password";
  }

  public function post($login)
  {
    $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : '';
    if (empty($password))
      return "Invalid password";
    $name = isset($_REQUEST['name']) ? $_REQUEST['name'] : '';
    if (empty($name))
      return "Invalid name";
    $statement = $this->db->prepare('INSERT INTO chat_users (login, password_hash, name) VALUES (:login, :password, :name)');

    $statement->execute([
      'login' => $login,
      'password' => md5($password),
      'name' => $name
    ]); 
    return true;
  }

  public function delete() // TODO
  {
  }
}
