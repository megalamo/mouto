<?php

namespace App\Controllers;

use PDO;
use App\Models\TagAlias;
use App\Models\TagVersion;
use App\Models\TagImplication;
use App\Models\TagRelationship;

class TagController
{
    public function __construct(private PDO $db, private array $config)
    {
    }

    /**
     * Route: ?page=tags&s=aliases
     */
    public function aliases(): void
    {
        $aliasModel = new TagAlias($this->db, $this->config['database']['tables']['alias']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['tag']) && !empty($_POST['alias'])) {
            $aliasModel->suggestAlias($_POST['tag'], $_POST['alias']);
            header("Location: /index.php?page=tags&s=aliases&success=1");
            exit;
        }

        $this->render('tags_aliases/index', [
            'aliases' => $aliasModel->getActive(),
            'success' => isset($_GET['success'])
        ]);
    }

    /**
     * Route: ?page=tags&s=implications
     */
    public function implications(): void
    {
        $impModel = new TagImplication($this->db, $this->config['database']['tables']['tag_implications']);

        $this->render('tags_implications/index', [
            'implications' => $impModel->getActive()
        ]);
    }

    /**
     * Route: ?page=tags&s=related&tag=X
     */
    public function relationships(): void
    {
        $targetTag = str_replace(' ', '_', trim($_GET['tag'] ?? ''));
        $related = [];

        if ($targetTag !== '') {
            $relModel = new TagRelationship($this->db, $this->config['database']['tables']['posts']);
            $related = $relModel->calculateRelated($targetTag);
        }

        $this->render('tags_relationships/index', [
            'targetTag' => $targetTag,
            'related' => $related
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