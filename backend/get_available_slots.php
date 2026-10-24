<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/TimeSlot.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? null) !== 'student') {
   header('Location: login.php');
} else {

   $database = new Database();
   $pdo = $database->connect();
   $timeSlotModel = new TimeSlot($pdo);

   $slots = $timeSlotModel->getAvailableSlots();

   foreach ($slots as $slot) {
      echo htmlspecialchars($slot['slot_date']) . '<br>';
      echo 'Start Time: ' . htmlspecialchars($slot['start_time']) . '<br>';
      echo 'End Time: ' . htmlspecialchars($slot['end_time']) . '<br> ';
      echo 'Remaining Capacity: ' . htmlspecialchars((string) $slot['remaining_capacity']) . '<br>';
      echo '<form action="book_appointment.php" method="POST">';
      echo '<input type="hidden" name="slot_id" value="' . (int) $slot['id'] . '">';
      echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
      echo '<button type="submit">Book Appointment</button>';
      echo '</form>';
      echo '<hr>';
      
   }
}
