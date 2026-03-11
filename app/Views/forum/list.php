<div class="app-container" style="display: block;">
    <main class="content"
        style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <h2>Forum Index</h2>
            <form method="post" action="/index.php?page=forum&amp;s=search" style="display: flex; gap: 0.5rem;">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search posts..."
                    style="padding: 0.5rem; border: 1px solid #ccc; border-radius: 4px;" />
                <button type="submit">Search</button>
            </form>
        </div>

        <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem;">
            <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                <th style="padding: 1rem; text-align: left;">Title</th>
                <th style="padding: 1rem; text-align: left;">Created by</th>
                <th style="padding: 1rem; text-align: left;">Updated</th>
                <th style="padding: 1rem; text-align: left;">Replies</th>
                <?php if ($userModel->gotpermission('is_admin')): ?>
                    <th style="padding: 1rem; text-align: left;">Admin</th>
                <?php endif; ?>
            </tr>

            <?php foreach ($topics as $row): ?>
                <tr style="border-bottom: 1px solid #eee; <?= $row['priority'] ? 'background: #fffdf2;' : '' ?>">
                    <td style="padding: 1rem;">
                        <?= $row['priority'] ? '📌 ' : '' ?>
                        <?= $row['locked'] ? '🔒 ' : '' ?>
                        <a href="/index.php?page=forum&s=view&id=<?= $row['id'] ?>">
                            <?= htmlspecialchars($row['topic']) ?>
                        </a>
                    </td>
                    <td style="padding: 1rem;">
                        <?= htmlspecialchars($row['author']) ?>
                    </td>
                    <td style="padding: 1rem;">
                        <?= date("Y-m-d H:i", $row['last_updated']) ?>
                    </td>
                    <td style="padding: 1rem;">
                        <?= $row['post_count'] - 1 ?>
                    </td>

                    <?php if ($userModel->gotpermission('is_admin')): ?>
                        <td style="padding: 1rem; font-size: 0.9em;">
                            <?php if ($row['priority']): ?>
                                <a href="?page=forum&s=edit&pin=0&id=<?= $row['id'] ?>">Unpin</a> |
                            <?php else: ?>
                                <a href="?page=forum&s=edit&pin=1&id=<?= $row['id'] ?>">Pin</a> |
                            <?php endif; ?>
                            <a href="?page=forum&s=remove&fid=<?= $row['id'] ?>" style="color: red;">Delete</a>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="paginator" style="margin-bottom: 2rem;">
            <?= $misc->pagination('forum', 'list', false, $limit, 6, $totalTopics, $currentPage, '', $search) ?>
        </div>

        <?php if ($userModel->isLoggedIn()): ?>
            <a href="#" onclick="document.getElementById('new_topic').style.display='block'; return false;"
                style="font-weight: bold;">+ New Topic</a>

            <form method="post" action="/index.php?page=forum&amp;s=add" id="new_topic"
                style="display:none; margin-top: 1.5rem; background: #f9f9f9; padding: 1.5rem; border-radius: 6px;">
                <div class="form-group">
                    <label>Topic Title:</label>
                    <input type="text" name="topic" required style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;" />
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Post Body:</label>
                    <textarea name="post" rows="6" required
                        style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;"></textarea>
                </div>
                <button type="submit"
                    style="margin-top: 1rem; background: #28a745; color: white; border: none; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px;">Create
                    Topic</button>
            </form>
        <?php endif; ?>

    </main>
</div>