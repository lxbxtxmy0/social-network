<?php

function processGetNextRequest(): void
{
    session_name('auth');
    session_start();

    header('Content-Type: application/json');

    if (empty($_SESSION['user_id'])) {
        echo json_encode(['success' => false, 'error' => 'Не авторизован']);
        exit;
    }

    require_once 'database/database.php';
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
                try {
                    $connectionDatabase->beginTransaction();

                    $statementBalance = $connectionDatabase->query("SELECT balance FROM user WHERE id = " . (int)$userIdentifier);
                    $currentBalance = (int)$statementBalance->fetchColumn();

                    if ($currentBalance >= $investmentAmount) {

                        // 1. Списываем деньги у инвестора
                        $statementUpdateBalance = $connectionDatabase->prepare("UPDATE user SET balance = balance - ? WHERE id = ?");
                        $statementUpdateBalance->execute([$investmentAmount, $userIdentifier]);

                        // 2. Отдаем все деньги автору мема
                        $statementCreator = $connectionDatabase->prepare("UPDATE user SET balance = balance + ? WHERE id = (SELECT user_id FROM meme WHERE id = ?)");
                        $statementCreator->execute([$investmentAmount, $memeIdentifier]);

                        // 3. Записываем историю доната
                        $statementInsertInvestment = $connectionDatabase->prepare("INSERT INTO investment (user_id, meme_id, donated_coins) VALUES (?, ?, ?)");
                        $statementInsertInvestment->execute([$userIdentifier, $memeIdentifier, $investmentAmount]);

                        $connectionDatabase->commit();
                    } else {
                        $connectionDatabase->rollBack();
                        echo json_encode(['success' => false, 'error' => 'Недостаточно коинов на балансе']);
                        exit;
                    }
                } catch (Exception $databaseError) {
                    $connectionDatabase->rollBack();
                    echo json_encode(['success' => false, 'error' => 'Ошибка базы данных']);
                    exit;
                }
            }
        }
    }

    $nextMeme = getNextUnseenMeme($connectionDatabase, $_SESSION['viewed_memes']);

    $statementFinalBalance = $connectionDatabase->query("SELECT balance FROM user WHERE id = " . (int)$userIdentifier);
    $finalBalanceAmount = (int)$statementFinalBalance->fetchColumn();

    if ($nextMeme) {
        echo json_encode([
            'success' => true,
            'meme' => $nextMeme,
            'new_balance' => $finalBalanceAmount
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'no_more_memes',
            'new_balance' => $finalBalanceAmount
        ]);
    }
}

processGetNextRequest();