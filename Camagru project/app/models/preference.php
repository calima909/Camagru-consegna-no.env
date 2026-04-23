<?php

require_once __DIR__ . '/../core/model.php';

class Preference extends Model
{
    public function notifyComments(int $userId): bool
    {
        $stmt = $this->db->prepare(
            "SELECT notify_comments FROM preferences WHERE user_id = ?"
        );
        $stmt->execute([$userId]);
        $row = $stmt->fetch();
        return $row ? (bool)$row['notify_comments'] : true;
    }

    public function setNotifyComments(int $userId, bool $value): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO preferences (user_id, notify_comments)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE notify_comments = VALUES(notify_comments)"
        );
        $stmt->execute([$userId, $value ? 1 : 0]);
    }
}