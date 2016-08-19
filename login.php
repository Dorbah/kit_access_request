<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$dbg=FALSE; $module='login';
	$PHP_AUTH_USER=strtoupper($_SERVER['PHP_AUTH_USER']);
	if ('AS.LOGINOV'==$PHP_AUTH_USER) { $dbg=TRUE; }###
	if ($dbg) {
		error_reporting(E_ALL | E_STRICT | E_NOTICE); #устанавливаем режим отображения - все ошибки и советы
	}
	
	
	
	
#	1 Сначала проверить, есть ли сессионные логин и пароль, записать их в $sess_login и $sess_pass
#	Если $sess_login или $sess_pass пустой, то $sess_login='' и $sess_pass=''
	$err=''; $sess_login=''; $sess_pass='';
	if ( isset($_SESSION['login']) ) { #Сначала проверим, вдруг авторизация уже есть
		$sess_login=$_SESSION['login'];
		if ( empty($sess_login) ) {
			$sess_login=''; #$err.='Логин сессии пустой !!!<br>';
		} else {# Логин НЕ пустой
			if ( isset($_SESSION['pass']) ) {
				$sess_pass=$_SESSION['pass'];
				if ( empty($sess_pass) ) {
					$sess_pass='';
					 unset($_SESSION['auth']);
				} else { #Пароль есть
					#	3 $sess_login и $sess_pass есть, перекидываем на protected.php и EXIT !!!
					echo "<script>document.location.href='protected.php';</script>";
					$mess=color( bold('Вы уже вошли, нажмите сюда для продолжения'), 'lime');
					echo "<noscript><a href=protected.php>$mess</a></noscript>";
					exit;# Обязательно выходим !!!!!!!!
				}#end else empty pass
			}#end if isset pass
		}#end else empty $login
	} else {
		unset($_SESSION['auth']);
		$err.='Время истекло, войдите снова';
	} ###
	
	
#	2 Проверить, пришли ли введённые в форму логин и пароль, записать их в $form_login и $form_pass
#		Если $form_login или $form_pass пустой, то $form_login='' и $form_pass=''
	#Если логин и пароль прилетели
	$form_login=''; $form_pass='';
	if ( isset($_REQUEST['send']) ) {
	
		if ( isset($_REQUEST['login']) ) { $form_login = substr(cut_domain(filter_aZ09($_REQUEST['login'])), 0, 20); } # REQUEST УЖЕ URLDecodирован
		if ( empty($form_login) ) {
			$err.='Введите логин !!!';
		} else {
			if ( isset($_REQUEST['pass']) ) { $form_pass = substr($_REQUEST['pass'], 0, 20); }#Низзя никак менять пароль, только отпилить
			if ( empty($form_pass) ) {
				$err.='Введите пароль !!!';
			} else {
				$_SESSION['login']=$form_login; $_SESSION['pass']=$form_pass; # Устанавливаем новые login & pass
				script("document.location.href='protected.php';");
				$mess=color( bold('Данные для входа получены, нажмите сюда для продолжения работы'), 'lime');
				echo "<noscript><a href='protected.php'>$mess</a></noscript>";
				#write_log("Try to login user: '$form_login' with pass '$form_pass'"); #Только пароли надо как то шифровать, а не в открытом виде записывать
				exit;# Обязательно выходим !!!!!!!!
			}#end else
		}#end else
	}#end if isset send
	#else { $err.='Логин и пароль не отправлены !!!'; } ###
	
	
	#	4 Если $form_login и $form_pass пустые, то вывести форму ввода
	#Теперь можно выводить инфу на страничку
	#htmlhead();#Низзя ничего выводить до отправки HeaderA !!!
	msg("START $module", $dbg);#Низзя ничего выводить до отправки HeaderA !!!
	show_auth($err);#Авторизации ещё не было или сессия протухла, спросим логин/пароль
	msg("END $module", $dbg, 'lime');
	#htmlfoot();
	
	
	
	
	
?>