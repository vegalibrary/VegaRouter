<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'My Website' ?></title>
    <link rel="stylesheet" href="/public/style.css">
</head>
<body>
    <?= $component('header'); ?>
    <main>
        <?= $content; ?>
    </main>
</body>
</html>
