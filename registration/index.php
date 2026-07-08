<?php

session_name('auth');
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /home');
    exit;
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Registration</title>
    <link href="style.css" rel="stylesheet">
    <script src="script.js"></script>
</head>
<body>
<div class="page">
    <h1>Регистрация нового профиля</h1>
    <form class="form">
        <label for="avatar">Аватар</label>
        <input type="file" id="avatar" name="avatar" accept="image/*">
        <label for="name">Имя</label>
        <input type="text" id="name" name="name">
        <label for="surname">Фамилия</label>
        <input type="text" id="surname" name="surname">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login">
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password">
        <button type="submit" class="register_button">Зарегистрироваться</button>
    </form>
</div>
</body>
</html>