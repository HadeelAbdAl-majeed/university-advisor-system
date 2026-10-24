<?php
session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Appointment.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'student') {
    http_response_code(403);
    echo 'Access denied.';
} else {
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        $csrfToken = $_POST['csrf_token'] ?? null;

        if (
            !is_string($csrfToken) ||
            !isset($_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $csrfToken)
        ) {

            http_response_code(403);
            echo 'Invalid CSRF token.';
        } else {
            $slotId = filter_input(INPUT_POST, 'slot_id', FILTER_VALIDATE_INT);
            if ($slotId === false || $slotId === null || $slotId <= 0) {
                echo 'Invalid slot ID.';
            } else {
                $noteInput = $_POST['note'] ?? '';
                $note = is_string($noteInput) ? trim($noteInput) : '';
                // $note = $note === '' ? null : $note;
                $database = new Database();
                $pdo = $database->connect();
                $appointmentModel = new Appointment($pdo);
                $success = $appointmentModel->bookAppointment(
                    (int) $_SESSION['user_id'],
                    $slotId,
                    $note
                );
                if ($success) {
                    echo 'Appointment booked successfully. Waiting for confirmation.';
                } else {
                    echo 'Booking failed. Please check the slot availability.';
                }
            }
        }
    }
}
