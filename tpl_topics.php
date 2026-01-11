<?php

global $authorized_user, $tpp, $months2;

$page = 1; // Страница с темами, которую надо показать
$show_stat = false;

// Немного изменяем часть запроса связанную с проигнорированными пользователями
$q_ignore_list = str_replace("id_user", "p.id_user", $q_ignore_list);

echo "<h3>Список тем</h3>\n";

// ------------------------- Определение параметров панели НАВИГАЦИИ --------------------------------------- //

//$number_of_posts = mysqli_fetch_array(mysqli_query("SELECT count(*) AS count FROM topics WHERE active=1")); // Общее число тем

$query = "SELECT count(p.id_topic) AS count FROM topics t, posts p WHERE t.id_topic=p.id_topic AND p.active=1 $q_ignore_list GROUP BY p.id_topic";
$posts = query($query);

$number_of_posts = 0;
while (mysqli_fetch_object($posts)) {
    $number_of_posts++;
}

//echo "Число постов: ", $number_of_posts[0], "<br />";
$number_of_pages = ceil($number_of_posts / $tpp); // Количество страниц, для отображения всех тем
//echo $number_of_pages, "<br />";

if ($number_of_posts > 0) {

    $show_stat = true;

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
    $offset = ($page - 1) * $tpp;
    $query = "SELECT MAX(p.id_post) AS pid, p.id_topic, t.title, t.id_user, t.author, t.pinned, t.closed, count(p.id_topic) AS count, DATE_FORMAT(MAX(p.time), '%e-%c-%Y, %T') AS time FROM topics t, posts p WHERE t.id_topic=p.id_topic AND p.active=1 $q_ignore_list GROUP BY p.id_topic ORDER BY pinned DESC, pid DESC LIMIT $offset, $tpp ";
    $posts = query($query);


    insert_navigation();  // Постраничная навигация
    //	insert_posts();
    echo "		<table class=\"topics\" cellspacing=\"1\">\n";
    echo "		<col width=55%>\n";
    echo "		<col width=20%>\n";
    echo "		<col width=5%>\n";
    echo "		<col width=20%>\n";
    echo "		<tr>\n";
    echo "		<th>Тема</th>\n";
    echo "		<th>Автор</th>\n";
    echo "		<th>Ответов</th>\n";
    echo "		<th>Последнее&nbsp;сообщение</th>\n";
    echo "		</tr>\n";

    $j = 0;
    while ($row = mysqli_fetch_object($posts)) {
        if ($j == 0) {
            echo "			<tr class=\"bg2\">\n";
            $j++;
        } else {
            echo "			<tr>\n";
            $j = 0;
        }

        echo "			<td>\n";
        if ($row->pinned == 1) {
            echo "Прибито. ";
        }
        echo "				<a href=\"index.php?action=messages&topic=".$row->id_topic."\">".htmlspecialchars($row->title, ENT_QUOTES)."</a>\n";
        if ($row->closed == 1) {
            echo " (закрыто)";
        }

        echo "			</td>\n";

        echo "			<td>\n";
        if ($authorized_user) {
            $query = "SELECT count(*) AS count FROM users WHERE id_user=".$_SESSION['id_user']." AND ignore_list LIKE \"%,".$row->id_user.",%\"";
            $q_is_ignored_author = query($query);
            $a_is_ignored_author = mysqli_fetch_array($q_is_ignored_author);
            if ($a_is_ignored_author[0] == 0) {
                echo "				".htmlspecialchars($row->author, ENT_QUOTES)."\n";
            } else {
                echo "				<span style=\"color: #888\">Проигнорированный пользователь</span>\n";
            }
        } else {
            echo "				".htmlspecialchars($row->author, ENT_QUOTES)."\n";
        }
        echo "			</td>\n";

        echo "			<td align=\"center\">\n";
        echo "				".(($row->count) - 1)."\n";
        echo "			</td>\n";

        $i = explode("-", $row->time);
        echo "			<td>\n";
        echo "				".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."\n";
        echo "			</td>\n";

        echo "			</tr>\n";

    } // Конец цикла вывода тем

    echo "		</table>";

    insert_navigation();

} else {
    echo "<p align=\"center\"><span style=\"color: #990000\">Нет ни одной темы.</span></p>";
}

if ($authorized_user) {
    include "form_new_topic.html";
};

if ($show_stat) {
    // Получение и вывод статистики форума.
    // Показываем количество сообщений, тем и активных пользователей (т.е. профили которых не удалены - active == 1).
    // При подсчёте количества сообщений и тем сообщения и темы от проигнорированных пользователей тоже учитываются.

    $query = "SELECT count(*) AS count FROM posts WHERE active=1";
    $q_posts_count = query($query);
    $a_posts_count = mysqli_fetch_array($q_posts_count);

    $query = "SELECT count(*) AS count FROM topics WHERE active=1";
    $q_topics_count = query($query);
    $a_topics_count = mysqli_fetch_array($q_topics_count);

    $query = "SELECT count(*) AS count FROM users WHERE active=1";
    $q_users_count = query($query);
    $a_users_count = mysqli_fetch_array($q_users_count);

    $query = "SELECT id_user, username FROM users WHERE active=1 ORDER BY id_user DESC LIMIT 0,1";
    $q_last_user = query($query);
    if ($o_last_user = mysqli_fetch_object($q_last_user)) {
        $last_user = htmlspecialchars($o_last_user->username, ENT_QUOTES);
    }

    echo "<p class=\"stat\"><span style=\"color: #888\"><b>Статистика форума:</b> написано <b>{$a_posts_count[0]}</b> сообщений в <b>{$a_topics_count[0]}</b> темах; зарегистрировано <b>{$a_users_count[0]}</b> пользователей; последний пользователь <b>$last_user</b></span></p>";
}
