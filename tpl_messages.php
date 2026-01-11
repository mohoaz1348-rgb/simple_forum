<?php
global $q_ignore_list, $ppp, $authorized_user;

$topic = 1; // Тема, которую показываем по-умолчанию
$page = 1; // Страница темы, которую надо показать
$code = "";

// Получаем список проигнорированных пользователей из базы и формируем часть запроса
//$q_ignore_list = create_ignore_query();

// ------------- Если задан параметр $_GET['msg'], то формируем $topic и $page ----------------- //

if (isset($_GET['msg'])) {
    $msg = (int)$_GET['msg'];
    if ($msg > 0) {
        $query = "SELECT id_topic FROM posts WHERE id_post=$msg AND active=1 $q_ignore_list";
        $id_topic_q = query($query);
        if ($row = mysqli_fetch_object($id_topic_q)) {
            $topic = $row->id_topic;
            $query = "SELECT count(*) AS count FROM posts WHERE id_topic=$topic AND id_post<$msg AND active=1 $q_ignore_list";
            $page_q = query($query);
            $offset_q = mysqli_fetch_array($page_q);
            if ($offset_q[0] > 0) {
                //	echo $page."<br>";
                $page = ceil(($offset_q[0] + 1) / $ppp);
                //	echo $page."<br>";
            }
        } else {
            $code = "msg_not_found";
        }
    } else {
        $code = "msg_not_found";
    }
}
//	echo $topic."<br>";
//	echo $page."<br>";

if (isset($_GET["topic"])) {
    $topic = (int)$_GET["topic"];
    //	echo "Page int: ", $page, "<br />";
    if ($topic < 1) {
        $topic = 1;
    }

}
?>
	
		
		<div id="main">
		

<?php

if ($code == "msg_not_found") {
    echo "<p class=\"error\">Сообщение не найдено или оно принадлежит проигнорированному пользователю.</p>";
} else {
    // ------------------------- Определение параметров панели НАВИГАЦИИ --------------------------------------- //

    $query = "SELECT count(*) AS count FROM posts WHERE id_topic={$topic} AND active=1 $q_ignore_list";
    $number_of_posts = mysqli_fetch_array(query($query)); // Общее число постов
    //echo $number_of_posts[0], "<br />";
    $number_of_pages = ceil($number_of_posts[0] / $ppp); // Количество страниц, для отображения всех постов
    //echo $number_of_pages, "<br />";

    if ($number_of_posts[0] > 0) {

        // ------------------ Извлечение записей из БАЗЫ ДАННЫХ -------------------------- //

        if (isset($_GET["page"])) {
            $page = (int)$_GET["page"];
            //	echo "Page int: ", $page, "<br />";
            if ($page < 1) {
                $page = 1;
            }
            if ($page > $number_of_pages) {
                $page = $number_of_pages;
            }
        }
        //echo "Page: ", $page, "<br />";



        $offset = ($page - 1) * $ppp;
        $query = "SELECT id_post, id_user, id_editor, username, msg, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time, DATE_FORMAT(last_edit, '%e-%c-%Y, %T') AS last_edit, title_topic FROM posts WHERE id_topic={$topic} AND active=1 $q_ignore_list ORDER BY id_post LIMIT $offset, $ppp ";
        $posts = query($query);

        $query = "SELECT title, id_user, pinned, closed FROM topics WHERE id_topic={$topic}";
        $theme_q = query($query);
        $theme_o = mysqli_fetch_object($theme_q);
        $theme = $theme_o->title;
        $is_pinned_int = $theme_o->pinned;
        $is_pinned = "";
        $is_closed_int = $theme_o->closed;
        $is_closed = "";
        $id_author = $theme_o->id_user;

        echo "<h3>Просмотр темы - ".htmlspecialchars($theme, ENT_QUOTES)."</h3>";
        //echo "<h3></h3>";

        echo "<h4><a href=\"index.php\">Главная</a> / <a href=\"index.php?action=messages&topic=".$topic."\"><span class=\"active\">".htmlspecialchars($theme, ENT_QUOTES)."</span></a></h3>";

        insert_navigation("action=messages&topic=$topic&");  // Постраничная навигация
        insert_posts($posts, "main");
        insert_navigation("action=messages&topic=$topic&");

    } else {
        echo "<p align=\"center\"><span color=#990000>Такой темы нет или в теме присутствуют только сообщения от проигнорированных пользователей.</span></p>";
    }

    if ($authorized_user) {
        if ($is_closed_int == 0) {
            include "form_answer.html";

            if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner") || ($_SESSION['id_user'] == $id_author)) {
                include "form_edit_title.html";
            }
        } else {
            echo "<p class=imp_notice>Тема закрыта</p>";
        }


        if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
            if ($is_pinned_int == 1) {
                $is_pinned = "CHECKED";
            }
            include "form_pinned.html";

            if ($is_closed_int == 1) {
                $is_closed = "CHECKED";
            }
            include "form_closed.html";
        }
    }

}
?>
		
				
		</div> <!-- id="main" -->
	
