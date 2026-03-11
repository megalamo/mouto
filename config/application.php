<?php

return [
    'name' => $_ENV['SITE_NAME'] ?? 'Default Booru',
    'url' => $_ENV['APP_URL'] ?? 'http://localhost',

    // Thumbnail configurations
    'thumbnail_url' => ($_ENV['APP_URL'] ?? 'http://localhost') . '/thumbnails/',
    'image_folder' => 'images',
    'thumbnail_folder' => 'thumbnails',
    'dimension' => 150,

    // Upload constraints
    'upload' => [
        'max_width' => 0, // 0 for no limit
        'max_height' => 0,
        'min_width' => 150,
        'min_height' => 150,
    ],

    // Feature Flags mapped to booleans
    'features' => [
        'registration_allowed' => filter_var($_ENV['ALLOW_REGISTRATION'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'anon_report' => filter_var($_ENV['ANON_REPORT'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'anon_edit' => filter_var($_ENV['ANON_EDIT'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'anon_comment' => filter_var($_ENV['ANON_COMMENT'] ?? true, FILTER_VALIDATE_BOOLEAN),
        'anon_vote' => filter_var($_ENV['ANON_VOTE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'anon_upload' => filter_var($_ENV['ANON_UPLOAD'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],

    'edit_limit_minutes' => 20,
];