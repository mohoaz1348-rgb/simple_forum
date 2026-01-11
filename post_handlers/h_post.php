<?php

include "../config.php";
include "../functions.php";

is_auth_user(); // Определяем авторизован пользователь или нет. Если нет, то выходим по exit.

$authorized_user = true;
$ignore_list = get_ignore_list();

// Формируем часть запроса игнорирования пользователя
if ($ignore_list == "") {
    $q_ignore_list = "";
} else {
    $q_ignore_list = "AND id_user not in (".trim($ignore_list, ",").")";
}




if (isset($_GET['action'])) {

    if ($_GET['action'] == "title") { // Меняем название темы
        if (isset($_POST['sid']) && isset($_POST['topic']) && isset($_POST["title"])) {
            if ($_POST['sid'] != session_id()) {
                exit;
            }
            $id_topic = (int)$_POST['topic'];

            $query = "SELECT id_user FROM topics WHERE id_topic=$id_topic";
            $theme_q = query($query);
            if ($theme_o = mysqli_fetch_object($theme_q)) {
                $id_author = $theme_o->id_user;

                // ------------- Проверка названия темы ---------------- //

                // Ограничим длину названия темы
                $title = trim(mb_substr(trim($_POST["title"]), 0, 80, "UTF-8"));
                if (empty($title)) {
                    echo "Введите название темы";
                    exit;
                }   // Проверим, что название темы не пустое
                $title = addslashes($title);

                if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner") || ($_SESSION['id_user'] == $id_author)) {
                    $query = "UPDATE topics SET title='$title' WHERE id_topic=$id_topic";
                    query($query);
                    header("Location: ../index.php?action=messages&topic=$id_topic#edit_title");
                }
            }

            if (isset($_POST["title"])) {

            }
        }
    }


    if ($_GET['action'] == "newtopic") { // Создаём новую тему и первый пост темы
        /* -------------------- Получение данных из полей формы ---------------------- */

        if (isset($_POST['sid'])) {
            if ($_POST['sid'] != session_id()) {
                exit;
            }

            $username = addslashes($_SESSION['user']);

            $id_user = $_SESSION['id_user'];

            // ------------- Проверка текста сообщения ---------------- //

            msg_prepare();

            // ------------- Проверка названия темы ---------------- //

            $title = trim(mb_substr(trim($_POST["title"]), 0, 80, "UTF-8"));   // Ограничим длину названия темы
            if (empty($title)) {
                echo "Введите название темы";
                exit;
            }   // Проверим, что название темы не пустое
            $title = addslashes($title);


            /* -------------------- Добавление записи в базу данных -------------------------- */


            $remote_addr = addslashes($_SERVER["REMOTE_ADDR"]);
            $ip = addslashes(getenv("HTTP_X_FORWARDED_FOR"));
            //	getenv("HTTP_CLIENT_IP");
            //	echo "ip: ", $ip;

            $query = "INSERT INTO topics (title, id_user, author, update_time) VALUES('$title', '$id_user', '$username', NOW())";
            query($query);
            $query = "INSERT INTO posts (id_user, username, msg, msg_search, time, remote_addr, http_info, id_topic, title_topic) VALUES ('$id_user', '$username', '$msg', '$msg_search', NOW(), '$remote_addr', '$ip', LAST_INSERT_ID(), '$title')";
            query($query);
            $query = "UPDATE posts SET last_edit=time WHERE id_post=LAST_INSERT_ID()";
            query($query);
        }

        header("Location: ../index.php");

    }





    if ($_GET['action'] == "new") { // Добавляем новый пост к существующей теме
        /* -------------------- Получение данных из полей формы ---------------------- */

        if (isset($_POST['sid'])) {
            if ($_POST['sid'] != session_id()) {
                exit;
            }



            $username = addslashes($_SESSION['user']);

            $id_user = $_SESSION['id_user'];

            // ------------- Проверка текста сообщения ---------------- //

            msg_prepare();

            // - Определяем название темы по $id_topic и вообще, есть ли тема с таким id - //

            $id_topic = (int)$_POST['id_topic'];
            $query = "SELECT count(*) AS count FROM topics WHERE id_topic={$id_topic}";
            $number_of_topics = mysqli_fetch_array(query($query));

            if ($number_of_topics[0] > 0) {
                $query = "SELECT title, closed FROM topics WHERE id_topic={$id_topic}";
                $query_result = query($query);
                $row = mysqli_fetch_object($query_result);
                $title = "Re: ".addslashes($row->title);
                // Проверим, что тема открыта. Если закрыта, то выходим и сообщение не добавляем.

                if ($row->closed == 1) {
                    echo "Тема, в которую Вы пытаетесь написать сообщение, закрыта!";
                    exit;
                }


                /* -------------------- Добавление записи в базу данных -------------------------- */


                $remote_addr = addslashes($_SERVER["REMOTE_ADDR"]);
                $ip = addslashes(getenv("HTTP_X_FORWARDED_FOR"));
                //	getenv("HTTP_CLIENT_IP");
                //	echo "ip: ", $ip;

                $query = "INSERT INTO posts (id_user, username, msg, msg_search, time, remote_addr, http_info, id_topic, title_topic) VALUES ('$id_user', '$username', '$msg', '$msg_search', NOW(), '$remote_addr', '$ip', '$id_topic', '$title')";
                query($query);
                $query = "UPDATE posts SET last_edit=time WHERE id_post=LAST_INSERT_ID()";
                query($query);
                //	setcookie("Name", stripslashes($username), 0x6FFFFFFF);
            }
        }
        //echo $msg;
        header("Location: ../index.php?action=messages&topic={$id_topic}&page=1000");
    }





    if ($_GET['action'] == "edit") { // Редактируем пост
        /* -------------------- Получение данных из полей формы ---------------------- */

        if (isset($_POST['pid']) && isset($_POST['sid'])) {
            if ($_POST['sid'] != session_id()) {
                exit;
            }

            $id_post = (int)$_POST['pid'];
            if ($id_post < 1) {
                exit;
            }

            // ------------- Проверка текста сообщения ---------------- //

            msg_prepare();

            if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
                $q_user = "";
            } else {
                $q_user = " AND id_user=".$_SESSION['id_user'];
            }

            //	mysqli_query("UPDATE posts SET msg='$msg', msg_search='$msg_search', last_edit=NOW() WHERE id_post=$id_post AND id_user=".$_SESSION['id_user']) or die ("Invalid query: " . mysqli_error());

            $query = "SELECT id_user FROM posts WHERE id_post=$id_post".$q_user;
            $q = query($query);

            if ($row2 = mysqli_fetch_object($q)) {
                $id_user = $row2 -> id_user;

                $query = "SELECT user_group FROM users WHERE id_user=$id_user";
                $q3 = query($query);
                if ($row3 = mysqli_fetch_object($q3)) {
                    $user_group = $row3 -> user_group; // Группа пользователя, которому принадлежит редактируемый пост

                    //if (($id_user==$_SESSION['id_user']) || ($_SESSION['group'] == "moderator"))
                    if (($id_user == $_SESSION['id_user']) || (($_SESSION['group'] == "moderator") && ($user_group == "user")) || (($_SESSION['group'] == "owner") && (($user_group == "user") || ($user_group == "moderator")))) {
                        $query = "UPDATE posts SET id_editor=".$_SESSION['id_user'].", msg='$msg', msg_search='$msg_search', last_edit=NOW() WHERE id_post=$id_post".$q_user;
                        query($query);

                        header("Location: ../index.php?action=messages&msg={$id_post}#{$id_post}");
                    } else {
                        $code = "post_not_exist";
                    }
                }
            } else {
                $code = "post_not_exist";
            }

        }
    }



    if ($_GET['action'] == "delete") { // Удаляем пост
        if (isset($_GET['pid']) && isset($_GET['sid'])) {
            if ($_GET['sid'] != session_id()) {
                exit;
            }

            $id_post = (int)$_GET['pid'];
            if ($id_post < 1) {
                exit;
            }

            $query = "SELECT id_user, id_topic FROM posts WHERE id_post=$id_post AND active=1";
            $q = query($query);
            if ($row = mysqli_fetch_object($q)) { // если удаляемое сообщение существует и ещё не удалено, то
                $id_user = $row -> id_user;
                $id_topic = $row -> id_topic;

                $query = "SELECT user_group FROM users WHERE id_user=$id_user";
                $q3 = query($query);
                if ($row3 = mysqli_fetch_object($q3)) {
                    $user_group = $row3 -> user_group; // Группа пользователя, которому принадлежит удаляемый пост

                    if (($id_user == $_SESSION['id_user']) || (($_SESSION['group'] == "moderator") && ($user_group == "user")) || (($_SESSION['group'] == "owner") && (($user_group == "user") || ($user_group == "moderator")))) {
                        // Если удаляемое сообщение принадлежит пользователю, который его удаляет, или удаляющим является модератор или владелец форума, то
                        $query = "UPDATE posts SET active=0 WHERE id_post=$id_post";
                        query($query); // Прибиваем сообщение

                        $query = "SELECT count(*) AS count FROM posts WHERE id_topic=".$id_topic." AND active=1";
                        $q = query($query); // Считаем оставшиеся в этой теме сообщения
                        $count_array = mysqli_fetch_array($q);
                        if ($count_array[0] == 0) { // Если в теме больше нет сообщений, то прибиваем тему и переходим на главную страницу
                            $query = "UPDATE topics SET active=0 WHERE id_topic=".$id_topic;
                            query($query);
                            //echo "active=0";
                            header("Location: ../index.php");
                        } else { // Если сообщения в теме остались, то переходим к сообщению перед удалённым
                            echo $q_ignore_list;
                            $query = "SELECT MAX(id_post) AS id_post FROM posts WHERE id_post<$id_post AND active=1 AND id_topic=".$id_topic." $q_ignore_list";
                            $q2 = query($query);
                            // Находим id активного сообщения, которое стоит перед удалённым и не принадлежит проигнорированному пользователю

                            if ($row2 = mysqli_fetch_object($q2)) {
                                if ($row2->id_post) {
                                    header("Location: ../index.php?action=messages&msg=".$row2->id_post."#".$row2->id_post); // и переходим к нему
                                } else {
                                    $query = "SELECT MIN(id_post) AS id_post FROM posts WHERE id_post>$id_post AND active=1 AND id_topic=".$id_topic." $q_ignore_list";
                                    $q4 = query($query);
                                    // Находим id активного сообщения, которое стоит после удалённого и не принадлежит проигнорированному пользователю
                                    if ($row4 = mysqli_fetch_object($q4)) {
                                        if ($row4->id_post) {
                                            header("Location: ../index.php?action=messages&msg=".$row4->id_post."#".$row4->id_post); // и переходим к нему
                                        } else { // если в теме остались только сообщения проигнорированных пользователей то,
                                            header("Location: ../index.php"); // переходим на главную страницу форума
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        echo "Вы не имеете права удалять это сообщение.";
                        exit;
                    }
                }
            } else {
                echo "Сообщение не найдено";
                exit;
            }
        }

    }

}
