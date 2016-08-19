<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$dbg=FALSE;# Отладку включить/выключить
	$PHP_AUTH_USER=strtoupper($_SERVER['PHP_AUTH_USER']);
	#if ('AS.LOGINOV'===$PHP_AUTH_USER) {$dbg=TRUE;}
	if ($dbg) {
		ini_set('display_errors','On'); #включаем отображение ошибок если выключено
		error_reporting(E_ALL | E_STRICT); #устанавливаем режим отображения - все ошибки и советы
		#message("dbg=TRUE");
	}#end if
	
	
	
	
	
	/*
	#Тут мы ненадолго закроем портал
	htmlhead();
	message('Pool is closed due to AIDS');
	message('Починяем примус', 'green');
	message("Можно ещё написать на почту <a href='mailto:AS.Loginov@SkyLink.ru'>AS.Loginov@SkyLink.ru</a>", 'blue');
	$user_name=$_SERVER['PHP_AUTH_USER'];
	$acc_lvl=access_level($user_name);
	message("Your login '$user_name' and access level is:$acc_lvl", 'lime');
	
	#$arr_user=get_user_info($user_name);
	#show_var($arr_user, 'arr_user');
	
	if (is_supervisior($user_name) ) {message('is_supervisior=TRUE');}
	if (is_admin($user_name) ) {message('is_admin=TRUE');}
	
	htmlfoot();
	exit;
	*/
	
	
	
	$tab="\t\t\t\t\t\t\t";
	#Пытаемся получить логин и пароль из сессии
	if (!isset($_SESSION['login']) OR !isset($_SESSION['pass']) OR !isset($_SESSION['auth']) ) { #Нет ножек - нет мультиков
		htmlhead();
		echo "$tab<!-- START2 index.php -->\r\n";
		echo "$tab<div style='width:20%; height:20%;' id='login' class='area'></div>\r\n";
		echo "$tab<script>pg('login.php','login','');</script>\r\n";
	} else { #Всё хорошо, выводим меню и страничку
		if ( isset($_SESSION['region']) ) { $region=$_SESSION['region']; } else {$_SESSION['region']=init_region(); }
		$login=$_SESSION['login']; $pass=$_SESSION['pass'];   
		
		htmlhead(get_user_fio($login));
		echo "$tab<!-- START index.php -->\r\n";
		msg("sess_login=$login get", $dbg, 'lime');###
		msg("sess_pass=$pass get", $dbg, 'green');###
		
		$ramka=''; if ($dbg) {$ramka='border=3 bordercolor=lime';}
		echo "$tab<TABLE $ramka width='100%' style='height:100%;' cellspacing=0 cellpadding=0>\r\n";
		echo "$tab	<TBODY>\r\n";
		echo "$tab		<TR valign='top'>\r\n";
		echo "$tab			<TD width='1%'>\r\n";
		echo "$tab				<div class='area' id='menu'>\r\n";
		show_menu(); #Показаем меню
		echo "$tab				</div>\r\n";
		echo "$tab			</TD>\r\n";
		
		echo "$tab			<TD>\r\n";
		echo "$tab				<DIV id='main' class='area' style='height:99%;'>Включите JavaScript в браузере</DIV>\r\n"; #Тут выведем DIVчик main
		echo "$tab				<script>pg('user_pass.php','main','');</script>"; # По умолчанию будем открывать страничку с паролями юзера
		echo "$tab			</TD>\r\n";
		echo "$tab		</TR>\r\n";
		echo "$tab	</TBODY>\r\n";
		echo "$tab</TABLE>\r\n";
	}#end else
	
	echo "$tab<!-- END index.php -->\r\n";
	htmlfoot();
?>