
		<h3>Вход</h3>
			
      <?php
      global $authorized_user;
      if (!$authorized_user) {
          include "form_auth.html";
      }; ?>
			
			<div id="errors">
			<?php
      if (isset($_GET['code'])) {
          if ($_GET['code'] == "auth_fail") {
              echo "<p class=\"error\">Неверный логин или пароль.</p>";
          }
          if ($_GET['code'] == "auth_inter_login") {
              echo "<p class=\"error\">Введите имя.</p>";
          }
          if ($_GET['code'] == "auth_inter_pwd") {
              echo "<p class=\"error\">Введите пароль.</p>";
          }
          if ($_GET['code'] == "auth_banned") {
              echo "<p class=\"error\">Вы забанены.</p>";
          }
      }
      ?>
			</div>
