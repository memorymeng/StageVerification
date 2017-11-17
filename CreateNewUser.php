<?php
$username = $_REQUEST['username'] ?? 'unknown';
$password = $_REQUEST['password'] ?? 'unknown';

require_once './model/ModelUser.php';
$result = [];
$result['id'] = -1;
$result['username'] = $username;
$result['success'] = true;
$result['error'] = 'none';

$someoneElse = ModelUser::findUserByName($username);
if (null != $someoneElse) {
    $result['id'] = $someoneElse->getUserId();
    $result['success'] = false;
    $result['error'] = "User name already exists, id#{$result['id']}, please try another one!";
    $result['permission'] = $someoneElse->getUserPermission();
} else {
    $user = [];
    $user['username'] = $username;
    $user['password'] = password_hash($password, PASSWORD_DEFAULT);
    $user['permission'] = 'user';

    $result['id'] = ModelUser::createNewUser($user);
}

echo json_encode($result);
