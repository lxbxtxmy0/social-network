<?php

function initializeTopPage(): array
{
    session_name('auth');
    session_start();

    $userIdentifier = $_SESSION['user_id'] ?? null;

    if (empty($userIdentifier)) {
        header('Location: /login');
        exit;
    }

    require_once '../database/database.php';
    $connectionDatabase = connectDatabase();

    $topMemes = getTopMemes($connectionDatabase);
    $login = getLoginById($connectionDatabase, $userIdentifier);
    $balance = getUserBalance($connectionDatabase, $userIdentifier);

    return [$topMemes, $login, $balance];
}

[$topMemes, $login, $balance] = initializeTopPage();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="/top/style.css" rel="stylesheet">
    <script src="/top/script.js" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top 100 Memes</title>
</head>
<body>
<div class="page_wrapper">
    <nav class="navigation_bar">
        <a href="/home" class="navigation_item">
            <img src="/src/img/home.svg" alt="Home">
        </a>
        <a href="/top" class="navigation_item active">
            <img src="/src/img/is_top.svg" alt="Top" style="width: 30px; height: 30px;">
        </a>
        <a href="/creatememe" class="navigation_item">
            <img src="/src/img/creatememe.svg" alt="Add">
        </a>
        <a href="/<?= htmlspecialchars($login ?? '') ?>" class="navigation_item">
            <img src="/src/img/profile.svg" alt="Profile">
        </a>
    </nav>

    <main class="main_content">
        <div class="top_container">
            <h1 class="page_title">Top 100 Memes</h1>

            <div class="posts_grid">
                <?php $rankIndex = 1; ?>
                <?php foreach ($topMemes as $memeItem): ?>
                    <?php
                    $rankClass = '';
                    if ($rankIndex <= 3) {
                        $rankClass = 'top_three';
                    }
                    ?>
                    <div class="post_card" data-meme-identifier="<?= htmlspecialchars($memeItem['id']) ?>">
                        <?php if ($memeItem['image']): ?>
                            <img src="<?= htmlspecialchars(trim($memeItem['image'])) ?>" alt="Post">
                        <?php endif; ?>

                        <div class="rank_badge <?= $rankClass ?>">
                            #<?= $rankIndex ?>
                        </div>

                        <div class="cap_badge">
                            cap $<?= htmlspecialchars($memeItem['investments']) ?>
                        </div>
                    </div>
                    <?php $rankIndex++; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
</div>

<div id="meme_modal_window" class="modal_window">
    <div class="modal_content">
        <div class="modal_left_side">
            <div class="modal_image_container">
                <img src="" alt="Meme" id="modal_main_image">
            </div>
        </div>

        <div class="modal_right_side">
            <div class="modal_header">
                <div class="modal_header_information">
                    <img src="" alt="avatar" id="modal_avatar">
                    <span id="modal_author_name"></span>
                </div>
                <button id="modal_close_button">&times;</button>
            </div>

            <h1 class="modal_title" id="modal_title"></h1>
            <div class="investment_capitalization" id="modal_capitalization"></div>
            <div class="modal_description" id="modal_description"></div>
            <div class="modal_date" id="modal_date"></div>
        </div>
    </div>
</div>
</body>
</html>