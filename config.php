<?php

$dbhost = "localhost";
$dbuser = "mario";
$dbpwd = "ggghhh";

$dbname = "forum_test";

// --------------------- BAЖНО! устанавливаем кодировку обмена данными с базой MySQL ------------------------ //
//---------------------- Она должна совпадать с кодировкой таблиц и кодировкой html-страниц ----------------- //

try {
    $link = mysqli_connect($dbhost, $dbuser, $dbpwd);
    mysqli_query($link, "SET NAMES utf8mb4");
    mysqli_select_db($link, $dbname);
} catch (\Throwable $th) {
    $error = "PHP cought exception: " . $th->getMessage() . PHP_EOL .
        "Trace:" . PHP_EOL .
        $th->getTraceAsString() . PHP_EOL;
    error_log($error, 0);
    exit;
}

function query($query)
{
    global $link;
    try {
        $result = mysqli_query($link, $query); //or die("Invalid query: " . mysqli_error($link));
    } catch (\Throwable $th) {
        $error = "PHP cought exception: " . $th->getMessage() . PHP_EOL .
            "Trace:" . PHP_EOL .
            $th->getTraceAsString() . PHP_EOL;
        error_log($error, 0);
        //echo "echo: Invalid query: " . mysqli_error($link) . "<br>\n";
        exit;
    }
    return $result;
}


$tpp = 7; // Количество тем на странице
$ppp = 5; // Количество постов на странице
$upp = 10; // Количество пользователей на странице
$half_link = 4;
$number_of_link = 2 * $half_link + 1; // Количество ссылок на панели навигации гостевой книги

function is_auth_user()
{
    if (isset($_COOKIE[session_name()])) {
        session_start();
        if (isset($_SESSION['authorized'])) {
            if ($_SESSION['authorized'] == 1) {
                //	echo "Добро пожаловать, ".$_SESSION['user'];
            } else {
                echo "authorized не равен 1";
                exit;
            }

        } else {
            echo "authorized - not set";
            exit;
        }
    } else {
        echo "Сессия не установлена. Авторизуйтесь!";
        exit;
    }
}
