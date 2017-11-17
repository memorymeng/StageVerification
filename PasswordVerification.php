<?php
$username = $_REQUEST["username"] ?? "unknown";
$password = $_REQUEST["password"] ?? "unknown";


require_once './model/ModelUser.php';
$info = [];
$user = ModelUser::findUserByName($username);
if (null != $user) {
    $info['userFind'] = true;
    if ($user->verifyPassword($password)) {
        $info['username'] = $user->getUsername();
        $info['permission'] = $user->getUserPermission();
        $info['psw_verified'] = true;
    } else {
        $info['psw_verified'] = false;
    }
} else {
    $info['userFind'] = false;
}


echo json_encode($info);
