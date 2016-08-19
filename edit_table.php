<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	# Модуль редактирования таблиц
	# Сюда будет прилетать СОКРАЩЁННОЕ название таблички
	# Будем делать запрос, смотреть
	$dbg=FALSE; $module='edit_table'; $err='';
	if ($dbg) {# Если режим отладки
		ini_set('display_errors','On'); #включаем отображение ошибок если выключено
		error_reporting(E_ALL | E_STRICT); #устанавливаем режим отображения - все ошибки и советы
	}#end if
	################################################################################################################
	#  ГОТОВО - Сделать чтобы по onChange данные сохранялись сразу в поле таблички
	#  ГОТОВО - Сделать чтобы после сохранения поля оно выводилось с новыми данными
	#  ГОТОВО - Cделать возможность добавления новой строки, как в user_pass
	#           Сделать постраничный вывод таблицы с ограничением количества строк на странице
	#           
	#           Подумать как сделать автоматическое получение типов и названий полей и также Lookup полей
	#           Можно сделать чтобы при клике по ячейке таблицы (onClick повесить на все ячейки, а TDшкам присвоить ID)
	#							появлялось поле для редактирования input type=text(date, number, checkbox)
	#             А затем по событию onchange записывалось и опять становилось обычным текстом с событием onclick
	################################################################################################################
	
	
	
	if ( !isset($_SESSION['auth']) ) { # Попытка обхода авторизации
		$redirect='login.php';
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'lime');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;# Обязательно выходим !!!!!!!!
	}#end if
	
	
	if (isset($_SESSION['login'])) {$login = $_SESSION['login'];} else {message("$module: Login NOT DEFINED !!!"); exit;}#Получаем логин супервизора
	if (!is_admin($login)) {message("$module: You DON'T HAVE sufficient privilegies to view this page !!!"); exit;}
	
	if (isset($_SESSION['pass'])) {$pass = $_SESSION['pass'];} else {message("$module: Pass NOT DEFINED !!!"); exit;}#Получаем пароль супервизора
	session_write_close();# Прочитали/записали из/в сессии - снимаем блокировку записи для ускорения работы скрипта
	
	


	
	
	
	
	if (!isset($_REQUEST['tbl']) ) { msg("$module: Название таблицы не задано", $dbg); exit; } #Дальше нечего ловить
	$tbl=substr($_REQUEST['tbl'], 0, 4); # Прилетело название таблицы
	msg("$module: tbl=$tbl", $dbg);###
	
	#Тут мы в зависимости от сокращённого названия таблички задаём русские названия для столбцов
	# уровень редактирования и тип столбца таблицы для представления данных
	# Уровень редактирования 0-не показывать, 1-просмотр, 2-редактирование
	# Тип поля для редактирования Integer, String, Date, Binary, LookUp listbox
	$allow_add_line=FALSE;		# Разрешить добавление новой строки
	$allow_delete_line=FALSE;	# Разрешить УДАЛЕНИЕ строки
	switch  ($tbl) { 
		case 'usrs':	$table='users'; $title='Пользователи из базы';
									$order='id';
									$fields=array('id'=>array('ID',							'1','I','id'),
																'lg'=>array('Логин',					'1','S','login'),
																'cn'=>array('ФИО',						'1','S','cn'),
																'ma'=>array('e-mail',					'1','S','mail'),
																'ti'=>array('Должность',			'1','S','title'),
																'dp'=>array('Отдел',					'0','S','department'),
																'co'=>array('Компания',				'0','S','company'),
																'on'=>array('Офис',						'0','S','physicaldeliveryofficename'),
																'ph'=>array('Телефон',				'0','S','telephonenumber'),
																'mb'=>array('Сотовый',				'0','S','mobile'),
																'ct'=>array('Дата создания',	'1','D','create_time'),
																'ut'=>array('Дата обновления','1','D','update_time'),
																'en'=>array('Разрешен',				'2','B','enabled'),
																'su'=>array('Супервизор',			'2','B','supervisior'),
																'ad'=>array('Админ',					'2','B','admin'),
																'un'=>array('UniqID',					'0','S','uniq_id')       # Редактировать пароль НИЗЗЯ, потом не расшифруется
																);
									break;
									
		case 'kusr':	$table='kit_users'; $title='СТАРАЯ ТАБЛИЦА Пользователи';
									$order='id';
									$fields=array('id'=>array('ID',							'1','I','id'),
																'lg'=>array('Логин',					'1','S','login'),
																'cn'=>array('ФИО',						'1','S','cn'),
																'ma'=>array('e-mail',					'1','S','mail'),
																'ti'=>array('Должность',			'1','S','title'),
																'dp'=>array('Отдел',					'0','S','department'),
																'co'=>array('Компания',				'0','S','company'),
																'on'=>array('Офис',						'0','S','physicaldeliveryofficename'),
																'ph'=>array('Телефон',				'0','S','telephonenumber'),
																'mb'=>array('Сотовый',				'0','S','mobile'),
																'ct'=>array('Дата создания',	'0','D','create_time'),
																'ut'=>array('Дата обновления','0','D','update_time'),
																'en'=>array('Разрешен',				'2','B','enabled'),
																'su'=>array('Супервизор',			'2','B','supervisior'),
																'un'=>array('UniqID',					'0','S','uniq_id')       # Редактировать пароль НИЗЗЯ, потом не расшифруется
																);
									break;
									
		case 'q4rn':	$table='queries4reencrypt'; $title='Запросы на перешифровку паролей';
									$order='user_id'; $allow_delete_line=TRUE;
									$fields=array('id'=>array('ID',								'1','I','id'),
																'ui'=>array('ID юзера',					'1','S','user_id'),
																'rt'=>array('ID типа ресурса',	'1','L','res_type_id','res_type','res_name'),	#Таблица и поле списка для выбора
																'np'=>array('Новый пароль',			'1','S','new_pass'),
																'bs'=>array('Строка занята',		'1','B','busy')
																);
									break;
									
		case 'rgac':	$table='reg_access'; $title='Результаты запросов доступа к региональным биллингам';
									$order='user_id,region_id'; $allow_delete_line=TRUE;
									$fields=array('id'=>array('ID',						'1','I','id'),
																'ui'=>array('ID юзера',			'1','L','user_id', 'users','login'),	#Таблица и поле списка для выбора
																'ri'=>array('ID региона',		'1','L','region_id', 'regions','name'),								#Хорошо бы сделать ещё один LookUp
																're'=>array('Результат',		'2','I','result'),
																'rd'=>array('Дата запроса',	'1','D','req_date')
																);
									break;
									
		case 'rgad':	$table='reg_admins'; $title='Региональные администраторы';
									$order='region_id'; $allow_add_line=TRUE; $allow_delete_line=TRUE;
									$fields=array('id'=>array('ID',						'1','I','id'), ### Сбросить в 0
																'fi'=>array('ФИО',					'2','S','fio'),
																'em'=>array('e-mail',				'2','S','email'),
																'de'=>array('Доп. e-mail',	'2','S','email2'),
																'vk'=>array('В копию',			'2','S','v_kopiyu'),
																'ri'=>array('Регион',				'2','L','region_id', 'regions','name')#Таблица и поле списка для выбора
																);
									break;
									
		case 'regs':	$table='regions'; $title='Справочник регионов';
									$order='id'; $allow_add_line=TRUE;
									$fields=array('en'=>array('Показывать',						'2','B','enable'),
																'id'=>array('ID',										'1','I','ID'),
																'nm'=>array('Регион',								'1','S','name'),
																'rp'=>array('Регион в Род.Падеже',	'1','S','name_rp'),
																'ur'=>array('URL проверки',					'1','S','url'),
																'at'=>array('Тип авторизации',			'1','S','auth_type'),
																'tp'=>array('Файл шаблона',					'1','S','tpl'),
																);
									break;
									
		case 'rtyp':	$table='res_type'; $title='Справочник типов ресурсов';
									$order='id'; $allow_add_line=TRUE;
									$fields=array('id'=>array('ID',					'2','I','id'),
																'so'=>array('Сортировка',	'2','I','sort_order'),
																'rs'=>array('Ресурс',			'2','S','res_name'),
																'rg'=>array('ID региона',	'2','L','reg_id', 'regions','name') #Хорошо бы тоже сделать LookUp
																);
									
									break;
									
		case 'rsrs':	$table='resources'; $title='Справочник ресурсов';
									$order='user_id,res_type_id'; $allow_delete_line=TRUE;
									$fields=array('id'=>array('ID',							'1','I','id'					),
																'ui'=>array('Юзер',						'1','L','user_id',     'users',    'login'	),	#Хорошо бы тоже сделать LookUp
																'rt'=>array('Тип ресурса',		'2','L','res_type_id', 'res_type', 'res_name'	),#Таблица и поле списка для выбора
																'lg'=>array('Логин',					'1','S','login'				),
																'ps'=>array('Пароль',					'0','S','pass'				), # Менять пароль НИЗЗЯ, потом не расшифруется
																'cm'=>array('Комментарий',		'2','S','comment'			)
															);
									break;
									
		default:			message("$module: Table '$tbl' not found"); exit;
	}#end switch
	
	
	/*
	# Надо придумать как подгружать script, менять в нём сокр.название таблицы,
	#  а потом подключать как функцию
	
	# Есть вариант такой:
	# http://www.softtime.ru/forum/read.php?id_forum=4&id_theme=70798
	#Постарайтесь УЯСНИТЬ, что ajax'ом вы ВСЕГДА получаете от сервера не "какой-то код" (PHP, JS, HTML и прочее),
	# а всего лишь некую СТРОКУ (набор символов).
	# Этот получаемый "набор символов" с помощью JS надо ПРЕВРАТИТЬ во что-то.
	#
	#Например, в HTML-код - надо полученный "набор символов" записать как innerHTML какого-то тега страницы.
	#
	#В случае JS-кода - надо на каком-то теге страницы создать объект <SCRIPT> и ВОВНУТРЬ этого объекта
	# прописать этот самый "набор символов" как код скрипта. Вот только тогда браузер сможет понять,
	# что "набор символов" - это исполняемый JS-код.
	// Пример:
	// <html> 
	// <head> 
	// <script> 
	// function myFunc () {
	// положим, получили от сервера респонс-текст, содержащий JS-код: 
	// var x = "alert ('YES');\ndocument.body.style.backgroundColor = 'red';"; 

	// var y = document.createElement ('script'); //Создаёте новый тег <SCRIPT> 
	// y.defer = true; //Даёте разрешение на исполнение скрипта после его "приживления" на странице 
	// y.text = x; //Записываете полученный от сервера "набор символов" как JS-код 
	// document.body.appendChild (y); //"Приживляете" тег <SCRIPT> 
	// } 
	// </script> 
	// </head> 
	// <body> 
	// <hr><u align="center" onclick="myFunc ()">Запуск получения "кода" от сервера</u><hr> 
	// </body> 
	// </html>
	#
	#
	echo "
	<script>
	function send(set, line, val){
		alert('send set='+set+' line='+line+' val='+val);
	}
	</script>
	";
	*/
	
	
	
	
	
	
	# Удалить строку из таблицы по её IDшнику
	if ( $allow_delete_line ) {
		$del=''; 
		
		if (isset($_REQUEST['del'])) { # Если есть запрос на удаление
			$del=intval($_REQUEST['del']);#обшкуриваем до целого
			if ($dbg) {show_var($del, 'del');}###
			
			if (!empty($del)) {
				$del_res=DelLine($table, $del);# Если не пустое - удаляем
				if (empty($del_res) ) {
					msg("$module: Строка '$del' НЕ удалена", $dbg);
				} else {
					msg("tbl='$tbl' Строка del='$del' удалена", $dbg, 'lime');###
				}#end if
			}#end if NOT empty($del)
			
		}
	}
	
	
	
	
	
	
	#Получим названия полей таблицы и признак autoincrement для редактирования и для записи
	# Хотя названия у нас уже есть в массиве,
	# мы потом будем проверять существование названия этого поля в таблице
	#$STH=search($table);
	$STH=query("SELECT sc.name, sc.colstat
							FROM syscolumns sc, sysobjects so
							WHERE so.Name = '$table'
							AND sc.id = so.id");
	if (empty($STH) ) {
		message("$module: Получение списка полей таблицы '$title' не удалось");
		exit;
	} else {
		if ($row=$STH->fetch()) { # PDO::FETCH_ASSOC
			$arr_fields=array();
			do {
				$fld_nam=$row[0];
				$arr_fields[$fld_nam]=$row[1]; # Название поля = colstat Признак автоинкрементного IDшника - записывать в это поле нельзя !!!
			} while ($row=$STH->fetch());
			
			#show_var($arr_fields, 'arr_fields'); ###
			
			#$arr_fields=array_keys($row);
		} else { message("$module: В таблице '$title' нет полей"); exit;}
	}#end else 
	#if ($dbg) {	show_var($arr_fields, 'arr_fields'); }###
	
	
	
	
	
	
	
	$set='';
	if (isset($_REQUEST['set']) ) {
		$set=substr(get_letters($_REQUEST['set']), 0, 2); # Получим 2 буквы сокр. названия поля
		
		if ( isset($_REQUEST['line']) ) { # Если прилетел set, считываем остальные параметры
			$line=get_int_val( substr($_REQUEST['line'], 0, 10) );
			
			if ( isset($_REQUEST['val']) ) {
				$new_val=$_REQUEST['val']; # Получим новое значение поля
			} else { alert("$module: Значение поля не задано"); exit; }
			
		} else { alert("$module: Line_ID не задан"); exit; }
	}#end if $set
	
	
	
	
	
	
	
	
	$script_name='edit_table.php';
	
	
	
	
	########################################################################
	#   Готовим для вывода шапку таблицы с русскими заголовками столбцов
	$arr_tpl_lookup_listbox=array(); # Массив шаблонов списков выбора
	$arr_lookup_fields=array(); # Массив полей списков выбора и их значений вида $arr[$field_name]=array('$ID'=>$name, ...)
	$req='req='; # Сюда будем собирать переменные параметров для передачи в запрос
	$edit_line=''; #Собираем пустую строку таблицы для добавления новой строки
	$onClick=''; # Сюда соберём событие нажатия на кнопку Добавить
	$amp_sign=''; # Сюда потом запихнём знак AND
	$plus_sign=''; # Сюда потом запихнём знак +
	
	#    Выводим шапку таблицы с русскими заголовками столбцов
	$tbl_hdr='';# Соберём в переменную код шапки таблицы для вывода на страницу
	$tbl_hdr.="<TABLE border=1 align='center'><TBODY>";
	$tbl_hdr.="<caption><b>$title</b></caption>\r\n";
	$tbl_hdr.="<TR>\r\n";
	foreach($fields as $key=>$value) {
		$field_caption=$value[0]; $edit_level=$value[1]; $val_type=$value[2]; $field_name=$value[3];
		
		$autoinc=''; if ( array_key_exists($field_name, $arr_fields) ) { $autoinc=$arr_fields[$field_name]; }
		
		if ('0'!==$edit_level ) {
			if ($dbg) {
				$tbl_hdr.="<TH>$field_caption:$autoinc:$val_type</TH>"; #Если показ НЕ запрещён - выводим заголовок столбца
			} else {
				$tbl_hdr.="<TH>$field_caption</TH>"; #Если показ НЕ запрещён - выводим заголовок столбца
			}
			
			$val_name='value'; # Тип получаемого значения из инпута - value или checked
			$input=''; $field_tiny_name=$key;
			if ( '0'==$autoinc ) { # Если НЕ автоинкрементное поле и можно добавлять строку, то добавим нормальный инпут
				switch ($val_type) {#
					case 'B':	$input="<input type='checkbox' id='$key'>";						$val_name='checked';	break;
					case 'I':	$input="<input type='number' class='inp' id='$key'>";												break;
					case 'S':	$input="<input type='text' class='inp' id='$key'>";													break;
					case 'D':	$input="<input type='date' class='inp' id='$key'>";													break;
					case 'L':	# Узнаём названия таблицы, lookup поля и сокр.имя поля, для которого делается lookup
										$list_tbl_name=$value[4]; $list_field=$value[5]; $field_tiny_name="lst_".$key;
										
										$arr_list_fields=array(); # Готовим шаблон для поля LookUp listbox
										$STH_ch=search($list_tbl_name, '', $list_field); # Надо ли сортировать список выбора ? И по какому полю ?
										# $mes = str_replace('[tiny_name]', $set, $mes);# Меняем шаблон
										#$input="<select name=\"$field_tiny_name\" size=1>";
										$input="<select id=\"[tiny_name]\" size=1>";
										$input.="<option value=\"0\" selected disabled style='color:red;'>[  Выберите  ]</option>";
										
										$row_ch = $STH_ch->fetch();
										$fld_enbl=''; $field_exists=FALSE;
										if ( isset($row_ch['enable']) ) { $fld_enbl='enable'; $field_exists=TRUE; }   # Если поле существует - возьмём его название
										if ( isset($row_ch['enabled']) ) { $fld_enbl='enabled'; $field_exists=TRUE; } # Если поле существует - возьмём его название
										
										do {
											$dsb='';
											$ID=$row_ch[0];
											$name=trim($row_ch[$list_field]);
											
											if ( $field_exists AND '0'==$row_ch[$fld_enbl]) { $dsb=' disabled'; }				# Если значение этого поля не 1 - задизаблим строку
											
											$arr_list_fields[$ID]=$name;
											$input.="<option value=\"$ID\"$dsb>$ID $name</option>";
										} while ($row_ch = $STH_ch->fetch()); 
										
										$input.='</select>';
										
										$arr_tpl_lookup_listbox[$field_name]=$input;							# Тут получили html шаблон списка
										$arr_lookup_fields     [$field_name]=$arr_list_fields;	  # Добавим массив ID=>значение в массив 
										
										# Теперь меняем параметр name='[tiny_name]' из выводимого в edit_line селекта
										$input = str_replace('[tiny_name]', $field_tiny_name, $input); # Меняем шаблон
										
										break;
										
					default:	$alert="$module: Field type '$val_type' NOT found !!!"; alert($alert); msg($alert, $dbg); exit;
				}#end switch
				
				$onClick.="$key=encodeURIComponent(document.getElementById(\"$field_tiny_name\").$val_name); \r\n";   ### Чтобы не портились русские буквы
				$req.="$plus_sign\"$amp_sign$key=\"+$key"; #Чтобы ID не добавлялся в INSERT, если он автоинкрементный
				$amp_sign='&'; $plus_sign='+'; # Теперь будем добавлять эти значки перед и после
			} else {
				$input='&nbsp;'; $fields[$key][1]=1; # Поле с автоинкрементным IDшником - значит только просмотр
			}
			
			$edit_line.="<TD>".$input."</TD>\r\n";
			
		}#end if
	}#end foreach
	$req.="+\"&tbl=$tbl&add=new\"; \r\n"; # добавляем в конце сокр.название таблицы "; "
	
	$onClick.=$req."\r\npg(\"$script_name\", \"main\", req);";
	
	$tbl_hdr.="<TH>+</TH>";
	$tbl_hdr.="</TR>\r\n";
	
	$add_btn="<img src='./img/check.png' width=16px style='height:16px;' border=0>";
	
	
	$edit_line.="<TD><a href='#' onClick='$onClick'>\r\n$add_btn</TD>\r\n";
	
	#$edit_line.="<TD><a href='#' onClick='alert(\"OK\");'>$add_btn</TD>"; #####
	
	#message('onClick="'.$onClick.'"', 'green'); ###
	#message('edit_line="'.make_harmless($edit_line).'"', 'blue'); ###
	
	if ( $allow_add_line ) {
		$tbl_hdr.="<TR>$edit_line</TR>\r\n";
	}
	####################################################################################################################################################
	
	
	
	
	
	
	
	
	
	
	
	
	
	#########################################
	#   Шаблоны полей для редактирования    #
	#########################################
	# Integer, String, Date, Binary, LookUp listbox
	# [tiny_name] - сокращённое название поля   # [line_id] - ID редактируемой строки   # [value] - начальное значение
	#######  Готовим шаблон для текстового поля
	$script=" onchange='pg(\"$script_name\", \"[tiny_name][line_id]\", \"tbl=$tbl&set=[tiny_name]&line=[line_id]&val=\"+encodeURI(this.value));'";
	$onKeyDown="onKeyDown='this.size=this.value.length+3;'";
	$tpl_txt_field="<input type=text id='it_[tiny_name][line_id]' size='[size]' value='[value]' $script $onKeyDown>";
	
	
	#######  Готовим шаблон для поля Binary
	$chkd_scrpt=" onclick='pg(\"$script_name\", \"[tiny_name][line_id]\", \"tbl=$tbl&set=[tiny_name]&line=[line_id]&val=[value]\");'";
	$tpl_bin_field="<input type='checkbox' [checked] $chkd_scrpt>";
	
	
	
	
	
	
	### Запись в базу отдельного поля
	if ( !empty($set) ) {  # set=rt&line=$res_id&val=123
		$mes=''; $field=''; $field_name='';
		if (array_key_exists($set, $fields) ) { # По сокращённому названию
			$field_name=$fields[$set][0];		# Получим русское название поля
			$field_type=$fields[$set][2];		# Получим тип поля
			$field     =$fields[$set][3];		# Расшифруем сокр. название поля
			
			if ($dbg) { show_var($field_name, 'field_name'); }###
			
			
			
			if (empty($line) ) {
				$err.="$module: LineID пустое"; $line=0;
			} else {
				$STH_srch=search($table, 'id=:id', '', array(':id', $line));# Ищем строку Line_ID в $table
				$row_srch = $STH_srch->fetch();
				if ( empty($row_srch) ) {
					$err.="$module: Строка с ID=$line не найдена";
				} else {
					$old_val=$row_srch[$field]; # Сохраним старое значение поля
					#if ('ps'===$set) { $old_val=DecryptByPassPhrase($old_val, $domain_user_pass); } # Пароль надо сначала расшифровать
				}#end if
				
				/*# Если новое значение пустое, то возвращаем старое и матюгаемся
				if (empty($new_val) ) {
					$err.="$module: Поле \'$field_name\' не может быть пустым"; $val=$old_val;
				}#end if
				*/
				$val=$new_val;
				
				msg("Прилетели tbl='$tbl'='$title' set='$set' line='$line' val='$val'", $dbg);###
				
				# Тут надо в зависимости от ТИПА поля его фильтровать
				# Integer, String, Date, Binary, LookUp listbox
				switch ($field_type) {
					case 'I':	$val=get_int_val($val); # Integer
										msg("Тип поля Integer", $dbg);###
										$mes = str_replace('[value]',			$val,		$tpl_txt_field);	#Меняем текстовый шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'S':	$val=make_harmless($val); # String
										msg("Тип поля String", $dbg);###
										$mes = str_replace('[value]',			$val,		$tpl_txt_field);	#Меняем текстовый шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'D':	$val=filter_date($val); # Date
										msg("Тип поля Date", $dbg);###
										$mes = str_replace('[value]',			$val,		$tpl_txt_field);	#Меняем текстовый шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[size]',			strlen($val),	$mes);
										break;
					
					case 'B':	$val=substr($val, 0, 2);	# on или ничего не прилетает		# Binary, но на самом деле Integer
										msg("Тип поля Binary", $dbg);###
										$chkd=''; $set_val='1';
										if (!empty($val) ) { $chkd=" checked='checked'"; $set_val='0'; }
										$mes = str_replace('[value]',		$set_val,	$tpl_bin_field);	# Меняем двоичный шаблон
										$mes = str_replace('[tiny_name]',	$set,		$mes); 						# Сокращённое название поля - чтобы враг не догадался
										$mes = str_replace('[line_id]',		$line,	$mes);
										$mes = str_replace('[checked]',	$chkd,	$mes);
										break;
					
					case 'L':	$val=get_int_val($val);
										msg("Тип поля LookUp listbox", $dbg);###
										
										$tpl_list_field=$arr_tpl_lookup_listbox[$field];
										$arr_list_fields=$arr_lookup_fields[$field];
										
										# Тут надо проверить, существует ли такой код в Lookup таблице
										if ( array_key_exists($val, $arr_list_fields) ) { # Есть такой id ?
											msg("Значение '$val' найдено ='".$arr_list_fields[$val]."'", $dbg);###
											$mes = str_replace('[line_id]', $line, $tpl_list_field);#Меняем шаблон
											$mes = str_replace(" selected", "", $mes); # Снимаем выбор со строки по умолчанию
											$mes = str_replace("value=\"$val\"", "value=\"$val\" selected", $mes); # Делаем выбранным пункт списка
											$mes = str_replace('[tiny_name]', $set, $mes);# Меняем шаблон
										} else { $err.="$module: Код '$val' для поля '$set' не найден"; }
										
										break;
										
					default:	$err.="$module: Тип поля \'$field_type\' не найден"; exit;
				}#end switch
				
				
				if (empty($err)) {# Если ошибок нет, то обновляем поле
				# id=:line_id чтобы можно было редактировать поле ID. А иначе получается запрос вида UPDATE $table SET id=:id WHERE id=:id
					$STH_upd=update($table, $field, 'id=:line_id', array( array(':line_id', $line), array(":$field", $val) ) ); # Запишем поле в базу
					
					#######################################################################################
					# ПЕРЕДЕЛАТЬ ЧТОБЫ НЕ НАДО БЫЛО ВСЮ СТРАНИЦУ ПЕРЕЗАГРУЖАТЬ
					# НАДО СДЕЛАТЬ ПЕРЕЗАПИСЬ ОТДЕЛЬНОЙ СТРОКИ ТАБЛИЦЫ С НОВЫМИ IDШНИКАМИ
					# ЛИБО ЗАПИСЫВАТЬ IDШНИК СТРОКИ В ОДНОМ МЕСТЕ И МЕНЯТЬ ЕГО ТАМ И ЧИТАТЬ ОТТУДА ПРИ ОТПРАВКЕ ПАРАМЕТРОВ
					if ( 'ID'===strtoupper($field) ) { #  После смены IDшника НУЖНО ПЕРЕГРУЖАТЬ ВСЮ СТРАНИЦУ (весь DIV main), чтобы сменить IDшники
						script("setTimeout(\"pg('edit_table.php', 'main', '&tbl=$tbl');\", 1);"); # всех TDшек в отредактированной строке
					}#end if
					#######################################################################################
				
				} else {
					alert($err); #Выведем сообщение об ошибке
				}#end else
				
				echo "$mes"; # А тут из шаблона выведем поле с новым значением
				exit;
			}#end else NOT empty($line)
			
			
			
		} else { $err.="$module: Поле '$set' не найдено"; }
		
		
	}#end else NOT empty($set)
	
	
	
	
	
	
	
	
	
	
	
	
	###  Добавление новой строки
	#    Сюда прилетают:    ВСЕ ПОЛЯ ЭТОЙ ТАБЛИЦЫ
	#    параметр add=new - флаг добавления новой строки
	#show_var($_REQUEST, '_REQUEST'); ###
	if ( isset($_REQUEST['add']) AND $allow_add_line ) {
		
		msg('Добавление новой строки', $dbg); ###
		
		show_var($_REQUEST, '_REQUEST');###
		
		$ins_fields=''; $comma=''; $bind=array();
		foreach($fields as $key=>$value) {
			$field_caption=$value[0]; $edit_level=$value[1]; $val_type=$value[2]; $field_name=$value[3];
			
			if ( '2'==$edit_level AND isset($_REQUEST[$key]) ) {
				$val=$_REQUEST[$key];
				switch ($val_type) { # Отфильтруем в зависимости от типа поля
					case 'I': msg("Integer $key='$val'",	$dbg, 'lime');###
										$val=get_int_val($val);
										break;
					
					case 'S': msg("String $key='$val'",		$dbg, 'lime');###
										$val=make_harmless($val);
										break;
					
					case 'D': msg("Date $key='$val'",			$dbg, 'lime');###
										$val=filter_date($val);
										break;
					
					case 'L': msg("List $key='$val'",			$dbg, 'lime');###
										$val=get_int_val($val);
										break;
					
					case 'B': msg("Binary $key='$val'",		$dbg, 'lime');###
										$val=get_int_val($val);
										break;
					
					default:	msg("$module: '$val_type' for '$field_caption' not found", $dbg); ###
				}#end switch
				
				#if ( !empty($val) ) {
					$ins_fields.=$comma.$field_name;
					$bind[]=array(":$field_name", $val);
				#}#end if
				
				$comma=','; #Теперь будем добавлять запятую спереди
			}#end if
			
		}#end foreach
		if ($dbg) { show_var($ins_fields, 'ins_fields'); } ###
		if ($dbg) { show_var($bind, 'bind'); } ###
		
		if (!empty($ins_fields) ) {
			$STH_ins=insert($table, $ins_fields, $bind); #Попробуем добавить новую строку
			if ($STH_ins) {
				msg("Line inserted", $dbg, 'lime');
			} else {
				$mess="$module: Не удалось добавить строку";
				alert($mess);
				msg($mess, $dbg);
			} ###
		}#end if
		
		
	}#end if
	
	
	
	
	
	
	
	
	
	
	
	
	
	##############################################################################
	#if ($dbg) {htmlhead();}### Для тестирования, после встраивания удалить !!!! ОТЛАДКА
	##############################################################################
	
	msg("START", $dbg);###
	
	
	
	#show_var($arr_tpl_lookup_listbox, 'arr_tpl_lookup_listbox');###
	#show_var($arr_lookup_fields, 'arr_lookup_fields');###
	#show_var($fields, 'fields');###
	
	
	
	echo $tbl_hdr; #Выводим сформированную шапку таблицы
	
	
	########################################
	#   Выводим строки таблицы с данными   #
	########################################
	$STH=search($table, '', $order);
	while ($row = $STH->fetch()) { # PDO::FETCH_NUM
		echo "<TR>"; $script='';
		$ID=$row[0]; # Получим IDшник этой строки
		
		foreach($fields as $key=>$value) {
			$edit_level=$value[1];
			if ('0'===$edit_level) { continue; } # Продолжаем разговор   # Маленькая оптимизация
			
			$str=''; #$field_caption=$value[0];
			$val_type=$value[2];		# Тип поля
			$field_name=$value[3];	# Полное название поля таблицы
			$val=$row[$field_name];	# Значение поля из таблицы
			
			if ('L'==$val_type) {
				$list=$arr_tpl_lookup_listbox[$field_name];				# HTML шаблон списка выбора поля $field_name
				$arr_list_fields=$arr_lookup_fields[$field_name]; # Массив полей списков выбора и их значений вида $arr[$field_name]=array('$ID'=>$name, ...)
			}

			$str=$val;
			switch ($edit_level) { 
				case '1':	if ('L'==$val_type) {#Для списка подставим вместо IDшника значение из LookUp поля другой таблички
										if (array_key_exists($val, $arr_list_fields) ) {
											$str=$val.':'.trim($arr_list_fields[$val]);
										}
									}
									
									if ( empty($str) ) { $str='&nbsp;'; }
									echo "<TD>$str</TD>";
									break;
									
				case '2':	$script=" onchange='pg(\"edit_table.php\", \"$key$ID\", \"tbl=$tbl&set=$key&line=$ID&val=\"+encodeURI(this.value));'";
									
									if ('S'===$val_type OR 'D'===$val_type OR 'I'===$val_type) {# Если Строка, Дата или Целое
										$str = str_replace('[value]',			$val, 				$tpl_txt_field);			#Меняем текстовый шаблон
										$str = str_replace('[tiny_name]',	$key,					$str); 		# Сокращённое название поля - чтобы враг не узнал
										$str = str_replace('[line_id]',		$ID, 					$str);
										$str = str_replace('[script]',		$script,			$str);
										$str = str_replace('[size]',			strlen($val),	$str);
									}
									
									if ('L'==$val_type) { # Если Список выбора
										$str=str_replace(' selected', '', $list); # Снимаем выбор со строки по умолчанию
										$str=str_replace("value=\"$val\"", "value=\"$val\" selected", $list); # Делаем выбранным определенный пункт списка
										$str=str_replace('size=1', "size=1 $script", $str); # Прицепляем к списку выбора скрипт реакции на событие
										$str=str_replace(' id="[tiny_name]"',	'', $str); 		# IDшник тут не нужен - всё равно передаём this.value
									}
									
									if ('B'==$val_type ) {# Если Двоичное поле
										$chkd=''; $set_val='1';
										if (!empty($val) ) { $chkd=" checked='checked'"; $set_val='0'; }#end if
										$chkd_scrpt=" onclick='pg(\"edit_table.php\", \"$key$ID\", \"tbl=$tbl&set=$key&line=$ID&val=$set_val\");'";
										$str="<input type='checkbox' $chkd $chkd_scrpt>";
									}#end if
									
									echo "<TD bgcolor='#C0FFC0' id='$key$ID'>$str</TD>";
									break;
			}#end switch
			
		}#end foreach
		
		$td_txt='&nbsp;';
		if ( $allow_delete_line ) {
			$del_sign="<img src='./img/cross.png' width=16px style='height:16px;' border=0 title='Удалить' alt='Удалить'>"; #Значок удаления
			
			$del_cmd=" onClick='if (confirm(\"Удалить строку $ID ?\") ) {pg(\"edit_table.php\", \"main\", \"tbl=$tbl&del=$ID\");};'";
			#$del_cmd="alert(\"Триггер на удаление в процессе разработки\");";
			$td_txt="<a href='#'$del_cmd>$del_sign</a>";
		}
		echo "<TD>$td_txt</TD>";
		
		echo "</TR>\r\n";
	}#end while
	echo '</TABLE>';
	
	
	
	msg("END", $dbg);###
	
	
	##############################################################################
	#if ($dbg) {htmlfoot();} ### Для тестирования, после встраивания удалить !!!! ОТЛАДКА
	##############################################################################
	
	
	
	
	
?>