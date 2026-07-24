<?php
session_name('auth');
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

require_once '../database/database.php';
$connection = connectDatabase();
$userId = $_SESSION['user_id'];
$balanceAmount = getUserBalance($connection, $userId);
$userLogin = getLoginById($connection, $userId);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="style.css" rel="stylesheet">
    <script src="script.js" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create meme</title>
</head>
<body>
<div class="page">
    <nav class="nav">
        <a href="../home" class="nav_item">
            <img src="../src/img/home.svg" alt="Home">
        </a>
        <a href="../creatememe" class="nav_item active">
            <img src="../src/img/is_creatememe.svg" alt="Add">
        </a>
        <a href="../<?= htmlspecialchars($userLogin) ?>" class="nav_item">
            <img src="../src/img/profile.svg" alt="Profile">
        </a>
        <div class="nav_balance">
            <span class="balance_value"><?= $balanceAmount ?></span>
            <span class="balance_label">Balance</span>
        </div>
    </nav>

    <main class="main_content">
        <div class="top_bar">
            <div class="top_bar_center">
                <span class="balance_label">balance</span>
                <span class="balance_value"><?= $balanceAmount ?></span>
            </div>
            <button type="submit" form="meme_form" class="submit_arrow">
                <img src="../src/img/arrow_right.svg" alt=">">
            </button>
        </div>

        <div id="error_container"></div>

        <form class="form" id="meme_form">
            <div class="upload_box">
                <input type="file" accept="image/jpeg, image/png, image/gif" class="file_input" id="file_input" hidden>
                <label for="file_input" class="upload_label">
                    <img src="../src/img/upload_icon.svg" alt="" class="upload_icon">
                    <span class="upload_text">upload meme</span>
                </label>
            </div>

            <button type="button" class="add_button">add photo</button>

            <div class="fields">
                <input type="text" id="title" name="title" placeholder="title">
                <textarea id="description" name="description" placeholder="description"></textarea>
                <input type="text" id="coins" name="coins" placeholder="invest">
                <button type="submit" class="submit_desktop">publish a post</button>
            </div>
        </form>
    </main>
</div>
</body>
</html>