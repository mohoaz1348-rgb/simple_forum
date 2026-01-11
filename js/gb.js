function check_login_js(event) {
  if (
    login_val == document.reg.login.value &&
    event != "onblur" &&
    event != "onfocus"
  ) {
    // Если значение поля не изменилось, то ...
    return 0;
  } else {
    if (event != "onblur" && event != "onfocus")
      document.getElementById("login_status").innerHTML = "";
    login_val = document.reg.login.value;
    clearTimeout(idt); // Прибиваем ожидание запуска проверки имени пользователя через AJAX

    var patt1 = new RegExp(
      "[^a-zA-ZабвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ 0-9]",
    );

    if (patt1.test(document.reg.login.value)) {
      // Если использованы недопустимые символы, то ...
      document.getElementById("login_status").innerHTML =
        '<span class="error">Недопустимый символ в имени.<br>Используйте русские/английские буквы, цифры, пробел.</span>';
      return 1;
    } else {
      var patt2 = new RegExp("^ ");
      if (patt2.test(document.reg.login.value)) {
        // Если имя начинается с пробела, то ...
        document.getElementById("login_status").innerHTML =
          '<span class="error">Имя не может начинаться с пробела.</span>';
        return 2;
      } else {
        var patt3 = new RegExp("( ){2,}");
        if (patt3.test(document.reg.login.value)) {
          // Если в имени идут подряд 2 пробела или больше, то ...
          document.getElementById("login_status").innerHTML =
            '<span class="error">Подряд идущие пробелы недопустимы.</span>';
          return 3;
        } else {
          var patt4 = new RegExp(" $");
          if (patt4.test(document.reg.login.value)) {
            // Если имя заканчивается на пробел, то ...
            if (event == "onblur") {
              document.getElementById("login_status").innerHTML =
                '<span class="error">Имя не может заканчиваться на пробел.</span>';
            } else {
              document.getElementById("login_status").innerHTML = "";
            }
            return 4;
          } else {
            if (document.reg.login.value.length < 3) {
              // Если имя состоит меньше чем из трёх символов, то ...
              if (event == "onblur") {
                document.getElementById("login_status").innerHTML =
                  '<span class="error">Имя должно состоять минимум из 3-х символов.</span>';
              } else {
                document.getElementById("login_status").innerHTML = "";
              }
              return 5;
            } else {
              idt = setTimeout("check_login()", 1000);
              return 7; // Можно запускать проверку имени на доступность через AJAX
            }
          }
        }
      }
    }
  }
}

function check_login() {
  var xmlhttp;
  //	document.getElementById("login_status").innerHTML=document.getElementById("login_status").innerHTML+" cl";

  if (window.XMLHttpRequest) {
    // code for IE7+, Firefox, Chrome, Opera, Safari
    xmlhttp = new XMLHttpRequest();
  } else {
    // code for IE6, IE5
    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttp.onreadystatechange = function () {
    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
      //	document.getElementById("login_status").innerHTML=document.getElementById("login_status").innerHTML+xmlhttp.responseText;
      document.getElementById("login_status").innerHTML = xmlhttp.responseText;
    }
  };
  xmlhttp.open(
    "GET",
    "ajax.php?action=check_login&login=" +
      encodeURIComponent(document.reg.login.value),
    true,
  );
  xmlhttp.send();
}

function check_pwd(event) {
  if (
    pwd_val == document.reg.password.value &&
    event != "onblur" &&
    event != "onfocus"
  ) {
    // Если значение поля пароля НЕ изменилось, то ...
    return 0;
  } else {
    if (event != "onblur" && event != "onfocus")
      document.getElementById("pwd_status").innerHTML = "";
    pwd_val = document.reg.password.value;
    var patt1 = new RegExp(
      "[^a-zA-ZабвгдеёжзийклмнопрстуфхцчшщъыьэюяАБВГДЕЁЖЗИЙКЛМНОПРСТУФХЦЧШЩЪЫЬЭЮЯ0-9]",
    );

    if (patt1.test(document.reg.password.value)) {
      // Если использованы недопустимые символы, то ...
      document.getElementById("pwd_status").innerHTML =
        '<span class="error">Недопустимый символ в пароле.<br>Используйте русские/английские буквы, цифры.</span>';
      return 1;
    } else {
      if (document.reg.password.value.length < 5) {
        // Если пароль состоит менее чем из 5-ти символов, то ...
        if (event == "onblur") {
          document.getElementById("pwd_status").innerHTML =
            '<span class="error">Пароль должен состоять минимум из 5-ти символов.</span>';
        } else {
          document.getElementById("pwd_status").innerHTML = "";
        }
        return 2;
      } else {
        document.getElementById("pwd_status").innerHTML =
          '<span class="message">правильно</span>';
        return 3;
      }
    }
  }
}

function check_pwd2() {
  //	var ch_pwd = check_pwd('');
  if (check_pwd("onblur") == 3) {
    if (document.reg.password.value == document.reg.password2.value) {
      document.getElementById("pwd2_status").innerHTML =
        '<span class="message">пароли совпадают</span>';
    } else {
      document.getElementById("pwd2_status").innerHTML =
        '<span class="error">пароли не совпадают</span>';
    }
  }
}

function check_mail() {
  if (document.reg.email.value.length == 0) {
    document.getElementById("mail_status").innerHTML =
      '<span class="error">Введите E-mail.</span>';
  } else {
    var patt1 = new RegExp("( ){1,}");

    if (patt1.test(document.reg.email.value)) {
      // Если использованы недопустимые символы, то ...
      document.getElementById("mail_status").innerHTML =
        '<span class="error">Пробел нельзя использовать в E-mail.</span>';
    } else {
      document.getElementById("mail_status").innerHTML =
        '<span class="message">правильно</span>';
    }
  }
}

function check() {
  if (document.gb.name.value.length == 0) {
    alert("Введите имя");
    document.gb.name.focus();
    return false;
  }
  if (document.gb.comment.value.length == 0) {
    alert("Напишите сообщение");
    document.gb.comment.focus();
    return false;
  }
  return true;
}

function fill_gb_fake_field() {
  var el = document.getElementById("gb_fake_field");
  el.value = "blya_budu";
}

function insert_text(open, close) {
  //var msgfield = document.forms["gb"]["comment"];
  var msgfield = document.getElementById("msg");
  var startPos = msgfield.selectionStart;
  var endPos = msgfield.selectionEnd;

  msgfield.value = 
    msgfield.value.substring(0, startPos) +
    open +
    msgfield.value.substring(startPos, endPos) +
    close +
    msgfield.value.substring(endPos, msgfield.value.length);

  if (startPos != endPos) {
    msgfield.selectionStart = msgfield.selectionEnd =
      endPos + open.length + close.length;
  } else {
    msgfield.selectionStart = msgfield.selectionEnd = endPos + open.length;
  }
  msgfield.focus();
  return;
}

function check_search() {
  if (
    (document.search.msg_crit.value.length == 0) &
    (document.search.name_crit.value.length == 0)
  ) {
    alert("Задайте критерии поиска");
    document.search.msg_crit.focus();
    return false;
  }
  return true;
}

function check_new_topic() {
  if (document.gb.title.value.length == 0) {
    alert("Введите название темы");
    document.gb.title.focus();
    return false;
  }
  if (document.gb.comment.value.length == 0) {
    alert("Напишите сообщение");
    document.gb.comment.focus();
    return false;
  }
  return true;
}
