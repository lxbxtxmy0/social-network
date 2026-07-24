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
    <script src="script.js" defer></script>
</head>
<body>
<div class="page_wrapper">
    <h1 class="page_title">Регистрация нового профиля</h1>
    <div id="error_container"></div>
    <form method="POST" class="registration_form" id="registration_form">
        <label for="avatar_input" class="input_label">Аватар</label>
        <input type="file" id="avatar_input" name="avatar" accept="image/jpeg, image/png, image/jpg" class="file_input">

        <label for="name_input" class="input_label">Имя</label>
        <input type="text" id="name_input" name="name" class="text_input">

        <label for="surname_input" class="input_label">Фамилия</label>
        <input type="text" id="surname_input" name="surname" class="text_input">

        <label for="login_input" class="input_label">Логин</label>
        <input type="text" id="login_input" name="login" class="text_input">

        <label for="password_input" class="input_label">Пароль</label>
        <input type="password" id="password_input" name="password" class="text_input">

        <button type="submit" class="submit_button">Зарегистрироваться</button>
    </form>
</div>
</body>
</html>