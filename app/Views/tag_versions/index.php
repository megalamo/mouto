<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 900px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
            Tag History for Post #
            <?= htmlspecialchars($id) ?>
        </h2>

        <div style="margin-bottom: 2rem;">
            <a href="/index.php?page=post&s=view&id=<?= htmlspecialchars($id) ?>">&laquo; Back to Post</a>
        </div>

        <table style="width: 100%; border-collapse: collapse; font-size: 0.95em;">
            <tr style="background: #f1f1f1;">
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc; width: 60%;">Tags</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc;">Editor</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc;">Action</th>
            </tr>
            <?php foreach ($history as $row): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem; line-height: 1.6; word-break: break-all;">
                        <?php
                        $tags = array_filter(explode(' ', trim($row['tags'])));
                        foreach ($tags as $tag):
                            ?>
                            <a href="/index.php?page=post&s=list&tags=<?= urlencode($tag) ?>"
                                style="display: inline-block; margin-right: 0.5rem;">
                                <?= htmlspecialchars($tag) ?>
                            </a>
                        <?php endforeach; ?>
                    </td>
                    <td style="padding: 0.75rem;">
                        <?= htmlspecialchars($row['username']) ?>
                    </td>
                    <td style="padding: 0.75rem;">
                        <?php if ($can_revert): ?>
                            <a href="/index.php?page=history&type=page_tags&id=<?= htmlspecialchars($id) ?>&version=<?= $row['version'] ?>"
                                style="color: #dc3545; font-weight: bold;">Revert to v
                                <?= $row['version'] ?>
                            </a>
                        <?php else: ?>
                            <span style="color: #888;">v
                                <?= $row['version'] ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>