<?php

session_name('auth');
session_start();

require_once '../validation/validation.php';
require_once '../database/database.php';

function createMemeRecord($connectionDatabase, $userIdentifier, $memeTitle, $memeDescription): int
{
    $createQuery = <<<SQL
        INSERT INTO meme (user_id, title, description)
        VALUES (:user_id, :title, :description)
    SQL;

    $statementExecute = $connectionDatabase->prepare($createQuery);
    $statementExecute->execute([
        'user_id' => $userIdentifier,
        'title' => $memeTitle,
        'description' => $memeDescription
    ]);

    return (int)$connectionDatabase->lastInsertId();
}

function saveImageRecords($connectionDatabase, $memeIdentifier, $imagePaths): void
{
    $addImageQuery = <<<SQL
        INSERT INTO image (meme_id, source, sort_order)
        VALUES (:meme_id, :source, :sort_order)
    SQL;

    for ($index = 0; $index < count($imagePaths); $index++) {
        $statementExecute = $connectionDatabase->prepare($addImageQuery);
        $statementExecute->execute([
            'meme_id' => $memeIdentifier,
            'source' => $imagePaths[$index],
            'sort_order' => $index
        ]);
    }
}

function decreaseUserBalance($connectionDatabase, $userIdentifier, $coinsAmount): void
{
    $getBalanceQuery = "SELECT balance FROM user WHERE id = " . (int)$userIdentifier;
    $statementExecute = $connectionDatabase->query($getBalanceQuery);
    $currentBalanceAmount = $statementExecute->fetchColumn();

    if ($currentBalanceAmount <= $coinsAmount + 5) {
        http_response_code(400);
        die(json_encode(['error' => 'Недостаточно коинов на балансе (должно оставаться 5)'], JSON_UNESCAPED_UNICODE));
    }

    $decreaseBalanceQuery = "UPDATE user SET balance = balance - :coins WHERE id = " . (int)$userIdentifier;
    $statementUpdate = $connectionDatabase->prepare($decreaseBalanceQuery);
    $statementUpdate->execute(['coins' => $coinsAmount]);
}

function saveInvestmentRecord($connectionDatabase, $userIdentifier, $memeIdentifier, $coinsAmount): void
{
    $saveInvestmentQuery = <<<SQL
        INSERT INTO investment (user_id, meme_id, donated_coins)
        VALUES (:user_id, :meme_id, :donated_coins)
    SQL;

    $statementExecute = $connectionDatabase->prepare($saveInvestmentQuery);
    $statementExecute->execute([
        'user_id' => $userIdentifier,
        'meme_id' => $memeIdentifier,
        'donated_coins' => $coinsAmount
    ]);
}

function processMemeCreation(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        die(json_encode(['error' => 'Только POST запросы'], JSON_UNESCAPED_UNICODE));
    }

    $userIdentifier = $_SESSION['user_id'];
    $connectionDatabase = connectDatabase();

    $memeTitle = $_POST['title'] ?? null;
    $coinsAmount = $_POST['coins'] ?? null;
    $memeDescription = $_POST['description'] ?? null;

    if (!$memeTitle || !$coinsAmount) {
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
    $filesData = [];
    $imagePaths = [];

    for ($index = 0; $index < $photosCount; $index++) {
        if ($_FILES['images']['error'][$index] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            die(json_encode(['error' => 'Ошибка загрузки файла номер ' . ($index + 1)], JSON_UNESCAPED_UNICODE));
        }

        $generatedFileName = uniqid() . '_' . basename($_FILES['images']['name'][$index]);
        $finalFilePath = $directoryPath . $generatedFileName;

        $filesData[] = [
            'temporary_path' => $_FILES['images']['tmp_name'][$index],
            'final_path' => $finalFilePath
        ];

        $imagePaths[] = $finalFilePath;
    }

    try {
        $connectionDatabase->beginTransaction();

        decreaseUserBalance($connectionDatabase, $userIdentifier, $coinsAmount);
        $memeIdentifier = createMemeRecord($connectionDatabase, $userIdentifier, $memeTitle, $memeDescription);
        saveImageRecords($connectionDatabase, $memeIdentifier, $imagePaths);
        saveInvestmentRecord($connectionDatabase, $userIdentifier, $memeIdentifier, $coinsAmount);

        foreach ($filesData as $fileItem) {
            if (!move_uploaded_file($fileItem['temporary_path'], $fileItem['final_path'])) {
                throw new Exception('Ошибка сохранения файла на сервер');
            }
        }

        $connectionDatabase->commit();

    } catch (Exception $serverError) {
        $connectionDatabase->rollback();

        foreach ($imagePaths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        http_response_code(500);
        die(json_encode(['error' => 'Ошибка сервера: ' . $serverError->getMessage()], JSON_UNESCAPED_UNICODE));
    }

    http_response_code(200);
    echo json_encode(['message' => 'Мем успешно создан'], JSON_UNESCAPED_UNICODE);
}

processMemeCreation();