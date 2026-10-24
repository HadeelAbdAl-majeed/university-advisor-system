<?php

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

try {
    $database = new Database();
    $pdo = $database->connect();
    $userModel = new User($pdo);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = trim($_POST['name'] ?? "");
        $email = trim($_POST['email'] ?? "");
        $password = $_POST['password'] ?? "";
        if ($name == '' || $email == '' || $password == '') {
            echo 'All fields are required.';
        } else {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                if (strlen($password) < 8) {
                    echo 'Password must be at least 8 characters.';
                } else {
                    $registered = $userModel->register($name, $email, $password);
                    if ($registered) {
                        echo 'Registration successful.';
                    } else {
                        echo 'Registration failed.';
                    }
                }
            } else {
                echo 'Invalid email address.';
            }
        }
    }
} catch (PDOException $error) {
    if ($error->getCode() == '23000' && ($error->errorInfo[1] ?? null) == 1062) {
        echo 'This email is already registered.';
    } else {
        echo 'Registration failed. Please try again.';
    }
}
