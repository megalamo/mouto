<?php require_once __DIR__ . '../partials/header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 400px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem; text-align: center;">Log In</h2>

        <?php if (!empty($error)): ?>
            <div
                style="color: #d9534f; background: #fdf7f7; padding: 0.75rem; border: 1px solid #d9534f; border-radius: 4px; margin-bottom: 1rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/index.php?page=login">
            <div class="form-group" style="margin-bottom: 1rem;">
                <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Username:</label>
                <input type="text" name="user" required
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">Password:</label>
                <input type="password" name="pass" required
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <button type="submit"
                style="width: 100%; padding: 0.75rem; font-size: 1rem; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">
                Log In
            </button>

            <div style="text-align: center; margin-top: 1rem; font-size: 0.9em;">
                <a href="/index.php?page=reset_password">Forgot password?</a>
            </div>
        </form>
    </main>
</div>