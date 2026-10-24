<?php

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Appointment.php';

if (!isset($_SESSION['user_id'])) {
    exit('Please log in first.');
}
if (($_SESSION['role'] ?? null) !== 'student') {
    exit('Access denied.');
}

try {
    $database = new Database();
    $pdo = $database->connect();
    $appointmentModel = new Appointment($pdo);

    $appointments = $appointmentModel->getByStudentId((int)$_SESSION['user_id']);

    foreach ($appointments as $appointment) {


        echo "<hr>";

        echo "<p>Student: " .
            htmlspecialchars($appointment['student_name']) .
            "</p>";

        echo "<p>Advisor: " .
            htmlspecialchars($appointment['advisor_name']) .
            "</p>";

        echo "<p>Date: " .
            htmlspecialchars($appointment['slot_date']) .
            "</p>";

        echo "<p>Start Time: " .
            htmlspecialchars($appointment['start_time']) .
            "</p>";

        echo "<p>End Time: " .
            htmlspecialchars($appointment['end_time']) .
            "</p>";

        echo "<p>Status: " .
            htmlspecialchars($appointment['status']) .
            "</p>";

        echo "<p>Note: " .
            htmlspecialchars($appointment['note'] ?? '') .
            "</p>";

        $slotDateTime = $appointment['slot_date'] . ' ' . $appointment['start_time'];
        $isFuture = strtotime($slotDateTime) > time();

        if (in_array($appointment['status'], ['pending', 'confirmed'], true)  && $isFuture) {
            echo '<form action="cancel_appointment.php" method="POST">';

            echo '<input type="hidden" name="appointment_id" value="' .
                (int) $appointment['appointment_id'] .
                '">';

            echo '<input type="hidden" name="csrf_token" value="' .
                htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') .
                '">';

            echo '<button type="submit">Cancel Appointment</button>';

            echo '</form>';
        }
    }
} catch (PDOException $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo "Unable to load appointments.";
}
