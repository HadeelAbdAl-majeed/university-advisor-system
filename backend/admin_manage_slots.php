<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/TimeSlot.php';

if (
    !isset($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? null) !== 'admin'
) {
    http_response_code(403);
    echo 'Access denied.';
} else {

    $database = new Database();
    $pdo = $database->connect();

    $timeSlotModel = new TimeSlot($pdo);
    $slots = $timeSlotModel->getAllSlots();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Time Slots</title>
</head>

<body>

    <h1>Manage Time Slots</h1>

    <a href="admin_create_slot.php">Create New Time Slot</a>

    <br><br>

    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>ID</th>
                <th>Advisor</th>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Capacity</th>
                <th>Booked</th>
                <th>Remaining</th>
            </tr>
        </thead>

        <tbody>

            <?php foreach ($slots as $slot): ?>

                <tr>
                    <td><?= (int) $slot['id'] ?></td>

                    <td>
                        <?= htmlspecialchars($slot['advisor_name'], ENT_QUOTES, 'UTF-8') ?>
                    </td>

                    <td><?= htmlspecialchars($slot['slot_date'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($slot['start_time'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= htmlspecialchars($slot['end_time'], ENT_QUOTES, 'UTF-8') ?></td>

                    <td><?= (int) $slot['capacity'] ?></td>

                    <td><?= (int) $slot['booked_count'] ?></td>

                    <td>
                        <?= (int) $slot['capacity'] - (int) $slot['booked_count'] ?>
                    </td>
                </tr>

            <?php endforeach; ?>

        </tbody>
    </table>

</body>
</html>

<?php
}