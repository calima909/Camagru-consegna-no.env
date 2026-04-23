<?php

require_once __DIR__ . '/../core/model.php';

class Comment extends Model
{
    public function create(int $imageId, int $userId, string $content): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO comments (image_id, user_id, content, created_at)
            VALUES (?, ?, ?, NOW())
        ");
        $stmt->execute([$imageId, $userId, $content]);
    }

    public function getByImage(int $imageId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.username 
            FROM comments c
            JOIN users u ON u.id = c.user_id
            WHERE c.image_id = ?
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$imageId]);
        return $stmt->fetchAll();
    }
}