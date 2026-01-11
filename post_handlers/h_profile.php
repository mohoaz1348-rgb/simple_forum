<?php

include "../config.php";
include "../functions.php";

is_auth_user(); // Определяем авторизован пользователь или нет. Если нет, то выходим по exit.

$authorized_user = true;

if (isset($_GET['action'])) {
    if ($_GET['action'] == "change") { // Изменяем настройки профиля пользователя
        // ------------------------------ Проверка введённых данных -------------------------------- //

        if (isset($_POST['uid']) && isset($_POST['sid'])) {
            if ($_POST['uid'] != $_SESSION['id_user']) {
                exit;
            }
            if ($_POST['sid'] != session_id()) {
                exit;
            }

            $exit = false;

            // current password

            $curr_pwd = mb_substr($_POST["curr_pwd"], 0, 12, "UTF-8");   // Ограничим длину пароля
            if (empty($curr_pwd)) {
                echo "Введите текущий пароль.<br>\n";
                $exit = true;
            }   // Проверим, что поле текущего пароля не пустое
            else {
                if (preg_match("/[^(a-zA-Z)|(абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ)|(0-9)]/u", $curr_pwd)) {
                    echo "Пароль может состоять только из следующих символов: буквы русского/английского алфавита, цифры.<br>\n";
                    $exit = true;
                }
            }

            // passwords

            $password = mb_substr($_POST["password"], 0, 12, "UTF-8");   // Ограничим длину пароля
            if (!empty($password)) {
                if (preg_match("/[^(a-zA-Z)|(абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ)|(0-9)]/u", $password)) {
                    echo "Пароль может состоять только из следующих символов: буквы русского/английского алфавита, цифры.<br>\n";
                    $exit = true;
                }
                if ($password != $_POST["password2"]) {
                    echo "Пароли не совпадают!<br>";
                    $exit = true;
                }
                $password = md5($password);
            }

            // email

            $email = trim(mb_substr(trim($_POST["email"]), 0, 50, "UTF-8"));   // Ограничим длину E-mail
            if (!empty($email)) {
                if (preg_match("/(\s)+/", $email)) {
                    echo "Пробелы в E-mail недопустимы.<br>\n";
                    $exit = true;
                }
            }


            if ($exit) {
                echo "Аккуратнее!";
                exit;
            }

            // Проверим, что пользователь с таким именем, паролем существует и его учётная запись активна

            $username_prep = prep_for_like($_SESSION['user']);
            $password_prep = prep_for_like(md5($curr_pwd));
            $query = "SELECT count(*) AS count FROM users WHERE username LIKE \"".$username_prep."\" AND password=\"".$password_prep."\" AND active=1";
            $same_users = query($query);
            //echo $query."<br>";
            $same_users_array = mysqli_fetch_array($same_users);
            //echo $same_users_array[0];

            if ($same_users_array[0] == 0) { // Если таких пользователей не нашли, то
                header("Location: ../index.php?action=profile&code=profile_curr_pwd_invalid");
            } else { // если нашли, то меняем настройки профиля
                if (!empty($password)) {
                    $query = "UPDATE users SET password='".addslashes($password)."' WHERE id_user=".$_SESSION['id_user'];
                    query($query);
                }
                if (!empty($email)) {
                    $query = "UPDATE users SET email='".addslashes($email)."' WHERE id_user=".$_SESSION['id_user'];
                    query($query);
                }
                //	echo "перед редиректом";
                header("Location: ../index.php?action=profile&code=profile_changed");
            }
        }
    }

    if ($_GET['action'] == "delete") { // Помечаем пользователя как неактивного
        if (isset($_GET['uid']) && isset($_GET['sid'])) {
            if (($_GET['uid'] == $_SESSION['id_user']) && ($_GET['sid'] == session_id())) {
                $query = "UPDATE users SET active=0 WHERE id_user=".$_SESSION['id_user']."";
                query($query);

                session_destroy();
                header("Location: ../index.php?action=profile&code=user_del_success");
            }
        }
    }

    if ($_GET['action'] == "ignore") { // Редактируем ignore_list
        if (isset($_POST['uid']) && isset($_POST['sid']) && isset($_POST['ignored_user'])) {
            if (($_POST['uid'] == $_SESSION['id_user']) && ($_POST['sid'] == session_id())) {
                // Сделать проверку на параметр $_POST["ignored_user"]
                $ignored_user = (int)$_POST['ignored_user'];

                $ignore_list = get_ignore_list();

                // Удаляем или добавляем пользователя в $ignore_list
                if (isset($_POST["ignore"])) {
                    if ($_POST["ignore"] == "ignored") {
                        if (stripos($ignore_list, ",".$ignored_user.",") === false) {
                            if ($ignore_list == "") {
                                $ignore_list = ",".$ignored_user.",";
                            } else {
                                $ignore_list = $ignore_list.$ignored_user.",";
                            }
                        }

                    }
                } else {
                    $ignore_list = str_replace(",".$ignored_user.",", ",", $ignore_list);
                    if ($ignore_list == ",") {
                        $ignore_list = "";
                    };
                }

                $query = "UPDATE users SET ignore_list='$ignore_list' WHERE id_user=".$_SESSION['id_user']."";
                query($query);

                header("Location: ../index.php?action=u_profile&uid=".$_POST["ignored_user"]);
            }
        }
    }
}
