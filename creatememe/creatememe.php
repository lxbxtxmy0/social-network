<?php

require_once '../validation.php';
require_once '../database.php';
$connection = connectDatabase();

session_name('auth');
session_start();
$userId = $_SESSION['user_id'];

function createMeme($connection, $userId, $title, $description): int
{
    $createMeme = <<<SQL
        INSERT INTO meme (user_id, title, description)
        VALUES (:user_id, :title, :description)
    SQL;
    $statement = $connection->prepare($createMeme);
    $statement->execute([
        'user_id' => $userId,
        'title' => $title,
        'description' => $description
    ]);

    return (int)$connection->lastInsertId();
}

function saveImages($connection, $postId, $savedImages): void
{
    $addImage = <<<SQL
    INSERT INTO image (meme_id, source, sort_order)
    VALUES (:meme_id, :source, :sort_order)
SQL;
    for ($i = 0; $i < count($savedImages); $i++) {
        $statement = $connection->prepare($addImage);
        $statement->execute([
            'meme_id' => $postId,
            'source' => $savedImages[$i],
            'sort_order' => $i
        ]);
    }
}

function downUserBalance($connection, $userId, $coins): void {
    $getUserBalance = "SELECT balance FROM user WHERE id = " . $userId;
    $statement = $connection->query($getUserBalance);
    $balance = $statement->fetchColumn();
    if ($balance <= $coins + 5) {
        http_response_code(400);
        die(json_encode(['error' => 'Недостаточно коинов на балансе(должно оставаться 5)'], JSON_UNESCAPED_UNICODE));
    }
    $downUserBalance = "UPDATE user SET balance = balance - :coins WHERE id = " . $userId;
    $statement = $connection->prepare($downUserBalance);
    $statement->execute(['coins' => $coins]);
}

function saveInvestment($connection, $userId, $memeId, $coins): void
{
    $saveInvestment = <<<SQL
    INSERT INTO investment (user_id, meme_id, donated_coins)
    VALUES (:user_id, :meme_id, :donated_coins)
SQL;
    $statement = $connection->prepare($saveInvestment);
    $statement->execute([
        'user_id' => $userId,
        'meme_id' => $memeId,
        'donated_coins' => $coins
    ]);
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Только POST запроы'], JSON_UNESCAPED_UNICODE));
}

$title = $_POST['title'] ?? null;
$coins = $_POST['coins'] ?? null;

if (!$title || !$coins) {
    http_response_code(400);
    die(json_encode(['error' => 'Заполнены не все поля'], JSON_UNESCAPED_UNICODE));
}

if (!isNumber($coins)) {
    http_response_code(400);
    die(json_encode(['error' => 'Неверно указано количество коинов'], JSON_UNESCAPED_UNICODE));
}

if ($coins < 5) {
    http_response_code(400);
    die(json_encode(['error' => 'Минимальная цена создания мема - 5 коинов'], JSON_UNESCAPED_UNICODE));
}


$countPhotos = count($_FILES['images']['name']);

if ($countPhotos <= 0) {
    http_response_code(400);
    die(json_encode(['error' => 'Нет картинок'], JSON_UNESCAPED_UNICODE));
}

$pathToDir = '../src/img/';
$savedImages = [];

try {
    for ($i = 0; $i < $countPhotos; $i++) {
        if ($_FILES['images']['error'][$i] != UPLOAD_ERR_OK) {
            throw new Exception('Ошибка загрузки файла номер' . ($i + 1));
        }
        $imageName = $pathToDir . uniqid() . '_' . basename($_FILES['images']['name'][$i]);
        if (!move_uploaded_file($_FILES['images']['tmp_name'][$i], $imageName)) {
            throw new Exception('Ошибка сохранения файла');
        }
        $savedImages[] = $imageName;
    }
} catch (Exception $error) {
    foreach ($savedImages as $image) {
        unlink($image);
    }
    http_response_code(500);
    die(json_encode(['error' => $error->getMessage()], JSON_UNESCAPED_UNICODE));
}

$description = $_POST['description'] ?? null;

try {
    $connection->beginTransaction();
    $memeId = createMeme($connection, $userId, $title, $description);
    saveImages($connection, $memeId, $savedImages);
    downUserBalance($connection, $userId, $coins);
    saveInvestment($connection, $userId, $memeId, $coins);
    $connection->commit();
} catch(Exception $error) {
    $connection->rollback();
    http_response_code(500);
    die(json_encode(['error' => 'Ошибка, повторите позже ' . $error->getMessage()], JSON_UNESCAPED_UNICODE));
}

http_response_code(200);
echo json_encode(['message' => 'Мем успешно создан'], JSON_UNESCAPED_UNICODE);
