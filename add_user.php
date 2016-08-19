<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test_KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';

# Требуется активная сессия супервизора или админа
# иначе пишет что нет прав доступа
#
# Сюда прилетает lg=$login, по нему нужно найти пользователя в LDAP,
# достать оттуда его данные, проверить, есть ли он в kit_users,
# если нет - то добавить его и создать запись в табличке выбранных
# пользователей user_box, чтобы потом можно было на него
# отправить запрос к рег.биллингам
# В конце нужно вернуть строку вида
# echo "<input id='cb_$user_login' type=checkbox         onClick='pg(\"add_user.php\", \"td_$user_login\", \"lg=$user_login\");'>"; # Если удаляли строку
# или
# echo "<input id='cb_$user_login' type=checkbox checked onClick='pg(\"add_user.php\", \"td_$user_login\", \"lg=$user_login\");'>"; # Если добавляли строку
	
	$module='add_user';
	$dbg=FALSE;
	
	if ($dbg) { msg("$module: START"); } ###
	
	
	
	
	if ( !isset($_SESSION['auth']) ) { # Попытка обхода авторизации
		$redirect='login.php';
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'lime');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;# Обязательно выходим !!!!!!!!
	}#end if
	
	
	if (isset($_SESSION['login'])) {$login_sv = $_SESSION['login'];} else {message("$module: Login NOT DEFINED !!!"); exit;}
	if (isset($_SESSION['pass'])) {$pass = $_SESSION['pass'];} else {message("$module: Password NOT DEFINED !!!"); exit;}
	session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку
	$login_sv=cut_domain($login_sv); #Отрезаем домен от логина
	
	$al=access_level($login_sv);# Получим права доступа $al[0]-супервизор='1'  $al[1]-админ='1'     Пустим только супервизоров и админов
	if ( !('1'==$al[0] OR '1'==$al[1]) ) {message("$module: You DON'T HAVE sufficient privilegies to view this page ($al) !!!"); exit;}
	
	
	
	#Получим IDшник строки, в которую выводить
	if (isset($_REQUEST['id'])) {
		$id=get_int_val($_REQUEST['id']);
	} else {
		message("$module: ID not set");
		exit;
	}
	
	
	if (isset($_REQUEST['lg'])) {
		$checked=''; $user_login='';
		
		$user_login=$_REQUEST['lg'];#Получим логин юзера, с которым производим операцию
		msg("$module: lg=$user_login", $dbg, 'magenta'); ###
		
		$user_id=add_user_from_ldap($user_login);#Добавить/обновить юзера по логину из LDAP и получить его IDшник
		msg("$module: user_id=$user_id", $dbg, 'blue'); ###
		
		$bind=array(array(':id_user', $user_id), array(':login_sv', strtoupper($login_sv)) );
		$STH_usbox=search('user_box', 'id_user=:id_user AND login_sv=:login_sv', '', $bind);#Ищем юзера в табличке выбранных
		if ($row_usbox=$STH_usbox->fetch()) {	# Если нашли
			$line_id=$row_usbox['id'];					#Получаем IDшник строки
			msg("$module: Строка $line_id УЖЕ есть", $dbg); ###
			if ( delete('user_box', 'id=:id', array(':id', $line_id)) ) { # Если получилось удалить
				msg("$module: УДАЛИЛИ СТРОКУ $line_id", $dbg, 'lime'); ###
			} else {
				msg("$module: НЕ УДАЛОСЬ УДАЛИТЬ СТРОКУ $line_id !!!", $dbg); ###
			}#end if (delete)
		} else {
			$mail_sv=$login_sv.'@SKYLINK.RU'; #Сконструируем хотя бы такой e-mail
			$bind=array(':login', strtoupper($login_sv));# БОЛЬШИЕ БУКВЫ нужны для поиска
			$STH=search('kit_users', 'login=:login', '', $bind);#Ищем супервизора по логину
			if ($row=$STH->fetch()) {$mail_sv=$row['mail'];}# Если найдём e-mail, то заменим
			$bind=array(array(':id_user', $user_id),
						array(':login_sv', strtoupper($login_sv) ), # Записываем логин сразу БОЛЬШИМИ БУКВАМИ
						array(':mail_sv', $mail_sv),
						array(':enable', TRUE) );
			if (insert('user_box', 'id_user,login_sv,mail_sv,enable', $bind)) { # Если получилось добавить
				$checked='checked';
				msg("$module: ВСТАВИЛИ СТРОКУ $user_id $login_sv $mail_sv $enable", $dbg, 'green'); ###
			} else {
				msg("$module: НЕ УДАЛОСЬ ВСТАВИТЬ СТРОКУ $line_id !!!", $dbg); ###
			}#end if (insert)
		}#end if
		
		#echo "<input type='checkbox' id='cb_$user_login' $checked onClick='pg(\"add_user.php\", \"td_$user_login\", \"lg=$user_login\");'>";
		echo "<input type='checkbox' id='cb_$id' $checked onClick='pg(\"add_user.php\", \"td_$id\", \"lg=$user_login&id=$id\");'>";
		
		msg("\r\n<br>EXIT", $dbg, 'lime'); ###
		exit;
	} else {
		message("$module: Login not set");
		exit;
	}#end if isset
	
	

  

	msg('END', $dbg, 'lime'); ###
?>