<?php
namespace App\Models;

use PDO;

class Forum
{
    public function __construct(
        private PDO $db,
        private array $tables
    ) {
    }

    public function getTopics(int $limit, int $offset, string $search = ''): array
    {
        $query = "SELECT t1.id, t1.topic, t1.author, t1.last_updated, t1.priority, t1.locked, COUNT(t2.id) AS post_count 
                FROM {$this->tables['forum_topics']} AS t1 
                LEFT JOIN {$this->tables['forum_posts']} AS t2 ON t1.id = t2.topic_id ";

        $params = [];
        if ($search !== '') {
            $tmp = array_filter(explode(" ", $search));
            $conditions = [];
            foreach ($tmp as $i => $current) {
                $conditions[] = "t2.post LIKE :search_$i";
                $params[":search_$i"] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $current) . '%';
            }
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
        }

        $query .= " GROUP BY t1.id ORDER BY t1.priority DESC, t1.last_updated DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getTopicCount(string $search = ''): int
    {
        if ($search === '') {
            return (int) $this->db->query("SELECT COUNT(*) FROM {$this->tables['forum_topics']}")->fetchColumn();
        }

        $query = "SELECT COUNT(DISTINCT t1.id) FROM {$this->tables['forum_topics']} AS t1 JOIN {$this->tables['forum_posts']} AS t2 ON t1.id = t2.topic_id WHERE ";
        $tmp = array_filter(explode(" ", $search));
        $conditions = [];
        $params = [];
        foreach ($tmp as $i => $current) {
            $conditions[] = "t2.post LIKE :search_$i";
            $params[":search_$i"] = '%' . str_replace(['%', '_'], ['\\%', '\\_'], $current) . '%';
        }
        $query .= implode(" AND ", $conditions);

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getTopic(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tables['forum_topics']} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function getPosts(int $topicId, int $limit, int $offset): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tables['forum_posts']} WHERE topic_id = :id ORDER BY id ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':id', $topicId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getPostCount(int $topicId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->tables['forum_posts']} WHERE topic_id = :id");
        $stmt->execute([':id' => $topicId]);
        return (int) $stmt->fetchColumn();
    }

    public function createTopic(string $title, string $postBody, string $author): int
    {
        $now = time();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->tables['forum_topics']} (topic, author, last_updated) VALUES (:topic, :author, :updated)");
            $stmt->execute([':topic' => $title, ':author' => $author, ':updated' => $now]);
            $topicId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare("INSERT INTO {$this->tables['forum_posts']} (title, post, author, creation_date, topic_id) VALUES (:title, :post, :author, :cdate, :tid)");
            $stmt->execute([':title' => $title, ':post' => $postBody, ':author' => $author, ':cdate' => $now, ':tid' => $topicId]);
            $postId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare("UPDATE {$this->tables['forum_topics']} SET creation_post = :cpost WHERE id = :pid");
            $stmt->execute([':cpost' => $postId, ':pid' => $topicId]);

            $this->db->commit();
            return $topicId;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function createReply(int $topicId, string $title, string $postBody, string $author): int
    {
        $now = time();
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->tables['forum_posts']} (title, post, author, creation_date, topic_id) VALUES (:title, :post, :author, :cdate, :tid)");
            $stmt->execute([':title' => $title, ':post' => $postBody, ':author' => $author, ':cdate' => $now, ':tid' => $topicId]);
            $postId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare("UPDATE {$this->tables['forum_topics']} SET last_updated = :updated WHERE id = :id");
            $stmt->execute([':updated' => $now, ':id' => $topicId]);

            $this->db->commit();
            return $postId;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}