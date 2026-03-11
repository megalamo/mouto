<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 600px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <h2 style="margin-bottom: 1.5rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Upload a New Post</h2>

        <?php if (!empty($error)): ?>
            <div
                style="color: #d9534f; background: #fdf7f7; padding: 1rem; border: 1px solid #d9534f; border-radius: 4px; margin-bottom: 1.5rem;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="post" action="/index.php?page=post&amp;s=add" enctype="multipart/form-data">

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">File:</label>
                <input type="file" name="upload"
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Source URL (Optional):</label>
                <input type="text" name="source" placeholder="http://..."
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">Title (Optional):</label>
                <input type="text" name="title"
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <div class="form-group" style="margin-bottom: 1.5rem;">
                <label style="font-weight: bold; display: block; margin-bottom: 0.25rem;">Tags:</label>
                <p style="font-size: 0.85em; color: #666; margin-bottom: 0.5rem;">Separate tags with spaces (ex:
                    green_eyes purple_hair)</p>
                <input type="text" id="tags" name="tags"
                    style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            </div>

            <div class="form-group"
                style="margin-bottom: 2rem; background: #f8f9fa; padding: 1rem; border-radius: 4px; border: 1px solid #eee;">
                <label style="font-weight: bold; margin-right: 1rem;">Rating:</label>
                <label style="margin-right: 1rem;"><input type="radio" name="rating" value="e" /> Explicit</label>
                <label style="margin-right: 1rem;"><input type="radio" name="rating" value="q" checked />
                    Questionable</label>
                <label><input type="radio" name="rating" value="s" /> Safe</label>
            </div>

            <?php if (!empty($myTags)): ?>
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label style="font-weight: bold; display: block; margin-bottom: 0.5rem;">My Tags (Click to add):</label>
                    <div style="font-size: 0.9em;">
                        <?php
                        $tags = explode(" ", str_replace('%20', ' ', $myTags));
                        foreach ($tags as $current):
                            if (trim($current) !== ''):
                                ?>
                                <a href="#"
                                    onclick="document.getElementById('tags').value += '<?= htmlspecialchars($current) ?> '; return false;"
                                    style="display: inline-block; background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 4px; margin: 0.25rem; text-decoration: none; color: #333;">
                                    <?= htmlspecialchars($current) ?> +
                                </a>
                            <?php
                            endif;
                        endforeach;
                        ?>
                    </div>
                </div>
            <?php else: ?>
                <p style="font-size: 0.9em; margin-bottom: 2rem;"><a href="/index.php?page=account_options">Set up "My Tags"
                        in your account options for quick access.</a></p>
            <?php endif; ?>

            <button type="submit" name="submit"
                style="background: #28a745; color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 4px; cursor: pointer; font-weight: bold; font-size: 1.1em; width: 100%;">Upload
                Post</button>
        </form>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>