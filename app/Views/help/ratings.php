<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 800px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1>Help: Ratings</h1>

        <div class="section" style="margin-bottom: 2rem;">
            <p>All posts on
                <strong><?= htmlspecialchars($config['app']['name'] ?? 'Default Booru', ENT_QUOTES, 'UTF-8') ?></strong>
                have one of three types of ratings: Safe, Questionable, and Explicit. Questionable is the default rating
                if you do not choose to specify one. <strong>Please take note</strong>: occasionally explicit images
                will be marked safe, and vice versa. You should not depend completely on rating filters unless you can
                tolerate the occasional wrongly rated image. If this happens to you, fix it so that other users don't
                have that happen to them.</p>
        </div>

        <div class="section" style="margin-bottom: 1.5rem;">
            <h3 style="color: #d9534f;">Explicit</h3>
            <p>Any image where the vagina or penis are exposed and easily visible. This includes depictions of sex,
                masturbation, or any sort of penetration.</p>
        </div>

        <div class="section" style="margin-bottom: 1.5rem;">
            <h3 style="color: #5cb85c;">Safe</h3>
            <p>Safe posts are images that you would not feel guilty looking at openly in public. Pictures of nudes,
                exposed nipples or pubic hair, cameltoe, or any sort of sexually suggestive pose are <b>NOT</b> safe and
                belong in questionable. Swimsuits and lingerie are borderline cases; some are safe, some are
                questionable.</p>
        </div>

        <div class="section" style="margin-bottom: 2rem;">
            <h3 style="color: #f0ad4e;">Questionable</h3>
            <p>Basically anything that isn't safe or explicit. This is the great middle area, and since it includes
                unrated posts, you shouldn't really expect anything one way or the other when browsing questionable
                posts.</p>
        </div>

        <div class="section" style="border-top: 1px solid #eee; padding-top: 1.5rem;">
            <h3>Search Filtering</h3>
            <p>You can filter search results by querying for <code>rating:safe</code>, <code>rating:questionable</code>,
                or <code>rating:explicit</code>. You can also combine them with other tags and they work as expected.
            </p>
            <p>If you want to remove a rating from your search results, use <code>-rating:safe</code>,
                <code>-rating:questionable</code>, and <code>-rating:explicit</code>.</p>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>