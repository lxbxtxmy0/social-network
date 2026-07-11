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

            </div>
            <div class="memes">

            </div>
        </main>
    </div>
</body>
</html>