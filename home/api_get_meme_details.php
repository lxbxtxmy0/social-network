<?php
session_name('auth');
session_start();

require_once '../database/database.php';

function processGetMemeDetailsRequest(): void
{
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false]);
        return;
    }

    $connectionDatabase = connectDatabase();
    $memeIdentifier = $_GET['id'] ?? null;

    if (!$memeIdentifier) {
        echo json_encode(['success' => false]);
        return;
    }

    $statementMeme = $connectionDatabase->prepare("SELECT * FROM meme WHERE id = ?");
    $statementMeme->execute([$memeIdentifier]);
    $memeData = $statementMeme->fetch(PDO::FETCH_ASSOC);

    if (!$memeData) {
        echo json_encode(['success' => false]);
        return;
    }

    $statementUser = $connectionDatabase->prepare("SELECT name, surname, avatar_source FROM user WHERE id = ?");
    $statementUser->execute([$memeData['user_id']]);
    $userData = $statementUser->fetch(PDO::FETCH_ASSOC);

    $statementImages = $connectionDatabase->prepare("SELECT source FROM image WHERE meme_id = ? ORDER BY sort_order ASC");
    $statementImages->execute([$memeIdentifier]);
    $imagesArray = $statementImages->fetchAll(PDO::FETCH_COLUMN);

    $statementInvestments = $connectionDatabase->prepare("SELECT SUM(donated_coins) FROM investment WHERE meme_id = ?");
    $statementInvestments->execute([$memeIdentifier]);
    $investmentsAmount = $statementInvestments->fetchColumn();

    if (!$investmentsAmount) {
        $investmentsAmount = 0;
    }

    $formattedDate = date('d M g:ia', strtotime($memeData['published_at']));

    echo json_encode([
        'success' => true,
        'data' => [
            'author_name' => htmlspecialchars($userData['name'] . ' ' . $userData['surname']),
            'author_avatar' => htmlspecialchars($userData['avatar_source']),
            'title' => htmlspecialchars($memeData['title']),
            'description' => htmlspecialchars($memeData['description']),
            'investments' => $investmentsAmount,
            'date' => $formattedDate,
            'images' => $imagesArray
        ]
    ]);
}

processGetMemeDetailsRequest();