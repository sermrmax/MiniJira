<?php

declare(strict_types=1);

$pdo = require dirname(__DIR__) . '/src/database.php';

$filter = $_GET['filter'] ?? 'all';

$allowedFilters = [
    'all',
    'active',
    'completed',
];

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$sql = '
    SELECT
        id,
        title,
        description,
        is_completed,
        created_at
    FROM tasks
';

if ($filter === 'active') {
    $sql .= ' WHERE is_completed = 0';
}

if ($filter === 'completed') {
    $sql .= ' WHERE is_completed = 1';
}

$sql .= ' ORDER BY id DESC';

$stmt = $pdo->query($sql);

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
            <div class="tasks-header">
                <h2>Мои задачи</h2>

                <nav
                    class="task-filters"
                    aria-label="Фильтры задач"
                >
                    <a
                        class="filter-link<?= $filter === 'all'
                            ? ' filter-link--active'
                            : '' ?>"
                        href="/?filter=all"
                    >
                        Все
                    </a>

                    <a
                        class="filter-link<?= $filter === 'active'
                            ? ' filter-link--active'
                            : '' ?>"
                        href="/?filter=active"
                    >
                        Активные
                    </a>

                    <a
                        class="filter-link<?= $filter === 'completed'
                            ? ' filter-link--active'
                            : '' ?>"
                        href="/?filter=completed"
                    >
                        Выполненные
                    </a>
                </nav>
            </div>

            <?php if ($tasks === []): ?>
                <p class="empty-message">
                    Задачи по выбранному фильтру не найдены.
                </p>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <?php
                            $isCompleted =
                                (int) $task['is_completed'] === 1;
                        ?>

                        <li
                            class="task-item<?= $isCompleted
                                ? ' task-item--completed'
                                : '' ?>"
                        >
                            <div class="task-item__content">
                                <h3>
                                    <?= escape($task['title']) ?>
                                </h3>

                                <?php if ($task['description'] !== ''): ?>
                                    <p>
                                        <?= escape(
                                            $task['description']
                                        ) ?>
                                    </p>
                                <?php endif; ?>

                                <time>
                                    Создано:
                                    <?= escape($task['created_at']) ?>
                                </time>
                            </div>

                            <div class="task-item__actions">
                                <a
                                    class="edit-button"
                                    href="/edit.php?id=<?= (int) $task['id'] ?>"
                                >
                                    Изменить
                                </a>

                                <form
                                    action="/toggle.php"
                                    method="post"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $task['id'] ?>"
                                    >

                                    <button
                                        class="toggle-button"
                                        type="submit"
                                    >
                                        <?= $isCompleted
                                            ? 'Вернуть'
                                            : 'Выполнено' ?>
                                    </button>
                                </form>

                                <form
                                    action="/delete.php"
                                    method="post"
                                >
                                    <input
                                        type="hidden"
                                        name="id"
                                        value="<?= (int) $task['id'] ?>"
                                    >

                                    <button
                                        class="delete-button"
                                        type="submit"
                                    >
                                        Удалить
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>