<?php

try
{
  require_once __DIR__ . '/../vendor/autoload.php';
  require_once __DIR__ . '/../config.php';
  $method = strtolower($_SERVER['REQUEST_METHOD']);
  $path = $_SERVER['REQUEST_URI'];
  $aPath = explode('/', trim($path, '/'));


//if (isset($aPath[0]) && $aPath[0] == 'users' && isset($aPath[2]) && )
//{

  $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";";
  $pdo = new PDO(
      $dsn,
      DB_USER,
      DB_PASSWORD,
      [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

  if (count($aPath) == 1 && $aPath[0] == 'auth' && $method == 'post')
  {
    $class = new \Chat\Users($pdo);
    $result = $class->auth();
    echo json_encode([ "result" => $result ]);
    exit;
  }

  if (count($aPath) == 2 && $aPath[0] == 'users' && ($method == 'post' || $method == 'delete'))
  {
    $class = new \Chat\Users($pdo);
    $result = $class->$method($aPath[1]);
    echo json_encode([ "result" => $result ]);
    exit;
  }

  if (count($aPath) == 4 && $aPath[0] == 'users' && !empty($aPath[1]) && $aPath[2] == 'messages' && !empty($aPath[3]) && ($method == 'post' || $method == 'delete' || $method == 'get'))
  {
    $class = new \Chat\Messages($pdo);
    $result = $class->$method($aPath[1], $aPath[3]);
    echo json_encode([ "result" => $result ]);
    exit;
  }

  if (count($aPath) == 3 && $aPath[0] == 'users' && !empty($aPath[1]) && $aPath[2] == 'messages' && $method == 'get')
  {
    $class = new \Chat\Messages($pdo);
    $result = $class->get_all($aPath[1]);
    echo json_encode([ "result" => $result ]);
    exit;
  }

  echo "Not Implemented";
} catch(\Throwable $e)
{
  echo json_encode([ "result" => $e->getFile().':'.$e->getLine().':'.$e->getMessage()."\n".$e->getTraceAsString() ]);
}

