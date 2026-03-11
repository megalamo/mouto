<div class="app-container" style="display: block;">
    <main class="content">

        <div style="margin-bottom: 1.5rem; font-size: 0.9em;">
            <a href="/index.php?page=forum&s=list">&laquo; Back to Forum Index</a>

            <?php if ($userModel->gotpermission('lock_forum_topics')): ?>
                | <a
                    href="/index.php?page=forum&s=edit&lock=<?= $topic['locked'] ? 'false' : 'true' ?>&id=<?= $topic['id'] ?>">
                    <?= $topic['locked'] ? 'Unlock Topic' : 'Lock Topic' ?>
                </a>
            <?php endif; ?>
        </div>

        <?php foreach ($posts as $row): ?>
            <div id="<?= $row['id'] ?>" class="forum-post"
                style="background: #fff; padding: 1.5rem; margin-bottom: 1rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

                <div
                    style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding-bottom: 0.5rem; margin-bottom: 1rem; font-size: 0.9em; color: #666;">
                    <div>
                        <b>
                            <?= htmlspecialchars($row['author']) ?>
                        </b>
                        (
                        <?= date("Y-m-d H:i:s", $row['creation_date']) ?>)
                    </div>
                    <div>
                        <?php if ($userModel->gotpermission('delete_forum_posts') || $_COOKIE['user'] === $row['author']): ?>
                            <a href="/index.php?page=forum&s=remove&pid=<?= $topic['id'] ?>&cid=<?= $row['id'] ?>"
                                style="color: red;">Delete</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="post-body" style="line-height: 1.6;">
                    <?php
                    // Process shortcodes logic from old procedural code
                    $body = htmlspecialchars_decode($row['post']);
                    $body = preg_replace('/\[post\](.*?)\[\/post\]/is', '<a href="index.php?page=post&s=view&id=$1">post #$1</a>', $body);
                    $body = preg_replace('/\[forum\](.*?)\[\/forum\]/is', '<a href="index.php?page=forum&s=view&id=$1">forum #$1</a>', $body);
                    $body = preg_replace('/\[forump\](.*?)\[\/forump\]/is', '<a href="index.php?page=forum&s=post&id=$1">forum post #$1</a>', $body);
                    echo nl2br($body);
                    ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div class="paginator" style="margin-bottom: 2rem;">
            <?= $misc->pagination('forum', 'view', $topic['id'], $limit, 6, $totalPosts, $currentPage) ?>
        </div>

        <?php if (!$topic['locked'] && $userModel->isLoggedIn()): ?>
            <a href="#" onclick="document.getElementById('reply_box').style.display='block'; return false;"
                style="font-weight: bold;">+ Post Reply</a>

            <form method="post" action="/index.php?page=forum&amp;s=add&amp;t=post&amp;pid=<?= $topic['id'] ?>"
                id="reply_box"
                style="display:none; margin-top: 1.5rem; background: #f9f9f9; padding: 1.5rem; border-radius: 6px;">
                <input type="hidden" name="conf" value="1" />
                <div class="form-group">
                    <label>Title (Optional):</label>
                    <input type="text" name="title" style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;" />
                </div>
                <div class="form-group" style="margin-top: 1rem;">
                    <label>Body:</label>
                    <textarea name="post" rows="6" required
                        style="width: 100%; padding: 0.5rem; margin-top: 0.25rem;"></textarea>
                </div>
                <button type="submit"
                    style="margin-top: 1rem; background: #007bff; color: white; border: none; padding: 0.5rem 1rem; cursor: pointer; border-radius: 4px;">Submit
                    Reply</button>
            </form>
        <?php elseif ($topic['locked']): ?>
            <p style="color: #dc3545; font-weight: bold;">🔒 This topic is locked. You cannot reply.</p>
        <?php endif; ?>

    </main>
</div>