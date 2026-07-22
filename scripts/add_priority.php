<?php

declare(strict_types=1);

$pdo = require dirname(__DIR__) . '/src/database.php';

$columns = $pdo
    ->query('PRAGMA table_info(tasks)')
    ->fetchAll();

$columnNames = array_column($columns, 'name');

if (in_array('priority', $columnNames, true)) {
    echo "Колонка priority уже существует.\n";

    exit;
}

$pdo->exec(
    "ALTER TABLE tasks
     ADD COLUMN priority TEXT NOT NULL DEFAULT 'medium'"
);

echo "Колонка priority успешно добавлена.\n";