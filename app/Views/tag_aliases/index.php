<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 800px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h2 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">Tag Aliases</h2>
        <p style="margin-bottom: 2rem; color: #666;">Aliases automatically redirect an incorrect or alternate tag to the
            primary, standardized tag.</p>

        <?php if (!empty($success)): ?>
            <div
                style="color: #155724; background: #d4edda; padding: 1rem; border: 1px solid #c3e6cb; border-radius: 4px; margin-bottom: 1.5rem;">
                Alias suggestion submitted and is pending administrator approval.
            </div>
        <?php endif; ?>

        <form method="post" action="/index.php?page=tags&s=aliases"
            style="margin-bottom: 2rem; background: #f8f9fa; padding: 1.5rem; border-radius: 6px;">
            <h4 style="margin-bottom: 1rem;">Suggest an Alias</h4>
            <div style="display: flex; gap: 1rem;">
                <div style="flex: 1;">
                    <label style="font-weight: bold; font-size: 0.9em;">Alias (What users type)</label>
                    <input type="text" name="alias" required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"
                        placeholder="e.g. boku_no_hero_academia" />
                </div>
                <div style="flex: 1;">
                    <label style="font-weight: bold; font-size: 0.9em;">Target Tag (What it should be)</label>
                    <input type="text" name="tag" required
                        style="width: 100%; padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;"
                        placeholder="e.g. my_hero_academia" />
                </div>
                <div style="display: flex; align-items: flex-end;">
                    <button type="submit"
                        style="background: #007bff; color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 4px; cursor: pointer;">Suggest</button>
                </div>
            </div>
        </form>

        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f1f1f1;">
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc;">Alias</th>
                <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #ccc;">Redirects To</th>
            </tr>
            <?php foreach ($aliases as $row): ?>
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 0.75rem;"><b><?= htmlspecialchars($row['alias']) ?></b></td>
                    <td style="padding: 0.75rem;"><a
                            href="/index.php?page=post&s=list&tags=<?= urlencode($row['tag']) ?>"><?= htmlspecialchars($row['tag']) ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>