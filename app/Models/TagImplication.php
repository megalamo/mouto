<?php

namespace App\Models;

use PDO;

class TagImplication
{
    public function __construct(private PDO $db, private string $table)
    {
    }

    public function getActive(): array
    {
        $stmt = $this->db->query("SELECT predicate, implication FROM {$this->table} WHERE status = 'accepted' ORDER BY predicate ASC");
        return $stmt->fetchAll();
    }

    /**
     * Scans an array of submitted tags and injects implied tags.
     */
    public function applyImplications(array $tags): array
    {
        if (empty($tags))
            return [];

        $placeholders = str_repeat('?,', count($tags) - 1) . '?';
        $stmt = $this->db->prepare("SELECT implication FROM {$this->table} WHERE status = 'accepted' AND predicate IN ($placeholders)");
        $stmt->execute($tags);

        $impliedTags = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Merge original tags with implied tags and remove duplicates
        return array_unique(array_merge($tags, $impliedTags));
    }
}