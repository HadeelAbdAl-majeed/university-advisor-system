<?php

session_start();

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
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

    $userModel = new User($pdo);
    $timeSlotModel = new TimeSlot($pdo);

    $advisors = $userModel->getActiveAdvisors();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $advisorId = filter_var(
            $_POST['advisor_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        $date = $_POST['slot_date'] ?? '';
        $startTime = $_POST['start_time'] ?? '';
        $endTime = $_POST['end_time'] ?? '';

        $capacity = filter_var(
            $_POST['capacity'] ?? null,
            FILTER_VALIDATE_INT
        );

        $csrfValid = isset($_POST['csrf_token'])
            && is_string($_POST['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);

        $validAdvisor = false;

        foreach ($advisors as $advisor) {
            if ((int) $advisor['id'] === $advisorId) {
                $validAdvisor = true;
                break;
            }
        }

        $dateValid = is_string($date)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
            && DateTimeImmutable::createFromFormat('!Y-m-d', $date)?->format('Y-m-d') === $date;

        $startValid = is_string($startTime)
            && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $startTime);

        $endValid = is_string($endTime)
            && preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $endTime);

        if (!$csrfValid) {
            $message = 'Invalid CSRF token.';
        } elseif (
            !$validAdvisor ||
            !$dateValid ||
            !$startValid ||
            !$endValid ||
            $capacity === false ||
            $capacity === null ||
            $capacity < 1
        ) {
            $message = 'Invalid slot data.';
            
        } elseif (
            strtotime("$date $startTime") <= time() ||
            $endTime <= $startTime
        ) {
            $message = 'Please select a valid future time slot.';
        } else {

            try {
                $success = $timeSlotModel->createSlot(
                    $advisorId,
                    $date,
                    $startTime,
                    $endTime,
                    $capacity
                );

                $message = $success
                    ? 'Time slot created successfully.'
                    : 'Could not create time slot.';
            } catch (PDOException $e) {
                $message = $e->getCode() === '23000'
                    ? 'This time slot already exists or has invalid references.'
                    : 'Database error while creating time slot.';
            }
        }
    }

?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Create Time Slot</title>
    </head>

    <body>

        <h1>Create Time Slot</h1>

        <?php if ($message !== ''): ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form action="admin_create_slot.php" method="POST">

            <input
                type="hidden"
                name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">

            <label for="advisor_id">Advisor:</label>
            <select name="advisor_id" id="advisor_id" required>
                <option value="">Select Advisor</option>

                <?php foreach ($advisors as $advisor): ?>
                    <option value="<?= (int) $advisor['id'] ?>">
                        <?= htmlspecialchars($advisor['name'], ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <br><br>

            <label for="slot_date">Date:</label>
            <input
                type="date"
                name="slot_date"
                id="slot_date"
                min="<?= date('Y-m-d') ?>"
                required>

            <br><br>

            <label for="start_time">Start Time:</label>
            <input
                type="time"
                name="start_time"
                id="start_time"
                required>

            <br><br>

            <label for="end_time">End Time:</label>
            <input
                type="time"
                name="end_time"
                id="end_time"
                required>

            <br><br>

            <label for="capacity">Capacity:</label>
            <input
                type="number"
                name="capacity"
                id="capacity"
                min="1"
                required>

            <br><br>

            <button type="submit">Create Time Slot</button>

        </form>

    </body>

    </html>

<?php
}
