<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	$module='logout';
	
	session_unset();
	session_destroy();
	
	echo "<script>document.location.href='index.php';</script>";
	$mess='<font color=lime><b>Вы вышли из системы, нажмите сюда для возврата на главную страницу</font></b>';
	echo "<noscript><a href='index.php'>$mess</a></noscript>";
	
?>