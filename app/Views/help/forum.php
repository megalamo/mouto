<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 800px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1>Help: Forum</h1>
        <p style="margin-bottom: 2rem;">All forum posts are sanitized, meaning all HTML is escaped. That being said,
            there are a few features you can use to format your posts:</p>

        <div class="section" style="margin-bottom: 1.5rem;">
            <h3>URLs</h3>
            <p>Any URL starting with <code>http://</code> or <code>https://</code> will automatically be converted into
                a clickable link.</p>
        </div>

        <div class="section" style="margin-bottom: 1.5rem;">
            <h3>Post Link Shorthand</h3>
            <p>You can link to specific image post/view pages using the following shorthand:
                <code>[post]nnnn[/post]</code>, where <em>nnnn</em> is the ID number of the image post.</p>
        </div>

        <div class="section" style="margin-bottom: 1.5rem;">
            <h3>Forum Link Shorthand</h3>
            <p>You can link to a specific forum board using the following shorthand: <code>[forum]nnnn[/forum]</code>,
                where <em>nnnn</em> is the ID number of the forum.</p>
        </div>

        <div class="section">
            <h3>Forum Post Link Shorthand</h3>
            <p>You can link to specific forum topics/posts using the following shorthand:
                <code>[forump]nnnn[/forump]</code>, where <em>nnnn</em> is the ID number of the forum post.</p>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>