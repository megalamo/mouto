<?php
namespace App\Models;

use PDO;

class Post
{
    public function __construct(
        private PDO $db,
        private string $postTable = 'posts' // Defaulting, but can be configured
    ) {
    }

    /**
     * Get a paginated list of posts.
     */
    public function getPosts(int $limit, int $offset, string $searchTags = 'all'): array
    {
        // If no specific tags are searched, just get the latest parent posts
        if ($searchTags === 'all' || trim($searchTags) === '') {
            $sql = "SELECT id, image, directory, score, rating, tags, owner 
                    FROM {$this->postTable} 
                    WHERE parent = 0 
                    ORDER BY id DESC 
                    LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll();
        }

        // TODO: Integrate your search.class.php logic here for complex tag searching
        // For now, a simple fallback for specific tags:
        $searchTerm = '%' . trim($searchTags) . '%';
        $sql = "SELECT id, image, directory, score, rating, tags, owner 
                FROM {$this->postTable} 
                WHERE tags LIKE :tags
                ORDER BY id DESC 
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tags', $searchTerm);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Get the total count of posts for pagination.
     */
    public function getTotalCount(string $searchTags = 'all'): int
    {
        if ($searchTags === 'all' || trim($searchTags) === '') {
            return (int) $this->db->query("SELECT COUNT(*) FROM {$this->postTable} WHERE parent = 0")->fetchColumn();
        }

        $searchTerm = '%' . trim($searchTags) . '%';
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->postTable} WHERE tags LIKE :tags");
        $stmt->bindValue(':tags', $searchTerm);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Check if a file hash already exists in the database.
     */
    public function hashExists(string $hash): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->postTable} WHERE hash = :hash");
        $stmt->execute([':hash' => $hash]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Get a random Post ID that does not violate the user's blacklist.
     */
    public function getRandomId(string $blacklist): int
    {
        // Get the absolute maximum ID to define the random range
        $maxId = (int) $this->db->query("SELECT MAX(id) FROM {$this->postTable}")->fetchColumn();
        if ($maxId < 1)
            return 0;

        $attempts = 0;
        $blacklistArray = array_filter(explode(" ", $blacklist));

        // Attempt to find a valid random post (max 100 attempts to prevent infinite loops)
        while ($attempts < 100) {
            $rand = mt_rand(1, $maxId);
            $stmt = $this->db->prepare("SELECT id, rating, tags FROM {$this->postTable} WHERE id >= :rand LIMIT 1");
            $stmt->execute([':rand' => $rand]);
            $row = $stmt->fetch();

            if ($row) {
                $isValid = true;

                // Check rating blacklists
                if (in_array('rating:' . strtolower($row['rating']), $blacklistArray)) {
                    $isValid = false;
                }

                // Check tag blacklists
                if ($isValid) {
                    $postTags = explode(" ", $row['tags']);
                    foreach ($blacklistArray as $bTag) {
                        if (strpos($bTag, 'rating:') === false && in_array($bTag, $postTags)) {
                            $isValid = false;
                            break;
                        }
                    }
                }

                if ($isValid) {
                    return (int) $row['id'];
                }
            }
            $attempts++;
        }

        return 0; // Fallback if no valid post is found within 100 attempts
    }

    /**
     * Insert a new post record into the database.
     */
    public function insert(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->postTable} 
            (creation_date, hash, image, source, title, owner, height, width, ext, rating, tags, directory, recent_tags, active_date, ip) 
            VALUES 
            (:cdate, :hash, :image, :source, :title, :owner, :h, :w, :ext, :rating, :tags, :dir, :rtags, :adate, :ip)
        ");

        $stmt->execute($data);
        return (int) $this->db->lastInsertId();
    }
}