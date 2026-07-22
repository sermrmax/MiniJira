<?php

declare(strict_types=1);

$pdo = require dirname(__DIR__) . '/src/database.php';

$stmt = $pdo->query(
    'SELECT id, title, description, is_completed, created_at
     FROM tasks
     ORDER BY id DESC'
);

$tasks = $stmt->fetchAll();

function escape(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Менеджер задач</title>

    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="container">
        <h1>Менеджер задач</h1>

        <form
            class="task-form"
            action="/create.php"
            method="post"
        >
            <label for="title">
                Название задачи
            </label>

            <input
                id="title"
                name="title"
                type="text"
                maxlength="255"
                placeholder="Например, изучить PDO"
                required
            >

            <label for="description">
                Описание
            </label>

            <textarea
                id="description"
                name="description"
                rows="4"
                placeholder="Дополнительная информация"
            ></textarea>

            <button type="submit">
                Добавить задачу
            </button>
        </form>

        <section class="tasks">
            <h2>Мои задачи</h2>

            <?php if ($tasks === []): ?>
                <p class="empty-message">
                    Задач пока нет.
                </p>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <li class="task-item">
                            <div>
                                <h3>
                                    <?= escape($task['title']) ?>
                                </h3>

                                <?php if ($task['description'] !== ''): ?>
                                    <p>
                                        <?= escape($task['description']) ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <time>
                                <?= escape($task['created_at']) ?>
                            </time>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>