<?php

function connectDatabase(): PDO
{
    $dsn = 'mysql:host=localhost;dbname=SocialNetwork;charset=utf8mb4';
    $user = 'root';
    $password = 'VasAnt2006';

    return new PDO($dsn, $user, $password);
}

function getLoginById($connection, $userId)
{
    $query = "SELECT login FROM user WHERE id = " . (int)$userId;
    $statement = $connection->query($query);

    return $statement->fetchColumn();
}

function getNextUnseenMeme($connection, $viewedIds = []): ?array
{
    if (!empty($viewedIds)) {
        $placeholders = implode(',', array_fill(0, count($viewedIds), '?'));
        $query = "SELECT * FROM meme WHERE id NOT IN ($placeholders) ORDER BY published_at DESC LIMIT 1";
        $statement = $connection->prepare($query);
        $statement->execute($viewedIds);
    } else {
        $query = "SELECT * FROM meme ORDER BY published_at DESC LIMIT 1";
        $statement = $connection->query($query);
    }

    $meme = $statement->fetch(PDO::FETCH_ASSOC);

    if (!$meme) {
        return null;
    }

    $userQuery = "SELECT name, surname, avatar_source, login FROM user WHERE id = " . (int)$meme['user_id'];
    $userStatement = $connection->query($userQuery);
    $userInfo = $userStatement->fetch(PDO::FETCH_ASSOC);

    $imageQuery = "SELECT source FROM image WHERE meme_id = " . (int)$meme['id'] . " ORDER BY sort_order ASC LIMIT 1";
    $imageStatement = $connection->query($imageQuery);
    $imageSource = $imageStatement->fetch(PDO::FETCH_COLUMN);

    $investmentsQuery = "SELECT SUM(donated_coins) FROM investment WHERE meme_id = " . (int)$meme['id'];
    $investmentsStatement = $connection->query($investmentsQuery);
    $investments = $investmentsStatement->fetchColumn();

    return [
        'id' => $meme['id'],
        'name' => htmlspecialchars($userInfo['name'] ?? ''),
        'surname' => htmlspecialchars($userInfo['surname'] ?? ''),
        'avatar_source' => $userInfo['avatar_source'],
        'login' => $userInfo['login'],
        'title' => htmlspecialchars($meme['title'] ?? ''),
        'description' => htmlspecialchars($meme['description'] ?? ''),
        'published_at' => $meme['published_at'],
        'image' => $imageSource,
        'investments' => $investments ? (int)$investments : 0
    ];
}

function getLastMeme($connection): array
{
    $query = "SELECT * FROM meme ORDER BY published_at DESC LIMIT 1";
    $statement = $connection->query($query);
    $meme = $statement->fetch(PDO::FETCH_ASSOC);

    $userQuery = "SELECT name, surname, avatar_source, login FROM user WHERE id = " . (int)$meme['user_id'];
    $userStatement = $connection->query($userQuery);
    $userInfo = $userStatement->fetch(PDO::FETCH_ASSOC);

    $imageQuery = "SELECT source FROM image WHERE meme_id = " . (int)$meme['id'] . " ORDER BY sort_order ASC LIMIT 1";
    $imageStatement = $connection->query($imageQuery);
    $imageSource = $imageStatement->fetch(PDO::FETCH_COLUMN);

    $investmentsQuery = "SELECT SUM(donated_coins) FROM investment WHERE meme_id = " . (int)$meme['id'];
    $investmentsStatement = $connection->query($investmentsQuery);
    $investments = $investmentsStatement->fetchColumn();

    return [
        'name' => htmlspecialchars($userInfo['name'] ?? ''),
        'surname' => htmlspecialchars($userInfo['surname'] ?? ''),
        'avatar_source' => $userInfo['avatar_source'],
        'login' => $userInfo['login'],
        'title' => htmlspecialchars($meme['title'] ?? ''),
        'description' => htmlspecialchars($meme['description'] ?? ''),
        'published_at' => $meme['published_at'],
        'image' => $imageSource,
        'investments' => $investments ? (int)$investments : 0
    ];
}

function getUserBalance($connection, $userId): int
{
    $query = "SELECT balance FROM user WHERE id = " . (int)$userId;
    $statement = $connection->query($query);

    return (int)$statement->fetchColumn();
}

function getUserIdByLogin($connection, $login): ?int
{
    $query = "SELECT id FROM user WHERE login = :login";
    $statement = $connection->prepare($query);
    $statement->execute(['login' => $login]);
    $id = $statement->fetchColumn();

    if ($id) {
        return (int)$id;
    } else {
        return null;
    }
}

function getUserMemes($connection, $userId): array
{
    $query = "SELECT id FROM meme WHERE user_id = " . (int)$userId . " ORDER BY published_at DESC";
    $statement = $connection->query($query);
    $memes = $statement->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($memes as $meme) {
        $imageQuery = "SELECT source FROM image WHERE meme_id = " . (int)$meme['id'] . " AND sort_order = 0";
        $imageStatement = $connection->query($imageQuery);
        $imageSource = $imageStatement->fetchColumn();

        $investmentsQuery = "SELECT SUM(donated_coins) FROM investment WHERE meme_id = " . (int)$meme['id'];
        $investmentsStatement = $connection->query($investmentsQuery);
        $investments = $investmentsStatement->fetchColumn();

        $result[] = [
            'id' => $meme['id'],
            'image' => $imageSource,
            'investments' => $investments ? (int)$investments : 0
        ];
    }

    return $result;
}

function getPersonalData($connection, $userId): array
{
    $query = "SELECT name, surname, avatar_source, balance, bio FROM user WHERE id = " . (int)$userId;
    $statement = $connection->query($query);

    return $statement->fetch(PDO::FETCH_ASSOC);
}

function getProfileInvestments($connection, $userId): array
{
    $query = "SELECT SUM(donated_coins) AS coins, meme_id FROM investment WHERE user_id = " . (int)$userId . " GROUP BY meme_id ORDER BY coins DESC LIMIT 3";
    $statement = $connection->query($query);
    $investmentsList = $statement->fetchAll(PDO::FETCH_ASSOC);

    $result = [];

    foreach ($investmentsList as $investment) {
        $memeQuery = "SELECT id, title, published_at FROM meme WHERE id = " . (int)$investment['meme_id'];
        $memeStatement = $connection->query($memeQuery);
        $memeInfo = $memeStatement->fetch(PDO::FETCH_ASSOC);

        $imageQuery = "SELECT source FROM image WHERE sort_order = 0 AND meme_id = " . (int)$investment['meme_id'];
        $imageStatement = $connection->query($imageQuery);
        $imageSource = $imageStatement->fetchColumn();

        $result[] = [
            'id' => $investment['meme_id'],
            'title' => htmlspecialchars($memeInfo['title'] ?? ''),
            'published_at' => $memeInfo['published_at'],
            'image' => $imageSource,
            'investments' => $investment['coins'],
            'earned' => 0 // Всегда возвращаем 0, раз колонки в базе пока нет
        ];
    }

    return $result;
}

function getProfileData($connection, $profileId): array
{
    $personalDataArray = getPersonalData($connection, $profileId);
    $investmentsArray = getProfileInvestments($connection, $profileId);
    $memesArray = getUserMemes($connection, $profileId);

    return [$personalDataArray, $investmentsArray, $memesArray];
}