# Models

This directory contains all the data model classes that handle database operations for the Mouto imageboard application.

## Overview

Models in Mouto follow the active record pattern with prepared statements for security. Each model class manages operations related to a specific entity (posts, users, comments, tags).

## Available Models

### Post Model (`Post.php`)
Handles all post/image-related database operations.

**Methods:**
- `create(array $data): int|false` - Create a new post
- `findById(int $id): array|false` - Retrieve a post by ID
- `getAll(array $filters, int $offset, int $limit, string $orderBy): array` - List posts with pagination and filtering
- `update(int $id, array $data): bool` - Update post information
- `delete(int $id): bool` - Delete a post
- `count(array $filters): int` - Count total posts
- `searchByTags(array $tags, int $offset, int $limit): array` - Search posts by tag IDs

**Example Usage:**
```php
$post = new Post($db);
$postId = $post->create([
    'title' => 'My First Post',
    'description' => 'This is an awesome image',
    'image' => 'image.jpg',
    'user_id' => 1,
    'created_at' => date('Y-m-d H:i:s'),
]);

$singlePost = $post->findById($postId);
$posts = $post->getAll([], 0, 20);
```

### User Model (`User.php`)
Manages user account operations and authentication.

**Methods:**
- `create(array $data): int|false` - Create a new user account
- `findById(int $id): array|false` - Retrieve a user by ID
- `findByUsername(string $username): array|false` - Find user by username
- `findByEmail(string $email): array|false` - Find user by email
- `verifyPassword(string $username, string $password): bool` - Verify login credentials
- `update(int $id, array $data): bool` - Update user information
- `delete(int $id): bool` - Delete a user account
- `usernameExists(string $username, ?int $excludeId): bool` - Check username availability
- `emailExists(string $email, ?int $excludeId): bool` - Check email availability
- `getAll(int $offset, int $limit): array` - List all users

**Features:**
- Automatic password hashing using Argon2ID
- Prepared statements for SQL injection prevention

**Example Usage:**
```php
$user = new User($db);
$userId = $user->create([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'plaintext_password', // Hashed automatically
]);

$isValid = $user->verifyPassword('john_doe', 'plaintext_password');
```

### Comment Model (`Comment.php`)
Handles post comment operations.

**Methods:**
- `create(array $data): int|false` - Create a new comment
- `findById(int $id): array|false` - Retrieve a comment by ID
- `getByPostId(int $postId, int $offset, int $limit): array` - Get comments for a post
- `countByPostId(int $postId): int` - Count comments on a post
- `update(int $id, array $data): bool` - Update a comment
- `delete(int $id): bool` - Delete a comment
- `getRecent(int $limit): array` - Get recent comments site-wide

**Example Usage:**
```php
$comment = new Comment($db);
$commentId = $comment->create([
    'post_id' => 1,
    'user_id' => 1,
    'content' => 'Great post!',
    'created_at' => date('Y-m-d H:i:s'),
]);

$comments = $comment->getByPostId(1, 0, 10);
```

### Tag Model (`Tag.php`)
Manages tags and categories for organizing posts.

**Methods:**
- `create(array $data): int|false` - Create a new tag
- `findById(int $id): array|false` - Retrieve a tag by ID
- `findByName(string $name): array|false` - Find tag by name
- `getOrCreate(string $name): int|false` - Get existing tag or create new one
- `getAll(int $offset, int $limit): array` - List all tags
- `search(string $query, int $limit): array` - Search tags by name
- `update(int $id, array $data): bool` - Update a tag
- `delete(int $id): bool` - Delete a tag
- `getByPostId(int $postId): array` - Get tags for a post
- `getPopular(int $limit): array` - Get trending tags

**Example Usage:**
```php
$tag = new Tag($db);
$tagId = $tag->getOrCreate('anime');
$popular = $tag->getPopular(10);
```

## Database Schema Requirements

### Posts Table
```sql
CREATE TABLE posts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    user_id INT,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Users Table
```sql
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at DATETIME,
    updated_at DATETIME
);
```

### Comments Table
```sql
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    post_id INT NOT NULL,
    user_id INT,
    content TEXT NOT NULL,
    created_at DATETIME,
    updated_at DATETIME,
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

### Tags Table
```sql
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) UNIQUE NOT NULL,
    created_at DATETIME
);
```

### Post-Tags Junction Table
```sql
CREATE TABLE post_tags (
    post_id INT NOT NULL,
    tag_id INT NOT NULL,
    PRIMARY KEY (post_id, tag_id),
    FOREIGN KEY (post_id) REFERENCES posts(id),
    FOREIGN KEY (tag_id) REFERENCES tags(id)
);
```

## Best Practices

1. **Always use prepared statements** - All models use parameterized queries to prevent SQL injection
2. **Type hints** - All methods include PHP 8.5+ type hints
3. **Return types** - Methods explicitly return `int|false`, `array|false`, or `bool`
4. **Null safety** - Use optional parameters with null checks
5. **Security** - Passwords are hashed with Argon2ID, inputs are validated in controllers

## PHP 8.5 Features Used

- Union types (`int|false`, `array|false`)
- Named arguments support
- Arrow functions in mapping operations
- Match expressions
- Strict type declarations