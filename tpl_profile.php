<?php

global $authorized_user, $ignore_list, $months2;

if ($authorized_user) {
    if (isset($_GET['code'])) {
        if ($_GET['code'] == "profile_changed") {
            echo "<p class=\"message\">Настройки профиля успешно изменены.</p>\n";
        }

        if ($_GET['code'] == "profile_curr_pwd_invalid") {
            echo "<p class=\"error\">Неправильный текущий пароль.</p>\n";
        }
    }
    $query = "SELECT email, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time FROM users WHERE id_user=".$_SESSION['id_user'];
    $settings = query($query);
    $row = mysqli_fetch_object($settings);

    $query = "SELECT count(*) AS count FROM posts WHERE id_user=".$_SESSION['id_user']." AND active=1";
    $posts = query($query);
    $row2 = mysqli_fetch_object($posts);

    $i = explode("-", $row->time);

    // Получаем имена проигнорированных пользователей
    $ignore_list_names = "";
    if ($ignore_list == "") {
        $ignore_list_names = "";
    } else {
        $query = "SELECT username FROM users WHERE id_user in (".trim($ignore_list, ",").") ORDER BY id_user";
        $q_ig_users = query($query);
        while ($row_ig_users = mysqli_fetch_object($q_ig_users)) {
            $ignore_list_names = $ignore_list_names." ".($row_ig_users->username).",";
        }
        $ignore_list_names = rtrim($ignore_list_names, ",");
    }
    $ignore_list_names = htmlspecialchars($ignore_list_names, ENT_QUOTES);
    // Получаем имена тех, кто меня игнорирует
    $ignore_me_list_names = "";
    $query = "SELECT username FROM users WHERE ignore_list LIKE \"%,".$_SESSION['id_user'].",%\" ORDER BY id_user";
    $q_ig_me_users = query($query);

    while ($row_ig_me_users = mysqli_fetch_object($q_ig_me_users)) {
        $ignore_me_list_names = $ignore_me_list_names." ".($row_ig_me_users->username).",";
    }
    $ignore_me_list_names = rtrim($ignore_me_list_names, ",");
    $ignore_me_list_names = htmlspecialchars($ignore_me_list_names, ENT_QUOTES);

    // echo "<h3>Основная информация - ".$_SESSION['user']."</h3>\n";
    echo "<h3>Мой профиль</h3>\n";
    echo "<ul class=\"pfl_info\">\n";
    echo "<li><b>Имя:</b> ".$_SESSION['user']."</li>\n";
    echo "<li><b>Дата регистрации:</b> ".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."</li>\n";
    echo "<li><b>Сообщений:</b> ".$row2->count."</li>\n";
    echo "<li><b>E-mail:</b> ".$row->email."</li>\n";
    echo "<li><b>Я игнорирую:</b> $ignore_list_names</li>\n";
    echo "<li><b>Меня игнорируют:</b> $ignore_me_list_names</li>\n";
    echo "</ul>\n";

    include "form_profile.html";
    echo "<a class=\"nav\" href=\"post_handlers/h_profile.php?action=delete&uid=".$_SESSION['id_user']."&sid=".session_id()."\" onclick=\"return confirm('Удалить учётную запись?');\">Удалить учётную запись</a>";
}

if (isset($_GET['code'])) {
    if ($_GET['code'] == "user_del_success") {
        echo "<p class=\"message\">Ваша учётная запись удалена.</p>";
    }
}
