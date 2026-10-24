<?php

class Appointment
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getByStudentId(int $studentId): array
    {
        $sql = '
     SELECT
        appointments.id AS appointment_id,
        students.name AS student_name,
        advisors.name AS advisor_name,
        time_slots.slot_date,
        time_slots.start_time,
        time_slots.end_time,
        appointments.note,
        appointments.status

    FROM appointments

    INNER JOIN users AS students
        ON appointments.student_id = students.id

    INNER JOIN time_slots
        ON appointments.slot_id = time_slots.id

    INNER JOIN users AS advisors
        ON time_slots.advisor_id = advisors.id

    WHERE appointments.student_id = :student_id

    ORDER BY
        time_slots.slot_date ASC,
        time_slots.start_time ASC
    ';

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'student_id' => $studentId
        ]);

        return $stmt->fetchAll();
    }

    public function bookAppointment(int $studentId, int $slotId, ?string $note = null): bool
    {
        try {
            $this->pdo->beginTransaction();
            $sql = 'SELECT * FROM time_slots WHERE id = :slot_id FOR UPDATE';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['slot_id' => $slotId]);

            $slot = $stmt->fetch();
            if (!$slot) {
                $this->pdo->rollBack();
                return false;
            }

            $slotDateTime = $slot['slot_date'] . ' ' . $slot['start_time'];
            if (strtotime($slotDateTime) <= time()) {
                $this->pdo->rollBack();
                return false;
            }

            $countSql = "SELECT COUNT(*) FROM appointments
             WHERE slot_id = :slot_id
             AND status IN ('pending', 'confirmed')";
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute(['slot_id' => $slotId]);

            $bookedCount = (int) $countStmt->fetchColumn();
            if ($bookedCount >= (int) $slot['capacity']) {
                $this->pdo->rollBack();
                return false;
            }

            $duplicateSql = "SELECT COUNT(*) FROM appointments
                 WHERE student_id = :student_id
                 AND slot_id = :slot_id";
            $duplicateStmt = $this->pdo->prepare($duplicateSql);
            $duplicateStmt->execute([
                'student_id' => $studentId,
                'slot_id' => $slotId
            ]);

            $duplicateCount = (int) $duplicateStmt->fetchColumn();
            if ($duplicateCount > 0) {
                $this->pdo->rollBack();
                return false;
            }

            $insertSql = "INSERT INTO appointments (student_id, slot_id, note, status)
              VALUES (:student_id, :slot_id, :note, 'pending')";
            $insertStmt = $this->pdo->prepare($insertSql);
            $insertStmt->execute([
                'student_id' => $studentId,
                'slot_id' => $slotId,
                'note' => $note
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $error) {

            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return false;
        }
    }

    public function cancelAppointment(int $studentId, int $appointmentId): bool
    {
        try {
            $this->pdo->beginTransaction();
            $sql = "SELECT a.*, ts.slot_date, ts.start_time
              FROM appointments AS a
              INNER JOIN time_slots AS ts ON a.slot_id = ts.id
              WHERE a.id = :appointment_id
              AND a.student_id = :student_id
              FOR UPDATE";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'appointment_id' => $appointmentId,
                'student_id' => $studentId
            ]);

            $appointment = $stmt->fetch();
            if (!$appointment) {
                $this->pdo->rollBack();
                return false;
            }

            $slotDateTime = $appointment['slot_date'] . ' ' . $appointment['start_time'];
            if (strtotime($slotDateTime) <= time()) {
                $this->pdo->rollBack();
                return false;
            }

            if (!in_array($appointment['status'], ['pending', 'confirmed'], true)) {
                $this->pdo->rollBack();
                return false;
            }

            $updateSql = "UPDATE appointments
              SET status = 'cancelled'
              WHERE id = :appointment_id
              AND student_id = :student_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->execute([
                'appointment_id' => $appointmentId,
                'student_id' => $studentId
            ]);

            $this->pdo->commit();
            return true;
        } catch (PDOException $error) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            return false;
        }
    }

    public function getPendingAppointments(): array
    {

        $sql = "SELECT
        a.id AS appointment_id,
        students.name AS student_name,
        advisors.name AS advisor_name,
        ts.slot_date,
        ts.start_time,
        ts.end_time,
        a.note,
        a.status

    FROM appointments AS a

    INNER JOIN users AS students
        ON a.student_id = students.id

    INNER JOIN time_slots AS ts
        ON a.slot_id = ts.id

    INNER JOIN users AS advisors
        ON ts.advisor_id = advisors.id

    WHERE a.status = 'pending'

    ORDER BY ts.slot_date ASC, ts.start_time ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function confirmAppointment(int $appointmentId): bool
    {
        $sql = "UPDATE appointments AS a
        INNER JOIN time_slots AS ts ON a.slot_id = ts.id
        SET a.status = 'confirmed'
        WHERE a.id = :appointment_id
        AND a.status = 'pending'
        AND TIMESTAMP(ts.slot_date, ts.start_time) > NOW()";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'appointment_id' => $appointmentId
        ]);
        return $stmt->rowCount() === 1;
    }

    public function rejectAppointment(int $appointmentId): bool
    {
        $sql = "UPDATE appointments AS a
        INNER JOIN time_slots AS ts ON a.slot_id = ts.id
        SET a.status = 'rejected'
        WHERE a.id = :appointment_id
        AND a.status = 'pending'
        AND TIMESTAMP(ts.slot_date, ts.start_time) > NOW()";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'appointment_id' => $appointmentId
        ]);
        return $stmt->rowCount() === 1;
    }

    public function getAllAppointments(): array
    {
        $sql = "SELECT
        a.id AS appointment_id,
        students.name AS student_name,
        advisors.name AS advisor_name,
        ts.slot_date,
        ts.start_time,
        ts.end_time,
        a.note,
        a.status
        FROM appointments AS a
        INNER JOIN users AS students ON a.student_id = students.id
        INNER JOIN time_slots AS ts ON a.slot_id = ts.id
        INNER JOIN users AS advisors ON ts.advisor_id = advisors.id
        ORDER BY ts.slot_date DESC, ts.start_time DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function getAppointmentStats(): array
    {
        $sql = "SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
    FROM appointments";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function getByAdvisorId(int $advisorId): array
    {
        $sql = "
        SELECT
            a.id,
            u.name AS student_name,
            ts.slot_date,
            ts.start_time,
            ts.end_time,
            a.note,
            a.status
        FROM appointments a
        INNER JOIN users u
            ON a.student_id = u.id
        INNER JOIN time_slots ts
            ON a.slot_id = ts.id
        WHERE ts.advisor_id = :advisor_id
        ORDER BY ts.slot_date ASC, ts.start_time ASC
    ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'advisor_id' => $advisorId
        ]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAdvisorAppointmentStats(int $advisorId): array
    {
        $sql = "
        SELECT
            COUNT(*) AS total,
            SUM(CASE WHEN a.status = 'pending' THEN 1 ELSE 0 END) AS pending,
            SUM(CASE WHEN a.status = 'confirmed' THEN 1 ELSE 0 END) AS confirmed,
            SUM(CASE WHEN a.status = 'rejected' THEN 1 ELSE 0 END) AS rejected,
            SUM(CASE WHEN a.status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled
        FROM appointments a
        INNER JOIN time_slots ts
            ON a.slot_id = ts.id
        WHERE ts.advisor_id = :advisor_id
    ";

        $stmt = $this->pdo->prepare($sql);

        $stmt->execute([
            'advisor_id' => $advisorId
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
