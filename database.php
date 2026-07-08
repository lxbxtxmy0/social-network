<?php

function connectDatabase(): PDO {
    $dsn = 'mysql:host=localhost;dbname=SocialNetwork;charset=utf8mb4';
    $user = 'root';
    $password = 'VasAnt2006';
    return new PDO($dsn, $user, $password);
}



