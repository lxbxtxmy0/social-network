<?php

session_name('auth');
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}


require_once '../database.php';
$connection = connectDatabase();
$memes = getLastMemes($connection);

$userId = $_SESSION['user_id'];
$balance = getUserBalance($connection, $userId);

?>


<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="style.css" rel="stylesheet">
    <script src="script.js"></script>
    <title>Home</title>
</head>
<body>
<div class="page">
    <header>
        <p>баланс <?= $balance ?></p>
    </header>
    <main>
        <?php
            foreach ($memes as $meme) {
                include 'view/post_pattern.php';
            }
        ?>
    </main>
</div>

</body>
</html>