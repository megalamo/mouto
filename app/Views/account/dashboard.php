<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 600px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <?php if ($isLoggedIn): ?>
            <h3><a href="/index.php?page=login&amp;code=01">&raquo; Logout</a></h3>
            <p style="margin-bottom: 1.5rem; color: #666;">Make like a tree and get out of here! Click here to logout.</p>

            <h3><a href="/index.php?page=account_profile&amp;id=<?= $userId ?>">&raquo; My Profile</a></h3>
            <p style="margin-bottom: 1.5rem; color: #666;">View your public statistics and submissions.</p>

            <h3><a href="/index.php?page=favorites&amp;s=view&amp;id=<?= $userId ?>">&raquo; My Favorites</a></h3>
            <p style="margin-bottom: 1.5rem; color: #666;">View all of your favorites and manage them.</p>
        <?php else: ?>
            <h2 style="margin-bottom: 1.5rem; color: #dc3545;">You are not logged in.</h2>

            <h3><a href="/index.php?page=login">&raquo; Login</a></h3>
            <p style="margin-bottom: 1.5rem; color: #666;">If you already have an account you can login here.</p>

            <?php if ($config['app']['features']['registration_allowed']): ?>
                <h3><a href="/index.php?page=reg">&raquo; Sign Up</a></h3>
                <p style="margin-bottom: 1.5rem; color: #666;">Get extra functionality. Just a login and password, no email
                    required!</p>
            <?php else: ?>
                <p style="margin-bottom: 1.5rem; font-weight: bold; color: #dc3545;">Registration is currently closed.</p>
            <?php endif; ?>
        <?php endif; ?>

        <h3><a href="/index.php?page=favorites&amp;s=list">&raquo; Everyone's Favorites</a></h3>
        <p style="margin-bottom: 1.5rem; color: #666;">View the most favorited posts across the board.</p>

        <h3><a href="/index.php?page=account_options">&raquo; Options</a></h3>
        <p style="color: #666;">Manage your tag blacklists and viewing options.</p>

    </main>
</div>