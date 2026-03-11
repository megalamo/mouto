<?php
namespace App\Models;

use PDO;

class Tag
{
    public function __construct(
        private PDO $db,
        private array $tables
    ) {
    }

    public function addIndexTag(string $tag): void
    {
        if (trim($tag) === "")
            return;

        $stmt = $this->db->prepare("SELECT index_count FROM {$this->tables['tag_index']} WHERE tag = :tag");
        $stmt->execute([':tag' => $tag]);
        $count = $stmt->fetchColumn();

        if ($count !== false) {
            $stmt = $this->db->prepare("UPDATE {$this->tables['tag_index']} SET index_count = index_count + 1 WHERE tag = :tag");
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->tables['tag_index']} (tag, index_count) VALUES (:tag, 1)");
        }
        $stmt->execute([':tag' => $tag]);
    }

    public function deleteIndexTag(string $tag): void
    {
        if (trim($tag) === "")
            return;

        $stmt = $this->db->prepare("SELECT index_count FROM {$this->tables['tag_index']} WHERE tag = :tag");
        $stmt->execute([':tag' => $tag]);
        $count = $stmt->fetchColumn();

        if ($count !== false) {
            if ($count > 1) {
                $stmt = $this->db->prepare("UPDATE {$this->tables['tag_index']} SET index_count = index_count - 1 WHERE tag = :tag");
            } else {
                $stmt = $this->db->prepare("DELETE FROM {$this->tables['tag_index']} WHERE tag = :tag");
            }
            $stmt->execute([':tag' => $tag]);
        }
    }

    public function alias(string $tag): string|false
    {
        $stmt = $this->db->prepare("SELECT tag FROM {$this->tables['alias']} WHERE alias = :tag AND status = 'accepted'");
        $stmt->execute([':tag' => $tag]);
        $row = $stmt->fetch();

        return $row ? $row['tag'] : false;
    }
}