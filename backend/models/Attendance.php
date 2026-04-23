<?php
/**
 * ============================================================
 * Attendance Model
 * ============================================================
 * Database operations for attendance records
 */

class Attendance
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Get attendance record by ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("
            SELECT a.*, s.first_name, s.last_name, s.email, s.student_number, 
                   e.name as event_name, e.date as event_date
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN events e ON a.event_id = e.id
            WHERE a.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get attendance for student and event
     */
    public function getByStudentEvent($studentId, $eventId)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE student_id = ? AND event_id = ?
        ");
        $stmt->execute([$studentId, $eventId]);
        return $stmt->fetch();
    }

    /**
     * Get all attendance records for event
     */
    public function getByEvent($eventId, $limit = null, $offset = 0)
    {
        $sql = "
            SELECT a.*, s.first_name, s.last_name, s.student_number, s.email, s.course, s.year_level
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            WHERE a.event_id = ?
            ORDER BY a.scanned_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$eventId, $limit, $offset]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$eventId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get all attendance records for student
     */
    public function getByStudent($studentId, $limit = null, $offset = 0)
    {
        $sql = "
            SELECT a.*, e.name as event_name, e.date as event_date, e.location
            FROM attendance a
            JOIN events e ON a.event_id = e.id
            WHERE a.student_id = ?
            ORDER BY a.scanned_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$studentId, $limit, $offset]);
        } else {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$studentId]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get all attendance records
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "
            SELECT a.*, s.first_name, s.last_name, s.student_number, e.name as event_name
            FROM attendance a
            JOIN students s ON a.student_id = s.id
            JOIN events e ON a.event_id = e.id
            ORDER BY a.scanned_at DESC
        ";
        
        if ($limit) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get attendance count
     */
    public function getCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM attendance");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get attendance count for event
     */
    public function getCountByEvent($eventId)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM attendance WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get unique student count for event
     */
    public function getUniqueStudentCount($eventId = null)
    {
        $sql = "SELECT COUNT(DISTINCT student_id) as total FROM attendance";
        
        if ($eventId) {
            $sql .= " WHERE event_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$eventId]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Record attendance
     */
    public function record($studentId, $eventId)
    {
        // Check if already recorded
        if ($this->exists($studentId, $eventId)) {
            return null; // Already exists
        }
        
        $id = generateUUID();
        $stmt = $this->db->prepare("
            INSERT INTO attendance (id, student_id, event_id)
            VALUES (?, ?, ?)
        ");
        
        $stmt->execute([$id, $studentId, $eventId]);
        return $id;
    }

    /**
     * Check if attendance already recorded
     */
    public function exists($studentId, $eventId)
    {
        $stmt = $this->db->prepare("
            SELECT id FROM attendance 
            WHERE student_id = ? AND event_id = ?
        ");
        $stmt->execute([$studentId, $eventId]);
        return $stmt->fetch() !== false;
    }

    /**
     * Update attendance record (e.g., scanned_out)
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowed = ['scanned_out_at'];
        
        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $params[] = $id;
        $sql = "UPDATE attendance SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete attendance record
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM attendance WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics()
    {
        $stats = [];
        
        // Total attendance
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM attendance");
        $stats['total_scans'] = $stmt->fetch()['total'] ?? 0;
        
        // Unique students
        $stmt = $this->db->query("SELECT COUNT(DISTINCT student_id) as total FROM attendance");
        $stats['unique_students'] = $stmt->fetch()['total'] ?? 0;
        
        // Unique events
        $stmt = $this->db->query("SELECT COUNT(DISTINCT event_id) as total FROM attendance");
        $stats['unique_events'] = $stmt->fetch()['total'] ?? 0;
        
        return $stats;
    }

    /**
     * Get attendance summary by event
     */
    public function getSummaryByEvent()
    {
        $stmt = $this->db->query("
            SELECT 
                e.id, e.name, e.date,
                COUNT(DISTINCT a.student_id) as attendees,
                COUNT(a.id) as total_scans
            FROM events e
            LEFT JOIN attendance a ON e.id = a.event_id
            GROUP BY e.id, e.name, e.date
            ORDER BY e.date DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get attendance summary by student
     */
    public function getSummaryByStudent()
    {
        $stmt = $this->db->query("
            SELECT 
                s.id, s.first_name, s.last_name, s.student_number,
                COUNT(a.id) as event_count
            FROM students s
            LEFT JOIN attendance a ON s.id = a.student_id
            GROUP BY s.id, s.first_name, s.last_name, s.student_number
            ORDER BY s.first_name, s.last_name
        ");
        return $stmt->fetchAll();
    }
}
