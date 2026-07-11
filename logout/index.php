<?php

session_name('auth');
session_start();


$_SESSION = [];
setcookie('auth', '', time() - 3600, '/');

session_destroy();

header('Location: /login');
