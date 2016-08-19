<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	
	
	$module='protected';
	$dbg=FALSE; $timeout='5';
	$mess=''; $color=''; $login=''; $pass=''; $user_name='';
	
	if (isset($_SESSION['login'])) {
		$login=$_SESSION['login'];
		if ('AS.Loginov'===$login) {$dbg=TRUE; } ###
		if ('DA.Pechenin'===$login) {$dbg=TRUE; } ###
	}#end if
	
	if ($dbg) { $timeout=600; }
	
	if (isset($_SESSION['pass'])) {
		$pass=$_SESSION['pass'];
	}#end if
	
	
	
	
	$ramka=''; if ($dbg) {$ramka=' border=1 bordercolor=blue';}
	
	echo "<HTML><head><meta http-equiv='refresh' content='$timeout;url=index.php'></head><BODY>\r\n";
	echo "<TABLE$ramka align='center' width='20%' style='height:100%;'><TR><TD>";
	echo "<div style='border: solid 1px #c0c0c0; margin: 5px; padding: 5px; background: #F6F7FF;' align=center>\r\n";
	
	
	msg('START', $dbg, 'blue');###
	msg('Вi таки пrишли на protected.php', $dbg);###
	msg("$module: login=$login", $dbg, 'lime');###
	msg("$module: pass=$pass", $dbg, 'lime');###
	
	
	
	if (!empty($pass)) { # Пароль НЕ ПУСТОЙ
		msg("$module: Pass=$pass", $dbg, 'lime'); ### Если пароль прилетел
		$user_name=cut_domain($login); $login=$domain.$user_name;		# Ставим правильный домен для бинда и РЕГбиллингов
		$ldapconn=ldap_conn(); $bind_ok=bind_ldap($ldapconn, $login, $pass); # Попытка авторизации через LDAP
		
		if (empty($bind_ok)) { # Нет такого юзера
			unset($_SESSION["login"]); unset($_SESSION["pass"]); unset($_SESSION['auth']);
			$mess='Пара логин/пароль неверна !'; $color='red';
		} else { #BIND OK
			msg("$module: Bind successful", $dbg, 'green');#### Если авторизация прошла
			$_SESSION['login']=$login;
			$_SESSION['pass']=$pass;
			$_SESSION['auth']='OK'; #Признак авторизации в LDAPe
			$user_id=add_user_from_ldap($user_name); # Добавим/обновим данные юзера в таблице users
			msg("$module: $user_id:$user_name add/updated", $dbg, 'magenta'); ###
			
			
			
			$old_encr_user_pass=Get_uniq_id_by($user_id);#У этого юзера есть зашифрованный ДОМпароль в uniq_id ?
			if (empty($old_encr_user_pass)) {# Если ДОМпароль пустой или НЕ РАСШИФРОВАЛСЯ - первый вход или юзер сменил ДОМпароль
				msg("$module: $user_name - Old encrypted DOMAIN pass is EMPTY or CAN'T DECRYPT", $dbg);
				CreateQuery4Reencrypt($user_id, 8, $pass);# Создаём запрос на зашифровку ДОМпароля  8 - Domain Pass
			} else {#NOT empty old_encr_user_pass - ДОМпароль НЕ ПУСТОЙ или РАСШИФРОВАЛСЯ
				
				# Если супервизору нужно создать MasterKey, то временно делаем его админом, логинимся под ним -
				# при этом создаётся запрос на зашифровку ему MasterKey пароля и потом нужно снять у него галочку Админ
				if (is_admin($user_name)) {
					msg("$module: Try to get MasterKey for user '$user_name'", $dbg, 'pink');###
					$masterkey=GetMasterKey($user_id, $pass);# Получаем MasterKey или пустую строку и делаем CreateQuery4Reencrypt($user_id, 9, $pass)
					
					if (empty($masterkey)) { # masterkey НЕ РАСШИФРОВАН или НЕ НАЙДЕН
						msg("$module: NO MasterKey or CAN'T DECRYPT for '$user_name' !!!", $dbg); #Матюгаемся
					} else {# masterkey НАЙДЕН и РАСШИФРОВАН
						$_SESSION['masterkey']=$masterkey; msg('mk set', $dbg, 'brown');### #MasterKey админу в сессию    # В root мне логи
						
						msg("<br>\r\n$module: Выберем IDшники юзеров, по которым есть запросы", $dbg, 'magenta');###
						$STH_usrs=query('SELECT DISTINCT user_id FROM [kit_access_request].[dbo].[queries4reencrypt]');
						while ( $row_usrs=$STH_usrs->fetch() ) { # Перебираем IDшники юзеров
							$user_id=$row_usrs['user_id'];
							msg("$module: Нашли ID=$user_id", $dbg, 'magenta');###
							
							msg("$module: Ищем незанятые запросы на перешифровку РЕГпаролей для user_id=$user_id", $dbg, 'green');###
							$STH_qrs=search('queries4reencrypt', 'user_id=:user_id AND (busy=0 OR busy IS NULL) AND (res_type_id NOT IN (8,9))', '', array(':user_id', $user_id));
							while ( $row_qrs=$STH_qrs->fetch() ) { #Пробегаемся по всем запросам на перешифровку ТОЛЬКО этого юзера
								$qrs_line_id=$row_qrs['id']; $res_type_id=$row_qrs['res_type_id']; $new_res_pass=$row_qrs['new_pass'];
								TryToReCryptResPass($user_id, $masterkey, $res_type_id, $new_res_pass, $qrs_line_id);
								if ($dbg) {echo "<br>\r\n";}###
							}#end while $row_qrs Пробегаемся по всем запросам на перешифровку ТОЛЬКО этого юзера
							
							# После отработки и удаления всех запросов по перешифровке РЕГпаролей этого юзера
							# должны остаться запросы на перешифровку ДОМпароля и MasterKey пароля
							# Но в этом надо ещё убедиться - вдруг связь с сервером глюканула и
							# запрос на перешифровку не удалился или есть занятые запросы, которые ещё в обработке
							# Иначе, если мы перешифруем ДОМпароль, а РЕГпароли ещё не будут перешифрованы, то их потом не расшифровать
							msg("$module: Запросы на перешифровку РЕГпаролей для '$user_id' кончились ?", $dbg, 'maroon');
							$STH=search('queries4reencrypt','user_id=:user_id AND (res_type_id NOT IN (8,9))', '', array(':user_id', $user_id));
							$row=$STH->fetch(); #show_var($row, 'row');###
							if (empty($row)) {  # Кончились все запросы от этого юзера ?
								msg("$module: Запросы для '$user_id' кончились", $dbg, 'green');
								
								# Теперь ищем НЕЗАНЯТЫЕ запросы ТОЛЬКО на зашифровку/перешифровку ДОМпароля и MasterKey пароля
								$STH_dom=search('queries4reencrypt','user_id=:user_id AND (busy=0 OR busy IS NULL) AND (res_type_id IN (8,9))', '', array(':user_id', $user_id));
								while ( $row_dom=$STH_dom->fetch() ) {
									$dom_line_id=$row_dom['id']; $res_type_id=$row_dom['res_type_id']; $new_res_pass=$row_dom['new_pass'];
									SetBusyFlagBy($dom_line_id, 1);#Заблочим строку, чтобы никто её пока не трогал
									
									if ($res_type_id==8) { #Обрабатываем запрос на перешифровку ДОМпароля
										if (SetEncryptedUniqID($user_id, $masterkey, $new_res_pass) ) {#Шифруем masterkey'ем новый ДОМпароль в uniq_id
											msg("$module: uniq_id UPDATED with '$new_res_pass'", $dbg, 'lime'); ###
											msg("$module: Зашифровали MasterKey'ем новый ДОМпароль в uniq_id", $dbg, 'blue'); ###
											DelLine('queries4reencrypt', $dom_line_id);		#Удаляем запрос на перешифровку ДОМпароля
										}#end if SetEncryptedUniqID
									}#end if res_type_id==8
									
									# Эту ветку можно выполнять только если нет запроса на перешифровку ДОМпароля
									if ($res_type_id==9) { #Тут выполним запрос на зашифровку MasterKey пароля
										msg("$module: res_type_id=9 Тут типа шифруем MasterKey ДОМпаролем для админа", $dbg, 'green');
										if (EncryptMasterKeyByDomPass($user_id, $masterkey, $new_res_pass) ) { #Шифруем MasterKey ДОМпаролем для админа
											msg("$module: Зашифровали MasterKey ДОМпаролем в resources для UserID=$user_id", $dbg, 'blue'); ###
											DelLine('queries4reencrypt', $dom_line_id);		#Удаляем запрос на зашифровку MasterKey пароля
										}#end if
									}#end if res_type_id==9
									
									SetBusyFlagBy($dom_line_id, 0);#Отпускаем строку заявки обратно В ЛЮБОМ СЛУЧАЕ
								}#end while
								
							}#end if Кончились запросы
							
							
							
							
							
							if ($dbg) {echo "<br>\r\n";}###
						}#end while row_usrs    # Перебираем IDшники юзеров
						
					}#end else   # masterkey НАЙДЕН и РАСШИФРОВАН
					
				}#end if is_admin
				
				
				
			}#end else NOT empty old_encr_user_pass
			
			
			
			
			
			
			
			msg("$module: Тут типа переходим на index.php", $dbg, 'lime');
			msg("$module: sess_login=".$_SESSION['login'], $dbg, 'green');
			msg("$module: sess_pass=".$_SESSION['pass'], $dbg, 'blue');
			msg("$module: sess_auth=".$_SESSION['auth'], $dbg, 'magenta');
			
			#if (!$dbg) {echo "<script>document.location.href='index.php';</script><noscript><a href=index.php>Вход выполнен, нажмите для продолжения работы</a></noscript>";}
			
			$mess='Вы успешно вошли в систему'; $color='lime';
			
			#exit;#Второй раз форму выводить не нужно
		}#end else NOT empty $bind_ok
		
	}#end if NOT empty($pass) # Пароль НЕ ПУСТОЙ
	
	
	
	
	
	
	
	
	
	
	msg('END', $dbg, 'lime');###
	
	message($mess, $color);
	echo "<a href=index.php>Вы будете переадресованы через <b><span id='time'>$timeout</span></b> секунд, нажмите СЮДА, если не хотите ждать</a>\r\n";
	echo "<script type='text/javascript'>
	var i = $timeout;//время в сек.
	function time(){
		document.getElementById('time').innerHTML = i;//визуальный счетчик
		i--;//уменьшение счетчика
		//if (i < 0) location.href = 'http://javascript.ru';//редирект
	}
	time();
	setInterval(time, 1000);
	</script>";
	echo "</div></TD></TR></TABLE></BODY></HTML>";
	
?>