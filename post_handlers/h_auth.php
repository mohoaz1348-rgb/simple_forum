<?php
include "../functions.php";
include "../config.php";


$code = "";
$username = trim(mb_substr(trim($_POST["login"]), 0, 33, "UTF-8"));   // Ограничим длину имени
if (empty($username)) {
    if ($code == "") {
        $code = "auth_inter_login";
    }
}   // Проверим, что имя пользователя не пустое

//	$username = preg_replace("/(\s){1,}/", "\x20", $username); //Меняем подряд идущие пробелы на один обычный пробел

$password = trim(mb_substr(trim($_POST["password"]), 0, 12, "UTF-8"));   // Ограничим длину пароля
if (empty($password)) {
    if ($code == "") {
        $code = "auth_inter_pwd";
    }
}   // Проверим, что имя пользователя не пустое

if ($code == "") {
    $username_prep = prep_for_like($username);
    $password_prep = prep_for_like(md5($password));
    $query = "SELECT count(*) AS count FROM users WHERE username LIKE \"".$username_prep."\" AND password=\"".$password_prep."\" AND active=1";
    $same_users = query($query);
    //echo $query."<br>";
    $same_users_array = mysqli_fetch_array($same_users);
    //echo $same_users_array[0];


    if ($same_users_array[0] == 0) {
        $code = "auth_fail";
    } else {

        $query = "SELECT id_user, username, user_group, banned_till FROM users WHERE username LIKE \"".$username_prep."\" AND banned_till<=NOW()";
        //echo $query;
        $same_user = query($query);

        if ($row = mysqli_fetch_object($same_user)) {
            session_start();
            $_SESSION['authorized'] = 1;

            $_SESSION['id_user'] = $row -> id_user;
            $_SESSION['user'] = $row -> username;
            $_SESSION['group'] = $row -> user_group;
        } else {
            $code = "auth_banned";
        }
    }
}

if ($code == "") {
    header("Location: ../index.php");
} else {
    header("Location: ../index.php?action=auth&code=$code");
}
