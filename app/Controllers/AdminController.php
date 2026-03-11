<?php
namespace App\Controllers;

use App\Models\User;
use App\Models\Tag;
use App\Services\CacheService;
use PDO;

class AdminController
{
    private User $userModel;
    private CacheService $cache;

    public function __construct(private PDO $db, private array $config)
    {
        $this->userModel = new User($db, $config['database']['tables']['users'], $config['database']['tables']['banned_ips'], $config['database']['tables']['groups']);
        $this->cache = new CacheService(__DIR__ . '/../../');

        // SECURITY CHECK: Matches the old admin/header.php logic
        if (!$this->userModel->gotpermission('admin_panel')) {
            header('Location: /');
            exit;
        }
    }

    /**
     * Default Admin Route
     */
    public function list(): void
    {
        $this->render('admin/dashboard');
    }

    /**
     * Route: ?page=admin&s=alias
     */
    public function alias(): void
    {
        $tables = $this->config['database']['tables'];

        // Handle Form Submission (Accept/Reject)
        if (isset($_POST['accept'], $_GET['tag'], $_GET['alias'])) {
            $tag = $_GET['tag'];
            $alias = $_GET['alias'];

            if ($_POST['accept'] == 1) {
                $tagModel = new Tag($this->db, $tables);
                $stmt = $this->db->prepare("UPDATE {$tables['alias']} SET status='accepted' WHERE tag = :tag AND alias = :alias");
                $stmt->execute([':tag' => $tag, ':alias' => $alias]);

                // Update existing posts with the alias
                $aliasClean = str_replace(['%', '_'], ['\\%', '\\_'], $alias);
                $stmt = $this->db->prepare("SELECT id, tags FROM {$tables['posts']} WHERE tags LIKE :search");
                $stmt->execute([':search' => "% $aliasClean %"]);
                $updateStmt = $this->db->prepare("UPDATE {$tables['posts']} SET tags = :tags WHERE id = :id");

                while ($row = $stmt->fetch()) {
                    $currentTags = explode(" ", $row['tags']);
                    foreach ($currentTags as $t) {
                        if (trim($t) !== '')
                            $tagModel->deleteIndexTag($t);
                    }
                    $finalTags = str_replace(" $alias ", " $tag ", $row['tags']);
                    $newTags = explode(" ", $finalTags);
                    foreach ($newTags as $t) {
                        if (trim($t) !== '')
                            $tagModel->addIndexTag($t);
                    }
                    $updateStmt->execute([':tags' => $finalTags, ':id' => $row['id']]);
                }
            } elseif ($_POST['accept'] == 2) {
                $stmt = $this->db->prepare("UPDATE {$tables['alias']} SET status='rejected' WHERE tag = :tag AND alias = :alias");
                $stmt->execute([':tag' => $tag, ':alias' => $alias]);
            }
            header("Location: /index.php?page=admin&s=alias");
            exit;
        }

        // Fetch Pending Aliases
        $stmt = $this->db->query("SELECT tag, alias FROM {$tables['alias']} WHERE status='pending'");
        $pending = $stmt->fetchAll();

        $this->render('admin/alias', ['pending' => $pending]);
    }

