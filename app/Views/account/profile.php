<div class="app-container" style="display: block;">
    <main class="content"
        style="background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">

        <h2 style="margin-bottom: 1rem; border-bottom: 1px solid #eee; padding-bottom: 0.5rem;">
            <?= htmlspecialchars($profile['user']) ?>
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem;">
            <div>
                <p><b>Join Date:</b>
                    <?= htmlspecialchars($profile['signup_date']) ?>
                </p>
                <p><b>Group:</b>
                    <?= htmlspecialchars($profile['group_name']) ?>
                </p>
                <p><b>Record Score:</b>
                    <?= $profile['record_score'] ?>
                </p>
            </div>
            <div>
                <p><b>Submissions:</b>
                    <?= $profile['post_count'] ?>
                </p>
                <p><b>Comments:</b>
                    <?= $profile['comment_count'] ?>
                </p>
                <p><b>Forum Posts:</b>
                    <?= $profile['forum_post_count'] ?>
                </p>
                <p><b>Tag Edits:</b>
                    <?= $profile['tag_edit_count'] ?>
                </p>
            </div>
        </div>

        <h4 style="margin-bottom: 1rem;">Recent Submissions <a
                href="/index.php?page=post&amp;s=list&amp;tags=user:<?= urlencode($profile['user']) ?>">&raquo;</a></h4>
        <div class="image-grid" style="margin-bottom: 2rem;">
            <?php foreach ($recentPosts as $prow): ?>
                <div class="thumb-item">
                    <a href="/index.php?page=post&amp;s=view&amp;id=<?= $prow['id'] ?>">
                        <img src="<?= $config['app']['thumbnail_url'] . $prow['dir'] ?>/thumbnail_<?= $prow['image'] ?>"
                            alt="Submission" />
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

        <h4 style="margin-bottom: 1rem;">Recent Favorites <a
                href="/index.php?page=favorites&amp;s=view&amp;id=<?= $profile['id'] ?>">&raquo;</a></h4>
        <div class="image-grid">
            <?php foreach ($recentFavs as $frow): ?>
                <div class="thumb-item">
                    <a href="/index.php?page=post&amp;s=view&amp;id=<?= $frow['favorite'] ?>">
                        <img src="<?= $config['app']['thumbnail_url'] . $frow['dir'] ?>/thumbnail_<?= $frow['image'] ?>"
                            alt="Favorite" />
                    </a>
                </div>
            <?php endforeach; ?>
        </div>

    </main>
</div>