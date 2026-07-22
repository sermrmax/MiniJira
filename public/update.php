<?php

declare(strict_types=1);

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

$pdo = require dirname(__DIR__) . '/src/database.php';

$stmt = $pdo->prepare(
    'UPDATE tasks
     SET title = :title,
         description = :description
     WHERE id = :id'
);

$stmt->execute([
    'id' => $id,
    'title' => $title,
    'description' => $description,
]);

header('Location: /');

exit;