<?php
/**
 * ============================================================
 * Event Model
 * ============================================================
 * Database operations for event records
 */

class Event
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database->getConnection();
    }

    /**
     * Get event by ID
     */
    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM events WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get all active events
     */
    public function getActive($orderBy = 'date', $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM events WHERE archived = 0 ORDER BY $orderBy";
        
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
     * Get all archived events
     */
    public function getArchived($orderBy = 'date DESC', $limit = null, $offset = 0)
    {
        $sql = "SELECT * FROM events WHERE archived = 1 ORDER BY $orderBy";
        
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
     * Get all events (active and archived)
     */
    public function getAll($archived = null)
    {
        if ($archived === null) {
            $stmt = $this->db->query("SELECT * FROM events ORDER BY date DESC");
        } else {
            $stmt = $this->db->prepare("SELECT * FROM events WHERE archived = ? ORDER BY date DESC");
            $stmt->execute([$archived ? 1 : 0]);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get event count
     */
    public function getCount($archivedOnly = false)
    {
        $sql = $archivedOnly ? 
            "SELECT COUNT(*) as total FROM events WHERE archived = 1" :
            "SELECT COUNT(*) as total FROM events WHERE archived = 0";
        
        $stmt = $this->db->query($sql);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Create new event
     */
    public function create($data)
    {
        $id = $data['id'] ?? generateUUID();
        
        $stmt = $this->db->prepare("
            INSERT INTO events 
            (id, name, date, location, description, archived) 
            VALUES (?, ?, ?, ?, ?, 0)
        ");
        
        $stmt->execute([
            $id,
            $data['name'],
            $data['date'],
            $data['location'],
            $data['description'] ?? null,
        ]);
        
        return $id;
    }

    /**
     * Update event
     */
    public function update($id, $data)
    {
        $fields = [];
        $params = [];
        
        $allowed = ['name', 'date', 'location', 'description'];
        
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
        $sql = "UPDATE events SET " . implode(', ', $fields) . " WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Archive event
     */
    public function archive($id)
    {
        $stmt = $this->db->prepare("UPDATE events SET archived = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Unarchive event
     */
    public function unarchive($id)
    {
        $stmt = $this->db->prepare("UPDATE events SET archived = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Delete event (permanently)
     */
    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Search events
     */
    public function search($query, $archivedOnly = false)
    {
        $search = '%' . $query . '%';
        $sql = "
            SELECT * FROM events 
            WHERE (name LIKE ? OR location LIKE ? OR date LIKE ?)";
        
        if (!$archivedOnly) {
            $sql .= " AND archived = 0";
        }
        
        $sql .= " ORDER BY date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$search, $search, $search]);
        return $stmt->fetchAll();
    }

    /**
     * Get events in date range
     */
    public function getByDateRange($startDate, $endDate, $archivedOnly = false)
    {
        $sql = "SELECT * FROM events WHERE date BETWEEN ? AND ?";
        
        if (!$archivedOnly) {
            $sql .= " AND archived = 0";
        }
        
        $sql .= " ORDER BY date";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$startDate, $endDate]);
        return $stmt->fetchAll();
    }

    /**
     * Get upcoming events (after today)
     */
    public function getUpcoming($limit = null)
    {
        $sql = "SELECT * FROM events WHERE date >= CURDATE() AND archived = 0 ORDER BY date";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll();
    }

    /**
     * Get past events (before today)
     */
    public function getPast($limit = null)
    {
        $sql = "SELECT * FROM events WHERE date < CURDATE() AND archived = 0 ORDER BY date DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit]);
        } else {
            $stmt = $this->db->query($sql);
        }
        return $stmt->fetchAll();
    }
}
