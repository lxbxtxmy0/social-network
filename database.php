<?php

function connectDatabase(): PDO {
    $dsn = 'mysql:host=localhost;dbname=SocialNetwork;charset=utf8mb4';
    $user = 'root';
    $password = 'VasAnt2006';
    return new PDO($dsn, $user, $password);
}

function getLastMemes($connection): array {
    $getMemes = "SELECT id, user_id, title, description, published_at FROM meme ORDER BY published_at DESC";
    $statement = $connection->query($getMemes);
    $memes = $statement->fetchAll(PDO::FETCH_ASSOC);
    $result = [];
    foreach ($memes as $meme) {
        $getUserInfo="SELECT name, surname, avatar_source, login FROM user WHERE id = " . $meme['user_id'];
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
