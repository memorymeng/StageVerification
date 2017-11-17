<?php

require_once './model/ModelUser.php';

$user = [];
$user['username'] = 'Andy';
$user['password'] = password_hash('qwerty123', PASSWORD_DEFAULT);
$user['permission'] = 'administrator';

$result = ModelUser::createNewUser($user);
var_dump($result);
