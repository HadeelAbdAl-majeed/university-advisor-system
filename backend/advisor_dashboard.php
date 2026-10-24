<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/Appointment.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? null) !== 'advisor'
) {
    http_response_code(403);
    echo 'Access denied.';
} else {

    $database = new Database();
    $pdo = $database->connect();

    $appointmentModel = new Appointment($pdo);

    $advisorId = (int) $_SESSION['user_id'];

    $stats = $appointmentModel->getAdvisorAppointmentStats($advisorId);

    $total = (int) ($stats['total'] ?? 0);
    $pending = (int) ($stats['pending'] ?? 0);
    $confirmed = (int) ($stats['confirmed'] ?? 0);
    $rejected = (int) ($stats['rejected'] ?? 0);
    $cancelled = (int) ($stats['cancelled'] ?? 0);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisor Dashboard</title>
</head>

<body>

    <h1>Advisor Dashboard</h1>

    <h2>Appointment Statistics</h2>

    <p>Total Appointments: <?= $total ?></p>
    <p>Pending Appointments: <?= $pending ?></p>
    <p>Confirmed Appointments: <?= $confirmed ?></p>
    <p>Rejected Appointments: <?= $rejected ?></p>
    <p>Cancelled Appointments: <?= $cancelled ?></p>

    <hr>

    <a href="advisor_appointments.php">View My Appointments</a>

</body>
</html>

<?php
}