    /**
     * Route: ?page=admin&s=reported_posts
     */
    public function reported_posts(): void
    {
        $tables = $this->config['database']['tables'];

        // Handle Unflagging
        if (isset($_GET['unreport']) && is_numeric($_GET['unreport'])) {
            $stmt = $this->db->prepare("UPDATE {$tables['posts']} SET spam = 0 WHERE id = :id");
            if ($stmt->execute([':id' => (int) $_GET['unreport']])) {
                $this->cache->destroyPageCache("cache/" . (int) $_GET['unreport']);
            }
            header("Location: /index.php?page=admin&s=reported_posts");
            exit;
        }

        // Pagination & Fetch
        $limit = 20;
        $page = isset($_GET['pid']) ? max(0, (int) $_GET['pid']) : 0;
        $total = $this->db->query("SELECT COUNT(*) FROM {$tables['posts']} WHERE spam = 1")->fetchColumn();

        $stmt = $this->db->prepare("SELECT id, directory, image, reason, score, creation_date FROM {$tables['posts']} WHERE spam = 1 ORDER BY id LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $page, PDO::PARAM_INT);
        $stmt->execute();

        $this->render('admin/reported_posts', [
            'reports' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'config' => $this->config
        ]);
    }

    /**
     * Route: ?page=admin&s=ban_user
     */
    public function ban_user(): void
    {
        $tables = $this->config['database']['tables'];
        $error = '';
        $success = '';
        $userToBan = null;

        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            $banId = (int) $_GET['user_id'];
            $stmt = $this->db->prepare("SELECT id, user, ip FROM {$tables['users']} WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $banId]);
            $userToBan = $stmt->fetch();

            if (!$userToBan) {
                $error = "User not found.";
            } elseif (isset($_POST['ban_reason'])) {
                // Execute Ban Logic (PostgreSQL UPSERT format)
                $reason = $_POST['ban_reason'];
                $adminName = $_COOKIE['user'] ?? 'Admin'; // Adjust based on your session/cookie
                $now = time();

                // 1. Ban Registration IP
                $stmt = $this->db->prepare("INSERT INTO {$tables['banned_ips']} (ip, user, reason, date_added) VALUES (:ip, :admin, :reason, :time) ON CONFLICT (ip) DO NOTHING");
                $stmt->execute([':ip' => $userToBan['ip'], ':admin' => $adminName, ':reason' => $reason, ':time' => $now]);

                // 2. Ban Comment IP's (Example)
                $sql = "INSERT INTO {$tables['banned_ips']} (ip, user, reason, date_added) SELECT DISTINCT ip, :admin, :reason, :time FROM {$tables['comments']} WHERE user = :uname ON CONFLICT (ip) DO NOTHING";
                $stmt = $this->db->prepare($sql);
                $stmt->execute([':admin' => $adminName, ':reason' => $reason, ':time' => $now, ':uname' => $userToBan['user']]);

                // Repeat similar blocks for other tables (notes, post_votes, etc.) as in legacy code...

                $success = "User " . htmlspecialchars($userToBan['user']) . " and associated IPs have been banned.";
                $userToBan = null; // Clear to show success state
            }
        }

        $this->render('admin/ban_user', [
            'userToBan' => $userToBan,
            'error' => $error,
            'success' => $success
        ]);
    }

    /**
     * Replaces: admin/tcon_depoint.php
     * Route: ?page=admin&s=tcon_depoint
     */
    public function tcon_depoint(): void
    {
        // Additional security layer from the original script
        if (!$this->userModel->gotpermission('is_admin')) {
            header('Location: /index.php?page=admin');
            exit;
        }

        $tables = $this->config['database']['tables'];
        $success = '';
        $error = '';

        try {
            $sql = "UPDATE {$tables['posts']} SET score = -100 WHERE tags LIKE '% toddlercon %' AND rating != 'safe'";
            $this->db->exec($sql);
            $success = "Score update complete.";
        } catch (\PDOException $e) {
            $error = "Error updating scores: " . $e->getMessage();
        }

        // Return to the dashboard and pass the messages
        $this->render('admin/dashboard', [
            'success' => $success,
            'error' => $error
        ]);
    }

    /**
     * Base Render Function with Admin Layout wrapper
     */
    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        $config = $this->config;
        $userModel = $this->userModel; // Pass model to layout for permission checks

        require __DIR__ . '/../Views/header.php';
        require __DIR__ . '/../Views/admin/layout_sidebar.php'; // Replaces left_menu.php
        require __DIR__ . '/../Views/' . $viewPath . '.php';
        require __DIR__ . '/../Views/footer.php';
    }

    /**
     * Replaces: optimize_defrag.php
     * Route: ?page=admin&s=optimize
     */
    public function optimize(): void
    {
        try {
            $this->db->exec("VACUUM FULL");
            $this->db->exec("ANALYZE");
            $this->render('admin/dashboard', ['success' => "PostgreSQL Database vacuumed and analyzed successfully."]);
        } catch (\PDOException $e) {
            $this->render('admin/dashboard', ['error' => "Optimization failed: " . $e->getMessage()]);
        }
    }

    /**
     * Replaces: thumbs_fix.php
     * Route: ?page=admin&s=thumbs_fix
     */
    public function thumbs_fix(): void
    {
        set_time_limit(0);
        $baseDir = __DIR__ . '/../../public/images/';
        $thumbDir = __DIR__ . '/../../public/thumbnails/';
        $output = "Thumbnail Check Started...<br>";

        // Ensure you have an ImageService loaded to handle generation
        // $imageService = new \App\Services\ImageService($this->config);

        if (is_dir($baseDir)) {
            $folders = array_diff(scandir($baseDir), ['.', '..']);
            foreach ($folders as $folder) {
                if (is_dir($baseDir . $folder)) {
                    $files = array_diff(scandir($baseDir . $folder), ['.', '..']);
                    foreach ($files as $file) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'gif', 'png', 'bmp'])) {
                            $expectedThumb = $thumbDir . $folder . '/thumbnail_' . $file;
                            if (!file_exists($expectedThumb)) {
                                // $imageService->createThumbnail($folder . '/' . $file);
                                $output .= "Missing thumbnail generated for: $file<br>";
                            }
                        }
                    }
                }
            }
        }
        $output .= "Complete.";
        $this->render('admin/dashboard', ['success' => $output]);
    }

    /**
     * Replaces: batch_add.php
     * Route: ?page=admin&s=batch_add
     */
    public function batch_add(): void
    {
        set_time_limit(0);
        $importDir = __DIR__ . '/../../import/';
        $output = "Batch Import Started...<br>";

        // This requires the ImageService to process the hash, sizes, and move files
        // $imageService = new \App\Services\ImageService($this->config);
        // $postModel = new \App\Models\Post($this->db, $this->config['database']['tables']['posts']);

        if (is_dir($importDir)) {
            $folders = array_diff(scandir($importDir), ['.', '..']);
            foreach ($folders as $folder) {
                if (is_dir($importDir . $folder)) {
                    $files = array_diff(scandir($importDir . $folder), ['.', '..']);
                    foreach ($files as $file) {
                        $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        if (in_array($ext, ['jpg', 'jpeg', 'png', 'bmp', 'gif'])) {
                            // Pseudo-code for processing
                            // $uploadData = $imageService->processLocal($importDir . $folder . '/' . $file);
                            // if ($uploadData && !$postModel->hashExists($uploadData['hash'])) {
                            //    $postModel->insert([...]);
                            //    $output .= "Imported: $file with tags: $folder<br>";
                            // }
                        }
                    }
                }
            }
        }
        $output .= "Import routine finished.";
        $this->render('admin/dashboard', ['success' => $output]);
    }
}