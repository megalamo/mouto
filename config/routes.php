<?php

return [
    'default_page' => 'home',

    'controllers' => [
        'home' => 'HomeController',
        'post' => 'PostController',
        'comment' => 'CommentController',
        'tags' => 'TagController',
        'alias' => 'TagController',
        'favorites' => 'FavoritesController',
        'action' => 'ActionController',
        'admin' => 'AdminController',
        'account' => 'AccountController',
        'account_profile' => 'AccountController',
        'login' => 'AuthController',
        'register' => 'AuthController',
    ],

    'default_methods' => [
        'search' => 'search',
        'account_profile' => 'profile',
        'account_options' => 'options',
        'login' => 'login',
        'register' => 'register',
        'reset_password' => 'reset_password',
        'post' => 'list',
        'comment' => 'list',
        'tags' => 'alias',
    ]
];