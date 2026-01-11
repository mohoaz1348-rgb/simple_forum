<?php

include "../config.php";

function get_data($smtp_conn)
{
    $data = "";
    while ($str = fgets($smtp_conn, 515)) {
        $data .= $str;
        if (substr($str, 3, 1) == " ") {
            break;
        }
    }
    return $data;
}

$exit = false;

$username = trim(mb_substr(trim($_POST["login"]), 0, 33, "UTF-8"));   // Ограничим длину имени
if (empty($username)) {
    echo "Введите имя.<br>\n";
    $exit = true;
}   // Проверим, что имя пользователя не пустое
else {
    if (mb_strlen($username, "UTF-8") < 3) {
        echo "Имя должно состоять минимум из трёх символов.<br>\n";
        $exit = true;
    }
    //	Проверим, что в логине используются разрешённые символы

    //if (preg_match("/[^(a-zA-Z)|(абвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ)|(0-9)|( )|(\-)|(\')|(\")]/u",$username))
    if (preg_match("/[^a-zA-ZабвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ 0-9]/u", $username)) {
        echo "Недопустимый символ в имени.<br>Используйте русские/английские буквы, цифры, пробел.<br>\n";
        $exit = true;
    }

    if (preg_match("/(\s){2,}/", $username)) {
        echo "Допустимы только одиночные пробелы в имени пользователя.<br>\n";
        $exit = true;
    }
}



$password = mb_substr($_POST["password"], 0, 12, "UTF-8");   // Ограничим длину пароля
if (empty($password)) {
    echo "Введите пароль.<br>\n";
    $exit = true;
}   // Проверим, что поле пароля не пустое
else {
    if (preg_match("/[^a-zA-ZабвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ0-9]/u", $password)) {
        echo "Пароль может состоять только из следующих символов: буквы русского/английского алфавита, цифры.<br>\n";
        $exit = true;
    }
}

if ($password != $_POST["password2"]) {
    echo "Пароли не совпадают!<br>";
    $exit = true;
}
$password = md5($password);

$email = trim(mb_substr(trim($_POST["email"]), 0, 50, "UTF-8"));   // Ограничим длину E-mail
if (empty($email)) {
    echo "Введите E-mail.<br>\n";
    $exit = true;
}   // Проверим, что E-mail не пустой
else {
    if (preg_match("/(\s)+/", $email)) {
        echo "Пробелы в E-mail недопустимы.<br>\n";
        $exit = true;
    }
}


if ($exit) {
    echo "Аккуратнее!";
    exit;
}

// Если прошли все проверки полей формы, то отсылаем письмо пользователю со ссылкой
/*
//	$subject = "Подтверждение регистрации";
    $subject = "subject";
    $message = "message";
    $headers = "From: Alex <av-molchanov@mail.ru>\r\n";
//Alexander_Mo@abbyy.com
//bW9sY2hhbm92LWF2QHlhbmRleC5ydQ==
//dHlsMWk3

    echo base64_encode("av-molchanov@mail.ru");
    echo '<br>';
    echo base64_encode("tyl1i7");
    echo '<br>';

    if (mail($email, $subject, $message, $headers)) echo "Почта была успешно принята для доставки.";
*/
/*
$header="Date: ".date("D, j M Y G:i:s")." +0700\r\n";
$header.="From: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode('Максим')))."?= <av-molchanov@mail.ru>\r\n";
$header.="X-Mailer: The Bat! (v3.99.3) Professional\r\n";
$header.="Reply-To: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode('Максим')))."?= <av-molchanov@mail.ru>\r\n";
$header.="X-Priority: 3 (Normal)\r\n";
$header.="Message-ID: <172562218.".date("YmjHis")."@mail.ru>\r\n";
$header.="To: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode('Сергей')))."?= <molchanov-av@yandex.ru>\r\n";
$header.="Subject: =?windows-1251?Q?".str_replace("+","_",str_replace("%","=",urlencode('проверка')))."?=\r\n";
$header.="MIME-Version: 1.0\r\n";
$header.="Content-Type: text/plain; charset=windows-1251\r\n";
$header.="Content-Transfer-Encoding: 8bit\r\n";

$text="привет, проверка связи.";

$smtp_conn = fsockopen("smtp.mail.ru", 587,$errno, $errstr, 10);
if(!$smtp_conn) {print "соединение с сервером не прошло"; fclose($smtp_conn); exit;}
$data = get_data($smtp_conn);
fputs($smtp_conn,"EHLO mail.ru\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 250) {print "ошибка приветсвия EHLO"; fclose($smtp_conn); exit;}
fputs($smtp_conn,"AUTH LOGIN\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 334) {print "сервер не разрешил начать авторизацию"; fclose($smtp_conn); exit;}

fputs($smtp_conn,base64_encode("av-molchanov@mail.ru")."\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 334) {print "ошибка доступа к такому юзеру"; fclose($smtp_conn); exit;}


fputs($smtp_conn,base64_encode("tyl1i7")."\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 235) {print "не правильный пароль"; fclose($smtp_conn); exit;}

fputs($smtp_conn,"MAIL FROM:av-molchanov@mail.ru\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 250) {print "сервер отказал в команде MAIL FROM"; fclose($smtp_conn); exit;}

fputs($smtp_conn,"RCPT TO:molchanov-av@yandex.ru\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 250 AND $code != 251) {print "Сервер не принял команду RCPT TO"; fclose($smtp_conn); exit;}

fputs($smtp_conn,"DATA\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 354) {print "сервер не принял DATA"; fclose($smtp_conn); exit;}

fputs($smtp_conn,$header."\r\n".$text."\r\n.\r\n");
$code = substr(get_data($smtp_conn),0,3);
if($code != 250) {print "ошибка отправки письма"; fclose($smtp_conn); exit;}

fputs($smtp_conn,"QUIT\r\n");
fclose($smtp_conn);
*/

$query = "SELECT count(*) AS count FROM users WHERE username LIKE \"".prep_for_like($username)."\" ";
$same_users = query($query);
//echo $query."<br>";
$same_users_array = mysqli_fetch_array($same_users);
//echo $same_users_array[0];

if ($same_users_array[0] > 0) {
    header("Location: ../index.php?action=register&code=reg_user_exist");
} else {
    $query = "INSERT INTO users (username, password, email, time, user_group, active, banned_till) VALUES ('".addslashes($username)."', '".addslashes($password)."', '".addslashes($email)."', NOW(), 'user', 1, NOW())";
    query($query);

    header("Location: ../index.php?action=register&code=reg_success");
}
