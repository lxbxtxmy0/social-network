<?php

function initializeHomePage(): array
{
    session_name('auth');
    session_start();

    if (empty($_SESSION['user_id'])) {
        header('Location: /login');
        exit;
    }

    require_once '../database/database.php';
    $connectionDatabase = connectDatabase();
    $userIdentifier = $_SESSION['user_id'];

    if (!isset($_SESSION['viewed_memes'])) {
        $_SESSION['viewed_memes'] = [];
    }

    $meme = getNextUnseenMeme($connectionDatabase, $_SESSION['viewed_memes']);

    if ($meme) {
        $_SESSION['viewed_memes'][] = $meme['id'];
    }

    $login = getLoginById($connectionDatabase, $userIdentifier);
    $balance = getUserBalance($connectionDatabase, $userIdentifier);

    $image = '';
    if ($meme) {
        if (!empty($meme['image'])) {
            $image = $meme['image'];
        }
    }

    return [$meme, $login, $balance, $image];
}

[$meme, $login, $balance, $image] = initializeHomePage();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="/home/style.css" rel="stylesheet">
    <script src="/home/script.js" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>
<div class="page_wrapper">
    <nav class="navigation_bar">
        <a href="/home" class="navigation_item active">
            <img src="/src/img/is_home.svg" alt="Home">
        </a>
        <a href="/top" class="navigation_item active">
            <img src="/src/img/top.svg" alt="Top" style="width: 30px; height: 30px;">
        </a>
        <a href="/creatememe" class="navigation_item">
            <img src="/src/img/creatememe.svg" alt="Add">
        </a>
        <a href="/<?= htmlspecialchars($login ?? '') ?>" class="navigation_item">
            <img src="/src/img/profile.svg" alt="Profile">
        </a>
        <div class="navigation_balance">
            <span class="balance_value"><?= htmlspecialchars($balance ?? 0) ?></span>
            <span class="balance_label">Balance</span>
        </div>
    </nav>

    <main class="main_content">
        <div class="post_container">
            <div class="post_card" id="current_post" data-meme-identifier="<?= htmlspecialchars($meme['id'] ?? '') ?>">
                <?php if ($meme): ?>
                    <div class="post_information">
                        <div class="header_information">
                            <img alt="avatar" src="<?= htmlspecialchars($meme['avatar_source'] ?? '') ?>" class="author_avatar">
                            <a href="/<?= htmlspecialchars($meme['login'] ?? '') ?>"><?= htmlspecialchars(($meme['name'] ?? '') . ' ' . ($meme['surname'] ?? '')) ?></a>
                        </div>
                        <h1 class="meme_title desktop_element"><?= htmlspecialchars($meme['title'] ?? '') ?></h1>
                        <p class="investments_badge desktop_element">cap <?= htmlspecialchars($meme['investments'] ?? 0) ?></p>
                    </div>

                    <div class="post_image_box">
                        <p class="investments_badge mobile_element">cap <?= htmlspecialchars($meme['investments'] ?? 0) ?></p>
                        <img src="<?= htmlspecialchars(trim($image)) ?>" alt="Фото" class="main_photo">
                    </div>

                    <h1 class="meme_title mobile_element text_center"><?= htmlspecialchars($meme['title'] ?? '') ?></h1>
                <?php else: ?>
                    <h1 class="meme_title empty_message">Мемы закончились! Приходите позже.</h1>
                <?php endif; ?>
            </div>

            <div class="action_buttons">
                <button type="button" class="action_button button_pass">
                    <img src="/src/img/arrow_left.svg" alt="<">
                    pass
                </button>
                <button type="button" class="action_button button_invest">
                    invest
                    <img src="/src/img/arrow_right.svg" alt=">">
                </button>
            </div>
        </div>
    </main>
</div>

<div id="meme_modal_window" class="modal_window">
    <div class="modal_content">
        <div class="modal_left_side">
            <div class="modal_slider_container" id="modal_slider_container"></div>
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
            <div class="investments_badge modal_capitalization" id="modal_capitalization"></div>
            <div class="modal_description" id="modal_description"></div>
            <div class="modal_date" id="modal_date"></div>
        </div>
    </div>
</div>

<div id="invest_modal_window" class="modal_window">
    <div class="invest_modal_box">
        <input type="number" id="invest_amount_input" placeholder="write the amount...">
        <button type="button" id="confirm_invest_button">invest</button>
    </div>
</div>
</body>
</html>