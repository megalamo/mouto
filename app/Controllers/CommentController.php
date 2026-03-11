<?php

namespace App\Controllers;

use App\Models\Comment;
use PDO;

class CommentController
{
    private Comment $commentModel;

    public function __construct(PDO $db, array $config)
    {
        $this->commentModel = new Comment($db, $config['database']['tables']);
    }
    
    // ... logic ...
}