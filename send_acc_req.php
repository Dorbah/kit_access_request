<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$dbg=FALSE; $module='send_acc_req';
	# Отправка запроса на регистрацию по почте региональным админам
	# Сюда прилетают: reg[1], reg[2], ..., reg[N] - в какие регионы отправить запрос
	# Из таблички user_box отфильтрованные по $_SESSION['login'] берём список юзеров,
	#  на которых надо отправить запрос
	#NULL/0-Не было запросов	#1-Первичная авторизация проведена,	#2-Запрос доступа отправлен,	#3-Доступ есть
	
	
	if ( !isset($_SESSION['auth']) ) { # Попытка обхода авторизации
		$redirect='login.php';
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'lime');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;# Обязательно выходим !!!!!!!!
	}#end if
	
	if (isset($_SESSION['login'])) {$supervisior = cut_domain($_SESSION['login']);} else {message("$module: Login NOT DEFINED !!!"); exit;}#Получаем логин супервизора
	if (!is_admin($supervisior)) {message("$module: You DON'T HAVE sufficient privilegies to view this page !!!"); exit;}
	if (isset($_SESSION['pass'])) {$super_pass = $_SESSION['pass'];} else {message("$module: Pass NOT DEFINED !!!"); exit;}#Получаем пароль супервизора
	if ( isset($_SESSION['region']) ) { $region=$_SESSION['region']; } else { $region=init_region(); $_SESSION['region']=$region; }# Получим массив region из сессии
	session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку
	
	echo "<div id='result'>\r\n"; #  Начало DIVчика result
	#if ('AS.Loginov'===$supervisior) {$dbg=TRUE;}
	msg('START', $dbg); ###
	#message("login=$supervisior");
	
	
	$arr_user_box=get_users_choosed_by($supervisior);# Получим массив логинов юзеров, которых выбрал супервизор
	# Структура: $arr_user_box[$login]=array($id, $id_user, $login_sv, $mail_sv, $enable);
	
	if (empty($arr_user_box)) {
		message('НИКТО НЕ ВЫБРАН !<br>Выберите пользователей (меню Выбор),<br>на которых нужно отправить запросы на доступ к региональным биллингам');
		exit;
	}
	
	
	
	
	###############################
	#  ВТОРОЙ ПРОХОД
	###############################
	if ( isset($_REQUEST['reg']) ) { # Прилетел массив регионов ?
		$regs=$_REQUEST['reg'];		# куда отправлять запросы
		#show_var($regs, 'regs');###
		
		if ( FALSE === array_search('true', $regs) ) {
			message("Не выбрано ни одного региона для отправки<br>");
		} else {
		
			###################################################################
			#Строим табличку для составления списка логинов для каждого региона
			#						15 22 33 34 36 40 ...
			#IO.Familia  3  1  0  1  2  3 ...
			#IO.Familia2 1  1  1  1  1  1 ...
			###################################################################
			$acc_info=array();# Табличка нужна для списка логинов для запроса
			if ($dbg) {echo "<div class='area'><TABLE border=1><TBODY>";} ###
			foreach ($arr_user_box as $login=>$values) { #Пробегаемся по каждому логину юзера из массива $arr_user_box
				list($id, $id_user, $login_sv, $mail_sv, $enable)=$values; #Распихиваем значения из $values
				$access_info=get_reg_access_info($login); #Получим массив результатов прошлых запросов по этому юзеру $access_info[$ID]=array($user_id, $result, $req_date);
				if ($dbg) {echo "<TR><TD>$login</TD>";} ###
				foreach($regs as $key=>$val) { #Идём по присланным регионам
					if ('true'===$val) {#Этот регион выбран ?
						$reg_ID=get_int_reg($key, $module, $region);#Обшкуриваем номер региона до целого
						$reg_name=$region[$reg_ID][0];      $url=$region[$reg_ID][2];
						$user_id=$access_info[$reg_ID][0];  $result =$access_info[$reg_ID][1];
						$bgcolor='white';
						if ('0'==$result OR NULL==$result) {$bgcolor='red';}
						if ('1'==$result) {$bgcolor='yellow';}
						if ('2'==$result) {$bgcolor='green';}
						if ('3'==$result) {$bgcolor='lime';}
						$acc_info[$reg_ID][$login]=array($result, $url, $id_user); ###### UserID надо передать в user_id_list
						if ($dbg) {echo "<TD bgcolor='$bgcolor'>$reg_ID:$result</TD>";} ###
					}#end if
				}#end foreach $regs
				if ($dbg) {echo "</TR>";} ###
			}#end foreach $arr_user_box
			if ($dbg) {echo "</TBODY></TABLE></div>";} ###
			
			
			
			#Теперь по подготовленной табличке формируем список логинов для запроса
			echo "<div class='area'><TABLE border=1><TBODY><TR><TH>Запрос в регион</TH><TH>На пользователей</TH><TH>Выполнен</TH></TR><TR>\r\n";
			foreach ($acc_info as $reg_ID=>$log_res) {
				#NULL/0-Не было запросов	#1-Первичная авторизация проведена,	#2-Запрос доступа отправлен,	#3-Доступ есть
				$logins=''; $user_id_list='';
				foreach ($log_res as $login=>$arr_res_url) {      #А дальше через запятую перечисляем логины,
					list($result, $url, $id_user)=$arr_res_url; #$result=intval($arr_res_url[0]); $url=$arr_res_url[1]; $id_user=$arr_res_url[2];
					if (1===intval($result) ) { # OR empty($url)) {  #Есть первичная авторизация(1) или пустой URL
						$logins.=$login.',';
						$user_id_list.=$id_user.','; #get_userID_by($login).','; #Небольшая оптимизация
					}#end if
				}#end foreach   # По другим запросы отправлять не нужно или ещё или уже
				$logins=substr($logins, 0, -1); #Убираем последнюю лишнюю запятую
				$user_id_list=substr($user_id_list, 0, -1); #Убираем последнюю лишнюю запятую
				$reg_name=$region[$reg_ID][0];
				
				echo "<TD>$reg_ID $reg_name</TD><TD>&nbsp;$logins</TD>";
				
				echo '<TD>&nbsp;';
				if ($dbg) { echo color(bold('ТЕСТ:'), 'magenta'); }
				# Отправляем запрос на доступ региональным админам
				#msg('ТИПА УСПЕШНО ОТПРАВИЛИ ПОЧТУ :-)', $dbg, 'lime'); ###
				if ( !empty($logins) ) {#Есть на кого отправлять запросы
					$res=FALSE;
					
					########################################################################
					#  Хорошо бы ещё передавать вместе со списком логинов и ФИО юзеров,
					#    чтобы не вытаскивать их потом запросами
					########################################################################
					if ($res=send_mail($supervisior, $super_pass, $logins, $reg_ID, $region, FALSE) ) {# TEST=TRUE
						msg("Запрос в рег=$reg_ID отправлен", $dbg, 'lime');
						$req_date = date("Y-m-d H:i:s");
						msg("Типа пишем в базу результат отправки запроса reg_ID='$reg_ID' user_id_list='$user_id_list' res=2 req_date='$req_date'", $dbg, 'lime'); ###
						#######################################################################
						# Запишем в базу результат отправки запроса
						if (!$dbg) { # В БАЗУ ЗАПИСЫВАЕМ ТОЛЬКО В СЛУЧАЕ РЕАЛЬНОЙ ОТПРАВКИ !!!
							$bind=array(array(':region_id', $reg_ID),
													array(':result', 2),							# $res=2; #Запрос доступа отправлен
													array(':req_date',	$req_date )
													);
							# Нельзя к одной именованной псевдопеременной привязать несколько значений, как например, в выражении IN() SQL запроса.
							# Поэтому будем подставлять список IDшников юзеров user_id_list в запрос напрямую, а не биндить через переменную
							$STH=update('reg_access', 'result,req_date', "region_id=:region_id AND user_id IN ($user_id_list)", $bind);#Сразу нескольким юзерам обновляем результат запроса доступа
							if ($dbg) {show_var($STH, 'STH');} ###
							if (empty($STH) ) {message("Не удалось записать");  } else {message('Записали успешно', 'lime');}
						}# if NOT debug ###
						#######################################################################
					} else {
						msg("Запрос в рег=$reg_ID НЕ отправлен", $dbg);
					}#end else NOT send_mail
				}#end if NOT empty($logins)
				echo "</TD></TR>\r\n";
			}#end foreach
			echo "</TBODY></TABLE></div>\r\n";
			
			msg("ТЕПЕРЬ ЖДИТЕ ОТВЕТЫ НА ВАШУ ПОЧТУ '$supervisior@SKYLINK.RU' ОТ РЕГИОНАЛЬНЫХ АДМИНИСТРАТОРОВ", !$dbg, 'blue');###
			exit; # Всем запросы отправили, выходим
		
		}#end else
		
		
	}#end if Прилетел массив регионов
	
	
	
	
	
	
	
	###############################
	#  ПЕРВЫЙ ПРОХОД
	###############################
	message('Выберите регионы, куда нужно отправить запросы', 'green');
	#########################################################################################################################################
	msg('ВНИМАНИЕ !<br>ОТ ВАШЕГО ИМЕНИ БУДУТ ОТПРАВЛЕНЫ РЕАЛЬНЫЕ ЗАПРОСЫ НА ДОСТУП !!!<br>(если Вам разрешена отправка на внешние адреса)', !$dbg);
	msg('ТЕСТОВЫЙ РЕЖИМ. ЗАПРОСЫ НА ДОСТУП НЕ ОТПРАВЛЯЮТСЯ', $dbg);
	#Вывести список выбранных пользователей из таблички user_box
	echo "<DIV class='area' id=logins style='float: left;'>\r\n";
	message("Выбраны пользователи", 'blue');
	echo "<Table border=1><TBody>";
	
	
	echo "<Tr><Th>ФИО</Th><Th>Логин</Th>";
	
	$arr_reg_IDs=array_keys($region); # Получим список IDшников регионов
	foreach($arr_reg_IDs as $key=>$val) { # Выведем его в заголовок таблицы
		$reg_name=$region[$val][0];
		$rg_nm=mb_substr($reg_name, 0, 3, 'UTF-8');
		echo "<Th title='$reg_name'>$val<br>$rg_nm</Th>";
	}#end foreach
	echo "</Tr>\r\n";
	
	
	# Посмотрим доступы на выбранных юзеров
	$user_IDs=''; #Сформируем список IDшников юзеров для запроса
	foreach($arr_user_box as $key=>$val) { # $arr_user_box[$login]=array($id, $id_user, $login_sv, $mail_sv, $enable);
		$user_ID=$val[1];
		$user_IDs.=$user_ID.',';
	}#end foreach
	$user_IDs=substr($user_IDs, 0, -1); #Отрезаем последнюю лишнюю ','
	$STH_srch=search('reg_access',"user_id IN ($user_IDs)", 'user_id');
	$arr_results=array();
	while ($row_srch = $STH_srch->fetch(PDO::FETCH_ASSOC)) {
		$user_id=$row_srch['user_id'];
		$region_id=$row_srch['region_id'];
		$result=$row_srch['result'];
		$req_date=$row_srch['req_date'];
		$arr_results[$user_id][$region_id]=array($result, $req_date);
	}#end while
	
	
	
	echo "<script>
	document.getElementsByClassName('demo').ondblclick = function() {myFunction()};
	
	function myFunction(id) {
    //document.getElementById(id).title = 'I was double-clicked!';
		alert('I was double-clicked! id='+id);
	}
