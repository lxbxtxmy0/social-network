<?php
session_name('auth');
session_start();

header('Content-Type: application/json');
require_once '../database/database.php';

function processGetNextMemeRequest(): void
{
    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Не авторизован']);
        return;
    }

    $connectionDatabase = connectDatabase();
    $userIdentifier = $_SESSION['user_id'];

    $actionType = $_POST['action'] ?? 'pass';
    $memeIdentifier = $_POST['meme_id'] ?? null;
    $investmentAmount = 0;

    if (isset($_POST['amount'])) {
        $investmentAmount = (int)$_POST['amount'];
    }

    if (!isset($_SESSION['viewed_memes'])) {
        $_SESSION['viewed_memes'] = [];
    }

    if ($memeIdentifier) {
        if (!in_array($memeIdentifier, $_SESSION['viewed_memes'])) {
            $_SESSION['viewed_memes'][] = $memeIdentifier;
        }
    }

    if ($actionType === 'invest') {
        if ($memeIdentifier) {
            if ($investmentAmount > 0) {

                $connectionDatabase->beginTransaction();

                $statementBalance = $connectionDatabase->query("SELECT balance FROM user WHERE id = " . $userIdentifier);
                $currentBalance = (int)$statementBalance->fetchColumn();

                if ($currentBalance >= $investmentAmount) {
                    $statementUpdateBalance = $connectionDatabase->prepare("UPDATE user SET balance = balance - ? WHERE id = ?");
                    $statementUpdateBalance->execute([$investmentAmount, $userIdentifier]);

                    $statementInsertInvestment = $connectionDatabase->prepare("INSERT INTO investment (user_id, meme_id, donated_coins) VALUES (?, ?, ?)");
                    $statementInsertInvestment->execute([$userIdentifier, $memeIdentifier, $investmentAmount]);

                    $connectionDatabase->commit();
                } else {
                    $connectionDatabase->rollBack();
                    echo json_encode(['success' => false, 'error' => 'Недостаточно коинов на балансе']);
                    return;
                }

            }
        }
    }

    $nextMemeData = getNextUnseenMeme($connectionDatabase, $_SESSION['viewed_memes']);

    if ($nextMemeData) {
        echo json_encode(['success' => true, 'meme' => $nextMemeData]);
    } else {
        echo json_encode(['success' => false, 'message' => 'no_more_memes']);
    }
}

processGetNextMemeRequest();