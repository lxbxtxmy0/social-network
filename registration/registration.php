<?php

require_once '../validation/validation.php';
require_once '../database/database.php';

function createUserRecord($connectionDatabase, $userName, $userSurname, $userLogin, $userPasswordHash, $avatarSourceString): int
{
    $createUserQuery = <<<SQL
        INSERT INTO user (name, surname, login, password, avatar_source)
        VALUES (:name, :surname, :login, :password, :avatar_source)
    SQL;

    $statementUser = $connectionDatabase->prepare($createUserQuery);
    $statementUser->execute([
        'name' => $userName,
        'surname' => $userSurname,
        'login' => $userLogin,
        'password' => $userPasswordHash,
        'avatar_source' => $avatarSourceString,
    ]);

    return (int)$connectionDatabase->lastInsertId();
}

function isLoginAvailable($connectionDatabase, $userLogin): bool
{
    $checkLoginQuery = "SELECT id FROM user WHERE login = :login";
    $statementCheck = $connectionDatabase->prepare($checkLoginQuery);
    $statementCheck->execute(['login' => $userLogin]);
    $userId = $statementCheck->fetchColumn();

    if ($userId) {
        return false;
    } else {
        return true;
    }
}

function processRegistration(): void
{
    $connectionDatabase = connectDatabase();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => 'Только POST запросы'], JSON_UNESCAPED_UNICODE));
    }

    $userName = $_POST['name'] ?? null;
    $userSurname = $_POST['surname'] ?? null;
    $userLogin = $_POST['login'] ?? null;
    $userPassword = $_POST['password'] ?? null;

    if (!$userName || !$userSurname || !$userLogin || !$userPassword) {
        http_response_code(400);
        die(json_encode(['error' => 'Не указаны все поля'], JSON_UNESCAPED_UNICODE));
    }

    if (!isLoginAvailable($connectionDatabase, $userLogin)) {
        http_response_code(400);
        die(json_encode(['error' => 'Логин уже занят'], JSON_UNESCAPED_UNICODE));
    }

    validateLogin($userLogin);
    validatePassword($userPassword);

    $userPasswordHash = password_hash($userPassword, PASSWORD_DEFAULT);
    $avatarFileArray = $_FILES['avatar'] ?? null;

    if (!$avatarFileArray || $avatarFileArray['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        die(json_encode(['error' => 'Не прикреплена аватарка'], JSON_UNESCAPED_UNICODE));
    }

    $directoryPath = '../src/img/';
    $fileNameString = uniqid() . '_' . basename($avatarFileArray['name']);
    $avatarSourceString = $directoryPath . $fileNameString;

    if (!move_uploaded_file($avatarFileArray['tmp_name'], $avatarSourceString)) {
        http_response_code(500);
        die(json_encode(['error' => 'Не удалось сохранить фото на сервер'], JSON_UNESCAPED_UNICODE));
    }

    $createdUserId = createUserRecord($connectionDatabase, $userName, $userSurname, $userLogin, $userPasswordHash, $avatarSourceString);

    session_name('auth');
    session_start();
    $_SESSION['user_id'] = $createdUserId;

    http_response_code(200);
    echo json_encode(['redirect' => '../home']);
}

processRegistration();