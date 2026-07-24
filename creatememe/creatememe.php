<?php

session_name('auth');
session_start();

require_once '../validation/validation.php';
require_once '../database/database.php';

function createMemeRecord($connection, $userId, $title, $description): int
{
    $createQuery = <<<SQL
        INSERT INTO meme (user_id, title, description)
        VALUES (:user_id, :title, :description)
    SQL;

    $statement = $connection->prepare($createQuery);
    $statement->execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description
    ]);

    return (int)$connection->lastInsertId();
}

function saveImageRecords($connection, $postId, $savedImagesArray): void
{
    $addImageQuery = <<<SQL
        INSERT INTO image (meme_id, source, sort_order)
        VALUES (:meme_id, :source, :sort_order)
    SQL;

    for ($index = 0; $index < count($savedImagesArray); $index++) {
        $statement = $connection->prepare($addImageQuery);
        $statement->execute([
                'meme_id' => $postId,
                'source' => $savedImagesArray[$index],
                'sort_order' => $index
        ]);
    }
}

function decreaseUserBalance($connection, $userId, $coinsAmount): void
{
    $getBalanceQuery = "SELECT balance FROM user WHERE id = " . (int)$userId;
    $statement = $connection->query($getBalanceQuery);
    $currentBalance = $statement->fetchColumn();

    if ($currentBalance <= $coinsAmount + 5) {
        http_response_code(400);
        die(json_encode(['error' => 'Недостаточно коинов на балансе (должно оставаться 5)'], JSON_UNESCAPED_UNICODE));
    }

    $decreaseBalanceQuery = "UPDATE user SET balance = balance - :coins WHERE id = " . (int)$userId;
    $statement = $connection->prepare($decreaseBalanceQuery);
    $statement->execute(['coins' => $coinsAmount]);
}

function saveInvestmentRecord($connection, $userId, $memeId, $coinsAmount): void
{
    $saveInvestmentQuery = <<<SQL
        INSERT INTO investment (user_id, meme_id, donated_coins)
        VALUES (:user_id, :meme_id, :donated_coins)
    SQL;

    $statement = $connection->prepare($saveInvestmentQuery);
    $statement->execute([
            'user_id' => $userId,
            'meme_id' => $memeId,
            'donated_coins' => $coinsAmount
    ]);
}

function processMemeCreation(): void
{
    $userId = $_SESSION['user_id'];
    $connection = connectDatabase();

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => 'Только POST запросы'], JSON_UNESCAPED_UNICODE));
    }

    $title = $_POST['title'] ?? null;
    $coinsAmount = $_POST['coins'] ?? null;

    if (!$title || !$coinsAmount) {
        http_response_code(400);
        die(json_encode(['error' => 'Заполнены не все поля'], JSON_UNESCAPED_UNICODE));
    }

    if (!isNumber($coinsAmount)) {
        http_response_code(400);
        die(json_encode(['error' => 'Неверно указано количество коинов'], JSON_UNESCAPED_UNICODE));
    }

    if ($coinsAmount < 5) {
        http_response_code(400);
        die(json_encode(['error' => 'Минимальная цена создания мема - 5 коинов'], JSON_UNESCAPED_UNICODE));
    }

    $photosCount = count($_FILES['images']['name']);

    if ($photosCount <= 0) {
        http_response_code(400);
        die(json_encode(['error' => 'Нет картинок'], JSON_UNESCAPED_UNICODE));
    }

    $directoryPath = '../src/img/';
    $savedImagesArray = [];

    try {
        for ($index = 0; $index < $photosCount; $index++) {
            if ($_FILES['images']['error'][$index] !== UPLOAD_ERR_OK) {
                throw new Exception('Ошибка загрузки файла номер ' . ($index + 1));
            }

            $imageName = $directoryPath . uniqid() . '_' . basename($_FILES['images']['name'][$index]);

            if (!move_uploaded_file($_FILES['images']['tmp_name'][$index], $imageName)) {
                throw new Exception('Ошибка сохранения файла');
            }

            $savedImagesArray[] = $imageName;
        }
    } catch (Exception $errorObject) {
        foreach ($savedImagesArray as $imagePath) {
            unlink($imagePath);
        }
        http_response_code(500);
        die(json_encode(['error' => $errorObject->getMessage()], JSON_UNESCAPED_UNICODE));
    }

    $description = $_POST['description'] ?? null;

    try {
        $connection->beginTransaction();
        decreaseUserBalance($connection, $userId, $coinsAmount);
        $memeId = createMemeRecord($connection, $userId, $title, $description);
        saveImageRecords($connection, $memeId, $savedImagesArray);
        saveInvestmentRecord($connection, $userId, $memeId, $coinsAmount);
        $connection->commit();
    } catch (Exception $errorObject) {
        $connection->rollback();
        http_response_code(500);
        die(json_encode(['error' => 'Ошибка, повторите позже: ' . $errorObject->getMessage()], JSON_UNESCAPED_UNICODE));
    }

    http_response_code(200);
    echo json_encode(['message' => 'Мем успешно создан'], JSON_UNESCAPED_UNICODE);
}

processMemeCreation();