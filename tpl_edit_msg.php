<?php
global $authorized_user;

?>
		
		<div id="main">
		<h3>Редактировать сообщение</h3>
		
<?php

if ($authorized_user) {

    $code = "";
    $sid = session_id();
    if (isset($_GET["pid"]) && isset($_GET["sid"])) {
        if ($_GET["sid"] == $sid) {
            // если ссылка на удаление была сформирована в этой же сессии, то
            $id_post = (int)$_GET["pid"];
            if ($id_post < 1) {
                $code = "post_not_exist";
            } else {
                if (($_SESSION['group'] == "moderator") || ($_SESSION['group'] == "owner")) {
                    $q_user = "";
                } else {
                    $q_user = " AND id_user=".$_SESSION['id_user'];
                }

                //	$q = mysqli_query("SELECT count(*) AS count FROM posts WHERE id_post=$id_post AND id_user=".$_SESSION['id_user']) or die ("Invalid query: " . mysqli_error());
                $query = "SELECT id_user FROM posts WHERE id_post=$id_post".$q_user;
                $q = query($query);

                if ($row2 = mysqli_fetch_object($q)) {
                    $id_user = $row2 -> id_user;

                    $query = "SELECT user_group FROM users WHERE id_user=$id_user";
                    $q3 = query($query);
                    if ($row3 = mysqli_fetch_object($q3)) {
                        // Группа пользователя, которому принадлежит удаляемый пост
                        $user_group = $row3 -> user_group;

                        //if (($id_user==$_SESSION['id_user']) || ($_SESSION['group'] == "moderator"))
                        if (($id_user == $_SESSION['id_user']) || (($_SESSION['group'] == "moderator") && ($user_group == "user")) || (($_SESSION['group'] == "owner") && (($user_group == "user") || ($user_group == "moderator")))) {
                            $query = "SELECT msg, id_topic FROM posts WHERE id_post=$id_post".$q_user;
                            $q2 = query($query);
                            $row = mysqli_fetch_object($q2);
                        } else {
                            $code = "post_not_exist";
                        }
                    }
                } else {
                    $code = "post_not_exist";
                }
            }
        } else {
            $code = "post_not_exist";
        }
    } else {
        $code = "post_not_exist";
    }

    if ($code == "post_not_exist") {
        echo "<p class=\"error\">Сообщение не существует, либо у Вас нет прав на его редактирование.</p>";
    } else {
        $query = "SELECT title FROM topics WHERE id_topic=".$row->id_topic;
        $q4 = query($query);
        $row4 = mysqli_fetch_object($q4);

        echo "<h4><a href=\"index.php\">Главная</a> / <a href=\"index.php?action=messages&topic=".$row->id_topic."\">".htmlspecialchars($row4->title, ENT_QUOTES)."</a> / <span class=\"active\">Редактировать сообщение <a href=\"index.php?action=messages&msg=$id_post#$id_post\"><span class=\"active\"><b>#$id_post</b></span></a></span></h4>";

        include "form_edit_msg.html";
    }
}
?>
		
		</div>
		