</script>";
	
	
	
	
	
	##################################################################
	#  Тут хорошо бы сразу вывести табличку юзеров с их доступами,
	# чтобы можно было увидеть в какие регионы у кого нет доступа
	# И можно галочки снять на тех регионах, где у всех доступ есть.
	#						15 22 33 34 36 40 ...
	#IO.Familia  3  1  0  1  2  3 ...
	#IO.Familia2 1  1  1  1  1  1 ...
	##################################################################
	foreach ($arr_user_box as $login=>$values) {
		$user_ID=$values[1];
		$FIO=get_user_fio($login);
		echo "<TR align=center><TD>$FIO</TD><TD>$login</TD>";
		
		# Тут выведем существующие доступы юзеров в строку
		foreach($arr_reg_IDs as $key=>$region_id) {
			list($result, $req_date) = $arr_results[$user_ID][$region_id];
			$bgcolor='white'; $descr='';
			$reg_name=$region[$region_id][0];
			
			if (empty($result) ) {$bgcolor='red';	$descr='Нет первичной авторизации';}
			if ('1'==$result) {$bgcolor='yellow';	$descr='Есть первичная авторизация';}
			if ('2'==$result) {$bgcolor='green';	$descr='Запрос доступа отправлен';}
			if ('3'==$result) {$bgcolor='lime'; 	$descr='Доступ есть';}
			$onDblClick="alert(\"OK \"+this.title);";
			echo "<TD class='dblclk$result' title='$descr в $reg_name с $req_date' onDblClick='$onDblClick'>$result</TD>";
			#echo "<TD class='dblclk$result' title='$descr в $reg_name с $req_date'>$result</TD>";
			#echo "<TD class='dblclk$result demo' id='id_$result' title='$descr в $reg_name с $req_date'>$result</TD>";
		}#end foreach
		
		echo "</TR>";
	}#end foreach
	
	
	
	echo "</TBody></Table>\r\n";

	message('Если требуется отправить запрос в регион, но он уже был отправлен (2),', 'green');
	message('то нужно зайти в Доступы и сбросить его статус в (1) для этого специалиста', 'green');
	message('Расшифровка статусов:', 'magenta');
	message('0 - первичная авторизация НЕ пройдена', 'red');
	message('1 - первичная авторизация пройдена', 'gold');
	message('2 - запрос доступа отправлен', 'green');
	message('3 - доступ к биллингу получен', 'lime');

	
	echo "</DIV>\r\n";
	
	$area='area'; if ( isset($_REQUEST['send']) ) { $area='redarea'; }
	echo "<DIV class='$area' id=regions style='float: left;' align='center'>\r\n";
	message("Выберите регионы", 'blue');
	echo '<form id=form_regs name=form_regs>';
	echo '<input type=hidden id=send value=yes>';
	show_regions();#Выводим список регионов для выбора
	echo "<input type=submit onclick=\"send_regs('send_acc_req.php', 'result', 'form_regs');\" value='Отправить запросы'>";
	echo '</form>';
	echo "</DIV>\r\n";
	
	
	
	echo "</div>";#Конец DIVчика result
	
	
	
	
	
	
	
	
	
	
	
	
	msg('END', $dbg, lime); ###
	
	
	
?>