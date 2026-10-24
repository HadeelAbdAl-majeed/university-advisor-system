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

    $stats = $appointmentModel->getAppointmentStats();
?>

    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <title>Admin Dashboard</title>
    </head>

    <body>

        <h1>Admin Dashboard</h1>

        <h3>Total Appointments: <?= (int) ($stats['total'] ?? 0) ?></h3>
        <h3>Pending: <?= (int) ($stats['pending'] ?? 0) ?></h3>
        <h3>Confirmed: <?= (int) ($stats['confirmed'] ?? 0) ?></h3>
        <h3>Rejected: <?= (int) ($stats['rejected'] ?? 0) ?></h3>
        <h3>Cancelled: <?= (int) ($stats['cancelled'] ?? 0) ?></h3>
        <h2>Appointments Overview</h2>

        <div style="max-width: 400px;">
            <canvas id="appointmentsChart"></canvas>
        </div>

        <script>
            const ctx = document.getElementById('appointmentsChart');

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pending', 'Confirmed', 'Rejected', 'Cancelled'],
                    datasets: [{
                        data: [
                            <?= (int) ($stats['pending'] ?? 0) ?>,
                            <?= (int) ($stats['confirmed'] ?? 0) ?>,
                            <?= (int) ($stats['rejected'] ?? 0) ?>,
                            <?= (int) ($stats['cancelled'] ?? 0) ?>
                        ],
                        backgroundColor: [
                            '#f59e0b',
                            '#10b981',
                            '#ef4444',
                            '#64748b'
                        ]
                    }]
                }
            });
        </script>

    </body>

    </html>

<?php
}

    
// }

// 
