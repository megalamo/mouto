<div class="app-container">
    <aside class="sidebar"
        style="background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h3 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Moderator Tools</h3>
        <ul style="list-style: none; padding: 0; line-height: 1.8;">
            <li><a href="/index.php?page=admin&s=reported_posts">Reported Posts</a></li>
            <li><a href="/index.php?page=admin&s=reported_comments">Reported Comments</a></li>
            <li><a href="/index.php?page=admin&s=alias">Alias Approvals</a></li>
        </ul>

        <?php if ($userModel->gotpermission('is_admin')): ?>
            <h3 style="margin-top: 2rem; margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Admin
                Tools</h3>
            <ul style="list-style: none; padding: 0; line-height: 1.8;">
                <li><a href="/index.php?page=admin&s=add_group">Add Group</a></li>
                <li><a href="/index.php?page=admin&s=edit_group">Edit Permissions</a></li>
                <li><a href="/index.php?page=admin&s=edit_user">Edit User</a></li>
                <li><a href="/index.php?page=admin&s=mass_parent">Mass Parent</a></li>
                <li><a href="/index.php?page=admin&s=ban_user">Ban User (Search)</a></li>
                <li><a href="/index.php?page=admin&s=optimize">Optimize Database</a></li>
                <li><a href="/index.php?page=admin&s=thumbs_fix">Regenerate Thumbnails</a></li>
                <li><a href="/index.php?page=admin&s=batch_add">Run Batch Import</a></li>
            </ul>
        <?php endif; ?>
    </aside>
    <h2>Moderation Dashboard</h2>
    <?php if (!empty($error)): ?>
        <div
            style="color: #d9534f; background: #fdf7f7; padding: 1rem; border: 1px solid #d9534f; border-radius: 4px; margin-bottom: 1rem;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div
            style="color: #155724; background: #d4edda; padding: 1rem; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 1rem;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <p>Select a tool from the sidebar to begin.</p>

    <main class="content"></main>
</div>