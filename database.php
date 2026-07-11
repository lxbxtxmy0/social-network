<?php

function connectDatabase(): PDO
{
    $dsn = 'mysql:host=localhost;dbname=SocialNetwork;charset=utf8mb4';
    $user = 'root';
    $password = 'VasAnt2006';
    return new PDO($dsn, $user, $password);
}

function getLastMemes($connection): array
{
    $getMemes = "SELECT * FROM meme ORDER BY published_at DESC";
    $statement = $connection->query($getMemes);
    $memes = $statement->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    foreach ($memes as $meme) {
        $getUserInfo = "SELECT name, surname, avatar_source, login FROM user WHERE id = " . $meme['user_id'];
        $statement = $connection->query($getUserInfo);
        $userInfo = $statement->fetch(PDO::FETCH_ASSOC);

        $getImages = "SELECT source FROM image WHERE meme_id = " . $meme['id'] . " ORDER BY sort_order ASC";
        $statement = $connection->query($getImages);
        $images = $statement->fetchAll(PDO::FETCH_COLUMN);

        $getInvestments = "SELECT SUM(donated_coins) FROM investment WHERE meme_id = " . $meme['id'];
        $statement = $connection->query($getInvestments);
        $investments = $statement->fetchColumn();

        $result[] = [
            'name' => htmlspecialchars($userInfo['name']),
            'surname' => htmlspecialchars($userInfo['surname']),
            'avatar_source' => htmlspecialchars($userInfo['avatar_source']),
            'login' => htmlspecialchars($userInfo['login']),
            'title' => htmlspecialchars($meme['title']),
            'description' => htmlspecialchars($meme['description']),
            'published_at' => $meme['published_at'],
            'images' => $images,
            'investments' => $investments
        ];
    }
    return $result;
}

function getUserBalance($connection, $userId): int
{
    $getBalance = "SELECT balance FROM user WHERE id = " . $userId;
    $statement = $connection->query($getBalance);
    $balance = $statement->fetchColumn();

    return $balance;
}

function getUserIdByLogin($connection, $login): int|null
{
    $getId = "SELECT id FROM user WHERE login = :login";
    $statement = $connection->prepare($getId);
    $statement->execute(['login' => $login]);
    $id = $statement->fetchColumn();

    return $id;
}

function getUserMemes($connection, $userId): array {
    $getMemes = "SELECT * FROM meme WHERE user_id = " . $userId . " ORDER BY published_at DESC";
    $statement = $connection->query($getMemes);
    $memes = $statement->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    foreach ($memes as $meme) {
        $getImages = "SELECT source FROM image WHERE meme_id = " . $meme['id'] . " ORDER BY sort_order ASC";
        $statement = $connection->query($getImages);
        $images = $statement->fetchAll(PDO::FETCH_COLUMN);

        $getInvestments = "SELECT SUM(donated_coins) FROM investment WHERE meme_id = " . $meme['id'];
        $statement = $connection->query($getInvestments);
        $investments = $statement->fetchColumn();

        $result[] = [
            'title' => htmlspecialchars($meme['title']),
            'description' => htmlspecialchars($meme['description']),
            'published_at' => $meme['published_at'],
            'images' => $images,
            'investments' => $investments
        ];
    }
    return $result;
}

function getPersonalData($connection, $userId): array {
    $getPersonalData = "SELECT name, surname, avatar_source, balance, bio FROM user WHERE id = " . $userId;
    $statement = $connection->query($getPersonalData);
    $personalData = $statement->fetch(PDO::FETCH_ASSOC);

    return $personalData;
}

function getProfileInvestments($connection, $userId): array {
    $getProfileInvestments = <<<SQL
        SELECT SUM(donated_coins) AS coins, meme_id FROM investment
        WHERE user_id = $userId GROUP BY meme_id ORDER BY coins DESC
    SQL;
    $statement = $connection->query($getProfileInvestments);
    $profileInvestments = $statement->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    foreach ($profileInvestments as $investment) {
        $getMemeInfo = "SELECT title, published_at FROM meme WHERE id = " . $investment['meme_id'];
        $statement = $connection->query($getMemeInfo);
        $memeInfo = $statement->fetch(PDO::FETCH_ASSOC);

        $result[] = [
            'title' => htmlspecialchars($memeInfo['title']),
            'published_at' => $memeInfo['published_at'],
            'investments' => $investment['coins']
        ];
    }
    return $result;
}

function getProfileData($connection, $profileId): array
{
    try {
        $personalData = getPersonalData($connection, $profileId);
        $investments = getProfileInvestments($connection, $profileId);
        $memes = getUserMemes($connection, $profileId);
    } catch(Exception $error) {
        http_response_code(500);
        die(json_encode(['error' => 'Ошибка сервера. Повторите позже' . $error->getMessage()], JSON_UNESCAPED_UNICODE));
    }
    return [$personalData, $investments, $memes];
}
