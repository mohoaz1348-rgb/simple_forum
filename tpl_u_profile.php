<?php

global $authorized_user, $months2;

if ($authorized_user && isset($_GET['uid'])) {

    $id_user = (int)$_GET['uid'];
    if ($id_user > 0) {

        $query = "SELECT id_user, username, email, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time, user_group, active, DATE_FORMAT(banned_till, '%e-%c-%Y, %T') AS banned_till FROM users WHERE id_user=$id_user";
        $users_q = query($query);

        if ($row = mysqli_fetch_object($users_q)) {

            $query = "SELECT count(*) AS count FROM posts WHERE id_user=".$id_user." AND active=1";
            $posts = query($query);
            $row2 = mysqli_fetch_object($posts);

            $i = explode("-", $row->time);
            $j = explode("-", $row->banned_till);

            if (($row->active) == 1) {

                echo "<h3>Информация о пользователе ".$row->username."</h3>\n";
                echo "<ul class=\"pfl_info\">\n";
                echo "<li><b>Имя:</b> ".$row->username."</li>\n";
                echo "<li><b>Дата регистрации:</b> ".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."</li>\n";
                echo "<li><b>Сообщений:</b> ".$row2->count."</li>\n";
                if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
                    echo "<li><b>E-mail:</b> ".$row->email."</li>\n";
                }

                // Определяем не забанен ли пользователь

                $query = "SELECT count(*) AS count FROM users WHERE id_user=$id_user AND banned_till<=NOW()";
                $user_q = query($query);

                $user_arr = mysqli_fetch_array($user_q);

                if ($user_arr[0] == 0) {
                    echo "<li><b>Забанен до:</b> ".$j[0]."&nbsp;".$months2[$j[1]]."&nbsp;".$j[2]."</li>";
                }

                echo "</ul>\n";

                // Игнорирование пользователя тем, кто зашёл в его профиль.

                // Определение проигнорирован пользователь или нет

                // Получаем список проигнорированных пользователей из базы и помещаем его в $ignore_list
                $query = "SELECT ignore_list FROM users WHERE id_user=".$_SESSION['id_user']."";
                $q_ig_users = query($query);
                $a_ig_users = mysqli_fetch_array($q_ig_users);

                if ($a_ig_users[0] == null) {
                    $ignore_list = "";
                } else {
                    $ignore_list = $a_ig_users[0];
                }
                if (stripos($ignore_list, $id_user.",") === false) {
                    // Эта переменная определяет проставление чекбокса "Проигн. пользователя" при просмотре его профиля
                    $is_ignored = "";
                } else {
                    $is_ignored = "CHECKED";
                }
                if (($_SESSION['group'] == "user") && ($_SESSION['id_user'] != $row->id_user) && ($row->user_group == "user")) {
                    include "form_ignore.html";
                }

                if ($_SESSION['group'] == "moderator") {
                    if (($row->user_group) == "user") {
                        include "form_moderator.html";
                    }
                }

                if ($_SESSION['group'] == "owner") {
                    if ((($row->user_group) == "user") || (($row->user_group) == "moderator")) {
                        include "form_moderator.html";
                        include "form_owner.html";
                    }
                }
            } else {
                if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
                    echo "<h3>Основная информация - ".$row->username."</h3>\n";
                    echo "<ul class=\"pfl_info\">\n";
                    echo "<li><b>Имя:</b> ".$row->username."</li>\n";
                    echo "<li><b>Дата регистрации:</b> ".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."</li>\n";
                    echo "<li><b>Сообщений:</b> ".$row2->count."</li>\n";
                    echo "<li><b>E-mail:</b> ".$row->email."</li>\n";
                    echo "</ul>\n";

                    echo "<p class=\"error\">Учётная запись удалена.</p>";
                    echo "<a class=\"nav\" href=\"post_handlers/h_moderate.php?action=resurrect&uid=".$row->id_user."&sid=".session_id()."\">Восстановить учётную запись</a>";
                }
            }
        }
    }
}
