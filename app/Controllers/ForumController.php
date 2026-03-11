<?php
namespace App\Controllers;

use App\Models\Forum;
use App\Models\User;
use App\Utils\Misc;
use PDO;

class ForumController
{
    private Forum $forumModel;
    private User $userModel;
    private Misc $misc;

    public function __construct(private PDO $db, private array $config)
    {
        $this->forumModel = new Forum($db, $config['database']['tables']);
        $this->userModel = new User($db, $config['database']['tables']['users'], $config['database']['tables']['banned_ips'], $config['database']['tables']['groups']);
        $this->misc = new Misc();
    }

    public function list(): void
    {
        $limit = 40;
        $pageLimit = 6;
        $page = isset($_GET['pid']) ? max(0, (int) $_GET['pid']) : 0;
        $search = $_GET['query'] ?? '';

        $totalTopics = $this->forumModel->getTopicCount($search);
        $topics = $this->forumModel->getTopics($limit, $page, $search);

        $this->render('forum/list', [
            'topics' => $topics,
            'search' => $search,
            'currentPage' => $page,
            'limit' => $limit,
            'totalTopics' => $totalTopics,
            'misc' => $this->misc,
            'userModel' => $this->userModel
        ]);
    }

    public function view(): void
    {
        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        $limit = 20;
        $page = isset($_GET['pid']) ? max(0, (int) $_GET['pid']) : 0;

        $topic = $this->forumModel->getTopic($id);
        if (!$topic) {
            header("Location: /index.php?page=forum&s=list");
            exit;
        }

        $totalPosts = $this->forumModel->getPostCount($id);
        $posts = $this->forumModel->getPosts($id, $limit, $page);

        $this->render('forum/view', [
            'topic' => $topic,
            'posts' => $posts,
            'currentPage' => $page,
            'limit' => $limit,
            'totalPosts' => $totalPosts,
            'misc' => $this->misc,
            'userModel' => $this->userModel
        ]);
    }

    public function search(): void
    {
        if (isset($_POST['search']) && trim($_POST['search']) !== "") {
            $search = urlencode($_POST['search']);
            header("Location: /index.php?page=forum&s=list&query=$search");
            exit;
        }
        header("Location: /index.php?page=forum&s=list");
    }

    public function add(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->userModel->isBannedIp($ip) || !$this->userModel->isLoggedIn()) {
            die("Action failed: Unauthorized.");
        }

        $title = htmlentities($_POST['title'] ?? $_POST['topic'] ?? '', ENT_QUOTES, 'UTF-8');
        $post = htmlentities($_POST['post'] ?? '', ENT_QUOTES, 'UTF-8');
        $author = $_COOKIE['user'] ?? 'Unknown'; // Ensure you set the username in cookies on login or retrieve it here

        if (empty(trim($title)) || empty(trim($post))) {
            die("Topic and body are required.");
        }

        // Reply to existing topic
        if (isset($_GET['t']) && $_GET['t'] === 'post' && isset($_GET['pid'])) {
            $topicId = (int) $_GET['pid'];
            $topic = $this->forumModel->getTopic($topicId);

            if ($topic && !$topic['locked']) {
                $postId = $this->forumModel->createReply($topicId, $title, $post, $author);

                // Calculate which page the new post is on to redirect properly
                $limit = 20;
                $totalPosts = $this->forumModel->getPostCount($topicId);
                $pid = floor(($totalPosts - 1) / $limit) * $limit;

                header("Location: /index.php?page=forum&s=view&id=$topicId&pid=$pid#$postId");
                exit;
            }
        }
        // New Topic
        else {
            $topicId = $this->forumModel->createTopic($title, $post, $author);
            header("Location: /index.php?page=forum&s=view&id=$topicId");
            exit;
        }
    }

    public function edit(): void
    {
        if (!$this->userModel->isLoggedIn()) {
            die("Unauthorized.");
        }

        $id = (int) ($_GET['id'] ?? 0);

        // Pinning
        if (isset($_GET['pin']) && $this->userModel->gotpermission('pin_forum_topics')) {
            $priority = (int) $_GET['pin'] > 0 ? 1 : 0;
            $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['forum_topics']} SET priority = :p WHERE id = :id");
            $stmt->execute([':p' => $priority, ':id' => $id]);
            header("Location: /index.php?page=forum&s=list");
            exit;
        }

        // Locking
        if (isset($_GET['lock']) && $this->userModel->gotpermission('lock_forum_topics')) {
            $locked = $_GET['lock'] === 'true' ? 1 : 0;
            $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['forum_topics']} SET locked = :l WHERE id = :id");
            $stmt->execute([':l' => $locked, ':id' => $id]);
            header("Location: /index.php?page=forum&s=view&id=$id");
            exit;
        }

        // Editing Post Body
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['cid'], $_GET['pid'])) {
            $cid = (int) $_GET['cid'];
            $tid = (int) $_GET['pid'];

            // Note: Add logic here to verify author identity vs user permissions before updating
            $title = htmlentities($_POST['title'] ?? '', ENT_QUOTES, 'UTF-8');
            $post = htmlentities($_POST['post'] ?? '', ENT_QUOTES, 'UTF-8');

            $stmt = $this->db->prepare("UPDATE {$this->config['database']['tables']['forum_posts']} SET title = :t, post = :p WHERE id = :cid");
            $stmt->execute([':t' => $title, ':p' => $post, ':cid' => $cid]);

            header("Location: /index.php?page=forum&s=view&id=$tid#$cid");
            exit;
        }
    }

    public function remove(): void
    {
        if (!$this->userModel->isLoggedIn()) {
            die("Unauthorized.");
        }

        // Delete Topic
        if (isset($_GET['fid']) && $this->userModel->gotpermission('delete_forum_topics')) {
            $fid = (int) $_GET['fid'];
            $this->db->prepare("DELETE FROM {$this->config['database']['tables']['forum_topics']} WHERE id = :id")->execute([':id' => $fid]);
            $this->db->prepare("DELETE FROM {$this->config['database']['tables']['forum_posts']} WHERE topic_id = :id")->execute([':id' => $fid]);
            header("Location: /index.php?page=forum&s=list");
            exit;
        }

        // Delete Post
        if (isset($_GET['cid'], $_GET['pid']) && $this->userModel->gotpermission('delete_forum_posts')) {
            $cid = (int) $_GET['cid'];
            $tid = (int) $_GET['pid'];
            $this->db->prepare("DELETE FROM {$this->config['database']['tables']['forum_posts']} WHERE id = :id")->execute([':id' => $cid]);
            header("Location: /index.php?page=forum&s=view&id=$tid");
            exit;
        }
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/header.php';
        require __DIR__ . '/../Views/' . $viewPath . '.php';
        require __DIR__ . '/../Views/footer.php';
    }
}