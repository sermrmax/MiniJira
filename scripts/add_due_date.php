<?php

declare(strict_types=1);

$pdo = require dirname(__DIR__) . '/src/database.php';

$columns = $pdo
    ->query('PRAGMA table_info(tasks)')
    ->fetchAll();

$columnNames = array_column($columns, 'name');

if (in_array('due_date', $columnNames, true)) {
    echo "Колонка due_date уже существует.\n";

    exit;
}

$pdo->exec(
    'ALTER TABLE tasks
     ADD COLUMN due_date TEXT DEFAULT NULL'
);

echo "Колонка due_date успешно добавлена.\n";