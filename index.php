<?php

function processMainRouter(): void
{
    session_name('auth');
    session_start();

    $userIdentifier = $_SESSION['user_id'] ?? null;

    if (empty($userIdentifier)) {
        header('Location: /login');
        exit;
    }

    $requestUri = $_SERVER['REQUEST_URI'];
    $parsedPath = parse_url($requestUri, PHP_URL_PATH);
    $userLogin = trim($parsedPath, '/');

    if (empty($userLogin)) {
        header('Location: /home');
        exit;
    }

    require_once 'database/database.php';
    $connectionDatabase = connectDatabase();

    $profileIdentifier = getUserIdByLogin($connectionDatabase, $userLogin);

    if (empty($profileIdentifier)) {
        die('Профиль не найден');
    }

    $isAdmin = ($profileIdentifier === $userIdentifier);
    $balanceAmount = getUserBalance($connectionDatabase, $userIdentifier);

    $profileData = getProfileData($connectionDatabase, $profileIdentifier);

    $personalData = $profileData[0] ?? [];
    $investments = $profileData[1] ?? [];
    $memes = $profileData[2] ?? [];
    $login = $userLogin;
    $balance = $balanceAmount;
    $admin = $isAdmin;

    $profileIconSource = 'profile.svg';

    if ($admin) {
        $profileIconSource = 'is_profile.svg';
    }

    require_once 'profile/index.php';
}

processMainRouter();