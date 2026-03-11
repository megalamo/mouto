<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Default Booru</title>
        <link rel="stylesheet" href="/public/css/application.css">
        <script src="/public/js/application.js" defer></script>
    </head>

    <body>
        <?php require dirname(__DIR__) . './partials/header.php'; ?>

        <div id="content">
            <div id="post-list"></div>
        </div>

        <?php require dirname(__DIR__) . './partials/footer.php'; ?>
    </body>
</html>