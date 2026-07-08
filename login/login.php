<?php

require_once '../database.php';
$connection = connectDataBase();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Только POST запросы'], JSON_UNESCAPED_UNICODE));
}

$login = $_POST['login'] ?? null;
$password = $_POST['password'] ?? null;

if (!$login || !$password) {
    http_response_code(400);
    die(json_encode(['error' => 'Не указаны нужные поля'], JSON_UNESCAPED_UNICODE));
}

$getUserData = "SELECT id, password FROM user WHERE login = :login";
$statement = $connection->prepare($getUserData);
$statement->execute(['login' => $login]);
$userDataFromDB = $statement->fetch(PDO::FETCH_ASSOC);

if (!$userDataFromDB) {
    http_response_code(401);
    die(json_encode(['error' => 'Пользователя не существует'], JSON_UNESCAPED_UNICODE));
}

if (!password_verify($password, $userDataFromDB['password'])) {
    http_response_code(403);
    die(json_encode(['error' => 'Неверно указан пароль'], JSON_UNESCAPED_UNICODE));
}

session_name('auth');
session_start();

$_SESSION['user_id'] = $userDataFromDB['id'];

http_response_code(200);
echo(json_encode(['message' => 'Успех'], JSON_UNESCAPED_UNICODE));

