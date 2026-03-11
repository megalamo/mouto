<?php
namespace App\Controllers;

use App\Utils\Misc;
use PDO;

class FavoritesController
{
    private Misc $misc;

    public function __construct(private PDO $db, private array $config)
    {
        $this->misc = new Misc();
    }

    /**
     * Route: ?page=favorites&s=list
     */
    public function list(): void
    {
        $limit = 50;
        $page = isset($_GET['pid']) ? max(0, (int) $_GET['pid']) : 0;
        $tables = $this->config['database']['tables'];

        $total = $this->db->query("SELECT COUNT(*) FROM {$tables['favorites_count']}")->fetchColumn();

        $stmt = $this->db->prepare("SELECT t2.user, t1.user_id, t1.fcount FROM {$tables['favorites_count']} AS t1 JOIN {$tables['users']} AS t2 ON t2.id = t1.user_id ORDER BY t2.user ASC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $page, PDO::PARAM_INT);
        $stmt->execute();

        $this->render('favorites/list', [
            'users' => $stmt->fetchAll(),
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'misc' => $this->misc
        ]);
    }

    /**
     * Route: ?page=favorites&s=view&id=X
     */
    public function view(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $limit = 50;
        $page = isset($_GET['pid']) ? max(0, (int) $_GET['pid']) : 0;
        $tables = $this->config['database']['tables'];

        $stmt = $this->db->prepare("SELECT fcount FROM {$tables['favorites_count']} WHERE user_id = :id");
        $stmt->execute([':id' => $id]);
        $total = $stmt->fetchColumn() ?: 0;

        $favorites = [];
        if ($total > 0) {
            $stmt = $this->db->prepare("SELECT t1.favorite, t2.image, t2.directory as dir, t2.tags, t2.owner, t2.score, t2.rating FROM {$tables['favorites']} AS t1 JOIN {$tables['posts']} AS t2 ON t2.id = t1.favorite WHERE t1.user_id = :id ORDER BY t1.added DESC LIMIT :limit OFFSET :offset");
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $page, PDO::PARAM_INT);
            $stmt->execute();
            $favorites = $stmt->fetchAll();
        }

        $this->render('favorites/view', [
            'favorites' => $favorites,
            'total' => $total,
            'userId' => $id,
            'page' => $page,
            'limit' => $limit,
            'misc' => $this->misc,
            'config' => $this->config
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