<?php

namespace App\Controllers;

use App\Models\User;
use PDO;

class AccountController
{
    private User $userModel;

    public function __construct(private PDO $db, private array $config)
    {
        $this->userModel = new User($db, $config['database']['tables']['users'], $config['database']['tables']['banned_ips'], $config['database']['tables']['groups']);
    }

    /**
     * Route: ?page=account
     */
    public function index(): void
    {
        $this->render('account/dashboard', [
            'isLoggedIn' => $this->userModel->isLoggedIn(),
            'userId' => $_COOKIE['user_id'] ?? 0,
            'config' => $this->config
        ]);
    }

    /**
     * Route: ?page=account_profile&id=X
     */
    public function profile(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $uname = $_GET['uname'] ?? '';

        if (!$id && !$uname) {
            header('Location: /index.php');
            exit;
        }

        $profile = $this->userModel->getProfile($id, $uname);
        if (!$profile) {
            header('Location: /index.php?page=post&s=list');
            exit;
        }

        // Fetch recent submissions
        $stmt = $this->db->prepare("SELECT id, directory as dir, image, tags, rating, score, owner FROM {$this->config['database']['tables']['posts']} WHERE owner = :user ORDER BY id DESC LIMIT 5");
        $stmt->execute([':user' => $profile['user']]);
        $recentPosts = $stmt->fetchAll();

        // Fetch recent favorites
        $stmt = $this->db->prepare("SELECT t1.favorite, t2.image, t2.directory as dir, t2.tags, t2.owner, t2.score, t2.rating FROM {$this->config['database']['tables']['favorites']} AS t1 JOIN {$this->config['database']['tables']['posts']} AS t2 ON t2.id = t1.favorite WHERE t1.user_id = :id ORDER BY t1.added DESC LIMIT 5");
        $stmt->execute([':id' => $profile['id']]);
        $recentFavs = $stmt->fetchAll();

        $this->render('account/profile', [
            'profile' => $profile,
            'recentPosts' => $recentPosts,
            'recentFavs' => $recentFavs,
            'config' => $this->config
        ]);
    }

    /**
     * Route: ?page=account_options
     */
    public function options(): void
    {
        $oneYear = time() + (60 * 60 * 24 * 365);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // User Blacklist
            $users = $_POST['users'] ?? '';
            $usersClean = str_replace(['\\', ' ', "'"], ["&#92;", "%20", "&#039;"], $users);
            setcookie("user_blacklist", $users ? strtolower($usersClean) : '', $users ? $oneYear : time() - 3600);

            // Tag Blacklist
            $tags = $_POST['tags'] ?? '';
            $tagsClean = str_replace(['\\', ' ', "'"], ["&#92;", "%20", "&#039;"], $tags);
            setcookie("tag_blacklist", $tags ? $tagsClean : '', $tags ? $oneYear : time() - 3600);

            // Thresholds
            setcookie('comment_threshold', is_numeric($_POST['cthreshold'] ?? '') ? $_POST['cthreshold'] : 0, $oneYear);
            setcookie('post_threshold', is_numeric($_POST['pthreshold'] ?? '') ? $_POST['pthreshold'] : 0, $oneYear);

            // My Tags
            $myTags = $_POST['my_tags'] ?? '';
            setcookie("tags", $myTags ? str_replace([" ", "'"], ["%20", "&#039;"], $myTags) : '', $myTags ? $oneYear : time() - 3600);

            if ($myTags && $this->userModel->isLoggedIn()) {
                $this->userModel->updateMyTags((int) $_COOKIE['user_id'], $myTags);
            }

            header("Location: /index.php?page=account_options&success=1");
            exit;
        }

        $this->render('account/options', [
            'success' => isset($_GET['success'])
        ]);
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/header.php';
        require __DIR__ . '/../Views/' . $viewPath . '.php';
        require __DIR__ . '/../Views/footer.php';
    }
}