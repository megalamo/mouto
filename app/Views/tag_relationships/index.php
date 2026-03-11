<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 600px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1.5rem;">Tag Relationships</h2>

        <form method="get" action="/index.php" style="margin-bottom: 2rem; display: flex; gap: 0.5rem;">
            <input type="hidden" name="page" value="tags" />
            <input type="hidden" name="s" value="related" />
            <input type="text" name="tag" value="<?= htmlspecialchars($targetTag) ?>" placeholder="Enter a tag..."
                style="flex: 1; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
            <button type="submit"
                style="padding: 0.5rem 1rem; background: #007bff; color: white; border: none; border-radius: 4px;">Calculate</button>
        </form>

        <?php if ($targetTag): ?>
            <h4 style="margin-bottom: 1rem;">Tags frequently used with "
                <?= htmlspecialchars($targetTag) ?>"
            </h4>
            <ul style="list-style: none; padding: 0; line-height: 1.8;">
                <?php foreach ($related as $relTag => $frequency): ?>
                    <li
                        style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 0.25rem 0;">
                        <a href="/index.php?page=post&s=list&tags=<?= urlencode($relTag) ?>">
                            <?= htmlspecialchars($relTag) ?>
                        </a>
                        <span style="color: #888; font-size: 0.9em;">
                            <?= $frequency ?> shared posts
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>