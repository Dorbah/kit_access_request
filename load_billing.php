<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$dbg=FALSE; $module='load_billing';# Название модуля для сообщений об ошибках
	# Модуль проводит первичную авторизацию на региональных биллингах по логину и паролю юзера и записывает результат в базу
	# Админ может ещё передать логин для проверки доступов конкретного юзера в $_REQUEST['lg']
	# "Cannot open connection named 'main'" - что за ошибка ? Возможно не проходит первичная авторизация, нужно пройти её вручную или через ACCESS
	
	
	if ($dbg) { show_var($_SESSION, '_SESSION'); } ###
	if ($dbg) { show_var($_REQUEST, '_REQUEST'); } ###
	#exit;
	
	
	if ( !isset($_SESSION['auth']) ) { # Попытка обхода авторизации
		$redirect='login.php';
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'lime');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;
	}
	
	if (isset($_SESSION['login']))  {$login = cut_domain($_SESSION['login']);} else {message("$module: Login NOT DEFINED !!!"); exit;}
	if (isset($_SESSION['pass']))   {$pass = $_SESSION['pass']; }              else {message("$module: Password NOT DEFINED !!!"); exit;}
	if (isset($_SESSION['region'])) {$region=$_SESSION['region']; } else { $region=init_region(); $_SESSION['region']=$region; }# Получим массив region из сессии
	
	if ('AS.Loginov'===$login) {$dbg=TRUE;}
	
	msg("$module START", $dbg);
	
	
	$timeout=100; if ($dbg) { $timeout=1000;}
	
	$is_admin=FALSE; $acc_lvl=access_level($login);
	if ( ('1'===$acc_lvl[0] OR '1'===$acc_lvl[1]) ) { $is_admin=TRUE; } # Проверим один раз и выставим флаг для админа и супервизора
	#if ($dbg) { show_var($is_admin, "is_admin($login)"); } ###
	msg("$module: access_level='$acc_lvl'", $dbg);###
	
	
	
	
	
	
	###########################
	#      ВТОРОЙ ПРОХОД      #
	###########################
	# Тут у нас в сессии уже есть
	#  - логин и пароль юзера, для которого делать первичную авторизацию
	#  - номер региона
	#NULL/0-Не было запросов	#1-Первичная авторизация проведена,	#2-Запрос доступа отправлен,	#3-Доступ есть
	$msg_reg_already_passed=color(bold('Первичная авторизация УЖЕ пройдена'), 'lime');
	if (isset($_REQUEST['rg'])) { # Если пришёл код региона
		if (isset($_SESSION['user_login']) ) { $login=$_SESSION['user_login']; }# Вытаскиваем из сессии засунутые туда в прошлый раз
		if (isset($_SESSION['user_pass']) )  { $pass =$_SESSION['user_pass'];  }# логин и пароль юзера, которого надо проверять
		session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку записи для ускорения работы скрипта
		
		$force=FALSE; if (isset($_REQUEST['force'])) { $force=TRUE; } # Всё равно пройти первичную авторизацию
		
		$region_id=get_int_reg($_REQUEST['rg'], $module, $region);# Обшкурим до целого и проверим наличие в $region
		$msg_first_reg_passed = color(bold('Первичная регистрация пройдена'), 'green');
		
		msg("reg_id=$region_id", $dbg);###
		msg("lg=$login", $dbg, 'blue');###
		
		$user_name=cut_domain($login);			# Отрезаем домен от логина
		$login=$domain.$user_name;					# Приделываем правильный домен
		$user_id=get_userID_by($user_name);	# Получаем IDшник юзера по логину или 0 если не найден
		if (empty($user_id)) {message("$module: Логин '$user_name' не найден"); exit;}
		
		$result=0; $line_id=0; #Нужно ли делать запрос на этого юзера в этот регион ?
		$bind=array(array(':user_id', $user_id), array(':region_id', $region_id)); # Уже делали на него запрос ?
		$STH=search('reg_access', '[user_id]=:user_id AND [region_id]=:region_id', '', $bind);
		if ($row = $STH->fetch()) { # Запрос уже выполнялся
			$line_id=$row['id'];		# ID строки для обновления
			$result=$row['result'];	# С каким результатом ?
			$req_date=$row['req_date'];
			
			if ( ($result>=1) AND (FALSE===$force) ) { echo "$msg_reg_already_passed $req_date"; exit; } #Если удачно, то снова авторизацию не надо делать
		}# end if
		
		# Запрос
		#  - Ещё не выполнялся $result==0
		#  - УЖЕ выполнялся, но надо сделать его ещё раз $force==TRUE Кнопка Ещё раз
		$res=1; # Предположим что всё хорошо
		$url=$region[$region_id][2];
		if (empty($url)) {
			$tpl=$region[$region_id][4];
			if ('KK'==$tpl OR 'DT'==$tpl) { message('RDP&nbsp;', 'magenta'); } # Если Краснодар или Питер
			if ('MS'==$tpl OR 'TV'==$tpl) { message('SBMS&nbsp;', 'maroon'); } # Если Москва или Тверь
		} else { # Если есть URL, который дёргать
			$auth_type=$region[$region_id][3];
			$data = get_curl($login, $pass, $url, $auth_type); #Делаем cURL к рег.биллингу
			
			# Нужно проверять есть ли вообще связь с биллингом и зарегался ли юзер на самом деле ???
			$arr_errs=array('/Http\/1.1 Service Unavailable/'													=>'Биллинг сейчас не работает', # Сообщение от нашего PROXY сервера
											'/No authority could be contacted for authentication/'		=>'No authority could be contacted for authentication',
											'/Cannot open connection named/'													=>'Cannot open connection named MAIN',
											'/\[Microsoft\]\[ODBC SQL Server Driver\]\[SQL Server\]/'	=>'Пользователь не добавлен в группу безопасности "Billing Sales ЕКЦ (EW)"',
											'/You are not authorized to view this page/'							=>'Неверные логин/пароль', # Эта ошибка не должна появляться, авторизуемся через LDAP
											'/Нет прав для доступа к данной странице/'								=>'Нет доступа к биллингу',
											);
			
			foreach ($arr_errs as $key=>$val) { # Разбираем присланный ответ, ищем ошибки
				if (0==$res) { break; } # Маленькая оптимизация, нашли ошибку - дальше нечего проверять
				if (preg_match($key, $data)) { message($val); $res=0; }# Если нашли ошибку, матюгаемся
			}#end foreach
			
			if (0!==$res) { echo "<input type='checkbox' name='$region_id' checked>$msg_first_reg_passed<br>"; } #Если ошибок нет
		}#end else !empty($url)
		
		
		# Запишем результат прохождения первичной регистрации юзера
		# Записывать статус в базу можно только если он NULL/0 или 1 - если статус>=2 - уже был отправлен запрос или есть доступ (3)
		#NULL/0-Не было запросов	#1-Первичная авторизация проведена,	#2-Запрос доступа отправлен,	#3-Доступ есть
		$res=intval($res); $region_id=intval($region_id); $user_id=intval($user_id); $line_id=intval($line_id); $result=intval($result);
		$req_date = date("Y-m-d H:i:s");
		
		$bind=array( array(':result', $res), array(':req_date', $req_date) );
		if (0!==$line_id) { # Значит запись существует
			if ($res>$result) { # Если результат изменился
				msg("res='$res'  result='$result'", $dbg);###
				$bind=array_merge($bind, array(':id', $line_id) );
				$STH=update('reg_access', 'result,req_date', 'id=:id', $bind);# то будем делать UPDATE
				msg("$res!=$result UPDATE", $dbg);###
			} else {
				msg("Результат не менялся, ничего не записываем", $dbg, 'grey');###
			}
		} else { # Запись НЕ существует
			$bind=array_merge($bind, array(':user_id', $user_id), array(':region_id', $region_id) );
			$STH=insert('reg_access', 'user_id,region_id,result,req_date', $bind);#Иначе будем делать INSERT
			msg("INSERT", $dbg);###
		}#end else
		
		exit;
	}#end if isset($_REQUEST['rg'])
	
	
	
	
	
	
	
	
	
	
	
	
	########################################################################
	# Первый проход - админ/супервизор передаёт логин юзера для проверки доступов
	########################################################################
	$login_sv='';
	if ( $is_admin ) { #if (is_admin($login) ) {
		$login_sv=$login; $pass_sv=$pass; # Сохраняем логин и пароль супервизора
		msg('is_admin=TRUE', $dbg, 'lime'); ###
		
		#Получаем переданный логин и расшифровываем ДОМпароль юзера для проверки
		if (isset($_REQUEST['lg']) ) { 
			$err_usr_must_login="'$login' должен хотя бы раз зайти в систему";
			$login=substr(cut_domain($_REQUEST['lg']), 0, 20);
			$user_id=get_userID_by($login); #Теперь надо расшифровать ДОМпароль этого юзера, чтобы сделать проверку доступа по его данным
			if (empty($user_id)) {
				message("$module: ID для '$login' не найден. $err_usr_must_login"); exit;
			} else {
				$encr_user_dom_pass=Get_uniq_id_by($user_id);#У этого юзера есть зашифрованный ДОМпароль в uniq_id ?
				if (empty($encr_user_dom_pass)) {# Если ДОМпароль пустой
					message("$module: ДомПароль для '$login' пустой. $err_usr_must_login"); exit;
				} else {#Пароль не пустой
					msg("$module: Try to get MasterKey for user '$login_sv'", $dbg, 'pink');###
					$supervisior_id=get_userID_by($login_sv);#Получим UserID супервизора или 0 если не найден
					if (empty($supervisior_id)) {
						message("$module: ID для '$login_sv' не найден"); exit;
					} else {
						$masterkey=GetMasterKey($supervisior_id, $pass_sv);# Получаем MasterKey или пустую строку и делаем CreateQuery4Reencrypt($supervisior_id, 9, $pass)
						if (empty($masterkey)) { # masterkey НЕ РАСШИФРОВАН или НЕ НАЙДЕН
							msg("$module: NO MasterKey or CAN'T DECRYPT for '$login_sv' !!!", $dbg); #Матюгаемся
						} else {# masterkey НАЙДЕН и РАСШИФРОВАН
							$pass=DecryptByPassPhrase($encr_user_dom_pass, $masterkey);#ЧТО ЧЕМ   #Расшифруем ДОМпароль юзера MasterKey'ем
							#if ($dbg) {show_var($pass, 'pass');}###
							
							message("Первичная авторизация для логина '$login'", 'lime');
							$_SESSION['user_login']=$login; # Ну вот получили мы логин и пароль юзера для проверки,
							$_SESSION['user_pass']=$pass;   # а дальше их надо запихать в сессию (потому как передавать их GET запросом некошеrно)
							# а потом ловить их после получения номера региона во ВТОРОМ ПРОХОДЕ
						}#end else
					}#end else NOT empty($supervisior_id)
				}#end else
			}#end else
		} else {
			unset($_SESSION['user_login']); unset($_SESSION['user_pass']);# Логин юзера НЕ прилетел - значит будем проверять свой доступ
		} #end if isset($_REQUEST['lg'])
	}#end if
	session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	message('Первичная авторизация на региональных биллингах', 'blue');
	msg("Login=$login", $dbg);###
	message('Пожалуйста, дождитесь окончания авторизации во всех регионах');
	
	$arr_acc_info=get_reg_access_info($login); # Получим массив результатов запросов юзера к регбиллингам
	echo "<table border='1'>\r\n";
	foreach($region as $key=>$value){
		$reg_name=$value[0]; $url=$value[2]; $auth_type=$value[3]; $tpl=$value[4];
		echo "<tr height='22'><td>$key</td><td width='150'>$reg_name</td><td id='rg_$key'>";
		$onClick="this.disabled=true; pg('load_billing.php', 'rg_$key', '&rg=$key&lg=$login&force=1');";
		$btn_repeat="&nbsp;<input type=button onClick=\"$onClick\" value='Ещё раз'>";
		
		if (array_key_exists($key, $arr_acc_info) ) {
			$res		=$arr_acc_info[$key][1];
			$rq_date=$arr_acc_info[$key][2];
			
			if ( empty($res) ) { # Не было запросов к биллингу
				script("setTimeout(\"pg('load_billing.php', 'rg_$key', '&rg=$key');\", $timeout);");
			} else {
				if ($dbg) { show_var($res, 'res'); message("lg=$login"); }###
				
				if ('1'===$res) {
					echo "$msg_reg_already_passed $rq_date";
					if ( ( $is_admin ) AND ('RK'==$tpl) ) { echo $btn_repeat; } # Нарисуем админу кнопочку "Ещё раз" для РегБиллингов
				} else {
					script("setTimeout(\"pg('check_billing.php', 'rg_$key', '&rg=$key');\", $timeout);"); # Автопроверка
				}
			}#end else
			
		}#end if
			
		echo "</td>\r\n</tr>\r\n";
	}# end foreach
	echo "</table>\r\n";
	
	
	msg("$module END", $dbg, 'lime');
	
?>