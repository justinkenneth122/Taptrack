<?php
/**
 * ============================================================
 * Authentication Controller
 * ============================================================
 * Handles login, registration, and authentication logic
 */

class AuthController
{
    private $student;
    private $adminUser;
    private $adminPass;
    private $emailPattern;

    public function __construct($studentModel, $adminUser, $adminPass, $emailPattern)
    {
        $this->student = $studentModel;
        $this->adminUser = $adminUser;
        $this->adminPass = $adminPass;
        $this->emailPattern = $emailPattern;
    }

    /**
     * Handle student login
     */
    public function loginStudent($email, $password)
    {
        $email = trim($email);
        
        // Validate email format
        if (!preg_match($this->emailPattern, $email)) {
            return [
                'success' => false,
                'message' => 'Invalid email format. Use R[Number]@feuroosevelt.edu.ph'
            ];
        }

        // Find student
        $student = $this->student->getByEmail($email);
        if (!$student) {
            return [
                'success' => false,
                'message' => 'No account found. Please register first.'
            ];
        }

        // Verify password
        if ($student['password'] !== $password) {
            return [
                'success' => false,
                'message' => 'Wrong password.'
            ];
        }

        // Set session
        $_SESSION['user_id'] = $student['id'];
        $_SESSION['user_role'] = 'student';
        $_SESSION['user_name'] = $student['first_name'] . ' ' . $student['last_name'];

        return [
            'success' => true,
            'message' => 'Login successful',
            'redirect' => '?page=student'
        ];
    }

    /**
     * Handle student registration
     */
    public function registerStudent($email, $password, $firstName, $lastName, $course, $yearLevel)
    {
        $email = trim($email);
        $firstName = trim($firstName);
        $lastName = trim($lastName);

        // Validate email format
        if (!preg_match($this->emailPattern, $email)) {
            return [
                'success' => false,
                'message' => 'Invalid email format.'
            ];
        }

        // Check required fields
        if (!$firstName || !$lastName || !$course || !$yearLevel || !$password) {
            return [
                'success' => false,
                'message' => 'Please fill in all fields.'
            ];
        }

        // Check if email already exists
        if ($this->student->emailExists($email)) {
            return [
                'success' => false,
                'message' => 'Email already registered. Please log in.'
            ];
        }

        // Create student
        $studentId = $this->student->create([
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'student_number' => extractStudentNumber($email),
            'course' => $course,
            'year_level' => $yearLevel,
            'password' => $password,
        ]);
    }

    /**
     * Handle admin login
     */
    public function loginAdmin($username, $password)
    {
        if ($username === $this->adminUser && $password === $this->adminPass) {
            $_SESSION['user_id'] = 'admin-1';
            $_SESSION['user_role'] = 'admin';
            $_SESSION['user_name'] = 'Admin';

            return [
                'success' => true,
                'message' => 'Admin login successful',
                'redirect' => '?page=admin'
            ];
        }

        return [
            'success' => false,
            'message' => 'Invalid credentials. Use admin / admin123'
        ];
    }

    /**
     * Handle logout
     */
    public function logout()
    {
        session_destroy();
        return [
            'success' => true,
            'message' => 'Logged out successfully',
            'redirect' => '?page=login'
        ];
    }



       public function completeRegistration($studentId) 
    {
        // Set session and redirect
        $_SESSION['user_id'] = $studentId;
        $_SESSION['user_role'] = 'student';
        unset($_SESSION['face_reg_student_id']);

        return [
            'success' => true,
            'message' => 'Face registration complete',
            'redirect' => '?page=student'
        ];
    } // This brace closes the function
}
