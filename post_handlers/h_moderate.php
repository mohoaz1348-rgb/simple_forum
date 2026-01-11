<?php

include "../config.php";

is_auth_user(); // Определяем авторизован пользователь или нет. Если нет, то выходим по exit.

if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) { // Если пользователь модератор или владелец, то
    if (isset($_GET['action'])) {
        if (isset($_POST['uid']) && isset($_POST['sid'])) {
            if ($_POST['sid'] != session_id()) {
                exit;
            }
            $id_user = (int)$_POST['uid'];
            if ($id_user < 1) {
                exit;
            }

            if ($_GET['action'] == "ban") { // Баним
                $days = 0; // Срок бана по-умолчанию
                $days = (int)$_POST['days'];
                if ($days < 0) {
                    $days = 0;
                }
                if ($days > 9999) {
                    $days = 9999;
                }

                if ($_SESSION['group'] == "owner") {
                    $query = "SELECT count(*) AS count FROM users WHERE id_user=$id_user AND active=1";
                    $users_q = query($query);
                    $users_count = mysqli_fetch_array($users_q);
                    if ($users_count[0] > 0) {
                        $query = "UPDATE users SET banned_till=DATE_ADD(NOW(), INTERVAL $days DAY) WHERE id_user=$id_user AND active=1";
                        query($query);
                        header("Location: ../index.php?action=u_profile&uid=$id_user");
                    }
                }
                if ($_SESSION['group'] == "moderator") {
                    $query = "SELECT count(*) AS count FROM users WHERE id_user=$id_user AND active=1 AND user_group='user'";
                    $users_q = query($query);
                    $users_count = mysqli_fetch_array($users_q);
                    if ($users_count[0] > 0) {
                        $query = "UPDATE users SET banned_till=DATE_ADD(NOW(), INTERVAL $days DAY) WHERE id_user=$id_user AND active=1 AND user_group='user'";
                        query($query);
                        header("Location: ../index.php?action=u_profile&uid=$id_user");
                    }
                }
            }

            if ($_GET['action'] == "group") { // Меняем группу пользователя
                if ($_SESSION['group'] == "owner") {
                    $query = "SELECT count(*) AS count FROM users WHERE id_user=$id_user AND active=1";
                    $users_q = query($query);
                    $users_count = mysqli_fetch_array($users_q);
                    if ($users_count[0] > 0) {
                        if (isset($_POST['group'])) {
                            if (($_POST['group'] == "user") || ($_POST['group'] == "moderator")) {
                                $query = "UPDATE users SET user_group='".$_POST['group']."' WHERE id_user=$id_user AND active=1";
                                query($query);
                                header("Location: ../index.php?action=u_profile&uid=$id_user");
                            }
                        }
                    }
                }
            }
        }

        if ($_GET['action'] == "resurrect") { // Восстановление профиля пользователя
            if (isset($_GET['uid']) && isset($_GET['sid'])) {
                if ($_GET['sid'] != session_id()) {
                    exit;
                }
                $id_user = (int)$_GET['uid'];
                if ($id_user < 1) {
                    exit;
                }

                $query = "SELECT count(*) AS count FROM users WHERE id_user=$id_user AND active=0";
                $users_q = query($query);
                $users_count = mysqli_fetch_array($users_q);
                if ($users_count[0] > 0) {
                    $query = "UPDATE users SET active=1 WHERE id_user=$id_user AND active=0";
                    query($query);
                    header("Location: ../index.php?action=u_profile&uid=$id_user");
                }
            }
        }


        if ($_GET['action'] == "pin") { // Пришпилить тему
            if (isset($_POST['sid']) && isset($_POST['topic'])) {
                if ($_POST['sid'] != session_id()) {
                    exit;
                }
                $id_topic = (int)$_POST['topic'];
                if ($id_topic < 1) {
                    exit;
                }
                $is_pinned = 0;

                if (isset($_POST['pin'])) {
                    if ($_POST['pin'] == "pinned") {
                        $is_pinned = 1;
                    }
                }

                $query = "UPDATE topics SET pinned=$is_pinned WHERE id_topic=$id_topic";
                query($query);
                header("Location: ../index.php?action=messages&topic=$id_topic#f_pin");
            }
        }

        if ($_GET['action'] == "close") { // Закрыть тему
            if (isset($_POST['sid']) && isset($_POST['topic'])) {
                if ($_POST['sid'] != session_id()) {
                    exit;
                }
                $id_topic = (int)$_POST['topic'];
                if ($id_topic < 1) {
                    exit;
                }
                $is_closed = 0;

                if (isset($_POST['close'])) {
                    if ($_POST['close'] == "closed") {
                        $is_closed = 1;
                    }
                }

                $query = "UPDATE topics SET closed=$is_closed WHERE id_topic=$id_topic";
                query($query);
                header("Location: ../index.php?action=messages&topic=$id_topic#f_close");
            }
        }
    }
} else {
    echo "Недостаточно прав для выполнения действия.";
    exit;
}
