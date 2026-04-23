<?php

require_once __DIR__ . '/../core/model.php';

class Image extends Model
{
    public function getByUser(int $userId, ?int $currentUserId = null): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                images.*, 
                COUNT(likes.id) AS likes_count,
                COALESCE(SUM(likes.user_id = ?), 0) AS liked_by_user
            FROM images
            LEFT JOIN likes ON likes.image_id = images.id
            WHERE images.user_id = ?
            GROUP BY images.id
            ORDER BY images.created_at DESC
        ");
        $stmt->execute([$currentUserId, $userId]);
        return $stmt->fetchAll();
    }
    
    public function create(int $userId, string $filename): void
    {
        $stmt = $this->db->prepare(
            "INSERT INTO images (user_id, filename) VALUES (?, ?)"
        );
        $stmt->execute([$userId, $filename]);
    }

    public function delete(string $filename): void
    {
        $stmt = $this->db->prepare("DELETE FROM images WHERE filename = ?");
        $stmt->execute([$filename]);
    }

    public function getByFilenameAndUser(string $filename, int $userId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM images WHERE filename = ? AND user_id = ?");
        $stmt->execute([$filename, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT images.*, users.username, users.email
            FROM images
            JOIN users ON images.user_id = users.id
            WHERE images.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        return $image ?: null;
    }

    public function getAll(?int $currentUserId = null, int $limit = 5, int $offset = 0): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                images.*, 
                users.username,
                COUNT(likes.id) AS likes_count,
                SUM(likes.user_id = ?) AS liked_by_user
            FROM images
            JOIN users ON images.user_id = users.id
            LEFT JOIN likes ON likes.image_id = images.id
            GROUP BY images.id
            ORDER BY images.created_at DESC
            LIMIT ? OFFSET ?
        ");

        $stmt->execute([$currentUserId, $limit, $offset]);

        return $stmt->fetchAll();
    }
    
    public function countAll(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) FROM images");
        return (int)$stmt->fetchColumn();
    }
}