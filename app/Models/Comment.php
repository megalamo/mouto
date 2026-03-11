<?php
namespace App\Models;

use PDO;

class Comment
{
    public function __construct(
        private PDO $db,
        private array $tables // Inject table names via config
    ) {
    }

    public function add(string $comment, string $username, int $postId, string $ip, int $userId): bool
    {
        $commentClean = htmlentities(trim($comment), ENT_QUOTES, 'UTF-8');

        // Prevent empty or extremely short comments
        if (strlen($commentClean) - substr_count($commentClean, ' ') < 3) {
            return false;
        }

        $this->db->beginTransaction();
        try {
            // Insert the comment
            $stmt = $this->db->prepare("
                INSERT INTO {$this->tables['comments']} (comment, ip, user, posted_at, post_id) 
                VALUES (:comment, :ip, :user, :posted_at, :post_id)
            ");
            $stmt->execute([
                ':comment' => $commentClean,
                ':ip' => $ip,
                ':user' => $username,
                ':posted_at' => time(),
                ':post_id' => $postId
            ]);

            // Update the post's last comment timestamp
            $stmt = $this->db->prepare("UPDATE {$this->tables['posts']} SET last_comment = CURRENT_TIMESTAMP WHERE id = :post_id");
            $stmt->execute([':post_id' => $postId]);

            // Increment global comment count
            $this->db->exec("UPDATE {$this->tables['post_count']} SET pcount = pcount + 1 WHERE access_key = 'comment_count'");

            // Increment user comment count
            if ($username !== "Anonymous") {
                $stmt = $this->db->prepare("UPDATE {$this->tables['users']} SET comment_count = comment_count + 1 WHERE user = :user");
                $stmt->execute([':user' => $username]);
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function vote(int $commentId, int $postId, string $ip, string $vote, int $userId, string $username): ?int
    {
        $query = "SELECT COUNT(*) FROM {$this->tables['comment_votes']} WHERE comment_id = :cid AND post_id = :id AND ip = :ip";
        $params = [':cid' => $commentId, ':id' => $postId, ':ip' => $ip];

        if ($username !== "Anonymous") {
            $query .= " OR (comment_id = :cid2 AND post_id = :id2 AND user_id = :user_id)";
            $params[':cid2'] = $commentId;
            $params[':id2'] = $postId;
            $params[':user_id'] = $userId;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        if ($stmt->fetchColumn() == 0) {
            $op = ($vote === "up") ? "+ 1" : "- 1";

            // Update Score
            $stmt = $this->db->prepare("UPDATE {$this->tables['comments']} SET score = score $op WHERE id = :cid");
            $stmt->execute([':cid' => $commentId]);

            // Record Vote
            $stmt = $this->db->prepare("INSERT INTO {$this->tables['comment_votes']} (ip, post_id, comment_id) VALUES (:ip, :id, :cid)");
            $stmt->execute([':ip' => $ip, ':id' => $postId, ':cid' => $commentId]);
        }

        // Return new score
        $stmt = $this->db->prepare("SELECT score FROM {$this->tables['comments']} WHERE id = :cid");
        $stmt->execute([':cid' => $commentId]);
        return (int) $stmt->fetchColumn();
    }
}