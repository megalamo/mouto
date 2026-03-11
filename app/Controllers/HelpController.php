<?php
namespace App\Controllers;

use PDO;

class HelpController
{
    public function __construct(private PDO $db, private array $config)
    {
    }

    /**
     * Route: ?page=help&s=index (or just ?page=help)
     */
    public function index(): void
    {
        $this->render('help/index', ['config' => $this->config]);
    }

    /**
     * Route: ?page=help&s=forum
     */
    public function forum(): void
    {
        $this->render('help/forum', ['config' => $this->config]);
    }

    /**
     * Route: ?page=help&s=posts
     */
    public function posts(): void
    {
        $this->render('help/posts', ['config' => $this->config]);
    }

    /**
     * Route: ?page=help&s=ratings
     */
    public function ratings(): void
    {
        $this->render('help/ratings', ['config' => $this->config]);
    }

    /**
     * Simple View Renderer
     */
    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $viewPath . '.php';
    }
}