<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Tag;
use App\Services\CacheService;
use App\Utils\Misc;
use PDO;

class HistoryController
{
    private User $userModel;
    private CacheService $cache;

    public function __construct(private PDO $db, private array $config)
    {
        $this->userModel = new User($db, $config['database']['tables']['users'], $config['database']['tables']['banned_ips'], $config['database']['tables']['groups']);
        $this->cache = new CacheService(__DIR__ . '/../../');
    }

    /**
     * Handles viewing and reverting both Tags and Notes
     * Route: ?page=history&type=...&id=X
     */
    public function view(): void
    {
        $type = $_GET['type'] ?? '';
        $id = (int) ($_GET['id'] ?? 0);
        $pid = (int) ($_GET['pid'] ?? 0);
        $tables = $this->config['database']['tables'];

        if ($type === 'note') {
            $stmt = $this->db->prepare("SELECT t1.updated_at, t1.version, t1.body, COALESCE(t2.user, 'Anonymous') as username FROM {$tables['notes_history']} AS t1 LEFT JOIN {$tables['users']} AS t2 ON t1.user_id = t2.id WHERE t1.id = :id AND t1.post_id = :pid ORDER BY t1.version DESC");
            $stmt->execute([':id' => $id, ':pid' => $pid]);

            $this->render('history/note', [
                'history' => $stmt->fetchAll(),
                'can_revert' => $this->userModel->gotpermission('reverse_notes'),
                'id' => $id,
                'pid' => $pid
            ]);
            return;
        }

        if ($type === 'tag_history') {
            $stmt = $this->db->prepare("SELECT t1.version, t1.tags, COALESCE(t2.user, 'Anonymous') as username FROM {$tables['tag_history']} AS t1 LEFT JOIN {$tables['users']} AS t2 ON t1.user_id = t2.id WHERE t1.id = :id ORDER BY t1.version DESC");
            $stmt->execute([':id' => $id]);

            $this->render('tag_versions/index', [
                'history' => $stmt->fetchAll(),
                'can_revert' => $this->userModel->gotpermission('reverse_tags'),
                'id' => $id
            ]);
            return;
        }

        // --- Revert Actions ---
        if ($type === 'page_note' && isset($_GET['version']) && $this->userModel->gotpermission('reverse_notes')) {
            $version = (int) $_GET['version'];
            $stmt = $this->db->prepare("SELECT * FROM {$tables['notes_history']} WHERE id = :id AND post_id = :pid AND version = :version");
            $stmt->execute([':id' => $id, ':pid' => $pid, ':version' => $version]);

            if ($row = $stmt->fetch()) {
                $up_stmt = $this->db->prepare("UPDATE {$tables['notes']} SET x = :x, y = :y, width = :w, height = :h, body = :body, version = :v WHERE id = :id AND post_id = :pid");
                $up_stmt->execute([':x' => $row['x'], ':y' => $row['y'], ':w' => $row['width'], ':h' => $row['height'], ':body' => $row['body'], ':v' => $row['version'], ':id' => $id, ':pid' => $pid]);
                $this->cache->destroyPageCache("cache/" . $pid);
            }
            header("Location: /index.php?page=post&s=view&id=$pid");
            exit;
        }

        if ($type === 'page_tags' && isset($_GET['version']) && $this->userModel->gotpermission('reverse_tags')) {
            $version = (int) $_GET['version'];
            $misc = new Misc();
            $tagModel = new Tag($this->db, $tables);

            $stmt = $this->db->prepare("SELECT t1.tags, t2.tags AS current_tags FROM {$tables['tag_history']} AS t1 JOIN {$tables['posts']} AS t2 ON t2.id = :id WHERE t1.id = :id AND t1.version = :version");
            $stmt->execute([':id' => $id, ':version' => $version]);

            if ($row = $stmt->fetch()) {
                // Remove old index
                foreach (array_filter(explode(" ", trim($row['current_tags']))) as $tag) {
                    $this->cache->destroyPageCache("search_cache/" . $misc->windowsFilenameFix($tag) . "/");
                    $tagModel->deleteIndexTag($tag);
                }
                // Add new index
                foreach (array_filter(explode(" ", trim($row['tags']))) as $tag) {
                    $this->cache->destroyPageCache("search_cache/" . $misc->windowsFilenameFix($tag) . "/");
                    $tagModel->addIndexTag($tag);
                }

                $up_stmt = $this->db->prepare("UPDATE {$tables['posts']} SET tags = :tags, recent_tags = :tags, tags_version = :version WHERE id = :id");
                $up_stmt->execute([':tags' => $row['tags'], ':version' => $version, ':id' => $id]);
                $this->cache->destroyPageCache("cache/" . $id);
            }
            header("Location: /index.php?page=post&s=view&id=$id");
            exit;
        }

        header("Location: /");
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/header.php';
        require __DIR__ . '/../Views/' . $viewPath . '.php';
        require __DIR__ . '/../Views/footer.php';
    }
}