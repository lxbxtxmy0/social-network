<?php

function renderRegistrationPage(): void
{
    session_name('auth');
    session_start();

    if (isset($_SESSION['user_id'])) {
        header('Location: /home');
        exit;
    }
}

renderRegistrationPage();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <title>Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/registration/style.css" rel="stylesheet">
    <script src="/registration/script.js" defer></script>
</head>
<body>
<div class="page_wrapper">
    <div class="registration_container">

        <h1 class="page_title">Create an account</h1>

        <div id="error_container"></div>

        <form method="POST" class="registration_form" id="registration_form">

            <div class="avatar_upload_box" id="avatar_upload_box">
                <input type="file" id="avatar_input" name="avatar" accept="image/jpeg, image/png, image/jpg" hidden>
                <div class="avatar_placeholder" id="avatar_placeholder">
                    <img src="/src/img/upload_icon.svg" alt="Upload" class="upload_icon">
                </div>
            </div>

            <input type="text" id="name_input" name="name" class="text_input" placeholder="name">
            <input type="text" id="surname_input" name="surname" class="text_input" placeholder="surname">
            <input type="text" id="login_input" name="login" class="text_input" placeholder="login">

            <div class="password_wrapper">
                <input type="password" id="password_input" name="password" class="text_input" placeholder="password">
            </div>

            <button type="submit" class="submit_button">register</button>
        </form>

        <div class="login_prompt">
            <span>Already have an account?</span>
            <a href="/login" class="login_link">Log in</a>
        </div>

    </div>
</div>
</body>
</html>