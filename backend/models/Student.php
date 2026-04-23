<?php
/**
 * ============================================================
 * Student Model
 * ============================================================
 * Database operations for student records
 */

class Student
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Get student by ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get student by email
     */
    public function getByEmail($email)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    /**
     * Get student by student number
     */
    public function getByStudentNumber($studentNumber)
    {
        $stmt = $this->db->prepare("SELECT * FROM students WHERE student_number = ?");
        $stmt->execute([$studentNumber]);
        return $stmt->fetch();
    }

    /**
     * Get all students
     */
    public function getAll($limit = null, $offset = 0)
    {
        $sql = "SELECT id, first_name, last_name, email, student_number, course, year_level, created_at 
                FROM students 
                ORDER BY created_at DESC";
        
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
     * Get student count
     */
    public function getCount()
    {
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM students");
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Create new student
     */
    public function create($data)
    {
        $id = $data['id'] ?? generateUUID();
        
        $stmt = $this->db->prepare("
            INSERT INTO students 
            (id, email, first_name, last_name, student_number, course, year_level, password) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $id,
            $data['email'],
            $data['first_name'],
            $data['last_name'],
            $data['student_number'] ?? extractStudentNumber($data['email']),
            $data['course'],
            $data['year_level'] ?? '1st Year',
            $data['password'] ?? '',
        ]);
        
        return $id;
    }

    /**
     * Update student
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowed = ['email', 'first_name', 'last_name', 'course', 'year_level', 'password'];
        
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
        $sql = "UPDATE students SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Delete student
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$id]);
    }


    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT id FROM students WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Check if student number exists
     */
    public function studentNumberExists($studentNumber, $excludeId = null)
    {
        $sql = "SELECT id FROM students WHERE student_number = ?";
        $params = [$studentNumber];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }

    /**
     * Search students
     */
    public function search($query, $limit = 20)
    {
        $search = '%' . $query . '%';
        $stmt = $this->db->prepare("
            SELECT id, first_name, last_name, email, student_number, course 
            FROM students 
            WHERE 
                first_name LIKE ? OR 
                last_name LIKE ? OR 
                email LIKE ? OR 
                student_number LIKE ?
            ORDER BY first_name, last_name
            LIMIT ?
        ");
        $stmt->execute([$search, $search, $search, $search, $limit]);
        return $stmt->fetchAll();
    }
}
