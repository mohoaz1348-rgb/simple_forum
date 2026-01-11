<?php
global $authorized_user, $upp, $months2;

$page = 1; // Страница с пользователями, которую надо показать
?>		
		<div id="main">

<?php
if ($authorized_user) {

    // ------ Определение параметров панели НАВИГАЦИИ ------- //

    if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
        $query = "SELECT count(*) AS count FROM users";
        // Общее число пользователей
        $number_of_posts = mysqli_fetch_array(query($query));
        //echo $number_of_posts, "<br />";
        // Количество страниц, для отображения всех пользователей
        $number_of_pages = ceil($number_of_posts[0] / $upp);
        //echo $number_of_pages, "<br />";
    } else {
        $query = "SELECT count(*) AS count FROM users WHERE active=1";
        // Общее число пользователей
        $number_of_posts = mysqli_fetch_array(query($query));
        //echo $number_of_posts[0], "<br />";
        // Количество страниц, для отображения всех пользователей
        $number_of_pages = ceil($number_of_posts[0] / $upp);
        //echo $number_of_pages, "<br />";
    }

    if ($number_of_posts[0] > 0) {
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

        $offset = ($page - 1) * $upp;

        if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
            $query = "SELECT id_user, username, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time, user_group, active FROM users ORDER BY active DESC, id_user LIMIT $offset, $upp";
            $users_q = query($query);
        } else {
            $query = "SELECT id_user, username, DATE_FORMAT(time, '%e-%c-%Y, %T') AS time, user_group, active FROM users WHERE active=1 ORDER BY id_user LIMIT $offset, $upp";
            $users_q = query($query);
        }

        echo "<h3>Список пользователей</h3>\n";

        insert_navigation("action=users&");  // Постраничная навигация

        echo "		<table class=\"users\" cellspacing=\"1\">\n";
        echo "		<col width=60%>\n";
        echo "		<col width=30%>\n";
        echo "		<col width=10%>\n";
        echo "		<tr>\n";
        echo "		<th>Имя пользователя</th>\n";
        echo "		<th>Дата регистрации</th>\n";
        echo "		<th>Сообщений</th>\n";
        echo "		</tr>\n";

        $j = 0;
        while ($row = mysqli_fetch_object($users_q)) {
            $i = "";
            if ($j == 0) {
                echo "			<tr class=\"bg2\">\n";
                $j++;
            } else {
                echo "			<tr>\n";
                $j = 0;
            }

            $username_prep = htmlspecialchars($row->username, ENT_QUOTES);

            if ($row->active == 1) {
                $formatting = "";
            } else {
                $formatting = " class=\"del_user\"";
            }
                echo "<td><a$formatting href=\"index.php?action=u_profile&uid=".$row->id_user."\">".$username_prep."</a></td>\n";
            $i = explode("-", $row->time);

            echo "<td>".$i[0]."&nbsp;".$months2[$i[1]]."&nbsp;".$i[2]."</td>\n";

            $query = "SELECT count(*) AS count FROM posts WHERE id_user=".$row->id_user." AND active=1";
            $posts = query($query);
            $row2 = mysqli_fetch_object($posts);

            echo "<td align=\"center\">".$row2->count."</td>\n";

            echo "			</tr>\n";
        }

        echo "		</table>";

        insert_navigation("action=users&");  // Постраничная навигация
    }
}
?>

		</div> <!-- id="main" -->
	
