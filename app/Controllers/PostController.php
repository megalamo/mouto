<?php
namespace App\Controllers;

use App\Models\Post;
use PDO;

class PostController
{
    private Post $postModel;



    public function __construct(private PDO $db, private array $config)
    {
        // Instantiate the model and pass the DB connection
        $this->postModel = new Post($this->db, $this->config['database']['tables']['posts']);
    }

    /**
     * Handles the 's=list' action (e.g., index.php?page=post&s=list)
     */
    public function list(): void
    {
        // 1. Get query parameters
        $page = isset($_GET['pid']) ? max(0, (int) $_GET['pid']) : 0;
        $tags = $_GET['tags'] ?? 'all';
        $limit = 20;

        // 2. Ask Model for data
        $totalPosts = $this->postModel->getTotalCount($tags);
        $posts = $this->postModel->getPosts($limit, $page, $tags);

        // 3. Prepare data for the view
        $viewData = [
            'config' => $this->config,
            'posts' => $posts,
            'tags' => htmlspecialchars($tags !== 'all' ? $tags : '', ENT_QUOTES),
            'currentPage' => $page,
            'limit' => $limit,
            'totalPosts' => $totalPosts,
            'totalPages' => ceil($totalPosts / $limit)
        ];

        // 4. Render the View
        $this->render('post/list', $viewData);
    }

    /**
     * Simple View Renderer
     */
    private function render(string $viewPath, array $data = []): void
    {
        // Extract array keys into variables for the view (e.g., $posts, $config)
        extract($data);

        // Include the view file
        require __DIR__ . '/../Views/' . $viewPath . '.php';
    }

    /**
     * Route: ?page=post&s=random
     */
    public function random(): void
    {
        $blacklist = $_COOKIE['tag_blacklist'] ?? '';
        $blacklist = str_replace(['&#92;', '&#039;', '%20'], ['\\', "'", ' '], $blacklist);

        // Append safe_only cookie rules
        if (isset($_COOKIE['safe_only'])) {
            $bArray = explode(" ", $blacklist);
            if (!in_array("rating:explicit", $bArray))
                $blacklist .= " rating:explicit";
            if (!in_array("rating:questionable", $bArray))
                $blacklist .= " rating:questionable";
        }

        // Check if all ratings are blacklisted (prevents infinite loops)
        $allRatingsBlocked = (
            strpos($blacklist, 'rating:explicit') !== false &&
            strpos($blacklist, 'rating:questionable') !== false &&
            strpos($blacklist, 'rating:safe') !== false
        );

        if ($allRatingsBlocked) {
            $blacklist = ''; // Override if the user broke their own settings
        }

        $randomId = $this->postModel->getRandomId($blacklist);

        if ($randomId > 0) {
            header("Location: /index.php?page=post&s=view&id=" . $randomId);
        } else {
            header("Location: /index.php?page=post&s=list");
        }
        exit;
    }

    /**
     * Route: ?page=post&s=add
     */
    public function add(): void
    {
        // Inject User Model to check permissions
        $userModel = new \App\Models\User($this->db, $this->config['database']['tables']['users'], $this->config['database']['tables']['banned_ips'], $this->config['database']['tables']['groups']);
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

        if ($userModel->isBannedIp($ip)) {
            die("Action failed: Banned IP");
        }

        $canUpload = false;
        if ($userModel->isLoggedIn()) {
            $canUpload = $userModel->gotpermission('can_upload');
        } else {
            $canUpload = $this->config['app']['features']['anon_upload'];
        }

        if (!$canUpload) {
            die("You do not have permission to upload.");
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $source = $_POST['source'] ?? '';
            $title = $_POST['title'] ?? '';
            $tagsRaw = htmlentities($_POST['tags'] ?? '', ENT_QUOTES, 'UTF-8');
            $rating = $_POST['rating'] ?? 'q';
            $userId = $_COOKIE['user_id'] ?? 0;

            // 1. Format and Apply Implications
            $tagsRaw = mb_strtolower(str_replace('%', '', $tagsRaw), 'UTF-8');
            $tagArray = array_filter(explode(' ', $tagsRaw));

            // -- NEW INJECTION --
            $implicationModel = new \App\Models\TagImplication($this->db, $this->config['database']['tables']['tag_implications']);
            $tagArray = $implicationModel->applyImplications($tagArray);

            // Standardize the final string (alphabetical, wrapped in spaces)
            asort($tagArray);
            $tags = ' ' . implode(' ', $tagArray) . ' ';

            // ... (Assume ImageService successfully handles upload and returns $uploadData)

            /*
            $postId = $this->postModel->insert([
                ':cdate' => date('Y-m-d H:i:s'),
                ':hash' => $uploadData['hash'],
                ':image' => $uploadData['filename'],
                // ... rest of data
                ':tags' => $tags,
                ':rtags' => $tags
            ]);
            */

            $postId = 123; // Placeholder assuming the insert above succeeded

            // 2. Record Tag Version 1
            // -- NEW INJECTION --
            $versionModel = new \App\Models\TagVersion(
                $this->db,
                $this->config['database']['tables']['tag_history'],
                $this->config['database']['tables']['users']
            );
            $versionModel->recordChange($postId, $tags, $userId, $ip);

            // Update Global Post Count and redirect...
            header("Location: /index.php?page=post&s=view&id=" . $postId);
            exit;
        }

        $this->render('post/add', [/* ... */]);
    }
}