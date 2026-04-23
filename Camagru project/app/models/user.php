<?php

require_once __DIR__ . '/../core/model.php';

class User extends Model
{
    public function getAll(): array {
        $stmt = $this->db->query("SELECT id, username, email FROM users");
        return $stmt->fetchAll();
    }
    
    public function create(string $username, string $email, string $password, $token): void {
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password, token) VALUES (?, ?, ?, ?)"
        );
    
        $stmt->execute([$username, $email, $password, $token]);
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );

        $stmt->execute([$email]);
        $user = $stmt->fetch();

        return $user ?: null;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, username, email FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function updateWithoutPassword(int $id, string $username, string $email): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users
                SET username = ?,
                    email = ?
                WHERE id = ?"
        );

        $stmt->execute([$username, $email, $id]);
    }

    public function updateWithPassword(int $id, string $username, string $email, string $password): void
    {
        $stmt = $this->db->prepare(
            "UPDATE users
                SET username = ?,
                    email = ?, 
                password = ? WHERE id = ?"
        );

        $stmt->execute([$username, $email, $password, $id]);
    }

    public function getByToken($token)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE token = ?"
        );

        $stmt->execute([$token]);

        return $stmt->fetch();
    }

    public function verifyEmail($token)
    {
        $stmt = $this->db->prepare(
            "UPDATE users
            SET email_verified = 1,
                token = NULL
            WHERE token = ?"
        );

        $stmt->execute([$token]);
    }

    public function setResetToken(string $email, string $token, string $expires)
    {
        $stmt = $this->db->prepare(
            "UPDATE users
            SET reset_token = ?, reset_expires = ?
            WHERE email = ?"
        );

        $stmt->execute([$token, $expires, $email]);
    }    

    public function findByResetToken($token)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE reset_token = ?"
        );

        $stmt->execute([$token]);
        return $stmt->fetch();
    }

    public function updatePasswordWithToken($hash, $token)
    {
        $stmt = $this->db->prepare(
            "UPDATE users
            SET password = ?, reset_token = NULL, reset_expires = NULL
            WHERE reset_token = ?"
        );

        $stmt->execute([$hash, $token]);
    }

    public function findByUsername(string $username)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM users WHERE username = ?"
        );

        $stmt->execute([$username]);
        return $stmt->fetch();
    }
}

