<?php

namespace App\Models;

use PDO;

class TagAlias
{
    public function __construct(private PDO $db, private string $table)
    {
        //
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT tag FROM {$this->table} WHERE status = 'accepted' ORDER BY alias ASC");
        return $stmt->fetchAll();
    }

    public function suggestAlias(string $targetTag, string $aliasTag): bool
    {
        $targetTag = str_replace(' ', '_', trim($targetTag));
        $aliasTag = str_replace(' ', '_', trim($aliasTag));

        // Check if it already exists
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE tag = :tag AND alias = :alias");
        $stmt->execute([':tag' => $targetTag, ':alias' => $aliasTag]);
        
        if ($stmt->fetchColumn() > 0) return false;

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (tag, alias, status) VALUES (:tag, :alias, 'pending')");
        return $stmt->execute([':tag' => $targetTag, ':alias' => $aliasTag]);
    }
}