<?php

require_once '../database/database.php';

function processLogin(): void
{
    $connectionDatabase = connectDatabase();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => 'Только POST запросы'], JSON_UNESCAPED_UNICODE));
    }

    $userLogin = $_POST['login'] ?? null;
    $userPassword = $_POST['password'] ?? null;

    if (!$userLogin || !$userPassword) {
        http_response_code(400);
        die(json_encode(['error' => 'Не указаны нужные поля'], JSON_UNESCAPED_UNICODE));
    }

    $queryUser = "SELECT id, password FROM user WHERE login = :login";
    $statementUser = $connectionDatabase->prepare($queryUser);
    $statementUser->execute(['login' => $userLogin]);
    $userData = $statementUser->fetch(PDO::FETCH_ASSOC);

    if (!$userData) {
        http_response_code(401);
        die(json_encode(['error' => 'Пользователя не существует'], JSON_UNESCAPED_UNICODE));
    }

    if (!password_verify($userPassword, $userData['password'])) {
        http_response_code(403);
        die(json_encode(['error' => 'Неверно указан пароль'], JSON_UNESCAPED_UNICODE));
    }

    session_name('auth');
    session_start();

    $_SESSION['user_id'] = $userData['id'];

    http_response_code(200);
    echo json_encode(['message' => 'Успех'], JSON_UNESCAPED_UNICODE);
}

processLogin();