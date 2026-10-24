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
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
} elseif (
    !isset($_SESSION['csrf_token'], $_POST['csrf_token']) ||
    !is_string($_POST['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    http_response_code(403);
    echo 'Invalid CSRF token.';
} else {

    $userId = filter_var($_POST['user_id'] ?? null, FILTER_VALIDATE_INT,['options' => ['min_range' => 1]]);

    $statusInput = $_POST['is_active'] ?? null;

    if ($userId === false ||  $userId === null ||!is_string($statusInput) || !in_array($statusInput, ['0', '1'], true)
    ) {
        http_response_code(400);
        echo 'Invalid user data.';
    } else {

        $database = new Database();
        $pdo = $database->connect();

        $userModel = new User($pdo);

        $success = $userModel->updateUserStatus(
            (int) $userId,
            $statusInput === '1'
        );

        if ($success) {
            echo 'User status updated successfully.';
        } else {
            echo 'User status could not be updated.';
        }
    }
}

?>
