<?php

declare(strict_types=1);

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if ($id === false || $id === null || $id < 1) {
    http_response_code(422);

    exit('Некорректный идентификатор задачи.');
}

$pdo = require dirname(__DIR__) . '/src/database.php';

$stmt = $pdo->prepare(
    'SELECT
        id,
        title,
        description,
        priority
     FROM tasks
     WHERE id = :id'
);

$stmt->execute([
    'id' => $id,
]);

$task = $stmt->fetch();

if ($task === false) {
    http_response_code(404);

    exit('Задача не найдена.');
}

function escape(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

$priority = match ((string) $task['priority']) {
    'low' => 'low',
    'high' => 'high',
    default => 'medium',
};

$description = (string) ($task['description'] ?? '');
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Редактирование задачи</title>

    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="container">
        <h1>Редактирование задачи</h1>

        <form
            class="task-form"
            action="/update.php"
            method="post"
        >
            <input
                type="hidden"
                name="id"
                value="<?= (int) $task['id'] ?>"
            >

            <label for="title">
                Название задачи
            </label>

            <input
                id="title"
                name="title"
                type="text"
                maxlength="255"
                value="<?= escape(
                    (string) $task['title']
                ) ?>"
                required
            >

            <label for="description">
                Описание
            </label>

            <textarea
                id="description"
                name="description"
                rows="5"
            ><?= escape($description) ?></textarea>

            <label for="priority">
                Приоритет
            </label>

            <select
                id="priority"
                name="priority"
            >
                <option
                    value="low"
                    <?= $priority === 'low'
                        ? 'selected'
                        : '' ?>
                >
                    Низкий
                </option>

                <option
                    value="medium"
                    <?= $priority === 'medium'
                        ? 'selected'
                        : '' ?>
                >
                    Средний
                </option>

                <option
                    value="high"
                    <?= $priority === 'high'
                        ? 'selected'
                        : '' ?>
                >
                    Высокий
                </option>
            </select>

            <div class="edit-actions">
                <button type="submit">
                    Сохранить
                </button>

                <a
                    class="cancel-link"
                    href="/"
                >
                    Отмена
                </a>
            </div>
        </form>
    </main>
</body>
</html>