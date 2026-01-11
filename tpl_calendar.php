<?php
global $q_ignore_list, $authorized_user, $months2;

$today = getdate(); // Текущая дата
$begin_year = $today["year"]; // Год начала записей в гостевой книге
$query = "SELECT count(*) AS count FROM posts WHERE active=1 $q_ignore_list";
$number_of_posts = mysqli_fetch_array(query($query)); // Общее число постов

if ($number_of_posts[0] > 0) { // Определяем год начала записей
    $query = "SELECT DATE_FORMAT(time, '%Y') AS time FROM posts WHERE active=1 $q_ignore_list ORDER BY id_post LIMIT 0,1";
    $arr_year = mysqli_fetch_array(query($query)); // Выбираем первый пост гостевой книги
    $begin_year = $arr_year[0];
}

$days = array(1 => 'пн', 'вт', 'ср', 'чт', 'пт', 'сб', 'вс');
$months = array(1 => 'Январь', 'Февраль', 'Март', 'Апрель', 'Май', 'Июнь', 'Июль', 'Август', 'Сентябрь', 'Октябрь', 'Ноябрь', 'Декабрь');

// Получаем список проигнорированных пользователей из базы и формируем часть запроса
//$q_ignore_list = create_ignore_query();

// Если календарь просматривает владелец или модератор форума, то надо показывать все сообщения последний раз изменённые за выбранную дату
$order_by = "id_post";
$time = "time";
$msg_edit = "Показаны все сообщения созданные";
if ($authorized_user) {
    if (($_SESSION['group'] == "owner") || (($_SESSION['group'] == "moderator"))) {
        $order_by = "last_edit";
        $time = "last_edit";
        $msg_edit = "Показаны все сообщения последний раз изменённые";
    }
}

// --- Проверка параметров массива $_GET и ИНИЦИАЛИЗАЦИЯ переменных даты --- //

$valid_month = 0;
$valid_year = 0;
$valid_day = 0;


if (isset($_GET['month'])) {
    $month = (int)$_GET['month'];
    if ($month >= 1 && $month <= 12) {
        $valid_month = 1;
    } else {
        $month = $today["mon"];
    }
} else {
    $month = $today["mon"];
}

if (isset($_GET['year']) && $valid_month == 1) {
    $year = (int)$_GET['year'];
    if ($year >= $begin_year && $year <= $today["year"]) {
        $valid_year = 1;
    } else {
        $year = $today["year"];
    }
} else {
    $year = $today["year"];
}

if (isset($_GET['day']) && $valid_month == 1 && $valid_year == 1) {
    $day = (int)$_GET['day'];
    $max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year); // Число дней заданного месяца
    if ($day >= 1 && $day <= $max_date) {
        $valid_day = 1;
    } else {
        $day = 0;
    }
} else {
    $day = 0;
}

echo "<h3>Календарь - выберите дату</h3>";

?>		
		
<form id="month_year" action="index.php" method="get" onsubmit="">
<input type="hidden" name="action" value="calendar">
<select id="month" name="month"  onchange="this.form.submit();">

<?php

// ------------------------------ Комбик с МЕСЯЦАМИ: построение списка значений ---------------- //

$i = 1;
while ($i <= 12) {
    echo "<option value=", $i;
    if ($month == $i) {
        echo " selected";
    } echo ">", $months[$i];
    $i++;
}
?>

</select>
<select id="year" name="year" onchange="this.form.submit();" >

<?php

// ------------------------------ Комбик с ГОДАМИ: построение списка значений ---------------- //

$i = $begin_year;
while ($i <= $today["year"]) {
    echo "<option ";
    if ($year == $i) {
        echo "selected";
    } echo ">", $i;
    $i++;
}
?>
</select>
</form>

<!--------------------------------- Вывод КАЛЕНДАРЯ  ------------------------------------->

<?php

// -------------------- Вывод названий дней недели и начало ТАБЛИЦЫ календаря -------------------------- //


