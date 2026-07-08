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
    <title>Login</title>
    <link href="style.css" rel="stylesheet">
    <script src="script.js"></script>
</head>
<body>
<div class="page">
    <h1>Логинация</h1>
    <form class="form">
        <label for="login">Логин</label>
        <input type="text" id="login" name="login">
        <label for="password">Пароль</label>
        <input type="password" id="password" name="password">
        <button type="submit">Войти</button>
    </form>
</div>
</body>
</html>