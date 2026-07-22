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

$id = filter_input(
    INPUT_POST,
    'id',
    FILTER_VALIDATE_INT
);

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$priority = $_POST['priority'] ?? 'medium';
$dueDate = trim($_POST['due_date'] ?? '');

$allowedPriorities = [
    'low',
    'medium',
    'high',
];

if ($id === false || $id === null || $id < 1) {
    http_response_code(422);

    exit('Некорректный идентификатор задачи.');
}

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
    'UPDATE tasks
     SET title = :title,
         description = :description,
         priority = :priority,
         due_date = :due_date
     WHERE id = :id'
);

$stmt->execute([
    'id' => $id,
    'title' => $title,
    'description' => $description,
    'priority' => $priority,
    'due_date' => $dueDate !== '' ? $dueDate : null,
]);

header('Location: /');

exit;