<?php

declare(strict_types=1);

function isValidDate(string $value): bool
{
    $date = DateTimeImmutable::createFromFormat(
        '!Y-m-d',
        $value
    );

    return $date !== false
        && $date->format('Y-m-d') === $value;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    exit('Метод запроса не поддерживается.');
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority = $_POST['priority'] ?? 'medium';
$dueDate = trim($_POST['due_date'] ?? '');

$allowedPriorities = [
    'low',
    'medium',
    'high',
];

if ($title === '') {
    http_response_code(422);

    exit('Название задачи обязательно.');
}

if (strlen($title) > 255) {
    http_response_code(422);

    exit('Название задачи слишком длинное.');
}

if (!in_array($priority, $allowedPriorities, true)) {
    http_response_code(422);

    exit('Некорректный приоритет задачи.');
}

if ($dueDate !== '' && !isValidDate($dueDate)) {
    http_response_code(422);

    exit('Некорректная дата выполнения.');
}

$pdo = require dirname(__DIR__) . '/src/database.php';

$stmt = $pdo->prepare(
    'INSERT INTO tasks (
        title,
        description,
        priority,
        due_date,
        is_completed,
        created_at
    ) VALUES (
        :title,
        :description,
        :priority,
        :due_date,
        0,
        :created_at
    )'
);

$stmt->execute([
    'title' => $title,
    'description' => $description,
    'priority' => $priority,
    'due_date' => $dueDate !== '' ? $dueDate : null,
    'created_at' => date('Y-m-d H:i:s'),
]);

header('Location: /');

exit;