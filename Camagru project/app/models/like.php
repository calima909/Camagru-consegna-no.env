<?php

require_once __DIR__ . '/../core/model.php';

class Like extends Model
{
    public function add(int $imageId, int $userId): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO likes (image_id, user_id)
            VALUES (?, ?)
        ");

        $stmt->execute([$imageId, $userId]);
    }

    public function remove(int $imageId, int $userId): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM likes
            WHERE image_id = ? AND user_id = ?
        ");

        $stmt->execute([$imageId, $userId]);
    }

    public function hasLiked(int $imageId, int $userId): bool
    {
        $stmt = $this->db->prepare("
            SELECT id FROM likes
            WHERE image_id = ? AND user_id = ?
        ");

        $stmt->execute([$imageId, $userId]);

        return (bool) $stmt->fetch();
    }

    public function countByImage(int $imageId): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) AS total
            FROM likes
            WHERE image_id = ?
        ");

        $stmt->execute([$imageId]);
        $result = $stmt->fetch();

        return (int) $result['total'];
    }

    public function toggle(int $imageId, int $userId): bool
    {
        if ($this->hasLiked($imageId, $userId)) {
            $this->remove($imageId, $userId);
            return false;
        } else {
            $this->add($imageId, $userId);
            return true;
        }
    }
}