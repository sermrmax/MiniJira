<?php

declare(strict_types=1);

$pdo = require dirname(__DIR__) . '/src/database.php';

$pdo->exec(
    '
    CREATE TABLE IF NOT EXISTS tasks (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        title TEXT NOT NULL,
        description TEXT,
        is_completed INTEGER NOT NULL DEFAULT 0,
        created_at TEXT NOT NULL
    )
    '
);

echo "База данных и таблица tasks успешно созданы.\n";