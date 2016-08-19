<?php
	$login_len=30; # Длина логина, ширина поля в таблице RESOURCES varchar(50)
	$pass_len=30; # Длина пароля, ширина поля в таблице RESOURCES varchar(MAX)
	
	$enable_multi_acc=FALSE; # Разрешение множественных аккаунтов для одного и того же ресурса
	
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$dbg=FALSE; $module='user_pass'; $err='';
	
	#Показать логины/пароли юзера от рег.биллингов
	# ГОТОВО - Сделать инлайн редактор как в Edit_Table
	# ГОТОВО - Пароль показывать при клике - меняться на input type=text #В password нельзя записать ничего
	#          Сделать подгрузку пароля только при наведении/клике, чтобы он не хранился в коде страницы вообще
	
	
	
	if ( !isset($_SESSION['auth']) ) { # Попытка обхода авторизации
		$redirect='login.php';
		echo "<div style='width:20%; height:20%;' id='login' class='area'></div>\r\n";
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'red');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;# Обязательно выходим !!!!!!!!
	}#end if
	
	
	$login = '';
	if (isset($_SESSION['login']) ) {	$login = cut_domain($_SESSION['login']); #Получаем логин юзера
	} else { alert("$module: Login NOT DEFINED !!!"); exit; } # Но если он пустой, то матюгаемся и валим
	
	$domain_user_pass='';
	if (isset($_SESSION['pass']) ) { $domain_user_pass=$_SESSION['pass']; }# Если юзер залогинился - значит и пароль у него есть
	if (empty($domain_user_pass) ) { alert("$module: Password NOT DEFINED !!!"); exit; } # Но если он пустой, то матюгаемся и валим
	
	if ('AS.LOGINOV'===strtoupper($login)) {$dbg=TRUE;} ###
	
	if (isset($_SESSION['masterkey'])) {$masterkey=$_SESSION['masterkey']; msg("mk set", $dbg, 'brown');} else {msg("mk NOT set", $dbg);} # Получим masterkey
	
	session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку
	
	
	
	
	
	
	msg('START', $dbg);
	msg("login=$login", $dbg, 'blue');
	msg("pass=$domain_user_pass", $dbg, 'magenta');
	
	
	$user_ID=get_userID_by($login); # Ищем IDшник юзера по логину
	if (empty($user_ID)) {alert("$module: User '$login' NOT FOUND"); exit;} #Логин не найден - дальше нечего ловить, упадаем
	msg("user_ID='$user_ID'", $dbg, 'green'); ###
	
	
	#Тут мы задаём русские названия для столбцов таблицы
	# уровень редактирования, тип столбца таблицы для представления данных и название поля в таблице
	# Уровень редактирования:       0-не показывать, 1-просмотр, 2-редактирование
	# Тип поля для редактирования:  Integer, String, Date, Binary, LookUp_listbox
	# Cокращённые названия полей нужны, чтобы не светить реальные, да и REQUEST покороче будет
	$table='resources'; $order_by='res_type_id'; $tbl_title='Справочник паролей';
	$list_tbl_name='res_type'; $list_field='res_name'; $list_sort_field='sort_order';	#TableName и FieldName списка для выбора
	$fields=array('id'=>array('ID',							'0','I','id'					),
								'ui'=>array('ID юзера',				'0','I','user_id'			),
								'rt'=>array('Тип ресурса',		'1','L','res_type_id'	),
								'lg'=>array('Логин',					'2','S','login'				),
								'ps'=>array('Пароль',					'2','S','pass'				),
								'cm'=>array('Комментарий',		'2','S','comment'			)
							);
	
	
	
	
	# Если прилетел запрос на удаление строки по её IDшнику
	if (isset($_REQUEST['del'])) {
		$del=intval($_REQUEST['del']);#обшкуриваем до целого
		if ($dbg) {show_var($del, 'del');}###
		
		if (!empty($del)) {
			$bind_del=array( array(':user_id', $user_ID), array(':id', $del) );
			$STH_del=search($table,'user_id=:user_id AND id=:id','',$bind_del); # Юзер удаляет свой пароль ?
			if ($row_del = $STH_del->fetch()) {
					DelLine($table, $del);				# Если свой - то удаляем
					msg("del='$del'", $dbg, lime);###
					$err.="Строка $del удалена";
			}#end if
		}#end if NOT empty($del)
		
	}#end if del
	
	
	
	$set='';
	if (isset($_REQUEST['set']) ) {
		$set=substr(get_letters($_REQUEST['set']), 0, 2); # Получим 2 буквы сокр. названия поля
		msg("set=$set", $dbg, 'aqua'); ###
		
		if ( isset($_REQUEST['line']) ) { # Если прилетел set, считываем остальные параметры
			$line=get_int_val( substr($_REQUEST['line'], 0, 10) );
			msg("line=$line", $dbg, 'blue'); ###
			
			if ( isset($_REQUEST['val']) ) {
				$new_val=$_REQUEST['val'];
				msg("new_val=$new_val", $dbg, 'blue'); ###
			} else { alert("$module: Значение поля не задано"); msg("$module: Значение поля не задано", $dbg); exit; }
		} else { alert("$module: Line_ID не задан"); msg("$module: Line_ID не задан", $dbg); exit; }
	}#end if $set
	
	
	
	
	
	
	############################		Готовим список выбора
	# Может тоже засунуть $sel_list и $arr_res_type в сессию для ускорения работы
	$arr_res_type=array();
	$sel_list='<select id=res_type size=1>';
	$sel_list.="<option value=\"0\" disabled>[  Выберите ресурс  ]</option>";
	$STH_ch=search($list_tbl_name, '(id<>8) AND (id<>9)', $list_sort_field); # 
	while ($row_ch = $STH_ch->fetch()) {
		$ID=$row_ch[0]; $name=trim($row_ch["$list_field"]);
		$arr_res_type[$ID]=$name;
		$sel_list.="<option value=\"$ID\">$ID $name</option>";
	}
	$sel_list.='</select>';
	#if ($dbg) { show_var($arr_res_type, 'arr_res_type'); }###
	
	
	#####################################################
	#   Тут добавим шаблоны полей для редактирования    #
	#####################################################
	# Может засунуть шаблоны полей в сессию для ускорения работы ?
	#######  Готовим шаблон для поля Тип ресурса
	$tpl_res_type_id=''; $sel_list_click=$sel_list; #  Делаем копию
	$onchange=" onchange='pg(\"user_pass.php\", \"rt[res_id]\", \"set=rt&line=[res_id]&val=\"+encodeURIComponent(this.value));'"; # Готовим событие onChange
	$sel_list_click = str_replace('size=1', "size=1 $onchange", $sel_list_click);# Прицепляем скрипт реакции на событие
	$tpl_res_type_id="<TD id='rt[res_id]'>$sel_list_click</TD>";
	
	#######  Готовим шаблон для поля логина
	$tpl_login=''; # $res_id=[res_id] $reg_login=[value]
	$onchange=" onchange='pg(\"user_pass.php\", \"lg[res_id]\", \"set=lg&line=[res_id]&val=\"+encodeURIComponent(this.value));'"; # Готовим событие onChange
	$tpl_login.="<TD id='lg[res_id]'><input type=text value='[value]' $onchange></TD>";
	
	#######  Готовим шаблон для поля пароля   # Тут делаем чтобы "Щёлкни мышкой" заменялся на поле ввода с текстом пароля при щелчке мышкой
	$tpl_pass='';                             #  и пропадал после onBlur - потери фокуса    а по onChange - пароль записывался в базу
	$onBlur="onBlur='document.getElementById(\"dv_ps[res_id]\").style.display=\"none\";
						document.getElementById(\"dv_aim[res_id]\").style.display=\"block\";'";
	$onChange="onChange='req=\"set=ps&line=[res_id]&val=\"+encodeURIComponent(this.value); pg(\"user_pass.php\", \"td_ps[res_id]\", req);'";
	$aim_cur="<div id='dv_aim[res_id]' onClick='this.style.display=\"none\";
						document.getElementById(\"dv_ps[res_id]\").style.display=\"block\";
						document.getElementById(\"it_ps[res_id]\").focus();' align=center>Щёлкни мышкой</div>
						<div id='dv_ps[res_id]' style='display:none;'><input type=text id='it_ps[res_id]' value='[value]' $onChange $onBlur></div>";
	$tpl_pass="<TD id='td_ps[res_id]'>$aim_cur</TD>";
	
	#######  Готовим шаблон для поля комментария
	$tpl_comment='';
	$onchange=" onchange='pg(\"user_pass.php\", \"cm[res_id]\", \"set=cm&line=[res_id]&val=\"+encodeURIComponent(this.value));'"; # Готовим событие onChange
	$onkeydown="onKeyDown='val=this.value; this.size=val.length+3;'";
	$tpl_comment="<TD id='cm[res_id]'><input type=text style='width:100%;' size='[size]' value='[value]' $onchange $onkeydown></TD></TD>";
	
	
	
	
	
	
	
	
	
	
	### Запись в базу отдельного поля
	if ( !empty($set) ) {  # set=rt&line=$res_id&val=123
		$mes=''; $field=''; $field_name='';
		msg("set=$set", $dbg, 'magenta'); ###
		
		if ( array_key_exists($set, $fields) ) {
			$field_name=$fields[$set][0];	# Получим русское название поля
			msg("field_name=$field_name", $dbg, 'lime'); ###
			$field=$fields[$set][3];			# Расшифруем сокр. название поля
			msg("field=$field", $dbg, 'green'); ###
		} else {
			$err.="Поле '$set' не найдено";
			alert($err); message($err); exit;
		}
		
		if (empty($line) ) {
		  $err.="LineID пустое"; $line=0;
		} else {
			msg("Search for Line_ID=$line", $dbg); ###
			
			# ТУТ НАДО ПРОВЕРИТЬ, СВОЮ ЛИ ЗАПИСЬ МЕНЯЕТ ЮЗЕР
			$bind_srch=array( array(':id', $line), array(':user_id', $user_ID) );
			$STH_srch=search($table, 'id=:id AND user_id=:user_id', '', $bind_srch ); # Есть строка с таким Line_ID и User_ID в resources ?
			$row_srch = $STH_srch->fetch();
			if ( empty($row_srch) ) {
				$err.="Строка с ID=$line не найдена";
				msg("Строка с ID=$line не найдена", $dbg); ###
			} else {
				$old_val=$row_srch["$field"];
				msg("Get old_val='$old_val' for '$field' at line='$line'", $dbg, 'blue'); ###
				if ('ps'===$set) { # Пароль надо сначала расшифровать
					$old_val=DecryptByPassPhrase($old_val, $domain_user_pass);
					msg("Pass decrypted='$old_val'", $dbg, 'green'); ###
				}#end if
			}#end if
			
			
			
			
			#if ( empty($new_val) ) {
			#	$err.="Поле \'$field_name\' не может быть пустым"; $val=$old_val;
			#	msg("Поле '$field_name' не может быть пустым", $dbg); ###
			#} else {
				$val=$new_val;
				msg("new_val='$new_val'", $dbg, 'green'); ###
			#}#end if
			
			
			if ( in_array($set, array('rt', 'lg', 'ps')) AND empty($new_val) ) {
				$err.="Поле '$field_name' пустое"; $val=stripslashes($old_val); # Вертаем взад старое значение поля
			}
			
			switch ($set) {
				case 'rt':	$val=get_int_val(substr($val, 0, 2));
										if ( array_key_exists($val, $arr_res_type) ) {# Есть такой res_type_id ?
											$mes = str_replace('[res_id]', $line, $tpl_res_type_id);#Меняем шаблон
											$mes = str_replace("value=\"$val\"", "value=\"$val\" selected", $mes);# Делаем выбранным пункт списка
										} else { $err.="Код '$field_name'='$val' не найден"; }
										break;
										
				case 'lg':	$login=$val; #Сохраняем исходный вид для вывода на страницу
										$val=make_harmless( filter_aZ09( substr($val, 0, $login_len) ) );
										$mes = str_replace('[res_id]', $line, $tpl_login);#Меняем шаблон
										$mes = str_replace('[value]', $login, $mes);
										break;
										
				case 'ps':	$pass=$val; #Сохраняем исходный вид для вывода на страницу
										$harmless_pass=make_harmless($pass); #Обшкурим и заэкранируем HTML код, иначе можно делать так <script>alert(document.cookie);</script>
										$harmless_pass=mb_substr($harmless_pass, 0, $pass_len, 'UTF-8'); # Пароль отпилим
										$val=EncryptByPassPhrase($domain_user_pass, $harmless_pass); # Если пароль, то зашифруем
										$mes = str_replace('[res_id]', $line, $tpl_pass);#Меняем шаблон
										$mes = str_replace('[value]', $pass, $mes); #Выводим нешифрованный пароль
										break;
										
				case 'cm':	$comment=$val; #Сохраняем исходный вид для вывода на страницу
										$val=make_harmless($val);
										$mes = str_replace('[res_id]', $line, $tpl_comment);
										$mes = str_replace('[value]', $comment, $mes);
										$mes = str_replace('[size]', strlen($comment), $mes);
										break;
			}#end switch
			
			#show_var($err, 'err');###
			
			if (empty($err)) {# Если ошибок нет, то обновляем поле
				$bind_upd=array( array(':id', $line), array(":$field", $val), array(':user_id', $user_ID) ); # Юзер только свои пароли может менять
				$STH_upd=update($table, $field, 'id=:id AND user_id=:user_id', $bind_upd); # Запишем поле в базу
			} else { alert($err); } #message($err); } #Выведем сообщение об ошибке
			
			echo "$mes"; # А тут из шаблона выведем поле с новым значением
			exit;
		}#end else NOT empty($line)
		
	}#end else NOT empty($set)
	
	
	
	
	
	
	###  Добавление новой строки
	if (isset($_REQUEST['rt'])) {# res_type нужно обшкурить до целого
		$rt=intval(substr($_REQUEST['rt'], 0, 2));#Берём только две цифры кода типа ресурса и обшкуриваем до целого
		if (empty($rt)) {
			$err.="Не выбран тип ресурса !!!\r\n"; # Если прислали фигню, матюгаемся и ничего не делаем
		} else {
			msg("new_res_type='$rt'", $dbg, lime);###
			
			# Надо проверить, есть такой IDшник в res_type, чтобы нельзя было добавить несуществующий ресурс
			if ( array_key_exists($rt, $arr_res_type) ) {
			
			
				if (isset($_REQUEST['lg'])) {
					$lg=substr(filter_aZ09($_REQUEST['lg']), 0, $login_len); # Логин отпилим # Вырезать всё, кроме латинских букв, цифр, . - _ и \
					$lg=make_harmless($lg); # Обшкурим и заэкранируем \ #$lg=htmlentities(addslashes($lg), ENT_QUOTES, "UTF-8");
					msg("new_login='$lg'", $dbg, lime);###
					
					if (empty($lg)) {
						$err.="Не задан логин !!!\r\n"; # Если логин пустой, матюгаемся и ничего не делаем
					} else {
						if (isset($_REQUEST['ps'])) { #Пароль надо зашифровать перед записью в базу
							$harmless_pass=make_harmless($_REQUEST['ps']); #Обшкурим и заэкранируем HTML код, иначе можно делать так <script>alert(document.cookie);</script>
							$ps=mb_substr($harmless_pass, 0, $pass_len, 'UTF-8'); # Пароль отпилим
							if ( empty($ps) ) { $err.="Не задан пароль !!!\r\n"; # Если пароль пустой, матюгаемся и ничего не делаем
							} else {
								msg("pass='$ps'", $dbg, 'lime');###
								$ps=EncryptByPassPhrase($domain_user_pass, $ps); #Шифровать ЧЕМ ЧТО
								msg("new_pass='$ps'", $dbg, lime);###
								
								if (isset($_REQUEST['rem'])) {
									$rem=make_harmless($_REQUEST['rem']); #Обшкурим и заэкранируем \
									msg("new_rem='$rem'", $dbg, lime);###
								}#end if
								
								#Сначала проверим, есть строка с такими user_ID и res_type_id в resources
								$bind_srch=array( array(':user_id', $user_ID), array(':res_type_id', $rt) );
								if ($dbg) {show_var($bind_srch, 'bind_srch');}###
								$STH_srch=search($table, 'user_id=:user_id AND res_type_id=:res_type_id', '', $bind_srch);
								if ($dbg) {show_var($STH_srch, 'STH_srch');}###
								$row_srch = $STH_srch->fetch();
								if ($dbg) {show_var($row_srch, 'row_srch');}###
								
								$make_insert=FALSE; # Флаг разрешения добавления записи
								if ( empty($row_srch) ) { #Если строки нету - добавим
									$make_insert=TRUE;
								} else {
									if ( $enable_multi_acc ) {	# Если такая строка есть и разрешены мультиаккаунты - добавим
										$make_insert=TRUE;
									} else {
										$err.="Такой ресурс уже есть\r\n"; # Лучше матюгаться, что такая строка уже есть, обновлять будем отдельные поля
									}#end else
								}
								
								if ( $make_insert ) { #Если добавление разрешено - добавим
									$bind=array( array(':user_id', $user_ID), array(':res_type_id', $rt), array(':login', $lg), array(':pass', $ps), array(':comment', $rem) );
									$STH_ins=insert($table, 'user_id,res_type_id,login,pass,comment', $bind);
									if ($dbg) {show_var($STH_ins, 'STH_ins');}###
									if ($STH_ins) {
										msg("Line inserted", $dbg, blue); $err.="Строка добавлена\r\n";
									} else {
										$err.="Не получилось добавить строку\r\n";
									}#end if
								}
								
							}#end else
							
							$ps=make_harmless($ps); #Обшкурим и заэкранируем HTML код, иначе можно делать так <script>alert(document.cookie);</script>
							
						}#end if ps
					}#end else NOT empty($lg)
					
				}#end if isset($_REQUEST['lg'])
				
				
				
			}#end if array_key_exists
			
			
		}#end else NOT empty(rt)
	}#end if rt
	
	
	
	
	
	
	if (is_admin($login)) {
		msg("is_admin=TRUE", $dbg, 'lime');
		
# Эту ветку нужно будет упразднить, чтобы супервизор не мог увидеть пароли юзера
/*	if (isset($_REQUEST['lg'])) { #Получаем присланный логин юзера
			$user_login=$_REQUEST['lg'];
			msg("user_login=$user_login", $dbg, lime);
			$login=$user_login; # Показывать будем данные по этому логину
			
			# Тут надо получить доменный пароль этого пользователя для расшифровки его региональных паролей
			#$uniq_id = Get_uniq_id_by(get_userID_by($login));#<-- Так будет два запроса к базе
			
			#$bind=array(':login', strtoupper($login));
			#$STH=search('users', 'UPPER(login)=:login', '', $bind);
			#$row = $STH->fetch();
			#$uniq_id=$row['uniq_id'];
			#msg("uniq_id=$uniq_id", $dbg, green);
			
			$uniq_id = Get_uniq_id_by_login($login);
			
			$domain_user_pass=DecryptByPassPhrase($uniq_id, $masterkey);
			msg("domain_user_pass=$domain_user_pass", $dbg, blue);
			
		}#end if isset
*/
	}#end if $is_admin
	
	
	
	
	echo "<div id='result'>\r\n";
	
	
	
	if (!empty($err) ) { alert("$module: $err"); message($err); } #Выведем сообщение об ошибке
	
	#Получим названия полей таблицы для редактирования и для записи
	$STH=search($table); # Можно оптимизировать запрос чтобы он только первую строку возвращал SELECT TOP(1)
	$colcount = $STH->columnCount()-1;
	if (empty($STH) ) {
		message("$module: Ошибка получения полей таблицы '$tbl_title'"); exit;
	} else {
		if ($row=$STH->fetch(PDO::FETCH_ASSOC)) {
			$arr_fields=array_keys($row);
			#if ($dbg) { show_var($arr_fields, 'arr_fields'); }###
		} else { message("$module: Таблица '$tbl_title' пустая"); exit;}
	}#end else
	
	##########################################################################
	#    Выводим шапку таблицы с русскими заголовками столбцов
	##########################################################################
	echo "<TABLE border=1><TBODY>";
	echo "<caption><b>Ваши пароли для доступа к ресурсам</b></caption>\r\n";
	echo "<TR>"; $i=0; #Счётчик для имён полей таблицы $arr_fields
	foreach($fields as $key=>$value) {
		#show_var($value, "$key");
		$field_caption=$value[0];
		$edit_level=$value[1];
		$val_type=$value[2];
		if ('0'!==$edit_level) {echo "<TH>$field_caption</TH>";} #Если показ НЕ запрещён - выводим заголовок столбца
	}#end foreach
	
	
	echo "<TH>+/-</TH>";
	echo "</TR>\r\n";
	
	
	
	
	
	
	$sel_list_click=$sel_list;# Делаем копию списка для вывода
	$sel_list_click=str_replace('value="0"', 'value="0" selected', $sel_list_click);# Делаем выбранным "[  Выберите ресурс  ]"
	
	# Готовим пустую строку для добавления
	$add_btn="<img src='./img/check.png' width=16px style='height:16px;' border=0 alt=='Добавить' title='Добавить'>";
	$edit_line ="<td>$sel_list_click</td><td><input type=text name=login id=login></td>";
	$edit_line.="<td><input type=password name=pass id=pass></td>";
	$edit_line.="<td><input type=text class='inp' name=comment id=comment></td>";
	$edit_line.="<td align='center'><a href='#' onClick='";
	$edit_line.="rt=encodeURIComponent(document.getElementById(\"res_type\").value); ";
	$edit_line.="lg=encodeURIComponent(document.getElementById(\"login\").value); ";
	$edit_line.="ps=encodeURIComponent(document.getElementById(\"pass\").value); ";
	$edit_line.="rem=encodeURIComponent(document.getElementById(\"comment\").value); ";
	$edit_line.="req=\"lg=\"+lg+\"&ps=\"+ps+\"&rt=\"+rt+\"&rem=\"+rem; ";
	$edit_line.="pg(\"user_pass.php\", \"result\", req );'>$add_btn</td>";
	echo "<TR>$edit_line</TR>\r\n";
	
	
	
	
	
	
	
	$del_sign="<img src='./img/cross.png' width=16px style='height:16px;' border=0 alt='Удалить' title='Удалить'>"; #Значок удаления
	$bind=array(':user_id', $user_ID); #Выводим все ресурсы юзера и пароли к ним
	$STH_res=search($table, 'user_id=:user_id AND res_type_id<>9', $order_by, $bind); #Кроме MasterKey пароля
	#$STH_res=search($table, 'user_id=:user_id', $order_by, $bind); #Все ресурсы для теста
	while ($row_res=$STH_res->fetch(PDO::FETCH_ASSOC)) {
		#if ($dbg) {show_var($row_res, 'row_res'); }###
		$res_id=$row_res['id'];
		#$user_id=$row_res['user_id'];
		$res_type_id=$row_res['res_type_id'];
		$res_type_name=$arr_res_type[$res_type_id];
		$reg_login=trim(stripslashes($row_res['login'])); #Вдруг в логине есть бэкслэш \
		$reg_pass=$row_res['pass']; #Никаких stripslashes к зашифрованному паролю не применять !!!
		$comment=trim(stripslashes($row_res['comment'])); if (empty($comment) ) {$comment='&nbsp;';}
		$pass_msg='PASS NOT SET';
		if (!empty($reg_pass)) {$decr_pass=DecryptByPassPhrase($reg_pass, $domain_user_pass); $pass_msg=remove_slashes($decr_pass);} #Пытаемся расшифровать
		if (empty($decr_pass)) { # Если не можем расшифровать РЕГпароль
			$pass_msg='НЕ РАСШИФРОВАН';
			CreateQuery4Reencrypt($user_ID, $res_type_id, $domain_user_pass);# Создаём запрос на перешифровку РЕГпароля
		}#end if
		
		$line='<TR>'; $read_tpl='<TD>[value]</TD>';
		$access=$fields['rt'][1];
		if ('2'===$access) { #######  Готовим поле списка Тип ресурса
			$mes = str_replace('[res_id]', $res_id, $tpl_res_type_id);#Меняем шаблон
			$mes = str_replace("value=\"$res_type_id\"", "value=\"$res_type_id\" selected", $mes);# Делаем выбранным пункт списка
		}
		if ('1'===$access) { $mes=str_replace('[value]', $arr_res_type[$res_type_id], $read_tpl); } #Меняем шаблон
		$line.=$mes;
		
		$access=$fields['lg'][1];
		if ('2'===$access) { #######  Готовим поле логина из шаблона
			$mes = str_replace('[res_id]', $res_id, $tpl_login);#Меняем шаблон
			$mes = str_replace('[value]', $reg_login, $mes);
		}
		if ('1'===$access) { $mes=str_replace('[value]', $reg_login, $read_tpl); } #Меняем шаблон
		$line.=$mes;
		
		$access=$fields['ps'][1];
		if ('2'===$access) { #######  Готовим поле пароля
			$mes = str_replace('[res_id]', $res_id, $tpl_pass);#Меняем шаблон
			$mes = str_replace('[value]', $pass_msg, $mes);
		}
		if ('1'===$access) { $mes=str_replace('[value]', $pass_msg, $read_tpl); } #Меняем шаблон
		$line.=$mes;
		
		$access=$fields['cm'][1];
		if ('2'===$access) { #######  Готовим поле комментария
			$mes = str_replace('[res_id]', $res_id, $tpl_comment);#Меняем шаблон
			$mes = str_replace('[value]', $comment, $mes);
			$mes = str_replace('[size]', strlen($comment), $mes);
		}
		if ('1'===$access) { $mes=str_replace('[value]', $comment, $read_tpl); } #Меняем шаблон
		$line.=$mes;
		
		#######  Добавим кнопку удаления
		$cmd="if (confirm(\"Удалить строку ?\") ) {pg(\"user_pass.php\", \"result\", \"del=$res_id\");};";
		$line.="<TD align=center><a href='#' onClick='$cmd'>$del_sign</a></TD></TR>\r\n";
		
		echo $line;
	}#end while
	echo "</TBODY></TABLE>\r\n";
	
	
	
	
	
	
	
	
	$field_res_type=bold($fields['rt'][0]);
	$login=bold($fields['lg'][0]);
	echo "<p>Запишите сюда свои логины/пароли для доступа к ресурсам.<br>
	<p>Поле $login<br>
	Здесь можно использовать латинские буквы a-z, A-Z, цифры 0-9 и символы  _ . - \ @<br>
	Все остальные символы удаляются.<br>
	<p><b>Добавление новой строки</b><br>
	В первой строке в столбце '$field_res_type' в раскрывающемся списке выберите биллинг,<br>
	дальше введите логин, затем пароль (он не будет виден, только точки или звёздочки)<br>
	и, потом, если нужно, введите комментарий, и в конце строки нажмите кнопку $add_btn Добавить.<br>";
	
	$str_en_mul_acc=bold(color('Запрещено', 'red')); if ($enable_multi_acc) { $str_en_mul_acc='<u>Разрешено</u>'; }
	echo "$str_en_mul_acc добавление нескольких учётных записей для одного ресурса<br>";
	
	echo "<p><b>Удаление строки</b><br>
	Чтобы удалить строку нужно нажать красный крестик '$del_sign' в конце строки.<br>
	<p><b>Редактирование</b><br>
	Чтобы поправить текст, просто впишите новые данные в поле и нажмите <b>Enter</b> на клавиатуре<br>
	или перейдите на другое поле - текст сразу запишется в таблицу.<br>
	<br>
	Если нужного вам ресурса нет в списке, выберите любой ресурс, начинающийся с <b>TEST</b>,<br>
	добавьте свои логин, пароль и обязательно комментарий с названием ресурса,<br>
	затем напишите в Спарк инженеру техподдержки, какой ресурс нужно переименовать.<br>
	<br>";
	message("После двух неудачных попыток ввода логина/пароля, обратитесь к инженерам техподдержки за помощью");
	
	
	msg('END', $dbg, lime); ###
	
	echo "</div>";
	
	
	
	
?>