<?php

echo "            <h3>Регистрация нового пользователя</h3>\n";
if (isset($_GET['code'])) {
    if ($_GET['code'] == "reg_user_exist") {
        echo "<p class=\"error\">Пользователь с таким именем уже существует.</p>\n";
    }
    if ($_GET['code'] == "reg_success") {
        echo "<p class=\"message\">Регистрация прошла успешно.</p>";
    } else {
        include "form_register.html";
    }
} else {
    include "form_register.html";
}
