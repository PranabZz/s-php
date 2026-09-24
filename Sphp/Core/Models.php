<?php

namespace Sphp\Core;
use Sphp\Core\Database;


/* TODO */

class Models
{
    protected $table;

    protected $env;

    protected $db;

    protected $fillables = [];

    protected $hidden_fields;

    public function __construct()
    {
        $this->env = function_exists('app') && app()->has('config')
            ? app('config')
            : (file_exists(__DIR__ . '/../../app/config/config.php') ? require __DIR__ . '/../../app/config/config.php' : []);

        $this->db = function_exists('app') && app()->has('db')
            ? app('db')
            : new Database($this->env);
    }

    public static function __callStatic(string $method, array $arguments)
    {
        return (new static())->$method(...$arguments);
    }

    public static function create($request)
    {
        return (new static())->performCreate($request);
    }

    public static function update($request, $id)
    {
        return (new static())->performUpdate($request, $id);
    }

    public static function delete($id)
    {
        return (new static())->performDelete($id);
    }

    public static function select(array $columns, array $where = [], string $orderBy = '', int $limit = 0)
    {
        return (new static())->performSelect($columns, $where, $orderBy, $limit);
    }

    public static function findByID($id)
    {
        return (new static())->performFindByID($id);
    }

    public static function findOne(array $where)
    {
        return (new static())->performFindOne($where);
    }

    public function performCreate($request)
    {
        try {
            $data = array_intersect_key($request, array_flip($this->fillables));

            if (empty($data)) {
                throw new \Exception("No valid fields provided for insertion.");
            }

            $columns = implode(", ", array_keys($data));
            $placeholders = implode(", ", array_fill(0, count($data), "?"));

            $query = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

            $result = $this->db->query($query, array_values($data));

            return $result;
        } catch (\Exception $e) {
           dd($e->getMessage());
        }
    }

    public function performUpdate($request, $id)
    {
        $data = array_intersect_key($request, array_flip($this->fillables));
        if (empty($data)) {
            throw new \Exception("No valid fields provided for updating.");
        }

        $updateFields = implode(", ", array_map(function ($field) {
            return "$field = ?";
        }, array_keys($data)));

        $query = "UPDATE {$this->table} SET $updateFields WHERE id = ?";

        $params = array_values($data);
        $params[] = $id;

        $this->db->query($query, $params);
    }

    public function performDelete($id)
    {
        if (empty($id)) {
            throw new \Exception("No valid fields provided for updating.");
        }

        $query = "DELETE FROM {$this->table} WHERE id = ?";

        $params[] = $id;
        $this->db->query($query, $params);
    }

    public function performSelect(array $columns, array $where = [], string $orderBy = '', int $limit = 0)
    {
        if (empty($columns)) {
            throw new \Exception("No valid fields provided for selection.");
        }

        $columns_in_string = implode(', ', $columns);

        $where_string = '';
        $params = [];
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
                $params[] = $value;
            }
            $where_string = 'WHERE ' . implode(' AND ', $conditions);
        }

        $order_by_string = $orderBy ? "ORDER BY $orderBy" : '';
        $limit_string = $limit > 0 ? "LIMIT $limit" : '';

        $query = "SELECT {$columns_in_string} FROM {$this->table} {$where_string} {$order_by_string} {$limit_string}";

        // Optional: Clean up whitespace
        $query = preg_replace('/\s+/', ' ', trim($query));

        return $this->db->query($query, $params);
    }

    public function performFindByID($id)
    {
        if (empty($id)) {
            throw new \Exception("No valid fields provided for selection.");
        }

        $query = "SELECT * FROM {$this->table} WHERE id = $id";
        $result = $this->db->query($query);

        return $result[0] ?? null;
    }

    public function performFindOne(array $where)
    {
        $result = $this->performSelect(['*'], $where);
        return $result[0] ?? null;
    }
}
