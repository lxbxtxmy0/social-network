<?php

session_name('auth');
session_start();
$userId = $_SESSION['user_id'];

if (empty($userId)) {
    header('Location: /login');
    exit;
}

$URI = $_SERVER['REQUEST_URI'];
$path = parse_url($URI, PHP_URL_PATH);
$login = trim($path, '/');

if (!$login) {
    header('Location: /home');
    exit;
}

require_once 'database.php';
$connection = connectDatabase();

$profileId = getUserIdByLogin($connection, $login);
if (!$profileId) {
    die('Профиль не найден');
}

$admin = $profileId === $userId;

[$personalData, $investments, $memes] = getProfileData($connection, $profileId);

require_once 'profile/index.php';