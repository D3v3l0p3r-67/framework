<?php
//Core is from: https://github.com/dcblogdev/pdo-wrapper

namespace Framework\Core;

use PDO;
use Exception;

/**
 * Wrapper for PDO
 */
class Database
{
    /**
     * hold database connection
     */
    protected $db;

    /**
     * Array of connection arguments
     * 
     * @param array $args
     */
    public function __construct($args = [])
    {
        $config = require __DIR__ . '/../config.local.php';

        $args = [
            'username' => $config['database']['username'],
            'database' => $config['database']['database'],
            'password' => $config['database']['password'],
            'type' => $config['database']['type'],
            'charset' => $config['database']['charset']
        ];

        if (!isset($args['database'])) {
            throw new Exception('&args[\'database\'] is required');
        }

        if (!isset($args['username'])) {
            throw new Exception('&args[\'username\']  is required');
        }

        $type     = isset($args['type']) ? $args['type'] : 'mysql';
        $host     = isset($args['host']) ? $args['host'] : 'localhost';
        $charset  = isset($args['charset']) ? $args['charset'] : 'utf8';
        $port     = isset($args['port']) ? 'port=' . $args['port'] . ';' : '';
        $password = isset($args['password']) ? $args['password'] : '';
        $database = $args['database'];
        $username = $args['username'];

        $this->db = new PDO("$type:host=$host;$port" . "dbname=$database;charset=$charset", $username, $password);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * get PDO instance
     * 
     * @return $db PDO instance
     */
    public function getPdo()
    {
        return $this->db;
    }

    /**
     * Run raw sql query 
     * 
     * @param  string $sql       sql query
     * @return void
     */
    public function raw($sql)
    {
        $this->db->query($sql);
    }

    /**
     * Run sql query
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @return object            returns a PDO object
     */
    public function run($sql, $args = [])
    {
        if (empty($args)) {
            return $this->db->query($sql);
        }

        $stmt = $this->db->prepare($sql);

        //check if args is associative or sequential?
        $is_assoc = (array() === $args) ? false : array_keys($args) !== range(0, count($args) - 1);
        if ($is_assoc) {
            foreach ($args as $key => $value) {
                if (is_int($value)) {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            $stmt->execute();
        } else {
            $stmt->execute($args);
        }

        return $stmt;
    }

    /**
     * Get arrrays of records
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns multiple records
     */
    public function rows($sql, $args = [], $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->run($sql, $args)->fetchAll($fetchMode);
    }

    /**
     * Get arrray of records
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns single record
     */
    public function row($sql, $args = [], $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->run($sql, $args)->fetch($fetchMode);
    }

    /**
     * Get records with all columns
     * 
     * @param  string $table     name of table
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns single or multiple record
     */
    public function getAll($table, $order = '', $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->get($table, "*", $order, $fetchMode);
    }


    /**
     * Get records with specified columns
     * @param  string $table     name of table
     * @param  integer $columns      returned columns
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns single or multiple record
     */
    public function get($table, $columns, $order = '', $fetchMode = PDO::FETCH_OBJ)
    {
        $query = "SELECT $columns FROM $table";

        if ($order) {
            $query = $query . ' order by ' . $order;
        }

        return $this->run($query)->fetchAll($fetchMode);
    }
    /**
     * Get record by id
     * 
     * @param  string $table     name of table
     * @param  integer $id       id of record
     * @param  object $fetchMode set return mode ie object or array
     * @return object            returns single record
     */
    public function getById($table, $id, $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->run("SELECT * FROM $table WHERE id = ?", [$id])->fetch($fetchMode);
    }

    /**
     * Filter records in a table based on provided data
     *
     * @param string $table Name of the table
     * @param array $data Associative array of column-value pairs for filtering
     * @param string $fetchMode Fetch mode for result data (optional, default: PDO::FETCH_OBJ)
     * @return object Returns records that match the filter
     */
    public function filter($table, $data, $fetchMode = PDO::FETCH_OBJ)
    {
        if (empty($data)) {
            return [];
        }

        $whereClause = [];
        $values = [];

        foreach ($data as $column => $value) {
            $whereClause[] = "$column = ?";
            $values[] = $value;
        }

        $whereClause = implode(' AND ', $whereClause);

        return $this->run("SELECT * FROM $table WHERE $whereClause", $values)->fetchAll($fetchMode);
    }

    /**
     * Get number of records
     * 
     * @param  string $sql       sql query
     * @param  array  $args      params
     * @param  object $fetchMode set return mode ie object or array
     * @return integer           returns number of records
     */
    public function count($sql, $args = [])
    {
        return $this->run($sql, $args)->rowCount();
    }

    /**
     * Get primary key of last inserted record
     */
    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    /**
     * insert record
     * 
     * @param  string $table table name
     * @param  array $data  array of columns and values
     */
    public function insert($table, $data)
    {
        //add columns into comma seperated string
        $columns = implode(',', array_keys($data));

        //get values
        $values = array_values($data);

        $placeholders = array_map(function ($val) {
            return '?';
        }, array_keys($data));

        //convert array into comma seperated string
        $placeholders = implode(',', array_values($placeholders));

        $this->run("INSERT INTO $table ($columns) VALUES ($placeholders)", $values);

        return $this->lastInsertId();
    }

    /**
     * update record
     * 
     * @param  string $table table name
     * @param  array $data  array of columns and values
     * @param  array $where array of columns and values
     */
    public function update($table, $data, $where)
    {
        //collect the values from data and where
        $values = [];

        //setup fields
        $fieldDetails = null;
        foreach ($data as $key => $value) {
            $fieldDetails .= "$key = ?,";
            $values[] = $value;
        }
        $fieldDetails = rtrim($fieldDetails, ',');

        //setup where 
        $whereDetails = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $whereDetails .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $values[] = $value;
            $i++;
        }

        $stmt = $this->run("UPDATE $table SET $fieldDetails WHERE $whereDetails", $values);

        return $stmt->rowCount();
    }

    /**
     * Delete records
     * 
     * @param  string $table table name
     * @param  array $where array of columns and values
     * @param  integer $limit limit number of records
     */
    public function delete($table, $where, $limit = 1)
    {
        //collect the values from collection
        $values = array_values($where);

        //setup where 
        $whereDetails = null;
        $i = 0;
        foreach ($where as $key => $value) {
            $whereDetails .= $i == 0 ? "$key = ?" : " AND $key = ?";
            $i++;
        }

        //if limit is a number use a limit on the query
        if (is_numeric($limit)) {
            $limit = "LIMIT $limit";
        }

        $stmt = $this->run("DELETE FROM $table WHERE $whereDetails $limit", $values);

        return $stmt->rowCount();
    }

    /**
     * Delete all records records
     * 
     * @param  string $table table name
     */
    public function deleteAll($table)
    {
        $stmt = $this->run("DELETE FROM $table");

        return $stmt->rowCount();
    }

    /**
     * Delete record by id
     * 
     * @param  string $table table name
     * @param  integer $id id of record
     */
    public function deleteById($table, $id)
    {
        $stmt = $this->run("DELETE FROM $table WHERE id = ?", [$id]);

        return $stmt->rowCount();
    }

    /**
     * Delete record by ids
     * 
     * @param  string $table table name
     * @param  string $column name of column
     * @param  string $ids ids of records
     */
    public function deleteByIds(string $table, string $column, string $ids)
    {
        $stmt = $this->run("DELETE FROM $table WHERE $column IN ($ids)");

        return $stmt->rowCount();
    }

    /**
     * truncate table
     * 
     * @param  string $table table name
     */
    public function truncate($table)
    {
        $stmt = $this->run("TRUNCATE TABLE $table");

        return $stmt->rowCount();
    }
}
