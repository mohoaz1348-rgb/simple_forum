<?php

include "config.php";
include "functions.php";

$months2 = array(1 => 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');

$action = "tpl_topics.php"; // По-умолчанию открываем главную страницу форума
$title = "Форум";

if (isset($_GET['action'])) {
    if ($_GET['action'] == "messages") {
        $action = "tpl_messages.php";
        $title = "Форум :: Просмотр темы";
    }
    if ($_GET['action'] == "register") {
        $action = "tpl_reg.php";
        $title = "Форум :: Регистрация";
    }
    if ($_GET['action'] == "profile") {
        $action = "tpl_profile.php";
        $title = "Форум :: Профиль";
    }
    if ($_GET['action'] == "calendar") {
        $action = "tpl_calendar.php";
        $title = "Форум :: Календарь";
    }
    if ($_GET['action'] == "search") {
        $action = "tpl_search.php";
        $title = "Форум :: Поиск";
    }
    if ($_GET['action'] == "edit_msg") {
        $action = "tpl_edit_msg.php";
        $title = "Форум :: Редактирование сообщения";
    }
    if ($_GET['action'] == "users") {
        $action = "tpl_users.php";
        $title = "Форум :: Пользователи";
    }
    if ($_GET['action'] == "u_profile") {
        $action = "tpl_u_profile.php";
        $title = "Форум :: Профиль пользователя";
    }
    if ($_GET['action'] == "auth") {
        $action = "tpl_auth.php";
        $title = "Форум :: Вход";
    }
}


// Переменная, которая служит для того, чтобы определить - авторизован пользователь или нет.
$authorized_user = false;

if (isset($_COOKIE[session_name()])) {
    // Определяем, авторизацию пользователя
    session_start();
    if (isset($_SESSION['authorized'])) {
        if ($_SESSION['authorized'] == 1) {
            if (isset($_GET['action'])) {
                // Выход пользователя
                if ($_GET['action'] == "exit") {
                    session_destroy();
                    header("Location: index.php");
                }
            }

            // Определяем не забанен ли пользователь

            $query = "SELECT count(*) AS count FROM users WHERE id_user=".$_SESSION['id_user']." AND banned_till<=NOW()";
            $user_q = query($query);

            $user_arr = mysqli_fetch_array($user_q);

            if ($user_arr[0] > 0) {
                $authorized_user = true;
            } else { // если забанен, то выходим
                session_destroy();
            }
        }
    }
}

$ignore_list = get_ignore_list();

if ($authorized_user) {
    // Формируем часть запроса игнорирования пользователя
    if ($ignore_list == "") {
        $q_ignore_list = "";
    } else {
        $q_ignore_list = "AND id_user not in (".trim($ignore_list, ",").")";
    }
} else {
    $q_ignore_list = "";
}
?>
<!doctype html>
<html>
<head>
<title><?php echo $title; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="css/main.css" rel="stylesheet" type="text/css" media="screen, projection">

<script src="js/gb.js" type="text/javascript"></script>

</head>
<body>

<div id="outer">
	<div id="header_header">
	</div>
	<div id="header">
	<h1><b>L</b>ight<b>W</b>eight <b>F</b>orum</h1>
	</div>
	
	<div id="content">
	
	<div id="navigation">
	<p class="main_nav">
		<a href="index.php">Главная</a> | <a href="index.php?action=calendar">Календарь</a> | <a href="index.php?action=search">Поиск</a>
	</p>
	
	<?php
        if ($authorized_user) {
            echo "<p class=\"auth_nav\"><span class=\"login\">".htmlspecialchars($_SESSION['user'], ENT_QUOTES)." : </span><a href=\"index.php?action=profile\">Профиль</a>";
            echo " | <a href=\"index.php?action=users\">Пользователи</a></li>";
            echo " | <a href=\"index.php?action=exit\">Выйти</a></p>";
        } else {
            echo "<p class=auth_nav>\n";
            echo "<a href=\"index.php?action=register\">Регистрация</a> | <a href=\"index.php?action=auth\">Вход</a>";
            echo "</p>\n";
        }
?>
	</div>
		<div id="main">
<?php
include "$action";
?>
		</div> <!-- id="main" -->
	</div> <!-- id="content" -->

</div> <!-- id="outer" -->
<div id="footer"><p></p></div>
</body>
</html>
