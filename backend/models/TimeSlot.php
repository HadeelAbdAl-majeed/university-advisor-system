<?php
class TimeSlot
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAvailableSlots(): array
    {
        $sql = "SELECT ts.*,
        COUNT(CASE
            WHEN a.status IN ('pending', 'confirmed') THEN 1
        END) AS booked_count,
        ts.capacity - COUNT(CASE
            WHEN a.status IN ('pending', 'confirmed') THEN 1
        END) AS remaining_capacity
        FROM time_slots AS ts
        LEFT JOIN appointments AS a
            ON a.slot_id = ts.id
        WHERE TIMESTAMP(ts.slot_date, ts.start_time) > NOW()
        GROUP BY ts.id
        HAVING remaining_capacity > 0
        ORDER BY ts.slot_date ASC, ts.start_time ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function createSlot(int $advisorId, string $date, string $startTime, string $endTime, int $capacity): bool
    {
        $sql = "INSERT INTO time_slots
            (advisor_id, slot_date, start_time, end_time, capacity)
            VALUES
            (:advisor_id, :slot_date, :start_time, :end_time, :capacity)";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            'advisor_id' => $advisorId,
            'slot_date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'capacity' => $capacity
        ]);
    }

    public function getAllSlots(): array
    {
        $sql = "
        SELECT
            ts.id,
            u.name AS advisor_name,
            ts.slot_date,
            ts.start_time,
            ts.end_time,
            ts.capacity,
            COUNT(a.id) AS booked_count
        FROM time_slots ts
        INNER JOIN users u
            ON ts.advisor_id = u.id
        LEFT JOIN appointments a
            ON a.slot_id = ts.id
            AND a.status IN ('pending', 'confirmed')
        GROUP BY
            ts.id,
            u.name,
            ts.slot_date,
            ts.start_time,
            ts.end_time,
            ts.capacity
        ORDER BY
            ts.slot_date DESC,
            ts.start_time ASC
    ";

        $stmt = $this->pdo->query($sql);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
