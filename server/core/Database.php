<?php

namespace Framework\Core;

require_once('./core/Logger.php');

use PDO;
use Exception;
use Framework\Core\LogLevel;

/**
 * Wrapper for SQLite using PDO
 */
class Database
{
    /**
     * Hold database connection
     */
    protected $db;
    protected $logQueries = true;

    /**
     * Array of connection arguments
     * 
     * @param array $args
     */
    public function __construct($args = [])
    {
        $database = './database/main.db';

        $this->db = new PDO("sqlite:$database");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Get PDO instance
     * 
     * @return PDO instance
     */
    public function getPdo()
    {
        return $this->db;
    }

    /**
     * Run raw SQL query 
     * 
     * @param string $sql SQL query
     * @return void
     */
    public function raw($sql)
    {
        $this->db->query($sql);
    }

    /**
     * Run SQL query
     * 
     * @param string $sql SQL query
     * @param array $args Params
     * @return PDOStatement returns a PDOStatement object
     */
    public function run($sql, $args = [])
    {
        if (empty($args)) {
            return $this->db->query($sql);
        }

        $stmt = $this->db->prepare($sql);

        // Check if args is associative or sequential
        $is_assoc = array_keys($args) !== range(0, count($args) - 1);
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

    public function rows($sql, $args = [], $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->run($sql, $args)->fetchAll($fetchMode);
    }

    public function row($sql, $args = [], $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->run($sql, $args)->fetch($fetchMode);
    }

    public function getAll($table, $order = '', $filter = [], $fetchMode = PDO::FETCH_OBJ)
    {
        return $this->get($table, "*", $order, $filter, $fetchMode);
    }

    public function get($table, $columns, $order = '', $filter = [], $fetchMode = PDO::FETCH_OBJ)
    {
        $this->assertIdentifier($table);
        $this->assertColumnList($columns);
        $query = "SELECT $columns FROM $table";
        $values = [];

        if (!empty($filter)) {
            $conditions = [];
            foreach ($filter as $column => $value) {
                if ($column !== '') {
                    $this->assertIdentifier($column);
                    $conditions[] = "$column = ?";
                    $values[] = $value;
                }
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        if ($order !== '') {
            $this->assertOrderBy($order);
            $query .= ' ORDER BY ' . $order;
        }

        $this->Log('Database.get: ' . $query);

        return $this->run($query, $values)->fetchAll($fetchMode);
    }

    public function getByFilter($table, $filter = [], $fetchMode = PDO::FETCH_OBJ)
    {
        $this->assertIdentifier($table);
        $query = "SELECT * FROM $table";
        $values = [];

        if (!empty($filter)) {
            $conditions = [];
            foreach ($filter as $column => $value) {
                if ($column !== '') {
                    $this->assertIdentifier($column);
                    $conditions[] = "$column = ?";
                    $values[] = $value;
                }
            }
            $query .= " WHERE " . implode(" AND ", $conditions);
        }

        return $this->run($query, $values)->fetch($fetchMode);
    }

    private function assertIdentifier(string $identifier): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
            throw new Exception("Invalid SQL identifier: $identifier");
        }
    }

    private function assertColumnList(string $columns): void
    {
        if ($columns === '*') {
            return;
        }

        foreach (array_map('trim', explode(',', $columns)) as $column) {
            $this->assertIdentifier($column);
        }
    }

    private function assertOrderBy(string $order): void
    {
        foreach (array_map('trim', explode(',', $order)) as $expression) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(?:\s+(?:ASC|DESC))?$/i', $expression)) {
                throw new Exception("Invalid ORDER BY expression: $expression");
            }
        }
    }
    public function getById($table, $id, $fetchMode = PDO::FETCH_OBJ)
    {
        $this->assertIdentifier($table);
        return $this->run("SELECT * FROM $table WHERE id = ?", [$id])->fetch($fetchMode);
    }
    public function getByName($table, $name, $fetchMode = PDO::FETCH_OBJ)
    {
        $this->assertIdentifier($table);
        return $this->run("SELECT * FROM $table WHERE name = ?", [$name])->fetch($fetchMode);
    }

