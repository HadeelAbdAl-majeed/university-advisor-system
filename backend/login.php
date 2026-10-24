<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$database = new Database();
$pdo = $database->connect();
$userModel = new User($pdo);

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $email = trim($_POST['email'] ?? "");
    $password = $_POST['password'] ?? "";
    $user = $userModel->findByEmail($email);

    if ($user !== false && password_verify($password, $user['password']) && (int) $user['is_active'] == 1) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } elseif ($user['role'] === 'advisor') {
            header('Location: advisor_dashboard.php');
        } elseif ($user['role'] === 'student') {
            header('Location: get_appointments.php');
        } else {
            session_unset();
            session_destroy();
            http_response_code(403);
            exit('Invalid user role.');
        }
        exit();
    } else {
        echo 'Invalid email or password.';
    }
}