echo "<table id=\"calendar\">\n";
echo "<tr>\n";

$i = 1;
while ($i <= 7) {
    echo "<th>".$days[$i]."</th>\n";
    $i++;
}
echo "</tr>\n";

// --------------------------------- Вывод чисел календаря ---------------------------------------- //

$date = 1; // Переменная содержащая число месяца, которое нужно вывести
// Число дней заданного месяца
$max_date = cal_days_in_month(CAL_GREGORIAN, $month, $year); 
// День недели, на который приходится 1-ое число месяца
$day_of_week = date("w", mktime(0, 0, 0, $month, 1, $year)); 
if ($day_of_week == 0) {
    $day_of_week = 7;
}

while ($date <= $max_date) {

    echo "<tr>\n";
    $i = 1;
    while ($i <= 7) {

        $query = "SELECT count(*) AS count FROM posts WHERE DATE_FORMAT($time, \"%e-%c-%Y\")=\"{$date}-{$month}-{$year}\" AND active=1 $q_ignore_list";
        $number_of_posts_for_date = mysqli_fetch_array(query($query)); // Общее число постов

        if ($date <= $max_date && $date >= 2) {
            if ($date == $day) {
                if ($number_of_posts_for_date[0] == 0) {
                    echo "<td><a class=\"disabled\">".$date."</a></td>\n";
                } else {
                    echo "<td><a class=\"active\">".$date."</a></td>\n";
                }
            } else {
                if ($number_of_posts_for_date[0] == 0) {
                    echo "<td><a class=\"disabled\">".$date."</a></td>\n";
                } else {
                    echo "<td><a href=\"index.php?action=calendar&day={$date}&month={$month}&year={$year}\">", $date, "</a></td>\n";
                }
            }
            $date++;
        }
        if ($date == 1 && $i == $day_of_week) {
            if ($date == $day) {
                if ($number_of_posts_for_date[0] == 0) {
                    echo "<td><a class=\"disabled\">".$date."</a></td>\n";
                } else {
                    echo "<td><a class=\"active\">".$date."</a></td>\n";
                }
            } else {
                if ($number_of_posts_for_date[0] == 0) {
                    echo "<td><a class=\"disabled\">".$date."</a></td>\n";
                } else {
                    echo "<td><a href=\"index.php?action=calendar&day={$date}&month={$month}&year={$year}\">", $date, "</a></td>\n";
                }
            }
            $date++;
        }

        if ($i < $day_of_week && $date == 1) {
            echo "<td align=center></td>\n";
        }
        $i++;
    }
    echo "</tr>\n";

}

echo "</table>\n";

// --------------------------------------- Вывод соообщений за выбранную дату --------------------------- //

if ($day != 0) {
    $query = "SELECT id_post, id_user, id_editor, username, msg, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time, DATE_FORMAT(last_edit, '%e-%c-%Y, %T') AS last_edit, title_topic FROM posts WHERE DATE_FORMAT($time, \"%e-%c-%Y\")=\"{$day}-{$month}-{$year}\" AND active=1 $q_ignore_list ORDER BY $order_by DESC";
    $posts = query($query); // Извлекаем ВСЕ посты за выбранную дату
    $query = "SELECT count(*) AS count FROM posts WHERE DATE_FORMAT($time, \"%e-%c-%Y\")=\"{$day}-{$month}-{$year}\" AND active=1 $q_ignore_list";
    $number_of_posts_for_date = mysqli_fetch_array(query($query)); // Общее число постов за дату
    //	$number_msg = $number_of_posts_for_date[0];

    echo "<p>$msg_edit <b>$day&nbsp;".$months2[$month]."&nbsp;{$year}&nbsp;г.</b></p>";
    echo "<p>Всего сообщений: <b>$number_of_posts_for_date[0]</b></p>";

    //echo $day;

    insert_posts($posts, "calendar"); // Вывод сообщений

    echo "<p style=\"font-size: 14px;\"><a href=\"#\">Наверх</a></p>";

}

?>		
