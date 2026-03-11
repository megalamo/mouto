<?php
namespace App\Models;

use PDO;

class Note
{
    public function __construct(private PDO $db, private array $tables)
    {
    }

    public function save(int $postId, int $id, int $x, int $y, int $w, int $h, string $body, string $ip, int $userId): void
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tables['notes']} WHERE id = :id AND post_id = :post_id");
        $stmt->execute([':id' => $id, ':post_id' => $postId]);

        if ($stmt->fetchColumn() > 0) {
            // Backup to history
            $stmt = $this->db->prepare("
                INSERT INTO {$this->tables['notes_history']}(x, y, width, height, body, version, ip, user_id, created_at, updated_at, id, post_id) 
                SELECT x, y, width, height, body, version, ip, user_id, created_at, updated_at, id, post_id 
                FROM {$this->tables['notes']} WHERE id = :id AND post_id = :post_id
            ");
            $stmt->execute([':id' => $id, ':post_id' => $postId]);

            // Update existing
            $stmt = $this->db->prepare("
                UPDATE {$this->tables['notes']} 
                SET x = :x, y = :y, width = :w, height = :h, body = :body, updated_at = CURRENT_TIMESTAMP, user_id = :uid, ip = :ip, version = version + 1 
                WHERE post_id = :post_id AND id = :id
            ");
            $stmt->execute([':x' => $x, ':y' => $y, ':w' => $w, ':h' => $h, ':body' => $body, ':uid' => $userId, ':ip' => $ip, ':post_id' => $postId, ':id' => $id]);
        } else {
            // Insert new
            $stmt = $this->db->prepare("SELECT COALESCE(MAX(id), 0) + 1 FROM {$this->tables['notes']} WHERE post_id = :post_id");
            $stmt->execute([':post_id' => $postId]);
            $nextId = $stmt->fetchColumn();

            $stmt = $this->db->prepare("
                INSERT INTO {$this->tables['notes']}(x, y, width, height, body, post_id, id, ip, user_id, created_at, updated_at) 
                VALUES(:x, :y, :w, :h, :body, :post_id, :nid, :ip, :uid, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([':x' => $x, ':y' => $y, ':w' => $w, ':h' => $h, ':body' => $body, ':post_id' => $postId, ':nid' => $nextId, ':ip' => $ip, ':uid' => $userId]);
        }
    }

    public function delete(int $postId, int $noteId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tables['notes']} WHERE post_id = :pid AND id = :nid");
        $stmt->execute([':pid' => $postId, ':nid' => $noteId]);

        if ($stmt->fetchColumn() == 1) {
            $stmt = $this->db->prepare("DELETE FROM {$this->tables['notes']} WHERE post_id = :pid AND id = :nid");
            $stmt->execute([':pid' => $postId, ':nid' => $noteId]);

            $stmt = $this->db->prepare("DELETE FROM {$this->tables['notes_history']} WHERE post_id = :pid AND id = :nid");
            $stmt->execute([':pid' => $postId, ':nid' => $noteId]);
            return true;
        }
        return false;
    }
}