<?php

namespace App\Controllers;

use App\Services\Misc;
use App\Models\User;
use PDO;

class AuthController
{
    private User $userModel;

    public function __construct(private PDO $db, private array $config)
    {
        $this->userModel = new User($this->db, $this->config['database']['tables']['users']);
    }

    /**
     * Route: ?page=login
     */
    public function login(): void
    {
        if ($this->userModel->isLoggedIn()) {
            header("Location: /index.php?page=account");
            exit;
        }

        $error = '';

        // Handle POST submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['user'] ?? '');
            $password = $_POST['pass'] ?? '';

            if ($username && $password) {
                if ($this->userModel->login($username, $password)) {
                    header("Location: /index.php?page=account");
                    exit;
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Please enter both a username and password.";
            }
        }

        // Render View
        $this->render('auth/login', [
            'config' => $this->config,
            'error' => $error
        ]);
    }

    /**
     * Route: ?page=reg
     */
    public function register(): void
    {
        if (!$this->config['app']['features']['registration_allowed']) {
            die("<br /><b>Registration is currently closed.</b>");
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if ($this->userModel->isBannedIp($ip)) {
            die("Action failed: Banned IP");
        }

        if ($this->userModel->isLoggedIn()) {
            header("Location: /index.php?page=account");
            exit;
        }

        $error = '';

        // Handle POST submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim(str_replace(' ', '_', $_POST['user'] ?? ''));
            $password = $_POST['pass'] ?? '';
            $confPass = $_POST['conf_pass'] ?? '';
            $email = $_POST['email'] ?? '';

            if (strlen($username) < 3 || preg_match('/[;,\\t]/', $username)) {
                $error = "Username must be at least 3 characters and cannot contain commas or semicolons.";
            } elseif ($password !== $confPass) {
                $error = "Passwords do not match.";
            } else {
                if ($this->userModel->signup($username, $password, $email)) {
                    $this->userModel->login($username, $password); // Auto-login
                    header("Location: /index.php?page=account");
                    exit;
                } else {
                    $error = "Signup failed. The username may already exist.";
                }
            }
        }

        // Render View
        $this->render('auth/register', [
            'config' => $this->config,
            'error' => $error
        ]);
    }

    /**
     * Route: ?page=login&code=01 (Logout)
     */
    public function logout(): void
    {
        setcookie("user_id", "", time() - 3600, "/");
        setcookie("pass_hash", "", time() - 3600, "/");
        header("Location: /index.php?page=home");
        exit;
    }

    private function render(string $viewPath, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../Views/' . $viewPath . '.php';
    }

    /**
     * Route: ?page=reset_password
     */
    public function reset_password(): void
    {
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        if ($this->userModel->isLoggedIn()) {
            header("Location: /index.php?page=account");
            exit;
        }

        $message = '';
        $misc = new \App\Utils\Misc();

        // 1. Handle initial request (send email)
        if (isset($_POST['username']) && !isset($_POST['new_password'])) {
            $username = trim($_POST['username']);
            $code = hash('sha256', bin2hex(random_bytes(64)) . mt_rand()); // Modern secure token

            $email = $this->userModel->setPasswordResetCode($username, $code);

            if ($email) {
                // To generate the link we must fetch the user's ID
                $profile = $this->userModel->getProfile(0, $username);
                $link = $this->config['app']['url'] . "/index.php?page=reset_password&code=" . $code . "&u=" . $profile['id'];
                $body = "Someone requested a password reset. If you did not request this, ignore it.\r\nReset link: " . $link;

                $misc->send_mail($email, "Password Recovery", $body);
                $message = "A recovery email has been sent.";
            } else {
                $message = "No valid email found for that account.";
            }
        }

        // 2. Handle returning from email link
        if (isset($_GET['code'], $_GET['u']) && is_numeric($_GET['u'])) {
            if ($this->userModel->verifyResetCode((int) $_GET['u'], $_GET['code'])) {
                $_SESSION['tmp_id'] = (int) $_GET['u'];
                $_SESSION['reset_code'] = $_GET['code'];
            } else {
                $message = "Invalid reset link.";
            }
        }

        // 3. Handle new password submission
        if (isset($_POST['new_password'], $_SESSION['tmp_id'], $_SESSION['reset_code'])) {
            $this->userModel->resetPassword($_SESSION['tmp_id'], $_SESSION['reset_code'], $_POST['new_password']);
            unset($_SESSION['tmp_id'], $_SESSION['reset_code']);
            $message = "Password changed successfully! You may now log in.";
        }

        $this->render('auth/reset_password', ['message' => $message]);
    }
}