    public function search($table, $search, $columns = null, $mode = 'OR', $fetchMode = PDO::FETCH_OBJ)
    {
        $this->assertIdentifier($table);
        $mode = strtoupper($mode);
        if (!in_array($mode, ['AND', 'OR'], true)) {
            throw new Exception("Invalid search mode: $mode");
        }

        if (empty($search)) {
            return [];
        }

        if (is_null($columns) || empty($columns)) {
            $stmt = $this->run("PRAGMA table_info($table)");
            $stmt->execute();
            $tableInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $columns = array_column($tableInfo, 'name');
        }

        $whereClause = [];
        $values = [];

        foreach ($columns as $column) {
            $this->assertIdentifier($column);
            $whereClause[] = "$column LIKE ?";
            $values[] = "%$search%";
        }

        $whereCondition = implode(" $mode ", $whereClause);
        $sql = "SELECT * FROM $table WHERE $whereCondition";

        return $this->run($sql, $values)->fetchAll($fetchMode);
    }

    public function count($sql, $args = [])
    {
        return $this->run($sql, $args)->rowCount();
    }

    public function lastInsertId()
    {
        return $this->db->lastInsertId();
    }

    public function insert($table, $data)
    {
        $this->assertIdentifier($table);
        $this->assertDataKeys($data);
        $columns = implode(',', array_keys($data));
        $values = array_values($data);
        $placeholders = implode(',', array_fill(0, count($data), '?'));
        $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $this->run($query, $values);

        $this->Log("query: " . $query);

        return $this->lastInsertId();
    }

    public function update($table, $data, $where)
    {
        $this->assertIdentifier($table);
        $this->assertDataKeys($data);
        $this->assertDataKeys($where);
        $values = [];

        $fieldDetails = '';
        foreach ($data as $key => $value) {
            $fieldDetails .= "$key = ?,";
            $values[] = $value;
        }
        $fieldDetails = rtrim($fieldDetails, ',');

        $whereDetails = '';
        foreach ($where as $key => $value) {
            $whereDetails .= "$key = ? AND ";
            $values[] = $value;
        }
        $whereDetails = rtrim($whereDetails, ' AND ');

        $stmt = $this->run("UPDATE $table SET $fieldDetails WHERE $whereDetails", $values);

        return $stmt->rowCount();
    }

    public function delete($table, $where, $limit = null)
    {
        $this->assertIdentifier($table);
        $this->assertDataKeys($where);
        $values = array_values($where);
        $whereDetails = '';

        foreach ($where as $key => $value) {
            $whereDetails .= "$key = ? AND ";
        }
        $whereDetails = rtrim($whereDetails, ' AND ');

        $sql = "DELETE FROM $table WHERE $whereDetails";
        if (is_numeric($limit)) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $stmt = $this->run($sql, $values);

        return $stmt->rowCount();
    }

    public function deleteAll($table)
    {
        $this->assertIdentifier($table);
        $stmt = $this->run("DELETE FROM $table");

        return $stmt->rowCount();
    }

    public function deleteById($table, $id)
    {
        $this->assertIdentifier($table);
        $stmt = $this->run("DELETE FROM $table WHERE id = ?", [$id]);

        return $stmt->rowCount();
    }

    public function deleteByIds(string $table, string $column, array $ids)
    {
        $this->assertIdentifier($table);
        $this->assertIdentifier($column);

        if ($ids === []) {
            return 0;
        }

        foreach ($ids as $id) {
            if (!is_int($id) && !(is_string($id) && ctype_digit($id))) {
                throw new Exception('Delete IDs must contain only integers.');
            }
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $this->run("DELETE FROM $table WHERE $column IN ($placeholders)", array_values($ids));

        return $stmt->rowCount();
    }

    public function truncate($table)
    {
        $this->assertIdentifier($table);
        $stmt = $this->run("DELETE FROM $table");

        return $stmt->rowCount();
    }

    private function assertDataKeys(array $data): void
    {
        if ($data === []) {
            throw new Exception('Database data cannot be empty.');
        }

        foreach (array_keys($data) as $column) {
            $this->assertIdentifier((string) $column);
        }
    }

    protected function Log($message)
    {
        if ($this->logQueries) {
            Logger::Write("Database.php => " . $message, LogLevel::DEBUG);
        }
    }
}
