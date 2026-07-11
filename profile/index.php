<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="style.css" rel="stylesheet">
    <script src="script.js"></script>
    <title>Profile</title>
</head>
<body>
<div class="page">
    <header>

    </header>
    <main>
        <div class="author_info">
            <img src="<?= $personalData['avatar_source'] ?>" width="100" height="100">
            <span><?= $personalData['name'] . ' ' . $personalData['surname'] ?></span>
            <span><?= $personalData['balance'] ?></span>
            <p><?= $personalData['bio'] ?></p>
        </div>
        <div class="investments">
            <?php
            foreach ($investments as $investment) {
                include 'view/investment_pattern.php';
            }
            ?>
        </div>
        <div class="memes">
            <?php
            foreach ($memes as $meme) {
                include 'view/meme_preview_pattern.php';
            }
            ?>
        </div>
    </main>
</div>
</body>
</html>