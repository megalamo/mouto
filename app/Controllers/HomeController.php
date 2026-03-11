<?php
namespace App\Controllers;

use PDO;

class HomeController
{
    public function __construct(private PDO $db, private array $config)
    {
    }

    /**
     * Replaces: index.php (Splash screen)
     * Route: / or ?page=home
     */
    public function index(): void
    {
        $tables = $this->config['database']['tables'];
        $pcount = 0;
        $vcount = 0;

        try {
            // Update hit counter
            $this->db->exec("UPDATE {$tables['hit_counter']} SET count = count + 1");

            // Fetch statistics
            $stmt = $this->db->query("SELECT t1.pcount, t2.count FROM {$tables['post_count']} AS t1 JOIN {$tables['hit_counter']} AS t2 WHERE t1.access_key='posts'");
            $row = $stmt->fetch();

            if ($row) {
                $pcount = $row['pcount'];
                $vcount = $row['count'];
            }
        } catch (\PDOException $e) {
            error_log("Counter Error: " . $e->getMessage());
        }

        $this->render('layouts/index', [
            'config' => $this->config,
            'pcount' => $pcount,
            'vcount' => $vcount
        ]);
    }

    /**
     * Replaces: search.php
     * Route: ?page=search
     */
    public function search(): void
    {
        $tags = urlencode(trim($_POST['tags'] ?? 'all'));
        if ($tags === "")
            $tags = "all";

        header("Location: /index.php?page=post&s=list&tags=$tags");
        exit;
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        // Note: The splash screen usually doesn't load the standard header/footer
        require __DIR__ . '/../Views/' . $viewPath . '.php';
    }
}