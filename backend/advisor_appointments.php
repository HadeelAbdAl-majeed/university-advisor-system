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

    $appointments = $appointmentModel->getByAdvisorId($advisorId);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Advisor Appointments</title>
</head>

<body>

    <h1>My Student Appointments</h1>

    <?php if (empty($appointments)): ?>

        <p>No appointments found.</p>

    <?php else: ?>

        <table border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Note</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($appointments as $appointment): ?>

                    <tr>
                        <td><?= (int) $appointment['id'] ?></td>

                        <td>
                            <?= htmlspecialchars($appointment['student_name'], ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td><?= htmlspecialchars($appointment['slot_date'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($appointment['start_time'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td><?= htmlspecialchars($appointment['end_time'], ENT_QUOTES, 'UTF-8') ?></td>

                        <td>
                            <?= htmlspecialchars($appointment['note'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </td>

                        <td>
                            <?= htmlspecialchars($appointment['status'], ENT_QUOTES, 'UTF-8') ?>
                        </td>
                    </tr>

                <?php endforeach; ?>

            </tbody>
        </table>

    <?php endif; ?>

</body>
</html>

<?php
}