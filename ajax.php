<?php

include "config.php";

if (isset($_GET['action'])) {
    if (($_GET['action'] == "check_login") && (isset($_GET['login']))) {
        $username = trim(mb_substr(trim($_GET["login"]), 0, 33, "UTF-8"));   // Ограничим длину имени
        if (!empty($username)) {

            $username_prep = prep_for_like($username);
            $query = "SELECT count(*) AS count FROM users WHERE username LIKE \"".$username_prep."\" ";
            $q_same_users = query($query);
            $a_same_users = mysqli_fetch_array($q_same_users);

            if ($a_same_users[0] > 0) {
                echo "<span class=\"error\">занято</span>";
            } else {
                echo "<span class=\"message\">свободно</span>";
            }
        }
    }
}
