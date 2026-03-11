<?php
namespace App\Models;

use PDO;

class Favorite
{
    public function __construct(private PDO $db, private array $tables)
    {
    }

    public function add(int $userId, int $postId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tables['favorites']} WHERE user_id = :uid AND favorite = :fid");
        $stmt->execute([':uid' => $userId, ':fid' => $postId]);

        if ($stmt->fetchColumn() < 1) {
            $stmt = $this->db->prepare("INSERT INTO {$this->tables['favorites']}(user_id, favorite) VALUES(:uid, :fid)");
            if ($stmt->execute([':uid' => $userId, ':fid' => $postId])) {

                $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tables['favorites_count']} WHERE user_id = :uid");
                $stmt->execute([':uid' => $userId]);

                if ($stmt->fetchColumn() < 1) {
                    $stmt = $this->db->prepare("INSERT INTO {$this->tables['favorites_count']}(user_id, fcount) VALUES(:uid, 1)");
                } else {
                    $stmt = $this->db->prepare("UPDATE {$this->tables['favorites_count']} SET fcount = fcount + 1 WHERE user_id = :uid");
                }
                $stmt->execute([':uid' => $userId]);
                return true;
            }
        }
        return false;
    }
}