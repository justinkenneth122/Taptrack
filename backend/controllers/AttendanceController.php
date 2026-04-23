<?php
/**
 * ============================================================
 * Attendance Controller
 * ============================================================
 * Handles attendance recording and management
 */

class AttendanceController
{
    private $attendance;
    private $student;
    private $event;

    public function __construct($attendanceModel, $studentModel, $eventModel)
    {
        $this->attendance = $attendanceModel;
        $this->student = $studentModel;
        $this->event = $eventModel;
    }

    /**
     * Record attendance from QR code
     */
    public function recordFromQR($data)
    {
        $studentId = $data['studentId'] ?? '';
        $eventId = $data['eventId'] ?? '';
        $system = $data['system'] ?? '';

        // Validate system identifier
        if ($system !== 'taptrack') {
            return [
                'success' => false,
                'message' => 'Invalid QR code — not a Taptrack code.'
            ];
        }

        // Validate student exists
        $student = $this->student->getById($studentId);
        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found.'
            ];
        }

        // Validate event exists
        $event = $this->event->getById($eventId);
        if (!$event) {
            return [
                'success' => false,
                'message' => 'Event not found.'
            ];
        }

        // Check if already recorded
        if ($this->attendance->exists($studentId, $eventId)) {
            return [
                'success' => false,
                'message' => $student['first_name'] . ' ' . $student['last_name'] . ' — Already recorded.'
            ];
        }

        // Record attendance
        $this->attendance->record($studentId, $eventId);

        return [
            'success' => true,
            'message' => '✓ ' . $student['first_name'] . ' ' . $student['last_name'] . ' — Attendance recorded!'
        ];
    }

    /**
     * Record attendance manually
     */
    public function recordManual($studentId, $eventId)
    {
        // Validate student exists
        $student = $this->student->getById($studentId);
        if (!$student) {
            return [
                'success' => false,
                'message' => 'Student not found.'
            ];
        }

        // Validate event exists
        $event = $this->event->getById($eventId);
        if (!$event) {
            return [
                'success' => false,
                'message' => 'Event not found.'
            ];
        }

        // Check if already recorded
        if ($this->attendance->exists($studentId, $eventId)) {
            return [
                'success' => false,
                'message' => 'Attendance already recorded for this student and event.'
            ];
        }

        // Record attendance
        $this->attendance->record($studentId, $eventId);

        return [
            'success' => true,
            'message' => 'Attendance recorded successfully.'
        ];
    }

    /**
     * Get attendance for event
     */
    public function getByEvent($eventId)
    {
        return $this->attendance->getByEvent($eventId);
    }

    /**
     * Get attendance for student
     */
    public function getByStudent($studentId)
    {
        return $this->attendance->getByStudent($studentId);
    }

    /**
     * Get all attendance records
     */
    public function getAll()
    {
        return $this->attendance->getAll();
    }

    /**
     * Get attendance count
     */
    public function getCount()
    {
        return $this->attendance->getCount();
    }

    /**
     * Get count by event
     */
    public function getCountByEvent($eventId)
    {
        return $this->attendance->getCountByEvent($eventId);
    }

    /**
     * Get unique student count
     */
    public function getUniqueStudentCount($eventId = null)
    {
        return $this->attendance->getUniqueStudentCount($eventId);
    }

    /**
     * Get attendance statistics
     */
    public function getStatistics()
    {
        return $this->attendance->getStatistics();
    }

    /**
     * Get summary by event
     */
    public function getSummaryByEvent()
    {
        return $this->attendance->getSummaryByEvent();
    }

    /**
     * Get summary by student
     */
    public function getSummaryByStudent()
    {
        return $this->attendance->getSummaryByStudent();
    }

    /**
     * Delete attendance record
     */
    public function delete($id)
    {
        $record = $this->attendance->getById($id);
        if (!$record) {
            return [
                'success' => false,
                'message' => 'Attendance record not found.'
            ];
        }

        try {
            $this->attendance->delete($id);
            return [
                'success' => true,
                'message' => 'Attendance record deleted.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting record: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get events attended by student
     */
    public function getStudentAttendedEvents($studentId)
    {
        $records = $this->attendance->getByStudent($studentId);
        $eventIds = array_column($records, 'event_id') ?? [];
        return $eventIds;
    }

    /**
     * Get students for event
     */
    public function getEventAttendees($eventId)
    {
        return $this->attendance->getByEvent($eventId);
    }
}
