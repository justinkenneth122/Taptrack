<?php
/**
 * ============================================================
 * API Routes Handler
 * ============================================================
 * Processes AJAX requests and returns JSON responses
 */

class ApiRouter
{
    private $auth;
    private $event;
    private $attendance;
    private $student;

    public function __construct($authCtrl, $eventCtrl, $attendanceCtrl, $studentModel)
    {
        $this->auth = $authCtrl;
        $this->event = $eventCtrl;
        $this->attendance = $attendanceCtrl;
        $this->student = $studentModel;
    }

    /**
     * Route API request
     */
    public function route($action)
    {
        header('Content-Type: application/json; charset=utf-8');

        try {
            switch ($action) {
                case 'scan_qr':
                    return $this->handleScanQR();
                case 'get_students':
                    return $this->handleGetStudents();
                case 'record_attendance':
                    return $this->handleRecordAttendance();
                default:
                    return $this->error('Unknown API action');
            }
        } catch (Exception $e) {
            return $this->error('API Error: ' . $e->getMessage());
        }
    }

    /**
     * Handle QR code scan
     */
    private function handleScanQR()
    {
        requireAdmin();
        $data = getJsonInput();

        if (!$data) {
            return $this->error('Invalid request data');
        }

        $result = $this->attendance->recordFromQR($data);
        return json_encode($result);
    }

    

    /**
     * Handle get students list
     */
    private function handleGetStudents()
    {
        requireAdmin();
        $students = $this->student->getAll();
        
        return json_encode([
            'success' => true,
            'data' => $students
        ]);
    }


    /**
     * Handle manual attendance recording
     */
    private function handleRecordAttendance()
    {
        requireAdmin();
        $data = getJsonInput();

        if (!$data) {
            $data = [
                'student_id' => $_POST['student_id'] ?? '',
                'event_id' => $_POST['event_id'] ?? ''
            ];
        }

        $result = $this->attendance->recordManual(
            $data['student_id'] ?? '',
            $data['event_id'] ?? ''
        );

        return json_encode($result);
    }

    /**
     * Return error response
     */
    private function error($message)
    {
        return json_encode([
            'success' => false,
            'message' => $message
        ]);
    }
}
