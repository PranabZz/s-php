<?php

/* 
  Database class which connects our project to the database 
*/

namespace Sphp\Core;

class Database
{

  public $connection;

  public function __construct($config = [])
  {
    if (empty($config) && file_exists(__DIR__ . '/../../app/config/config.php')) {
      $config = require __DIR__ . '/../../app/config/config.php';
    }

    $connection = strtolower($config['connection'] ?? env('DB_CONNECTION', 'sqlite'));

    switch ($connection) {
      case 'sqlite':
        $defaultPath = __DIR__ . '/../../app/Database/database.sqlite';
        $dbPath = $config['database'] ?? env('DB_DATABASE', $defaultPath);

        if ($dbPath !== ':memory:' && !str_starts_with($dbPath, '/') && !preg_match('/^[a-zA-Z]:/', $dbPath)) {
          $root = realpath(__DIR__ . '/../../') ?: (__DIR__ . '/../..');
          $dbPath = $root . '/' . ltrim($dbPath, '/');
        }

        if ($dbPath !== ':memory:') {
          $dir = dirname($dbPath);
          if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
          }
          if (!file_exists($dbPath)) {
            touch($dbPath);
          }
        }

        $this->connection = new \PDO("sqlite:{$dbPath}");
        $this->connection->exec('PRAGMA foreign_keys = ON;');
        break;

      case 'pgsql':
      case 'postgres':
      case 'postgresql':
        $host = $config['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = $config['port'] ?? env('DB_PORT', '5432');
        $database = $config['database'] ?? env('DB_DATABASE', 'sphp');
        $username = $config['username'] ?? env('DB_USERNAME', 'postgres');
        $password = $config['password'] ?? env('DB_PASSWORD', '');

        $dsn = "pgsql:host={$host};port={$port};dbname={$database};";
        $this->connection = new \PDO($dsn, $username, $password);
        break;

      case 'mysql':
      default:
        $host = $config['host'] ?? env('DB_HOST', '127.0.0.1');
        $port = $config['port'] ?? env('DB_PORT', '3306');
        $database = $config['database'] ?? env('DB_DATABASE', 'sphp');
        $username = $config['username'] ?? env('DB_USERNAME', 'root');
        $password = $config['password'] ?? env('DB_PASSWORD', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
        $this->connection = new \PDO($dsn, $username, $password);
        break;
    }

    $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->connection->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
  }

  /* 
    Query function takes two parameter 

      $parameter['query'] => query such as 'SELECT * FROM `users`'
      $paramerter['parms'] => any paramerter or variables that are comming from the user end
  */

  public function query($query, $params = array())
  {
    $statement = $this->connection->prepare($query);

    $statement->execute($params);

    return $statement->fetchAll(\PDO::FETCH_ASSOC);
  }
}
