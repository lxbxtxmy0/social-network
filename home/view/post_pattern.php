<div class="post">
    <div class="head">
        <img src="<?= $meme['avatar_source'] ?>" width="20" height="20">
        <a href="../<?= $meme['login'] ?>"><?= $meme['name'] . ' ' . $meme['surname'] ?></a>
    </div>
    <div class="photos">
        <?php foreach ($meme['images'] as $image): ?>
        <img src="<?= htmlspecialchars($image) ?>" alt="Фото" width="160" height="160">
        <?php endforeach; ?>
    </div>
    <p class="description"><?= $meme['description'] ?></p>
    <span class="published_at"><?= $meme['published_at'] ?></span>
    <p class="investments"><?= $meme['investments'] ?></p>
</div>