<?php

require_once './model/ModelUser.php';

$user = [];
$user['username'] = 'Ray';
$user['password'] = password_hash('OROCHIsv1', PASSWORD_DEFAULT);
$user['permission'] = 'administrator';

$result = ModelUser::createNewUser($user);
var_dump($result);
