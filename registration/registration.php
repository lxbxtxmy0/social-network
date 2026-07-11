<?php

require_once '../validation/validation.php';
require_once '../database/database.php';
$connection = connectDatabase();

function createUser($connection, $name, $surname, $login, $password, $avatarSource): int
{
    $createUser = <<<SQL
        INSERT INTO user (name, surname, login, password, avatar_source)
        VALUES (:name, :surname, :login, :password, :avatar_source)
    SQL;
    $statement = $connection->prepare($createUser);
    $statement->execute([
        'name' => $name,
        'surname' => $surname,
        'login' => $login,
        'password' => $password,
        'avatar_source' => $avatarSource,
    ]);
    return $connection->lastInsertId();
}

function isAccessibleLogin($connection, $login): bool {
    $checkLogin = "SELECT login FROM user WHERE login = :login";
    $statement = $connection->prepare($checkLogin);
    $statement->execute(['login' => $login]);
    $login = $statement->fetchColumn;
    return isset($login);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Только POST'], JSON_UNESCAPED_UNICODE));
}

$name = $_POST['name'] ?? null;
$surname = $_POST['surname'] ?? null;
$login = $_POST['login'] ?? null;

if (!isAccessibleLogin($connection, $login)) {
    http_response_code(400);
    die(json_encode(['error' => 'Логин уже занят'], JSON_UNESCAPED_UNICODE));
}

$password = $_POST['password'] ?? null;

if (!$name || !$surname || !$login || !$password) {
    http_response_code(400);
    die(json_encode(['error' => 'Не указаны все поля'], JSON_UNESCAPED_UNICODE));
}

validateLogin($login);
validatePassword($password);

$password = password_hash($password, PASSWORD_DEFAULT);

$avatar = $_FILES['avatar'] ?? null;

if (!$avatar || $avatar['error'] != UPLOAD_ERR_OK) {
    http_response_code(400);
    die(json_encode(['error' => 'Не прикреплена аватарка'], JSON_UNESCAPED_UNICODE));
}

$pathToDir = '../src/img/';
$fileName = uniqid() . '_' . basename($avatar['name']);
$avatarSource = $pathToDir . $fileName;

if (!move_uploaded_file($avatar['tmp_name'], $avatarSource)) {
    http_response_code(500);
    die(json_encode(['error' => 'Не удалось сохранить на сервер'], JSON_UNESCAPED_UNICODE));
}

$userId = createUser($connection, $name, $surname, $login, $password, $avatarSource);

session_name('auth');
session_start();
$_SESSION['user_id'] = $userId;

http_response_code(200);
echo json_encode(['redirect' => '../home']);

