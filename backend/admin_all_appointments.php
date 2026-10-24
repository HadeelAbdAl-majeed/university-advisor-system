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

    $database = new Database();
    $pdo = $database->connect();

    $appointmentModel = new Appointment($pdo);

    $appointments = $appointmentModel->getAllAppointments();

    foreach ($appointments as $appointment) {
        echo '<hr>';

        echo '<p>Appointment ID: ' .
            (int) $appointment['appointment_id'] .
            '</p>';
        echo '<p>Student: ' .
            htmlspecialchars($appointment['student_name'], ENT_QUOTES, 'UTF-8') .
            '</p>';

        echo '<p>Advisor: ' .
            htmlspecialchars($appointment['advisor_name'], ENT_QUOTES, 'UTF-8') .
            '</p>';
        echo '<p>Date: ' .
            htmlspecialchars($appointment['slot_date'], ENT_QUOTES, 'UTF-8') .
            '</p>';

        echo '<p>Start Time: ' .
            htmlspecialchars($appointment['start_time'], ENT_QUOTES, 'UTF-8') .
            '</p>';

        echo '<p>End Time: ' .
            htmlspecialchars($appointment['end_time'], ENT_QUOTES, 'UTF-8') .
            '</p>';

        echo '<p>Note: ' .
            htmlspecialchars($appointment['note'] ?? '', ENT_QUOTES, 'UTF-8') .
            '</p>';

        echo '<p>Status: ' .
            htmlspecialchars($appointment['status'], ENT_QUOTES, 'UTF-8') .
            '</p>';
    }
}
