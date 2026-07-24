<?php
$profileIconString = 'profile.svg';

if ($admin) {
    $profileIconString = 'is_profile.svg';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="/profile/style.css" rel="stylesheet">
    <script src="/profile/script.js" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(($personalData['name'] ?? '') . ' ' . ($personalData['surname'] ?? '')) ?></title>
</head>
<body>
<div class="page_wrapper">
    <nav class="navigation_bar">
        <a href="/home" class="navigation_item">
            <img src="/src/img/home.svg" alt="Home">
        </a>
        <a href="/creatememe" class="navigation_item">
            <img src="/src/img/creatememe.svg" alt="Add">
        </a>
        <a href="/<?= htmlspecialchars($login ?? '') ?>" class="navigation_item active">
            <img src="/src/img/<?= $profileIconString ?>" alt="Profile">
        </a>
        <div class="navigation_balance">
            <span class="balance_value"><?= htmlspecialchars($balance ?? ($personalData['balance'] ?? 0)) ?></span>
            <span class="balance_label">Balance</span>
        </div>
    </nav>
    <main class="main_content">
        <div class="profile_container">
            <div class="profile_header">
                <img src="<?= htmlspecialchars($personalData['avatar_source'] ?? '') ?>" class="profile_avatar" alt="Avatar">
                <h1 class="profile_name"><?= htmlspecialchars(($personalData['name'] ?? '') . ' ' . ($personalData['surname'] ?? '')) ?></h1>
                <?php if (!empty($personalData['bio'])): ?>
                    <p class="profile_biography"><?= htmlspecialchars($personalData['bio']) ?></p>
                <?php endif; ?>
            </div>
            <?php if (!empty($investments)): ?>
                <div class="profile_section">
                    <h2 class="section_title">Top investment</h2>
                    <div class="investments_list">
                        <?php foreach ($investments as $investmentItem): ?>
                            <div class="investment_card" data-meme-identifier="<?= htmlspecialchars($investmentItem['id'] ?? '') ?>">
                                <div class="investment_image_box">
                                    <?php if (!empty($investmentItem['image'])): ?>
                                        <img src="<?= htmlspecialchars(trim($investmentItem['image'])) ?>" alt="Investment">
                                    <?php endif; ?>
                                </div>
                                <div class="investment_statistics">
                                    <span class="investment_green">$<?= htmlspecialchars($investmentItem['investments'] ?? 0) ?></span>
                                    <?php if (($investmentItem['earned'] ?? 0) > 0): ?>
                                        <span class="investment_purple">+<?= htmlspecialchars($investmentItem['earned']) ?></span>
                                    <?php else: ?>
                                        <span class="investment_purple" style="color: #626168;">+0</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (!empty($memes)): ?>
                <div class="profile_section">
                    <h2 class="section_title">My posts</h2>
                    <div class="posts_grid">
                        <?php foreach ($memes as $memeItem): ?>
                            <div class="post_card" data-meme-identifier="<?= htmlspecialchars($memeItem['id'] ?? '') ?>">
                                <?php if (!empty($memeItem['image'])): ?>
                                    <img src="<?= htmlspecialchars(trim($memeItem['image'])) ?>" alt="Post">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
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