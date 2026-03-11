<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 800px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Tag Implications</h2>
        <p style="margin-bottom: 2rem; color: #666;">When a post is tagged with the predicate, the implied tag is
            automatically added to the post.</p>

        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f1f1f1;">
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc;">Predicate (If tag is...)
                </th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc;">Implication (Then add...)
                </th>
            </tr>
            <?php foreach ($implications as $row): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem;"><a
                            href="/index.php?page=post&s=list&tags=<?= urlencode($row['predicate']) ?>"><b><?= htmlspecialchars($row['predicate']) ?></b></a>
                    </td>
                    <td style="padding: 0.75rem;">&rarr; <a
                            href="/index.php?page=post&s=list&tags=<?= urlencode($row['implication']) ?>"><?= htmlspecialchars($row['implication']) ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>