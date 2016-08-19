<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$dbg=FALSE; $module='check_billing';# Название модуля для сообщений об ошибках
	msg("$module: START", $dbg);
	
	# Здесь проверяется доступ к региональному биллингу по region_ID
	#Для проверки доступа нужно попытаться открыть страницу вида:
	#http://srv-ewbis-izh.skylink.local/page.asp?MenuTypeID=4&SubmitID=-1   "Поиск клиента"
	#Она должна открываться если есть доступ    Если возвращает страницу "Нет прав", значит доступа нет
	# "Cannot open connection named 'main'" - что за ошибка ? Возможно глюки на сервере регбиллинга
	###########################################################################
	#  Можно сделать, чтобы при самостоятельной проверке юзером доступа
	#  если у него нет доступа в какие то регионы, то супервизорам/админам
	#  приходило письмо, что у этого юзера ещё нет доступа и
	#  нужно отправить запрос на доступ - Вова Семёнов предложил идею
	#
	#  Сделать возможность выбирать, доступ к какой страничке нужно проверить
	#
	#
	###########################################################################
	
	if ( !isset($_SESSION['auth']) ) { # Попытка входа без LDAP авторизации с пустым паролем
		$redirect='login.php';
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'lime');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;# Обязательно выходим !!!!!!!!
	}#end if
	
	
	if (isset($_SESSION['login']))  {$login = cut_domain($_SESSION['login']);} else {message("$module: Login NOT DEFINED !!!"); exit;}
	if (isset($_SESSION['pass']))   {$pass  = $_SESSION['pass'];}               else {message("$module: Password NOT DEFINED !!!"); exit;}
	if (isset($_SESSION['region'])) {$region= $_SESSION['region']; } else { $region=init_region(); $_SESSION['region']=$region; }# Получим массив region из сессии
	
	$timeout=100;
	#if ('AS.LOGINOV'===strtoupper($login) ) { $dbg=TRUE; }
	
	$is_admin=FALSE; $acc_lvl=access_level($login);
	if ( ('1'===$acc_lvl[0] OR '1'===$acc_lvl[1]) ) { $is_admin=TRUE; } # Проверим один раз и выставим флаг для админа и супервизора
	
	if ($dbg) {
		$timeout=1000;
		ini_set('display_errors','On'); #включаем отображение ошибок если выключено
		error_reporting(E_ALL | E_STRICT); #устанавливаем режим отображения - все ошибки и советы
		show_var($is_admin, "is_admin($login)");###
		message("$module: access_level='$acc_lvl'");###
	}#end if
	
	
	
	
	
	
	$txt_acc_granted=color(bold('3:Доступ к биллингу есть'), 'blue');
	
	
	
	#Массив URLей и связанных с ними строчек для проверки доступности пунктов меню  # URL, preg_match строка, путь в меню
	$arr_check=array( 'cw'=>array('?MenuTypeID=4&SubmitID=0',    '/Работа с клиентами/', 'Работа с клиентами'),
										'cs'=>array('?MenuTypeID=4&SubmitID=-1',   '/Поиск клиента/',      'Поиск клиента'),
										'tr'=>array('?MenuTypeID=4&SubmitID=-15',  '/Тарификация/',        'Абонент-Тарификация-Тарификация'),
										'ru'=>array('?MenuTypeID=4&SubmitID=-156', '/Разовые услуги/',     'Работа с клиентами-Разовые услуги')
										);
	
	
	###########################
	#      ВТОРОЙ ПРОХОД      #
	###########################
	if (isset($_REQUEST['rg'])) { # Если прилетел регион
		if (isset($_SESSION['user_login']) ) { $login=$_SESSION['user_login']; } # Вытаскиваем из сессии засунутые туда на первом проходе логин
		if (isset($_SESSION['user_pass']) )  { $pass =$_SESSION['user_pass'];  } #  и пароль юзера, которого надо проверять
		session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку записи для ускорения работы скрипта
		
		msg("lg='$login' ps='$pass'", $dbg); ###
		
		$force=FALSE; if (isset($_REQUEST['force'])) { $force=TRUE; } # Истинная СИЛА :-D # Во что бы то ни стало пройти проверку доступа
		
		$rg=get_int_reg($_REQUEST['rg'], $module, $region);# Обшкурим до целого и проверим наличие в $region
		
		$tpl=$region[$rg][4];
		$user_name=cut_domain($login);			# Отрезаем домен от логина
		$login=$domain.$user_name;					# Приделываем правильный домен
		$user_id=get_userID_by($user_name);	# Получаем IDшник юзера по логину или 0 если не найден
		if (empty($user_id)) {message("$module: Логин '$user_name' не найден"); exit;}
		
		#Сначала проверяем, был ли уже запрос в этот регион и с каким результатом
		$bind=array( array(':user_id', intval($user_id)), array(':region_id', intval($rg)) );
		$STH=search('reg_access', 'user_id=:user_id AND region_id=:region_id', '', $bind);
		if ($row=$STH->fetch(PDO::FETCH_ASSOC)) {	#Если запись уже существует,
			$line_id  = intval($row['id']);				# Получаем IDшник строки
			$result   = intval($row['result']);		# Результат проверки доступа к рег.биллингу
			$req_date =        $row['req_date'];	# и дату запроса
			
			#msg("line_id=$line_id result=$result req_date=$req_date", $dbg);###
			
			if ( (3===$result) AND (FALSE===$force) ) { echo $txt_acc_granted.'&nbsp;'.$req_date; exit; } #Если удачно, то снова проверку не надо делать
			
			if ('KK'==$tpl OR 'DT'==$tpl) { $msg=color(bold('RDP&nbsp;'), 'magenta'); } # Если Краснодар или Питер
			if ('MS'==$tpl OR 'TV'==$tpl) { $msg=color(bold('SBMS&nbsp;'), 'maroon'); } # Если Москва или Тверь
			
			
			$check_page='cs';# Какую страничку в биллинге проверять по умолчанию
			if ( isset($_REQUEST['ch']) ) { # Если задано какую страничку проверять
				$ch=substr($_REQUEST['ch'], 0, 2); # берём из запроса
				if ( array_key_exists($ch, $arr_check) ) { $check_page=$ch; } # Проверим существование ключа
			} 
			
			$url=$region[$rg][2].$arr_check[$check_page][0];
			$chk_str=$arr_check[$check_page][1];
			$chk_name=$arr_check[$check_page][2];
			# Есть первичная авторизация(1) или запрос отправлен(2) или перепроверка (force) - будем посмотреть доступ
			if ( ( (1==$result) OR (2==$result) OR (TRUE===$force) ) AND ('RK'===$tpl) ) { # Если истинная сила
				$btn_repeat="&nbsp;<input type=button onClick=\"this.disabled=true; pg('check_billing.php', 'rg_$rg', '&rg=$rg&lg=$login&force=1');\" value='Ещё раз'>";
				$auth_type=$region[$rg][3];
				$data = get_curl($login, $pass, $url, $auth_type); #Делаем cURL к рег.биллингу
				
				$arr_errs=array('/Нет прав для доступа к данной странице, обратитесь к администратору./'=>'Нет доступа к биллингу',
												'/You are not authorized to view this page/'=>'Неверные логин/пароль', # Эта ошибка не должна появляться, авторизуемся через LDAP
												'/\[Microsoft\]\[ODBC SQL Server Driver\]\[SQL Server\]/'=>'Пользователь не добавлен в группу безопасности "Billing Sales ЕКЦ (EW)".',
												'/Http\/1.1 Service Unavailable/'=>'Биллинг сейчас не работает (Http/1.1 Service Unavailable)',
												'/No authority could be contacted for authentication/'=>'No authority could be contacted for authentication',
												'/Cannot open connection named/'=>'Cannot open connection named MAIN',
												#'/Ожидайте ответа системы/'=>'Ожидайте ответа системы', # Походу эта строка есть на КАЖДОЙ СТРАНИЦЕ   # Если страница почему то не загрузилась, то "Ожидайте ответа системы..."
												);
				
				#Разбираем присланный ответ
				foreach ($arr_errs as $key=>$val) { # Перебираем ошибки
					if (preg_match($key, $data)) { # Если нашли ошибку
						echo color(bold($val), 'red').$btn_repeat; # Матюгаемся
						#show_var(htmlspecialchars($data), 'data'); ###
						exit; #  и упадаем
					} 
				}
				
				if ( preg_match($chk_str, $data) ) { #Доступ есть, открылся запрошенный URL
					$req_date = date("Y-m-d H:i:s"); $result=3;
					$bind=array(array(':result',		$result	),		# Записываем в базу результат выполнения проверки доступа к региональному биллингу
											array(':req_date',	$req_date	),	# ТОЛЬКО в случае изменения для ускорения обработки
											array(':id',				intval($line_id))
										);
					$STH=update('reg_access', 'result,req_date', 'id=:id', $bind);# будем делать UPDATE
				} else {# Если результат запроса не определился, просто выведем что прилетело
					$msg.=color(bold('Неизвестная ошибка, обратитесь к разработчику'), 'red');
					$html_data=htmlspecialchars($data);
					$msg.="<!-- $html_data -->";
					if ($dbg) { show_var($html_data, 'htmlspecialchars(data)'); }###
				}#end else
				
			}#end if res==1 OR res==2
			
			if (2===$result) { #Эта проверка стоит здесЯ, чтобы ПОСЛЕ "Нет доступа" выводилось сообщение "Запрос отправлен"
				$msg.=color(bold("&nbsp;2:Запрос доступа отправлен"), 'green')."&nbsp;$req_date";
				if ('RK'==$tpl) {# Покажем кнопочку Проверить только для РегБиллингов
					$onclick="pg('check_billing.php', 'rg_$rg', '&rg=$rg&lg=$login&force=1');";
					$msg.="&nbsp;<input type=button class='btn' onclick=\"$onclick\" value='Проверить'>";
				}#end if tpl='RK'
			}#end if res==2
			
			
			if (3===$result) {
				$msg.="<input type='checkbox' checked>3: $txt_acc_granted $req_date";
				$msg.="&nbsp;<b>$chk_name</b>";
				if ('RK'===$tpl) {# Покажем кнопочку Проверить только для РегБиллингов
					$onclick="pg('check_billing.php', 'rg_$rg', '&rg=$rg&lg=$login&force=1');";
					$msg.="&nbsp;<input type=button class='btn' onclick=\"$onclick\" value='Проверить'>";
				}#end if tpl='RK'
			}#end if res==3
			
			
			echo "$msg&nbsp;\r\n";
			
		} else { # Иначе в этом регионе не было запросов - сразу вызываем load_billing для первичной авторизации
			echo "<script>setTimeout(\"pg('load_billing.php', 'rg_$rg', '&rg=$rg');\", $timeout);</script>\r\n";
		}#end else Если запись НЕ существует
		
		exit; #Запрос по региону сделали - теперь упадаем
	}#end if isset($_REQUEST['rg'])
	
	
	
	
	
	
	
	
	# ПЕРВЫЙ проход - админ передаёт в сессию логин юзера для проверки доступов
	$login_sv='';
	if ($is_admin) { #(is_admin($login) ) {
		msg("<br>$module: FIRST PASS", $dbg, 'blue');###
		msg("$module: is_admin=TRUE", $dbg, 'lime'); ###
		
		$login_sv=$login; $pass_sv=$pass; # Сохраняем логин и пароль супервизора
		if ( isset($_REQUEST['lg']) ) { # Есть логин юзера, для которого надо смотреть доступ ?
			$login=substr(cut_domain($_REQUEST['lg']), 0, 20); #Получаем переданный логин и расшифровываем пароль юзера для проверки
			msg("$module: user_login='$login'", $dbg, 'green');###
			
			#$user_id=get_userID_by($login); #Теперь надо расшифровать пароль этого юзера, чтобы сделать проверку доступа по его данным
			#msg("$module: user_id='$user_id'", $dbg, 'lime');###
			
			#if (empty($user_id)) {
			#	message("$module: ID для '$login' не найден"); exit;
			#} else {
				
				#$encr_user_dom_pass=Get_uniq_id_by($user_id);#У этого юзера есть зашифрованный ДОМпароль в uniq_id ?
				$encr_user_dom_pass=Get_uniq_id_by_login($login);#У этого юзера есть зашифрованный ДОМпароль в uniq_id ?
				
				if (empty($encr_user_dom_pass)) {# Если ДОМпароль пустой
					message("$module: ДомПароль для '$login' пустой"); exit;
				} else {#Пароль не пустой
					msg("$module: Try to get MasterKey for user '$login_sv'", $dbg, 'magenta');###
					$supervisior_id=get_userID_by($login_sv);#или 0 если не найден
					msg("$module: sv_id='$supervisior_id'", $dbg, 'blue'); ###
					if (empty($supervisior_id)) {
						message("$module: UserID для '$login_sv' не найден"); exit;
					} else {
						$masterkey=GetMasterKey($supervisior_id, $pass_sv);# Получаем MasterKey или пустую строку и делаем CreateQuery4Reencrypt($supervisior_id, 9, $pass)
						msg("$module: mk='$masterkey'", $dbg, 'aqua'); ###
						if (empty($masterkey)) { # masterkey НЕ РАСШИФРОВАН или НЕ НАЙДЕН
							msg("$module: NO MasterKey or CAN'T DECRYPT for '$login_sv' !!!", $dbg); #Матюгаемся
						} else {# masterkey НАЙДЕН и РАСШИФРОВАН
							
							msg("$module: enc_us_dom_pass='$encr_user_dom_pass'", $dbg, 'blue'); ###
							$pass=DecryptByPassPhrase($encr_user_dom_pass, $masterkey);#ЧТО ЧЕМ   #Расшифруем доменный пароль юзера MasterKey'ем
							if ($dbg) {show_var($pass, 'DeCrypted User DomPass');}###
							
							message("Проверка доступа для логина '$login'", 'blue');
							
							$_SESSION['user_login']=$login; #Ну вот получили мы логин и пароль юзера для проверки,
							$_SESSION['user_pass']=$pass;   # а дальше их надо запихать в сессию, а потом ловить их после получения номера региона
						}#end else
					}#end else NOT empty($supervisior_id)
				}#end else
			#}#end else
			
		} else {
			unset($_SESSION['user_login']); unset($_SESSION['user_pass']);# Логин юзера НЕ прилетел - значит будем проверять свой доступ
		} #end if isset($_REQUEST['lg'])
	}#end if
	
	
	
	
	session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	msg("$module START", $dbg);###
	
	# Выводим список регионов и смотрим существующие доступы в базе
	$arr_acc_info=get_reg_access_info($login); # Получим массив результатов запросов юзера к регбиллингам
	message('Проверка доступа к региональным биллингам', 'blue');
	message('Пожалуйста, дождитесь окончания проверки всех регионов');
	echo "<table border='1'>\r\n";
	
	#NULL/0-Не было запросов/первичной авторизации	#1-Первичная авторизация проведена,	#2-Запрос доступа отправлен,	#3-Доступ есть
	foreach($region as $key=>$value) {
		list($reg_name, $reg_name_rp, $url, $auth_type, $tpl) = $value; #Распихиваем массив в переменные
		if (!empty($url) ) { $reg_name="<a href='$url'>$reg_name</a>"; }
		echo "<tr height='22'><td>$key</td><td width='150'>$reg_name</td><td id='rg_$key'>"; #<img src='./img/wait.gif'>
		
		if (array_key_exists($key, $arr_acc_info) ) {
			$res=$arr_acc_info[$key][1];
			$rq_date=$arr_acc_info[$key][2];
			
			if ( empty($res) ) {
				message('&nbsp;Нет первичной авторизации');
				script("setTimeout(\"pg('load_billing.php', 'rg_$key', '&rg=$key');\", $timeout);");
			} else {
				#if ($dbg) {echo var_dump($res)." lg=$login "; }###
				if ('3'==$res) {
					echo "$txt_acc_granted $rq_date";
					if ( ( $is_admin ) AND ('RK'==$tpl) ) {  # Нарисуем админу кнопочку Ещё раз для РегБиллингов
						
						#message('TEST');###
						foreach($arr_check as $ch=>$val) {
							$caption=$val[2];
							$btn_repeat="&nbsp;<input type=button onClick=\"this.disabled=true; pg('check_billing.php', 'rg_$key', '&rg=$key&lg=$login&force=1&ch=$ch');\" value='$caption'>";
							echo $btn_repeat;
						}#end foreach
						
					}
				} else {
					script("setTimeout(\"pg('check_billing.php', 'rg_$key', '&rg=$key');\", $timeout);");
				}#end else
			}#end else
			
		}#end if
		
		
		echo "</td></tr>\r\n";
	}#end foreach
	echo '</table>';
	
	msg("$module END", $dbg, 'lime');###
	
	
	
	
?>