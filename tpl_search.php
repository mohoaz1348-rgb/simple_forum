<?php

global $q_ignore_list, $ppp;

echo "<h3>Поиск - задайте критерии</h3>";

// --- Инициализация и проверка КРИТЕРИЕВ ПОИСКА данных ---------- //

if (isset($_GET["name_crit"])) {
    // Ограничим длину имени и уберем пробельные символы с концов
    $name_crit = trim(mb_substr(trim($_GET["name_crit"]), 0, 33, "UTF-8"));
} else {
    $name_crit = "";
}

if (isset($_GET["msg_crit"])) {
    // Ограничим длину сообщения
    $msg_crit = trim(mb_substr(trim($_GET["msg_crit"]), 0, 64, "UTF-8"));
} else {
    $msg_crit = "";
}

// ---------------------- ФОРМА поиска ---------------------------//

include "form_search.html";

// ------------ Определение параметров панели НАВИГАЦИИ ---------- //

if (!(empty($msg_crit) && empty($name_crit))) {

    //Меняем подряд идущие пробельные символы на один обычный пробел
    $msg_crit = preg_replace("/(\s){1,}/", "\x20", $msg_crit);
    $msg_crit_array = explode("\x20", $msg_crit);
    $query_msg_crit = "";
    foreach ($msg_crit_array as $msg_crit_part) {
        $query_msg_crit .= " msg_search LIKE \"%".prep_for_like($msg_crit_part)."%\" AND";
    }
    $query_crit = "WHERE".$query_msg_crit." username LIKE \"%".prep_for_like($name_crit)."%\" ";

    $query = "SELECT count(*) AS count FROM posts ".$query_crit."AND active=1 $q_ignore_list";
    //echo $query;
    // Количество найденных сообщений
    $number_of_posts = mysqli_fetch_array(query($query));

    // Вывод количества найденных постов в браузер
    echo "<p>Всего найдено сообщений: <b>", $number_of_posts[0], "</b></p>";

    // Количество страниц, для отображения всех постов
    $number_of_pages = ceil($number_of_posts[0] / $ppp);


    // ------------- НАЧАЛО :: Если посты найдены, то выводим --------------- //

    if ($number_of_posts[0] > 0) {

        // -------------- Извлечение записей из БАЗЫ ДАННЫХ ----------------- //


        $page = 1; // Страница гостевой книги, которую надо показать по умолчанию

        // ------------- Проверка поступившего параметра "page" ------- //

        if (isset($_GET["page"])) {
            $page = (int)$_GET["page"];
            //		echo "Page int: ", $page, "<br />";
            if ($page < 1) {
                $page = 1;
            }
            if ($page > $number_of_pages) {
                $page = $number_of_pages;
            }
        }
        //		echo "Page: ", $page, "<br />";

        // ------------------ Запрос к БД ---------------------- //

        $offset = ($page - 1) * $ppp;

        $query = "SELECT id_post, id_user, id_editor, username, msg, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time, DATE_FORMAT(last_edit, '%e-%c-%Y, %T') AS last_edit, title_topic FROM posts ".$query_crit."AND active=1 $q_ignore_list ORDER BY id_post DESC LIMIT $offset, $ppp";
        //	echo $query;
        // Поиск сообщений
        $posts = query($query);

        // ------------------------ НАЧАЛО :: Вывод сообщений на страницу ----------------------------- //

        // Вывод панели навигации
        insert_navigation("action=search&name_crit=".urlencode($name_crit)."&msg_crit=".urlencode($msg_crit)."&");
        insert_posts($posts, "search"); // Вывод сообщений
        insert_navigation("action=search&name_crit=".urlencode($name_crit)."&msg_crit=".urlencode($msg_crit)."&");

        // ------------------------- КОНЕЦ :: Вывод сообщений на страницу --------------------------- //
    }
}
