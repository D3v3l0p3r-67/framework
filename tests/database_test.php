<?php

chdir(__DIR__ . '/../server');

require_once './core/Database.php';

use Framework\Core\Database;

$database = new class extends Database {
    public function __construct()
    {
        $this->db = new PDO('sqlite::memory:');
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
};

$database->raw('CREATE TABLE items (id INTEGER PRIMARY KEY, name TEXT, position INTEGER)');
$database->insert('items', ['name' => 'one', 'position' => 2]);
$database->insert('items', ['name' => 'two', 'position' => 1]);

$quotedValue = '" OR 1=1 --';
$database->insert('items', ['name' => $quotedValue, 'position' => 3]);

$row = $database->getByFilter('items', ['name' => $quotedValue]);
if (!$row || $row->name !== $quotedValue) {
    throw new RuntimeException('Filter values are not safely parameterized.');
}

$rows = $database->getAll('items', 'position DESC', ['name' => 'one']);
if (count($rows) !== 1 || $rows[0]->name !== 'one') {
    throw new RuntimeException('Parameterized get() returned unexpected rows.');
}

$database->update('items', ['name' => 'updated'], ['id' => 1]);
if ($database->getById('items', 1)->name !== 'updated') {
    throw new RuntimeException('Parameterized update returned unexpected data.');
}

$database->deleteByIds('items', 'id', [1, '2']);
$remainingRows = $database->getAll('items');
if (count($remainingRows) !== 1 || $remainingRows[0]->name !== $quotedValue) {
    throw new RuntimeException('Parameterized deleteByIds deleted unexpected rows.');
}

try {
    $database->search('items', 'value', ['name'], 'UNION SELECT');
    throw new RuntimeException('Unsafe search mode was accepted.');
} catch (Exception $exception) {
    if (!str_starts_with($exception->getMessage(), 'Invalid search mode:')) {
        throw $exception;
    }
}

$transactionResult = $database->transaction(function (Database $database): string {
    $database->insert('items', ['name' => 'committed', 'position' => 4]);
    return 'result';
});
if ($transactionResult !== 'result' || !$database->getByFilter('items', ['name' => 'committed'])) {
    throw new RuntimeException('Transaction did not commit or return its callback result.');
}

try {
    $database->transaction(function (Database $database): void {
        $database->insert('items', ['name' => 'rolled-back', 'position' => 5]);
        throw new RuntimeException('rollback');
    });
} catch (RuntimeException $exception) {
    if ($exception->getMessage() !== 'rollback') {
        throw $exception;
    }
}
if ($database->getByFilter('items', ['name' => 'rolled-back'])) {
    throw new RuntimeException('Transaction did not roll back after an exception.');
}

foreach ([0, -1] as $invalidLimit) {
    try {
        $database->delete('items', ['name' => 'committed'], $invalidLimit);
        throw new RuntimeException('Invalid delete limit was accepted.');
    } catch (Exception $exception) {
        if ($exception->getMessage() !== 'Delete limit must be a positive integer.') {
            throw $exception;
        }
    }
}

try {
    $database->getAll('items; DROP TABLE items');
    throw new RuntimeException('Unsafe identifiers were accepted.');
} catch (Exception $exception) {
    if (!str_starts_with($exception->getMessage(), 'Invalid SQL identifier:')) {
        throw $exception;
    }
}

echo "Database tests passed.\n";
