<?php

session_name('auth');
session_start();

if (empty($_SESSION['user_id'])) {
    header('Location: /login');
    exit;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link href="style.css" rel="stylesheet">
    <script src="script.js"></script>
    <title>Create meme</title>
</head>
<body>
<div class="page">
    <h1>Создание мема</h1>
    <input type="file" accept="image/jpeg, image/png, image.jpg"" class="file_input">
    <form class="form">
        <label for="title">Заголовок</label>
        <input type="text" id="title" name="title">
        <button type="button" class="add_button">Добавить фото</button>
        <label for="description">Описание</label>
        <textarea id="description" name="description"></textarea>
        <label for="coins">Сколько инвестировать</label>
        <input type="text" id="coins" name="coins">
        <button type="submit">Создать</button>
    </form>
</div>
</body>
</html>