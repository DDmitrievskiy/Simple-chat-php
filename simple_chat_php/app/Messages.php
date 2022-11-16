<?php
namespace Chat;

class Messages {

  public function __construct($pdo)
  { 
    $this->db = $pdo;
  }

  public function check_rules($token, $login)
  {
    $statement = $this->db->prepare('SELECT * FROM auth_tokens where token = :token');
    $statement->execute( [ 'token' => $token ] );
    $row = $statement->fetch();
    if (!empty($row))
      return ($row['login'] == ADMIN_LOGIN || $login == $row['login']) && (int)$row['expires'] > time();
  }

  public function post($from, $id)
  {
    $headers = getallheaders();
    if (empty($headers['Authorization']) || !$this->check_rules($headers['Authorization'], $from))
      return "Bad token";
    $to = isset($_REQUEST['recipient']) ? $_REQUEST['recipient'] : ''; // TODO check user exist
    if (empty($to))
      return "Invalid recipient";
    $message = isset($_REQUEST['message']) ? $_REQUEST['message'] : '';
    if (empty($message))
      return "Invalid message";
    $statement = $this->db->prepare('INSERT INTO chat_messages (author, recipient, message, id) VALUES (:author, :recipient, :message, :id)');

    $statement->execute([
      'author' => $from,
      'recipient' => $to,
      'message' => $message,
      'id' => $id
    ]);
    return true;
  }

  public function get($from, $id)
  {
    $headers = getallheaders();
    if (empty($headers['Authorization']) || !$this->check_rules($headers['Authorization'], $from))
      return "Bad token";
    $statement = $this->db->prepare('SELECT * FROM chat_messages where id = :id');
    $statement->execute( [ 'id' => $id ] );
    $row = $statement->fetch();
    if (!empty($row) && ($row['author'] == $from || $row['recipient'] == $from))
      return $row;
    return "Not Found";  
  }

  public function get_all($login)
  {  
    $headers = getallheaders();
    if (empty($headers['Authorization']) || !$this->check_rules($headers['Authorization'], $login))
      return "Bad token";
    $statement = $this->db->prepare('SELECT * FROM chat_messages where author = :author or recipient = :author');
    $statement->execute( [ 'author' => $login ] );
    $rows = $statement->fetchAll();
    $result = [];
    if (!empty($rows))
      foreach($rows as $row)
        $result[] = $row;
    return $result;
  }

  public function delete() // TODO
  {
  }
}
