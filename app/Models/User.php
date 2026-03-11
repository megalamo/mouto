<?php
namespace App\Models;

use PDO;

class User
{
    public function __construct(
        private PDO $db,
        private string $userTable = 'users',
        private string $bannedIpTable = 'banned_ip',
        private string $groupTable = 'groups' // Added group table for permissions
    ) {
    }

    /**
     * Authenticate a user and set login cookies.
     */
    public function login(string $username, string $password): bool
    {
        $hashedPassword = hash('sha1', hash('md5', $password));

        $stmt = $this->db->prepare("SELECT id, user FROM {$this->userTable} WHERE user = :user AND pass = :pass LIMIT 1");
        $stmt->execute([
            ':user' => $username,
            ':pass' => $hashedPassword
        ]);

        $row = $stmt->fetch();

        if ($row) {
            setcookie("user_id", (string) $row['id'], time() + (60 * 60 * 24 * 365), "/");
            setcookie("pass_hash", $hashedPassword, time() + (60 * 60 * 24 * 365), "/");
            return true;
        }

        return false;
    }

    /**
     * Register a new user.
     */
    public function signup(string $username, string $password, string $email = ''): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->userTable} WHERE LOWER(user) = LOWER(:user)");
        $stmt->execute([':user' => $username]);

        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $hashedPassword = hash('sha1', hash('md5', $password));
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $date = date("Y-m-d H:i:s");

        $stmt = $this->db->prepare("
            INSERT INTO {$this->userTable} (user, pass, email, ip, signup_date, ugroup) 
            VALUES (:user, :pass, :email, :ip, :date, 2)
        ");

        return $stmt->execute([
            ':user' => $username,
            ':pass' => $hashedPassword,
            ':email' => $email,
            ':ip' => $ip,
            ':date' => $date
        ]);
    }

    /**
     * Check if the current user's IP is banned.
     */
    public function isBannedIp(string $ip): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->bannedIpTable} WHERE ip = :ip");
        $stmt->execute([':ip' => $ip]);
        return $stmt->fetchColumn() > 0;
    }

    /**
     * Check if a user is currently logged in via cookies.
     */
    public function isLoggedIn(): bool
    {
        if (isset($_COOKIE['user_id'], $_COOKIE['pass_hash']) && is_numeric($_COOKIE['user_id'])) {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->userTable} WHERE id = :id AND pass = :pass");
            $stmt->execute([
                ':id' => $_COOKIE['user_id'],
                ':pass' => $_COOKIE['pass_hash']
            ]);
            return $stmt->fetchColumn() > 0;
        }
        return false;
    }

    /**
     * Check if the current user has a specific group permission.
     */
    public function gotpermission(string $permission): bool
    {
        // Basic sanitization of the column name to prevent structural SQL injection
        $permission = preg_replace('/[^a-zA-Z0-9_]/', '', $permission);

        if ($this->isLoggedIn()) {
            $stmt = $this->db->prepare("
                SELECT t2.$permission 
                FROM {$this->userTable} AS t1 
                JOIN {$this->groupTable} AS t2 ON t1.ugroup = t2.id 
                WHERE t1.id = :id
            ");
            $stmt->execute([':id' => $_COOKIE['user_id']]);
            return (bool) $stmt->fetchColumn();
        }

        // If not logged in, check anonymous/default group permissions
        $stmt = $this->db->prepare("SELECT $permission FROM {$this->groupTable} WHERE default_group = 1 LIMIT 1");
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    /**
     * Fetch all statistics for a user's profile.
     */
    public function getProfile(int $id = 0, string $username = ''): ?array
    {
        $sql = "SELECT t1.id, t1.user, t1.record_score, t1.post_count, t1.comment_count, 
                       t1.tag_edit_count, t1.forum_post_count, t1.signup_date, t2.group_name 
                FROM {$this->userTable} AS t1 
                JOIN {$this->groupTable} AS t2 ON t2.id = t1.ugroup ";

        if ($id > 0) {
            $sql .= "WHERE t1.id = :val";
            $val = $id;
        } else {
            $sql .= "WHERE t1.user = :val";
            $val = $username;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':val' => $val]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Update a user's personal "My Tags" list.
     */
    public function updateMyTags(int $userId, string $tags): void
    {
        $stmt = $this->db->prepare("UPDATE {$this->userTable} SET my_tags = :tags WHERE id = :id");
        $stmt->execute([':tags' => $tags, ':id' => $userId]);
    }

    /**
     * Password Reset Methods
     */
    public function setPasswordResetCode(string $username, string $code): ?string
    {
        $stmt = $this->db->prepare("SELECT email, id FROM {$this->userTable} WHERE user = :user LIMIT 1");
        $stmt->execute([':user' => $username]);
        $row = $stmt->fetch();

        if ($row && !empty($row['email']) && strpos($row['email'], '@') !== false) {
            $stmt = $this->db->prepare("UPDATE {$this->userTable} SET mail_reset_code = :code WHERE user = :user");
            $stmt->execute([':code' => $code, ':user' => $username]);
            return $row['email'];
        }
        return null;
    }

    public function verifyResetCode(int $id, string $code): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->userTable} WHERE id = :id AND mail_reset_code = :code");
        $stmt->execute([':id' => $id, ':code' => $code]);
        return $stmt->fetchColumn() > 0;
    }

    public function resetPassword(int $id, string $code, string $newPassword): void
    {
        $hashedPassword = hash('sha1', hash('md5', $newPassword));
        $stmt = $this->db->prepare("UPDATE {$this->userTable} SET pass = :pass, mail_reset_code = NULL WHERE id = :id AND mail_reset_code = :code");
        $stmt->execute([':pass' => $hashedPassword, ':id' => $id, ':code' => $code]);
    }
}