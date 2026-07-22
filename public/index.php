<?php

declare(strict_types=1);

$pdo = require dirname(__DIR__) . '/src/database.php';

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$allowedFilters = [
    'all',
    'active',
    'completed',
];

if (!in_array($filter, $allowedFilters, true)) {
    $filter = 'all';
}

$conditions = [];
$params = [];

if ($filter === 'active') {
    $conditions[] = 'is_completed = 0';
}

if ($filter === 'completed') {
    $conditions[] = 'is_completed = 1';
}

if ($search !== '') {
    $conditions[] = '
        (
            title LIKE :search
            OR description LIKE :search
        )
    ';

    $params['search'] = '%' . $search . '%';
}

$sql = '
    SELECT
        id,
        title,
        description,
        priority,
        due_date,
        is_completed,
        created_at
    FROM tasks
';

if ($conditions !== []) {
    $sql .= ' WHERE ' . implode(' AND ', $conditions);
}

$sql .= ' ORDER BY id DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$tasks = $stmt->fetchAll();

$today = date('Y-m-d');

function escape(string $value): string
{
    return htmlspecialchars(
        $value,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

function filterUrl(string $filter, string $search): string
{
    $parameters = [
        'filter' => $filter,
    ];

    if ($search !== '') {
        $parameters['search'] = $search;
    }

    return '/?' . http_build_query($parameters);
}

function normalizePriority(string $priority): string
{
    return match ($priority) {
        'low' => 'low',
        'high' => 'high',
        default => 'medium',
    };
}

function priorityLabel(string $priority): string
{
    return match (normalizePriority($priority)) {
        'low' => 'Низкий',
        'high' => 'Высокий',
        default => 'Средний',
    };
}

function formatDate(string $date): string
{
    $timestamp = strtotime($date);

    if ($timestamp === false) {
        return $date;
    }

    return date('d.m.Y', $timestamp);
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

            <label for="priority">
                Приоритет
            </label>

            <select
                id="priority"
                name="priority"
            >
                <option value="low">
                    Низкий
                </option>

                <option
                    value="medium"
                    selected
                >
                    Средний
                </option>

                <option value="high">
                    Высокий
                </option>
            </select>

            <label for="due_date">
                Срок выполнения
            </label>

            <input
                id="due_date"
                name="due_date"
                type="date"
            >

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
                        href="<?= escape(
                            filterUrl('all', $search)
                        ) ?>"
                    >
                        Все
                    </a>

                    <a
                        class="filter-link<?= $filter === 'active'
                            ? ' filter-link--active'
                            : '' ?>"
                        href="<?= escape(
                            filterUrl('active', $search)
                        ) ?>"
                    >
                        Активные
                    </a>

                    <a
                        class="filter-link<?= $filter === 'completed'
                            ? ' filter-link--active'
                            : '' ?>"
                        href="<?= escape(
                            filterUrl('completed', $search)
                        ) ?>"
                    >
                        Выполненные
                    </a>
                </nav>
            </div>

            <form
                class="search-form"
                action="/"
                method="get"
            >
                <input
                    type="hidden"
                    name="filter"
                    value="<?= escape($filter) ?>"
                >

                <label
                    class="visually-hidden"
                    for="search"
                >
                    Поиск задач
                </label>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="<?= escape($search) ?>"
                    placeholder="Поиск по названию и описанию"
                >

                <button type="submit">
                    Найти
                </button>

                <?php if ($search !== ''): ?>
                    <a
                        class="clear-search"
                        href="<?= escape(
                            filterUrl($filter, '')
                        ) ?>"
                    >
                        Сбросить
                    </a>
                <?php endif; ?>
            </form>

            <?php if ($tasks === []): ?>
                <p class="empty-message">
                    Задачи не найдены.
                </p>
            <?php else: ?>
                <ul class="task-list">
                    <?php foreach ($tasks as $task): ?>
                        <?php
                            $isCompleted =
                                (int) $task['is_completed'] === 1;

                            $description = (string) (
                                $task['description'] ?? ''
                            );

                            $priority = normalizePriority(
                                (string) $task['priority']
                            );

                            $dueDate = (string) (
                                $task['due_date'] ?? ''
                            );

                            $isOverdue =
                                !$isCompleted
                                && $dueDate !== ''
                                && $dueDate < $today;

                            $taskClasses = [
                                'task-item',
                            ];

                            if ($isCompleted) {
                                $taskClasses[] =
                                    'task-item--completed';
                            }

                            if ($isOverdue) {
                                $taskClasses[] =
                                    'task-item--overdue';
                            }
                        ?>

                        <li class="<?= escape(
                            implode(' ', $taskClasses)
                        ) ?>">
                            <div class="task-item__content">
                                <h3>
                                    <?= escape(
                                        (string) $task['title']
                                    ) ?>
                                </h3>

                                <?php if ($description !== ''): ?>
                                    <p>
                                        <?= escape($description) ?>
                                    </p>
                                <?php endif; ?>

                                <div class="task-meta">
                                    <span
                                        class="priority-badge priority-badge--<?= escape(
                                            $priority
                                        ) ?>"
                                    >
                                        <?= escape(
                                            priorityLabel($priority)
                                        ) ?>
                                    </span>

                                    <?php if ($dueDate !== ''): ?>
                                        <time
                                            class="due-date<?= $isOverdue
                                                ? ' due-date--overdue'
                                                : '' ?>"
                                            datetime="<?= escape(
                                                $dueDate
                                            ) ?>"
                                        >
                                            Срок:
                                            <?= escape(
                                                formatDate($dueDate)
                                            ) ?>

                                            <?php if ($isOverdue): ?>
                                                — просрочено
                                            <?php endif; ?>
                                        </time>
                                    <?php endif; ?>
                                </div>

                                <time
                                    class="created-date"
                                    datetime="<?= escape(
                                        (string) $task['created_at']
                                    ) ?>"
                                >
                                    Создано:
                                    <?= escape(
                                        (string) $task['created_at']
                                    ) ?>
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