<?php require_once __DIR__ . '/../header.php'; ?>

<div class="app-container" style="justify-content: center; display: flex;">
    <main class="content"
        style="max-width: 800px; width: 100%; background: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <h1>Help: Posts</h1>
        <p style="margin-bottom: 2rem;">A post represents a single file that's been uploaded. Each post can have several
            tags, comments, and notes. If you have an account, you can also add a post to your favorites.</p>

        <div class="section" style="margin-bottom: 2rem;">
            <h3>Search</h3>
            <p>Searching for posts is straightforward. Simply enter the tags you want to search for, separated by
                spaces. For example, searching for <code>original panties</code> will return every post that has both
                the original tag <strong>AND</strong> the panties tag.</p>
        </div>

        <div class="section">
            <h3>Tag List</h3>
            <p>In both the listing page and the show page you'll notice a list of tag links with characters next to
                them. Here's an explanation of what they do:</p>
            <dl style="margin-top: 1rem; line-height: 1.6;">
                <dt style="font-weight: bold; margin-top: 0.5rem;">+</dt>
                <dd style="margin-left: 1.5rem;">This adds the tag to the current search.</dd>

                <dt style="font-weight: bold; margin-top: 0.5rem;">&ndash;</dt>
                <dd style="margin-left: 1.5rem;">This adds the negated tag to the current search.</dd>

                <dt style="font-weight: bold; margin-top: 0.5rem;">Number (e.g., 950)</dt>
                <dd style="margin-left: 1.5rem;">The number next to the tag represents how many posts there are. This
                    isn't always the total number of posts for that tag. It may be slightly out of date as cache isn't
                    always refreshed.</dd>
            </dl>
            <p style="margin-top: 1.5rem;">When you're not searching for a tag, by default the tag list will show the
                last few tags added to the database. When you are searching for tags, the tag list will show related
                tags, alphabetically.</p>
        </div>
    </main>
</div>

<?php require_once __DIR__ . '/../footer.php'; ?>