<?php

/* 
  Database class which connects our project to the database 
*/

namespace Sphp\Core;

class Database
{

  public $connection;

  // Everytime we make a database object the contructor will make a database connection to the host and database wiht the username and password provided

      public function __construct($config)
      {
    $dsn = '';
    switch ($config['driver']) {
      case 'mysql':
        $dsn = 'mysql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'];
        break;
      case 'pgsql':
        $dsn = 'pgsql:host=' . $config['host'] . ';port=' . $config['port'] . ';dbname=' . $config['database'];
        break;
      case 'sqlite':
        $dsn = 'sqlite:' . $config['database'];
        break;
      default:
        throw new \Exception("Unsupported database driver: {$config['driver']}");
    }

    $this->connection = new \PDO($dsn, $config['username'], $config['password']);
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

  public function lastInsertId()
  {
    return $this->connection->lastInsertId();
  }
}
