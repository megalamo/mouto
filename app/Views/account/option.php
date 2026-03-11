<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 800px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2>Account Options</h2>
        <p style="margin-bottom: 2rem; color: #666;">Separate individual tags and users with spaces. You must have
            cookies enabled. User blacklist is case sensitive.</p>

        <?php if ($success): ?>
            <div
                style="color: #155724; background: #d4edda; padding: 1rem; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 1.5rem;">
                Options saved successfully!
            </div>
        <?php endif; ?>

        <form action="/index.php?page=account_options" method="post">
            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold;">Tag Blacklist</label>
                <p style="font-size: 0.9em; color: #666; margin-bottom: 0.5rem;">Any post containing a blacklisted tag
                    will be hidden.</p>
                <textarea name="tags" rows="4"
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars(str_replace('%20', ' ', $_COOKIE['tag_blacklist'] ?? '')) ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold;">User Blacklist</label>
                <p style="font-size: 0.9em; color: #666; margin-bottom: 0.5rem;">Any post or comment from a blacklisted
                    user will be hidden.</p>
                <input type="text" name="users"
                    value="<?= htmlspecialchars(str_replace('%20', ' ', $_COOKIE['user_blacklist'] ?? '')) ?>"
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label style="font-weight: bold;">Comment Threshold</label>
                    <input type="number" name="cthreshold" value="<?= $_COOKIE['comment_threshold'] ?? 0 ?>"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
                </div>
                <div class="form-group">
                    <label style="font-weight: bold;">Post Threshold</label>
                    <input type="number" name="pthreshold" value="<?= $_COOKIE['post_threshold'] ?? 0 ?>"
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label style="font-weight: bold;">My Tags</label>
                <p style="font-size: 0.9em; color: #666; margin-bottom: 0.5rem;">Accessible quickly when you add or edit
                    a post.</p>
                <textarea name="my_tags" rows="4"
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"><?= htmlspecialchars(str_replace('%20', ' ', $_COOKIE['tags'] ?? '')) ?></textarea>
            </div>

            <button type="submit"
                style="background: #007bff; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-weight: bold;">Save
                Options</button>
        </form>
    </main>
</div>