<?php
/**
 * ============================================================
 * Event Controller
 * ============================================================
 * Handles event management operations
 */

class EventController
{
    private $event;

    public function __construct($eventModel)
    {
        $this->event = $eventModel;
    }

    /**
     * Get all active events
     */
    public function getActive()
    {
        return $this->event->getActive();
    }

    /**
     * Get all archived events
     */
    public function getArchived()
    {
        return $this->event->getArchived();
    }

    /**
     * Get single event
     */
    public function getById($id)
    {
        return $this->event->getById($id);
    }

    /**
     * Create event
     */
    public function create($name, $date, $location, $description = null)
    {
        // Validate required fields
        if (!$name || !$date || !$location) {
            return [
                'success' => false,
                'message' => 'Please fill in name, date, and location.'
            ];
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return [
                'success' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD.'
            ];
        }

        try {
            $eventId = $this->event->create([
                'name' => $name,
                'date' => $date,
                'location' => $location,
                'description' => $description,
            ]);

            return [
                'success' => true,
                'message' => "Event \"$name\" created successfully.",
                'event_id' => $eventId
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error creating event: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Update event
     */
    public function update($id, $data)
    {
        $event = $this->event->getById($id);
        if (!$event) {
            return [
                'success' => false,
                'message' => 'Event not found.'
            ];
        }

        // Validate date if provided
        if (isset($data['date']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
            return [
                'success' => false,
                'message' => 'Invalid date format. Use YYYY-MM-DD.'
            ];
        }

        try {
            $this->event->update($id, $data);
            return [
                'success' => true,
                'message' => 'Event updated successfully.'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error updating event: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Archive event
     */
    public function archive($id)
    {
        $event = $this->event->getById($id);
        if (!$event) {
            return [
                'success' => false,
                'message' => 'Event not found.'
            ];
        }

        try {
            $this->event->archive($id);
            return [
                'success' => true,
                'message' => "Event \"" . $event['name'] . "\" archived successfully."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error archiving event: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete event permanently
     */
    public function delete($id)
    {
        $event = $this->event->getById($id);
        if (!$event) {
            return [
                'success' => false,
                'message' => 'Event not found.'
            ];
        }

        try {
            $this->event->delete($id);
            return [
                'success' => true,
                'message' => "Event \"" . $event['name'] . "\" deleted permanently."
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error deleting event: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Search events
     */
    public function search($query)
    {
        return $this->event->search($query);
    }

    /**
     * Get event count (active only)
     */
    public function getCount()
    {
        return $this->event->getCount(false);
    }

    /**
     * Get upcoming events
     */
    public function getUpcoming()
    {
        return $this->event->getUpcoming();
    }
}
