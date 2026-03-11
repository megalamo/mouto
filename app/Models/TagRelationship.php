<?php

namespace App\Models;

use PDO;

class TagRelationship
{
    public function __construct(private PDO $db, private string $postTable)
    {
    }

    /**
     * Calculates related tags by scanning posts that contain the target tag.
     * Note: In a massive database, this should be heavily cached!
     */
    public function calculateRelated(string $targetTag, int $limit = 25): array
    {
        $searchTag = "% $targetTag %";

        // Fetch up to 1000 recent posts containing this tag to calculate relationships
        $stmt = $this->db->prepare("SELECT tags FROM {$this->postTable} WHERE tags LIKE :tag LIMIT 1000");
        $stmt->execute([':tag' => $searchTag]);

        $tagCounts = [];

        while ($row = $stmt->fetch()) {
            $postTags = array_filter(explode(' ', trim($row['tags'])));
            foreach ($postTags as $tag) {
                if ($tag === $targetTag)
                    continue; // Skip the target tag itself

                if (!isset($tagCounts[$tag])) {
                    $tagCounts[$tag] = 1;
                } else {
                    $tagCounts[$tag]++;
                }
            }
        }

        // Sort by highest frequency
        arsort($tagCounts);

        // Return top $limit related tags
        return array_slice($tagCounts, 0, $limit, true);
    }
}