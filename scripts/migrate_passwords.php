<?php

chdir(__DIR__ . '/../server');

require_once './core/Database.php';

use Framework\Core\Database;

$database = new Database();
$pdo = $database->getPdo();
$pdo->beginTransaction();

try {
    $pdo->exec('CREATE TABLE IF NOT EXISTS fw_migration (name TEXT PRIMARY KEY, applied_at TEXT NOT NULL)');
    $migration = '2026-08-13-hash-user-passwords';
    $alreadyApplied = $database->row('SELECT name FROM fw_migration WHERE name = ?', [$migration]);

    if ($alreadyApplied) {
        $pdo->rollBack();
        echo "Password migration was already applied.\n";
        exit(0);
    }

    $users = $database->rows('SELECT id, password FROM fw_user');
    $migrated = 0;
    foreach ($users as $user) {
        if (password_get_info($user->password)['algo'] !== null) {
            continue;
        }

        $database->run(
            'UPDATE fw_user SET password = ? WHERE id = ?',
            [password_hash($user->password, PASSWORD_DEFAULT), $user->id]
        );
        $migrated++;
    }

    $database->run(
        'INSERT INTO fw_migration (name, applied_at) VALUES (?, ?)',
        [$migration, gmdate(DATE_ATOM)]
    );
    $pdo->commit();
    echo "Migrated $migrated user password(s).\n";
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}
