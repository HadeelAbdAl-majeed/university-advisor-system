<?php

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

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
    $appointments = $appointmentModel->getPendingAppointments();

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
        echo '<form action="admin_confirm_appointment.php" method="POST">';

        echo '<input type="hidden" name="appointment_id" value="' . (int) $appointment['appointment_id'] . '">';

        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';

        echo '<button type="submit">Confirm Appointment</button>';

        echo '</form>';
        echo '<form action="admin_reject_appointment.php" method="POST">';

        echo '<input type="hidden" name="appointment_id" value="' . (int) $appointment['appointment_id'] . '">';

        echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';

        echo '<button type="submit">Reject Appointment</button>';

        echo '</form>';
    }
}
