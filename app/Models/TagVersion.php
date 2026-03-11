<?php

namespace App\Models;

use PDO;

class TagVersion
{
    public function __construct(
        private PDO $db,
        private string $historyTable,
        private string $usersTable
    ) {
    }

    public function getVersionsForPost(int $postId): array
    {
        $stmt = $this->db->prepare("
            SELECT t1.version, t1.tags, t1.updated_at, COALESCE(t2.user, 'Anonymous') as username 
            FROM {$this->historyTable} AS t1 
            LEFT JOIN {$this->usersTable} AS t2 ON t1.user_id = t2.id 
            WHERE t1.id = :id 
            ORDER BY t1.version DESC
        ");
        $stmt->execute([':id' => $postId]);
        return $stmt->fetchAll();
    }

    public function recordChange(int $postId, string $newTags, int $userId, string $ip): void
    {
        // Calculate next version number
        $stmt = $this->db->prepare("SELECT COALESCE(MAX(version), 0) + 1 FROM {$this->historyTable} WHERE id = :id");
        $stmt->execute([':id' => $postId]);
        $nextVersion = $stmt->fetchColumn();

        $stmt = $this->db->prepare("
            INSERT INTO {$this->historyTable} (id, tags, version, user_id, ip, updated_at) 
            VALUES (:id, :tags, :version, :user_id, :ip, CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            ':id' => $postId,
            ':tags' => $newTags,
            ':version' => $nextVersion,
            ':user_id' => $userId,
            ':ip' => $ip
        ]);
    }
}