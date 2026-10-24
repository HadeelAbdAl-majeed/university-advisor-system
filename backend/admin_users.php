<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? null) !== 'admin'
) {
    http_response_code(403);
    echo 'Access denied.';
} else {

    $database = new Database();
    $pdo = $database->connect();

    $userModel = new User($pdo);

    $users = $userModel->getAllUsers();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Manage Users</title>
    </head>

    <body>

        <h1>Manage Users</h1>

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['id'] ?></td>

                        <td>
                            <?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($user['role'], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= (int) $user['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                        </td>

                        <td>
                            <?php if ($user['role'] !== 'admin'): ?>

                                <form action="admin_update_user_status.php" method="POST">

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        value="<?= (int) $user['id'] ?>">

                                    <input
                                        type="hidden"
                                        name="is_active"
                                        value="<?= (int) $user['is_active'] === 1 ? '0' : '1' ?>">

                                    <button type="submit">
                                        <?= (int) $user['is_active'] === 1 ? 'Deactivate' : 'Activate' ?>
                                    </button>

                                </form>

                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </body>

    </html>
<?php
}
