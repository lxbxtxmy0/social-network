<?php

function isDigit($char): bool
{
    return $char >= '0' && $char <= '9';
}

function isNumber($string): bool {
    for ($i = 0; $i < strlen($string); $i++) {
        if (!isDigit($string[$i])) {
            return false;
        }
    }
    return true;
}
function isLetter($char): bool
{
    return $char >= 'a' && $char <= 'z' || $char >= 'A' && $char <= 'Z';
}


function validateLogin($login): void
{
    $length = strlen($login);
    if ($length < 8) {
        http_response_code(400);
        die(json_encode(['error' => 'Логин слишком короткий'], JSON_UNESCAPED_UNICODE));
    }
    if ($length > 50) {
        http_response_code(400);
        die(json_encode(['error' => 'Логин слишком длинный'], JSON_UNESCAPED_UNICODE));
    }
    if (isDigit($login[0]) || $login[0] == '_') {
        http_response_code(400);
        die(json_encode(['error' => 'Логин некорректен'], JSON_UNESCAPED_UNICODE));
    }
    for ($i = 1; $i < $length; $i++) {
        if (!(isLetter($login[$i]) || isDigit($login[$i]) || $login[$i] == '_')) {
            http_response_code(400);
            die(json_encode(['error' => 'Логин содержит недопустимые символы'], JSON_UNESCAPED_UNICODE));
        }
    }
}

function validatePassword($password): void
{
    $length = strlen($password);
    if ($length < 8) {
        http_response_code(400);
        die(json_encode(['error' => 'Пароль слишком короткий'], JSON_UNESCAPED_UNICODE));
    }
    if ($length > 65) {
        http_response_code(400);
        die(json_encode(['error' => 'Пароль слишком длинный'], JSON_UNESCAPED_UNICODE));
    }
    if ($password[0] == ' ') {
        http_response_code(400);
        die(json_encode(['error' => 'Пароль не может начинаться с пробела'], JSON_UNESCAPED_UNICODE));
    }
    if ($password[$length - 1] == ' ') {
        http_response_code(400);
        die(json_encode(['error' => 'Пароль не может заканчиваться пробелом'], JSON_UNESCAPED_UNICODE));
    }
}