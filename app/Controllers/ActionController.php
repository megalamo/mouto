<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Favorite;
use App\Models\Note;
use App\Services\CacheService;
use PDO;

class ActionController
{
    private User $userModel;
    private Favorite $favModel;
    private Note $noteModel;
    private CacheService $cache;

    public function __construct(private PDO $db, private array $config)
    {
        $this->userModel = new User($db, $config['database']['tables']['users']);
        $this->favModel = new Favorite($db, $config['database']['tables']);
        $this->noteModel = new Note($db, $config['database']['tables']);
        $this->cache = new CacheService(__DIR__ . '/../../'); // Root dir reference
    }

    /**
     * Replaces: public/addfav.php
     * Route: ?page=action&s=addfav
     */
    public function addfav(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->userModel->isBannedIp($ip))
            exit;

        if (isset($_GET['id']) && is_numeric($_GET['id'])) {
            if ($this->userModel->isLoggedIn()) {
                $this->favModel->add((int) $_COOKIE['user_id'], (int) $_GET['id']);
                header('Location: /index.php?page=post&s=view&id=' . (int) $_GET['id']);
            } else {
                header('Location: /index.php?page=account');
            }
        }
    }

    /**
     * Replaces: public/report.php
     * Route: ?page=action&s=report
     */
    public function report(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->userModel->isBannedIp($ip))
            exit;

        // Strict typing
        if (!$this->userModel->isLoggedIn() && !$this->config['app']['features']['anon_report']) {
            exit;
        }

        $type = $_GET['type'] ?? '';
        $rid = isset($_GET['rid']) ? (int) $_GET['rid'] : 0;

        if ($rid > 0) {
            if ($type === 'comment') {
                $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['comments']} SET spam = 1 WHERE id = :rid");
                if ($stmt->execute([':rid' => $rid])) {
                    $stmt = $this->db->prepare("SELECT post_id FROM {$this->config['database']['tables']['comments']} WHERE id = :rid");
                    $stmt->execute([':rid' => $rid]);
                    if ($postId = $stmt->fetchColumn()) {
                        $this->cache->destroyPageCache("cache/" . $postId);
                    }
                    echo "pass";
                } else {
                    echo "fail";
                }
            } elseif ($type === 'post') {
                $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['posts']} SET spam = 1 WHERE id = :rid");
                echo $stmt->execute([':rid' => $rid]) ? "pass" : "fail";
            }
        }
    }

    public function vote(): void
    {
        if (!$this->userModel->isLoggedIn() && !$this->config['app']['features']['anon_vote']) {
            header('Location: /index.php?page=account');
            exit;
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $type = $_GET['type'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $userId = $this->userModel->isLoggedIn() ? (int) $_COOKIE['user_id'] : 0;

        if ($id > 0 && in_array($type, ['up', 'down'])) {
            $queryPart = $userId > 0 ? " OR (post_id = :id2 AND user_id = :uid)" : "";
            $params = [':id' => $id, ':ip' => $ip];
            if ($userId > 0) {
                $params[':id2'] = $id;
                $params[':uid'] = $userId;
            }

            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->config['database']['tables']['post_votes']} WHERE (post_id = :id AND ip = :ip) $queryPart");
            $stmt->execute($params);

            if ($stmt->fetchColumn() < 1) {
                $op = $type === 'up' ? '+ 1' : '- 1';
                $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['posts']} SET score = score $op WHERE id = :id");
                $stmt->execute([':id' => $id]);

                $stmt = $this->db->prepare("INSERT INTO {$this->config['database']['tables']['post_votes']}(ip, post_id, rated, user_id) VALUES(:ip, :id, :type, :uid)");
                $stmt->execute([':ip' => $ip, ':id' => $id, ':type' => $type, ':uid' => $userId]);
            }
            header("Location: /index.php?page=post&s=view&id=" . $id);
        }
    }

    public function savenote(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->userModel->isBannedIp($ip) || !$this->userModel->isLoggedIn() || !$this->userModel->gotpermission('alter_notes')) {
            exit;
        }

        $note = $_GET['note'] ?? [];
        if (isset($_GET['id'], $note['post_id'], $note['x'], $note['y'], $note['width'], $note['height'])) {
            $body = htmlentities($note['body'] ?? '', ENT_QUOTES, 'UTF-8');
            $body = str_replace(
                ["&lt;tn&gt;", "&lt;/tn&gt;", "&lt;br /&gt;", "&lt;b&gt;", "&lt;/b&gt;", "&lt;i&gt;", "&lt;/i&gt;"],
                ["<tn>", "</tn>", "<br />", "<b>", "</b>", "<i>", "</i>"],
                $body
            );

            $this->noteModel->save(
                (int) $note['post_id'],
                (int) $_GET['id'],
                (int) $note['x'],
                (int) $note['y'],
                (int) $note['width'],
                (int) $note['height'],
                $body,
                $ip,
                (int) $_COOKIE['user_id']
            );
            $this->cache->destroyPageCache("cache/" . (int) $note['post_id']);
        }
    }

    /**
     * Replaces: image_data.php
     * Route: ?page=action&s=image_data&start=0&limit=100
     */
    public function image_data(): void
    {
        $start = (int) ($_GET['start'] ?? 0);
        $limit = (int) ($_GET['limit'] ?? 100);
        if ($limit > 100)
            $limit = 100;

        $stmt = $this->db->prepare("SELECT id, image, directory, score, rating, tags, height, width, hash FROM {$this->config['database']['tables']['posts']} WHERE id >= :start LIMIT :limit");
        $stmt->bindValue(':start', $start, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll();
        $count = count($rows);

        header("Content-type: text/xml");
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<posts count="' . $count . '" offset="0">';

        foreach ($rows as $row) {
            $tags = htmlspecialchars(trim($row['tags']), ENT_XML1, 'UTF-8');
            $thumb_width = 0;
            $thumb_height = 0;

            // Note: In production, it's better to store thumb dimensions in the DB rather than hitting the disk.
            $thumb_path = __DIR__ . "/../../public/thumbnails/" . $row['directory'] . "/thumbnail_" . $row['image'];
            if (file_exists($thumb_path)) {
                $thumbnail_data = getimagesize($thumb_path);
                if ($thumbnail_data) {
                    $thumb_width = $thumbnail_data[0];
                    $thumb_height = $thumbnail_data[1];
                }
            }

            echo sprintf(
                '<post height="%d" score="%d" file_url="%s" parent_id="0" sample_url="%s" sample_width="%d" sample_height="%d" preview_url="%s" rating="%s" tags="%s" id="%d" width="%d" change="%d" md5="%s" creator_id="1" has_children="false" created_at="%s" status="active" source="" has_notes="false" has_comments="false" preview_width="%d" preview_height="%d"/>',
                $row['height'],
                $row['score'],
                $this->config['app']['url'] . "/images/" . $row['directory'] . "/" . $row['image'],
                $this->config['app']['url'] . "/images/" . $row['directory'] . "/" . $row['image'], // Assuming no separate sample generation
                $row['width'],
                $row['height'],
                $this->config['app']['thumbnail_url'] . $row['directory'] . "/thumbnail_" . $row['image'],
                $row['rating'],
                $tags,
                $row['id'],
                $row['width'],
                time(),
                $row['hash'],
                date("D M d H:i:s O Y"),
                $thumb_width,
                $thumb_height
            );
        }
        echo '</posts>';
        exit;
    }

    /**
     * Replaces: thumbnail.php
     * Route: ?page=action&s=thumbnail&id=X
     */
    public function thumbnail(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        if ($id < 1)
            exit;

        $stmt = $this->db->prepare("SELECT image, directory, ext FROM {$this->config['database']['tables']['posts']} WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if (!$row)
            exit;

        $thumb_path = __DIR__ . "/../../public/thumbnails/" . $row['directory'] . "/thumbnail_" . $row['image'];

        if (file_exists($thumb_path)) {
            header("Cache-Control: store, cache");
            header("Pragma: cache");
            header("Content-type: image/" . str_replace(".", "", $row['ext']));
            readfile($thumb_path);
        }
        exit;
    }

    /**
     * Route: ?page=action&s=editpost
     */
    public function editpost(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->userModel->isBannedIp($ip)) {
            die("Action failed: Banned IP");
        }

        // Verify POST validation block
        if (($_POST['pconf'] ?? 0) != "1") {
            header('Location: /index.php');
            exit;
        }

        if (!$this->userModel->isLoggedIn() && !$this->config['app']['features']['anon_edit']) {
            header('Location: /index.php?page=account');
            exit;
        }

        $id = (int) ($_POST['id'] ?? 0);
        $userId = $this->userModel->isLoggedIn() ? (int) $_COOKIE['user_id'] : 0;

        $tagsRaw = str_replace('%', '', htmlentities($_POST['tags'] ?? '', ENT_QUOTES, 'UTF-8'));
        $tagsRaw = mb_strtolower($tagsRaw, 'UTF-8');

        $tagArray = array_filter(explode(' ', $tagsRaw));

        // 1. Inject Tag Implications
        $implicationModel = new \App\Models\TagImplication($this->db, $this->config['database']['tables']['tag_implications']);
        $tagArray = $implicationModel->applyImplications($tagArray);

        asort($tagArray);
        $newTags = ' ' . implode(' ', $tagArray) . ' ';

        // 2. Fetch Old Tags to compare
        $stmt = $this->db->prepare("SELECT tags FROM {$this->config['database']['tables']['posts']} WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $oldTags = $stmt->fetchColumn();

        // 3. Process only if tags actually changed
        if ($newTags !== $oldTags) {
            // FIX: Pass the entire tables array, not individual strings
            $tagModel = new \App\Models\Tag($this->db, $this->config['database']['tables']);

            $oldTagsArray = array_filter(explode(' ', trim($oldTags)));

            // Decrement removed tags
            foreach ($oldTagsArray as $old) {
                if (!in_array($old, $tagArray)) {
                    $tagModel->deleteIndexTag($old);
                    $this->cache->destroyPageCache("search_cache/" . str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $old));
                }
            }

            // Increment new tags
            foreach ($tagArray as $new) {
                if (!in_array($new, $oldTagsArray)) {
                    $tagModel->addIndexTag($new);
                    $this->cache->destroyPageCache("search_cache/" . str_replace(['\\', '/', ':', '*', '?', '"', '<', '>', '|'], '_', $new));
                }
            }

            // 4. Update the Post
            $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['posts']} SET tags = :tags, recent_tags = :tags WHERE id = :id");
            $stmt->execute([':tags' => $newTags, ':id' => $id]);

            // 5. Record the Version History
            $versionModel = new \App\Models\TagVersion($this->db, $this->config['database']['tables']['tag_history'], $this->config['database']['tables']['users']);
            $versionModel->recordChange($id, $newTags, $userId, $ip);

            // Destroy Post Page Cache
            $this->cache->destroyPageCache("cache/" . $id);
        }

        // Return to the post view
        header("Location: /index.php?page=post&s=view&id=" . $id);
        exit;
    }
}