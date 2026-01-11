<?php

// Удаление пользовательских тэгов, для работы поиска по сообщениям
function delete_user_tags($msg_search)
{
    $msg_search = preg_replace("/\[q\=([^\r\n]*)\](.+)\[\/q\]/Uis", "$1 $2", $msg_search);

    $msg_search = preg_replace("/\[url\=(.*)\]/Ui", "$1 ", $msg_search);
    $msg_search = str_ireplace("[/url]", "", $msg_search);

    $b_i_tags = array("[b]","[/b]","[i]","[/i]");
    $msg_search = str_ireplace($b_i_tags, "", $msg_search);

    return $msg_search;
}


function insert_posts($posts, $action = "main")
{
    global $theme, $months2, $msg_crit, $msg_crit_array, $authorized_user;

    echo "		<ul id=\"posts\">\n";
    echo "			<li class=\"separator\"></li>\n";

    while ($row = mysqli_fetch_object($posts)) {
        $edit_delete = "";

        echo "			<li id=\"".$row->id_post."\">\n";
        echo "				<div class=\"post_info\">\n";
        //	echo "				<a href=#><span class=\"theme\"> ".htmlspecialchars($row->title_topic, ENT_QUOTES)."</span></a>\n";  // Название темы
        $i = explode("-", $row->time);
        $username = $row->username;
        $id_user = $row->id_user;
        //		if ($action == "search") // Если выводим сообщения на странице поиска, то:
        //		{
        //			echo "				<p class=\"date\"><a href=\"index.php?action=calendar&day=".$i[0]."&month=".$i[1]."&year=".substr($i[2],0,4)."#".$row->id_post."\" title=\"Перейти к этому сообщению\">".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."</a></p>\n"; // Дата и время
        //		}
        //		else // Если выводим сообщения в ленте или на странице календаря, то:
        //		{
        if ($action == "main") {
            if ($authorized_user) {
                $query = "SELECT user_group FROM users WHERE id_user=$id_user";
                $q3 = query($query);
                if ($row3 = mysqli_fetch_object($q3)) {
                    // Группа пользователя, которому принадлежит удаляемый пост
                    $user_group = $row3 -> user_group;

                    //if (($id_user==$_SESSION['id_user']) || ($_SESSION['group'] == "moderator"))
                    if (($id_user == $_SESSION['id_user']) || (($_SESSION['group'] == "moderator") && ($user_group == "user")) || (($_SESSION['group'] == "owner") && (($user_group == "user") || ($user_group == "moderator")))) {
                        $edit_delete = "<a href=\"index.php?action=edit_msg&pid=".$row->id_post."&sid=".session_id()."\">Редактировать</a>&nbsp;|&nbsp;<a href=\"post_handlers/h_post.php?action=delete&pid=".$row->id_post."&sid=".session_id()."\" onclick=\"return confirm('Удалить это сообщение?');\">Удалить</a>&nbsp;| ";
                    }
                }
            }
        }
        echo "				<p class=\"date\">$edit_delete<a href=\"index.php?action=messages&msg=".$row->id_post."#".$row->id_post."\" title=\"Ссылка на сообщение\">#</a>&nbsp;&nbsp;<span class=\"date\">".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."</span></p>\n"; // Дата и время
        //		}

        // Имя пользователя
        echo "				<p class=\"author\">".htmlspecialchars($username, ENT_QUOTES)."</p>\n";

        echo "				</div>\n";

        $msg = $row->msg;
        $msg = nl2br(htmlspecialchars($msg, ENT_QUOTES));
        $msg = replace_user_tags($msg);

        // -------------------  Подсветка найденных совпадений ---------------------- //

        if ($action == "search") {
            if (!empty($msg_crit)) {
                $msg = msg_hilight($msg, $msg_crit_array);
            }
        }

        // ---------------- Замена табуляций и НЕодиночных пробелов ---------------------- //
        /*
                $msg = str_replace("\t", "        ", $msg); // Замена табуляции на 8 обычных пробелов
                $msg = str_replace("  ", "&nbsp; ", $msg); // Замена парных пробелов (два раза)
                $msg = str_replace("  ", "&nbsp; ", $msg);
                $msg = preg_replace("/^\x20/", "&nbsp;",$msg); //Заменяем ОДИНОЧНЫЙ пробел в начале сообщения на &nbsp;
                $msg = preg_replace("/(?<=(\s))\x20/", "&nbsp;",$msg); // Заменяем ОДИНОЧНЫЙ пробел в начале строки на &nbsp;
        */

        echo "				".$msg."\n"; // Вывод сообщения
        $last_edit = $row->last_edit;
        if (($last_edit != $row->time) && ($row->id_editor)) {
            $query = "SELECT username FROM users WHERE id_user=".$row->id_editor;
            $q_author = query($query);
            $name_author = htmlspecialchars(mysqli_fetch_object($q_author)->username, ENT_QUOTES);
            $k = explode("-", $last_edit);
            echo "<p class=\"last_edit\">Сообщение последний раз отредактировано <b>".$k[0]."&nbsp;".$months2[$k[1]]."&nbsp;".$k[2]."</b> пользователем <b>$name_author</b></p>";
        }

        echo "			</li>\n";

    } // Конец цикла вывода сообщений

    echo "		</ul>\n";
}



