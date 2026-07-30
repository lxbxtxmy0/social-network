<?php

function renderLoginPage(): void
{
    session_name('auth');
    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: /home');
        exit;
    }
}

renderLoginPage();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/login/style.css" rel="stylesheet">
    <script src="/login/script.js" defer></script>
</head>
<body>
<div class="page_wrapper">
    <div class="login_container">

        <div class="logo_gradient"></div>
        <h1 class="page_title">Login to profile</h1>

        <div id="error_container"></div>

        <form method="POST" class="login_form" id="login_form">
            <input type="text" id="login_input" name="login" class="text_input" placeholder="login">

            <div class="password_wrapper">
                <input type="password" id="password_input" name="password" class="text_input" placeholder="password">
            </div>

            <button type="submit" class="submit_button">login to account</button>
        </form>

        <div class="register_prompt">
            <span>Don't have an account?</span>
            <a href="/registration" class="register_link">Create one.</a>
        </div>

    </div>
</div>
</body>
</html>