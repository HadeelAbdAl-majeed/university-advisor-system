<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Appointment.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? null) !== 'admin'
) {
    http_response_code(403);
    echo 'Access denied.';
} else {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $csrfToken = $_POST['csrf_token'] ?? null;

        if (
            !is_string($csrfToken) ||
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $csrfToken)
        ) {
            http_response_code(403);
            echo 'Invalid CSRF token.';
        } else {
            $appointmentId = filter_input(INPUT_POST, 'appointment_id', FILTER_VALIDATE_INT);

            if ($appointmentId === false || $appointmentId === null || $appointmentId <= 0) {
                http_response_code(400);
                echo 'Invalid appointment ID.';
            } else {
                try {
                    $database = new Database();
                    $pdo = $database->connect();

                    $appointmentModel = new Appointment($pdo);

                    $success = $appointmentModel->rejectAppointment($appointmentId);
                    if ($success) {
                        echo 'Appointment rejected successfully.';
                    } else {
                        http_response_code(400);
                        echo 'Rejection failed. Please check the appointment status or time.';
                    }
                } catch (PDOException $error) {
                    error_log($error->getMessage());
                    http_response_code(500);
                    echo 'Unable to reject appointment.';
                }
            }
        }
    } else {
        http_response_code(405);
        echo 'Method not allowed.';
    }
}