function msg_hilight($msg, $msg_crit_array)
{
    // Разбиваю на части (в качестве разделителей - все тэги)
    $msg_split_tags = preg_split("/(\<.*\>)/U", $msg, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $msg_hilighted = "";
    foreach ($msg_split_tags as $msg_part) {
        if (!preg_match("/(\<.*\>)/U", $msg_part)) {
            // Декодирую строку
            $msg_part = htmlspecialchars_decode($msg_part, ENT_QUOTES);
            $msg_crit_ptn = "";
            $i = 0;
            // Формирую шаблон поиска (чтобы подсвечивалось несколько слов)
            foreach ($msg_crit_array as $msg_crit_part) {
                if ($i >= 1) {
                    $msg_crit_ptn .= "|";
                }
                $msg_crit_ptn .= "(?:".preg_quote($msg_crit_part, "/").")";
                $i++;
            }
            //	echo $msg_crit_ptn."<br>\n";
            // Подсвечиваю найденное
            $msg_part = preg_replace("/(".$msg_crit_ptn.")/iu", "[hlght]$1[/hlght]", $msg_part);

            $msg_part = htmlspecialchars($msg_part, ENT_QUOTES);
            $msg_part = preg_replace("/\[hlght\](.*)\[\/hlght\]/Ui", "<span class=\"hilight\">$1</span>", $msg_part);
        }
        $msg_hilighted .= $msg_part;
        //		echo "Part: ".$msg_part."<br>\n";
    }
    return $msg_hilighted;
}




function replace_user_tags($msg)
{
    //	global $msg;
    $tags_array = array(
      '[b]' => '[/b]',
      '[i]' => '[/i]',
    );
    // Тэги, которые удаляются внутри тега <a>
    $b_i_tags = array("[b]","[/b]","[i]","[/i]");

    // ---------------------- НАЧАЛО :: Замена тэгов ----------------------------- //

    // Цитаты БЕЗ возможности указания автора:

    //	$msg = preg_replace("/\[q\](.*)\[\/q\]/Uis", "</p><blockquote><p>$1</p></blockquote><p>", $msg); // Заменяю тэги цитат и вставляю тэги параграфов
    //	$msg_split_quote = preg_split("/(\<\/p\>\<(?:\/){0,1}blockquote\>\<p\>)/", $msg, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE); // Разбиваю на части (в качестве разделителей - тэги цитат)

    // Цитаты С возможностью указания автора:

    $msg = preg_replace("/\[q\=([^\r\n]*)\](.+)\[\/q\]/Uis", "</p><cite>$1</cite><blockquote><p>$2</p></blockquote><p>", $msg); // Заменяю тэги цитат и вставляю тэги параграфов
    // Разбиваю на части (в качестве разделителей - тэги цитат)
    $msg_split_quote = preg_split("/(\<\/p\>(?:\<cite\>.*\<\/cite\>)?\<(?:\/){0,1}blockquote\>\<p\>)/", $msg, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

    $msg_quote = "";
    foreach ($msg_split_quote as $msg_part_quote) {
        // Вставляю тэги параграфов вместо 2-х и более подряд идущих <br />
        $msg_part_quote = preg_replace("/((\<br \/\>)(\s){0,}){2,}/i", "</p><p>\n", $msg_part_quote);

        // Разбиваю на части (в качестве разделителя - </p><p>)
        $msg_split_p = preg_split("/(\<\/p\>\<p\>)/", $msg_part_quote, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

        $msg_p = "";

        foreach ($msg_split_p as $msg_part_p) {
            // ------------ Замена тэгов ссылок [url=...]...[/url] ---------------------- //

            // Находим сслылки вида [url=http://google.ru]гугл[/url]
            $msg_part_p = preg_replace("/\[url\=((?:(?:http\:\/\/)|(?:https\:\/\/)|(?:ftp\:\/\/)).+)\](.+)\[\/url\]/Ui", "<a href=\"$1\">$2</a>", $msg_part_p);
            // Находим оставшиеся сслылки вида [url=google.ru]гугл[/url]
            $msg_part_p = preg_replace("/\[url\=(.+)\](.+)\[\/url\]/Ui", "<a href=\"http://$1\">$2</a>", $msg_part_p);

            // Находим сслылки вида [url=]http://google.ru[/url]
            $msg_part_p = preg_replace("/\[url\=\]((?:(?:http\:\/\/)|(?:https\:\/\/)|(?:ftp\:\/\/)).+)\[\/url\]/Ui", "<a href=\"$1\">$1</a>", $msg_part_p);
            // Находим оставшиеся сслылки вида [url=]google.ru[/url]
            $msg_part_p = preg_replace("/\[url\=\](.+)\[\/url\]/Ui", "<a href=\"http://$1\">$1</a>", $msg_part_p);

            // Разбиваем строку на части
            $msg_split_p_href = preg_split("/(\<a(?:.*)\>(?:.*)\<\/a\>)/Ui", $msg_part_p, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);

            $msg_part_p_no_b_i_inside_a = "";

            foreach ($msg_split_p_href as $msg_part_href) {
                if (preg_match("/(\<a(?:.*)\>(?:.*)\<\/a\>)/Ui", $msg_part_href)) {
                    // Удаляю тэги b,i внутри ссылки.
                    $msg_part_href = str_replace($b_i_tags, "", $msg_part_href);
                }
                $msg_part_p_no_b_i_inside_a .= $msg_part_href;
                //				echo "part: ".$msg_part_href."<br>\n";
            }

            $msg_part_p = $msg_part_p_no_b_i_inside_a;

            // --------------- НАЧАЛО :: Замена тэгов [b] и [i] --------------- //
            // --- Не работает для тэгов [url=...] !!! ------------- //
            $i = 0; // Начальная позиция поиска тэгов

            // $tags_array - ассоциативный массив тэгов. Ключи - открывающие тэги, значения - закрывающий тэг

            // cюда пихаем цикл в котором двигаем указатель поиска тэгов - $i
            while ($i < strlen($msg_part_p)) {

                $first_tag = array(0 => '', 1 => false);

                //$first_tag[0] = "";
                //$first_tag[1] = false;
                if (stripos($msg_part_p, "[b]", $i) !== false) {
                    $first_tag[0] = "[b]";
                    $first_tag[1] = stripos($msg_part_p, "[b]", $i);
                    if (stripos($msg_part_p, "[i]", $i) !== false) {
                        if ($first_tag[1] > stripos($msg_part_p, "[i]", $i)) {
                            $first_tag[0] = "[i]";
                            $first_tag[1] = stripos($msg_part_p, "[i]", $i);
                        }
                    }
                } else {
                    if (stripos($msg_part_p, "[i]", $i) !== false) {
                        $first_tag[0] = "[i]";
                        $first_tag[1] = stripos($msg_part_p, "[i]", $i);
                    }
                }


                // Если открывающий тэг найден
                if ($first_tag[1] !== false) {
                    // Нахожу первый закрывающийся тэг такого же типа
                    $j = stripos($msg_part_p, $tags_array[$first_tag[0]], $first_tag[1]);

                    // Если закрывающий тэг найден, то меняю скобки у ПАРЫ тэгов на HTML-скобки
                    if ($j !== false) {
                        $close_tag = $first_tag[0];
                        $close_tag = str_replace("[", "</", $close_tag);
                        $close_tag = str_replace("]", ">", $close_tag);
                        // Меняю скобки закрывающего тэга
                        $msg_part_p = substr_replace($msg_part_p, $close_tag, $j, strlen($tags_array[$first_tag[0]]));
                        $open_tag = $first_tag[0];
                        $open_tag = str_replace("[", "<", $open_tag);
                        $open_tag = str_replace("]", ">", $open_tag);
                        // Меняю скобки открывающего тэга
                        $msg_part_p = substr_replace($msg_part_p, $open_tag, $first_tag[1], strlen($first_tag[0]));

                        // ------------- Внутри first_tag нахожу все тэги и меняю их скобки на HTML-скобки ------- //

                        // Если внутри first_tag есть хоть один символ, то ...
                        if ($j - ($first_tag[1] + strlen($first_tag[0])) > 0) {
                            $inside_first_tag = substr($msg_part_p, $first_tag[1] + strlen($first_tag[0]), $j - ($first_tag[1] + strlen($first_tag[0])));

                            if ($first_tag[0] == "[i]") {
                                $inside_first_tag = preg_replace("/\[b\](.*)\[\/b\]/Uis", "<b>$1</b>", $inside_first_tag);
                            }
                            if ($first_tag[0] == "[b]") {
                                $inside_first_tag = preg_replace("/\[i\](.*)\[\/i\]/Uis", "<i>$1</i>", $inside_first_tag);
                            }

                            $msg_part_p = substr_replace($msg_part_p, $inside_first_tag, $first_tag[1] + strlen($first_tag[0]), $j - ($first_tag[1] + strlen($first_tag[0])));
                        }

                        //$i = stripos($msg_part_p, $tags_array[$first_tag[0]], $first_tag[1]);//+strlen($tags_array[$first_tag[0]]); // Нахожу первый закрывающийся тэг такого же типа;
                        $i = $j + strlen($tags_array[$first_tag[0]]);
                    } else {
                        $i = $first_tag[1] + strlen($first_tag[0]);
                    }
                } else {
                    break;
                }
            }

            // --------------- КОНЕЦ :: Замена тэгов [b] и [i] --------------- //

            $msg_p .= $msg_part_p;
        }
        $msg_part_quote = $msg_p;

        $msg_quote .= $msg_part_quote;
        //		echo "Part: ".$msg_part_quote;
    }
    $msg = $msg_quote;
    $msg = "<p>".$msg."</p>";

    // Удаляю <br />, стоящие перед тэгами цитат
    $msg = preg_replace("/(\<br \/\>(\s){0,})(?=\<\/p\>)/i", "\n", $msg);
    // Удаляю <br />, стоящие после тэгов цитат
    $msg = preg_replace("/(?<=\<p\>)((\s){0,}\<br \/\>)/i", "", $msg);

    // Убираю пустые параграфы
    $msg = preg_replace("/\<p\>(\s){0,}\<\/p\>/", "", $msg);

    // Убираю пустые цитаты
    $msg = preg_replace("/\<cite\>(\s){0,}\<\/cite\>/", "", $msg);
    // Убираю пустые цитаты
    $msg = preg_replace("/\<blockquote\>(\s){0,}\<\/blockquote\>/", "", $msg);

    // Замена XHTML тэга <br /> на HTML тэг <br>
    $msg = preg_replace("/\<br \/\>/i", "<br>", $msg);

    //	$msg = preg_replace("/\<b\>(.*)\<\/b\>/Ui", "<strong>$1</strong>", $msg); // Меняю <b> на </strong>
    //	$msg = preg_replace("/\<i\>(.*)\<\/i\>/Ui", "<em>$1</em>", $msg); // Меняю <i> на </em>

    // ---------------------- КОНЕЦ :: Замена тэгов ----------------------------- //


    return $msg;
}

function insert_navigation($query_params = "")
{
    global $number_of_link, $number_of_pages, $half_link, $page;
    // $number_of_pages - количество страниц, которое требуется, чтобы отобразить все сообщения гостевой книги

    //echo "<p align=right><font class=label_bold>Страницы:&nbsp;&nbsp;</font>";

    echo "<ul class=\"posts_nav\">\n";
    echo "<li class=\"label\">Страницы:</li>\n";

    if ($number_of_link >= $number_of_pages) {
        $i = 1;
        while ($i <= $number_of_pages) {
            if ($i == $page) {
                echo "<li><a class=\"active\">".$i."</a></li>\n";
            } else {
                echo "<li><a href=\"?".$query_params."page=".$i."\">".$i."</a></li>\n";
            }
            $i++;
        }
    }

    if ($number_of_link < $number_of_pages) {
        // Решаем вопрос ставить или не ставить перемотку на первую страницу

        if ($page > $half_link + 1) {
            echo "<li><a class=\"first_last\" title=\"Первая страница\" href=\"?".$query_params."page=1\">|&lt;&lt;-</a></li>\n";
        }

        // Если область отображения ссылок на страницы прижата вплотную к левому краю, то

        if ($page <= $half_link + 1) {
            $i = 1;
            while ($i <= $number_of_link) {
                if ($i == $page) {
                    echo "<li><a class=\"active\">".$i."</a></li>\n";
                } else {
                    echo "<li><a href=\"?".$query_params."page=".$i."\">".$i."</a></li>\n";
                }
                $i++;
            }
        }

        // Если область отображения ссылок на страницы прижата вплотную к правому краю, то

        if (($page + $half_link) >= $number_of_pages) {
            $i = $number_of_pages - $number_of_link + 1;
            while ($i <= $number_of_pages) {
                if ($i == $page) {
                    echo "<li><a class=\"active\">".$i."</a></li>\n";
                } else {
                    echo "<li><a href=\"?".$query_params."page=".$i."\">".$i."</a></li>\n";
                }
                $i++;
            }
        }

        // Если область отображения ссылок на страницы не прижата ни к одному из краёв, то

        if (($page > $half_link + 1) && (($page + $half_link) < $number_of_pages)) {
            $i = $page - $half_link;
            while ($i <= $page + $half_link) {
                if ($i == $page) {
                    echo "<li><a class=\"active\">".$i."</a></li>\n";
                } else {
                    echo "<li><a href=\"?".$query_params."page=".$i."\">".$i."</a></li>\n";
                }
                $i++;
            }
        }

        // Решаем вопрос ставить или не ставить перемотку до упора вперёд

        if (($page + $half_link) < $number_of_pages) {
            echo "<li><a class=\"first_last\" title=\"Последняя страница №".$number_of_pages."\" href=\"?".$query_params."page=".$number_of_pages."\">-&gt;&gt;|</a></li>\n";
        }
    }
    echo "</ul>\n";
}


function get_ignore_list()
{
    global $authorized_user;

    if ($authorized_user) {
        // Получаем список проигнорированных пользователей из базы и помещаем его в $ignore_list
        $query = "SELECT ignore_list FROM users WHERE id_user=".$_SESSION['id_user']."";
        $q_ig_users = query($query);
        $a_ig_users = mysqli_fetch_array($q_ig_users);

        if ($a_ig_users[0] == null) {
            $ignore_list = "";
        } else {
            $ignore_list = $a_ig_users[0];
        }
    } else {
        $ignore_list = "";
    }

    return $ignore_list;
}



function msg_prepare()
{
    global $msg, $msg_search;

    $msg = trim(mb_substr(trim($_POST["comment"]), 0, 25000, "UTF-8"));   // Ограничим длину сообщения
    if (empty($msg)) {
        echo "Напишите сообщение";
        exit;
    }   // Проверим, что сообщение не пустое

    $msg_search = $msg;
    $msg_search = delete_user_tags($msg_search);  // Удаление пользовательских тэгов

    $msg = addslashes($msg);  // Экранируем обратный слэш, двойную и одиночную кавычку, для корректного формирования запроса к БД
    $msg_search = addslashes($msg_search);  // Экранируем обратный слэш, двойную и одиночную кавычку, для корректного формирования запроса к БД
}

function prep_for_like($string)
{
    $prep_string = "";
    $prep_string = addcslashes(addslashes(str_replace("\\", "\\\\", $string)), "_%");
    return $prep_string;
}
