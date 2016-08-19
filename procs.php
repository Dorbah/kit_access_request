<?php
	include_once 'config.php';
	#«Пишите код так, как будто сопровождать его будет склонный к насилию психопат, который знает, где вы живёте»
	#>Злые юзеры, так и норовят что нить сломать :-D
	
	# В начало константу ставить хочешь ты :-D (C) 'Мастер'===$Йода
	
# Буратино дали три яблока. Два он съел. Сколько яблок осталось у Буратино?
# Думаете одно? Ничего подобного.
# Никто же не знает сколько уже у него было яблок до этого. 
# Мораль - обнуляйте переменные !!!
	
	
	
	
	#Функция запрета кеширования скриптов и стилей
	# На входе список подключаемых файлов с относительными путями вида:
	# $arr_files=array('js/ajax.js', 'styles.css');
	# Использование: echo deny_cache($arr_files);
	function deny_cache($arr_files){
		$module='deny_cache';
		
		if (empty($arr_files) ) { echo "$module: Список файлов пустой"; return false;}
		
		$str='';#Вернём HTML в строке
		foreach($arr_files as $filename) {
			clearstatcache(TRUE, $filename);
			if (file_exists($filename)) {
				$mod_time=filemtime($filename);
				
				$ext = substr($filename, strrpos($filename, '.')+1);
				switch ($ext) {
					case 'js':
						$str.="\t\t<script type='text/javascript' src='$filename?$mod_time'></script>";
						break;
						
					case 'css':
						$str.="\t\t<link rel='stylesheet' type='text/css' href='$filename?$mod_time' media='all' />";
						break;
				}#end switch
				
				$str.="\r\n";
			} else { $str.="<!-- File '$filename' not found -->";	}
			#end if
		}#end foreach
		
		return $str;
	}#end func deny_cache
	
	
	
	function htmlhead($FIO='') {
		$dbg=FALSE; $ramka='';
		
		if ($dbg) {$ramka='border=1 bordercolor=red';}
		
		echo "<!-- START htmlhead -->
<HTML>
	<HEAD>
		<Title>Пароли и доступ к региональным биллингам</Title>
		<meta http-equiv='content-type' content='text/html; charset=UTF8'>\r\n";
		
		echo deny_cache(array('js/ajax.js', 'styles.css')); #массив подключаемых файлов с относительными путями
		
		$curr_dir=getcwd();  $color='white';
		if ( FALSE!==strpos($curr_dir, 'test') ) {$color='#6A5ACD';}
		
echo "	</HEAD>
	<BODY leftmargin=0 topmargin=0 rightmargin=0 bottommargin=0 bgcolor='$color'>
		<!-- START DIV body -->
		<DIV id='body'>
			<TABLE $ramka width='100%' style='height:100%;' cellspacing=0 cellpadding=0>
				<TBODY>
					<TR style='height: 1%;'>
						<TD colspan=2>
							<div style='width: 100%; height: 75px; background-color: #003577; background-image: url(img/bg-top.jpg); background-repeat: repeat-y;'>
								<div style='position: absolute; width: 100%; float: left; color: $color;'><H2>Пароли и доступ к региональным биллингам</H1></div>
								<div style='position: absolute; width: 100%; float: right; font-size: 8pt; font-family: Verdana; color: white;' align='right'>$FIO</div>
								<div style='width: 100px; height: 75px; position: absolute; float: left; background-image: url(img/rostelecom.png); background-repeat: no-repeat; cursor: pointer;' onclick='document.location.href=\"index.php\"'></div>
							</div>
						</TD>
					</TR>
					<TR style='height:99%;' align='center' valign='middle'>
						<TD width='99%' align='center'>
							<!-- END htmlhead -->\r\n";
	}#end func htmlhead
	
	
	#Функция выводит форму запроса логина/пароля
	function show_auth($err='') {
		$dbg=FALSE; $ramka='border=0'; $ramka2='border=0';
		if ($dbg) {$ramka='border=2 bordercolor=green'; $ramka2='border=3 bordercolor=blue';}
		if ( isset($_SERVER['PHP_AUTH_USER']) ) {$def_login=IOFamilia($_SERVER['PHP_AUTH_USER']);} else {$def_login='';}
		
		$err_msg=color(bold($err), 'red').'<br>'.color(bold('Введите логин/пароль<br>для входа в Windows'), 'green');
		
		$tab="\t\t\t\t\t\t";
		#echo "$tab<Table $ramka width='100%' style='height: 100%;' align=center cellspacing='0' cellpadding='0'>\r\n";
		#echo "$tab	<Tr style='height:100%;' valign=middle>\r\n";
		#echo "$tab		<Td align=center width='1%'>\r\n";
		echo "$tab			<form action='login.php' method='post'>\r\n";
		#echo "$tab				<div class='area' align='center' valign='middle'>\r\n";
		echo "$tab					<TABLE $ramka2 width='100%' style='height: 100%;' align='center' cellspacing='0' cellpadding='0'>\r\n";
		echo "$tab						<TR><TD colspan=2 align='center' width='1%'><font color=green><b>Вход в систему</b></font></TD></TR>\r\n";
		echo "$tab						<TR><TD colspan=2 align='center' width='1%'>$err_msg</TD></TR>\r\n";
		echo "$tab						<TR><TD>Логин:</TD><TD width='99%'><input type=text size='20' style='width:100%;' name='login' id='login' value='$def_login'></TD></TR>\r\n";
		echo "$tab						<TR><TD>Пароль:</TD><TD width='99%'><input type=password size='15' style='width:100%;' name='pass' id='pass'></TD></TR>\r\n";
		echo "$tab						<TR><TD colspan=2 width='100%' align='center'><input type=submit name='send' id='send' value='Вход'></TD></TR>\r\n";
		echo "$tab					</TABLE>\r\n";
		#echo "$tab				</div>\r\n";
		echo "$tab			</form>\r\n";
		#echo "$tab		</Td>\r\n";
		#echo "$tab	</Tr>\r\n";
		#echo "$tab</Table>\r\n";
	}#end func show_auth
	
	
	
	#Функция выводит меню
	function show_menu(){
		$dbg=FALSE; $module='Show_menu'; $tab="\t\t\t\t\t\t\t\t\t\t\t\t";
		
		if ( isset($_SESSION['login']) ) {
			$login=$_SESSION['login'];
			
			echo "$tab<!-- START $module -->\r\n";
			if (isset($_SESSION['pass'])) { $pass=$_SESSION['pass']; } else { message("$module: Password NOT DEFINED !!!"); exit; }
			
			#Выведем меню для всех юзеров
			echo "$tab<a href='#' onclick=\"pg('load_billing.php','main','');\">Авторизация</a><br>\r\n";
			echo "$tab<a href='#' onclick=\"pg('check_billing.php','main','');\">Проверка</a><br>\r\n";
			echo "$tab<a href='#' onclick=\"pg('user_pass.php','main','');\">Пароли</a><br>\r\n";
			
			echo "<br>\r\n";
			
			$al=access_level($login);# Получим права доступа $al[0]-супервизор='1'  $al[1]-админ='1'
			
			#Выведем меню для СУПЕРВИЗОРА
			if ('1'===$al[0]) { #if (is_supervisior($login) ) {
				echo "$tab<a href='#' onclick=\"pg('get_ldap_users.php','main','');\">Выбор</a><br>\r\n";
				#echo "$tab<a href='#' onclick=\"alert('Он работает !!!');\">Жмякай</a><br>\r\n";
			}#end if
			
			# Выведем меню для АДМИНА
			if ('1'===$al[1]) { #if (is_admin($login) ) {
				$masterkey='';
				if (isset($_SESSION['masterkey'])) {	#Если у админа
					$masterkey=$_SESSION['masterkey'];	# есть ключ
					msg('mk set', $dbg, 'green');			# This is XOPOWO
					#msg("mk='$masterkey'", $dbg);
					
					#echo "<br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('get_ldap_users.php','main','');\">Выбор</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('send_acc_req.php','main','');\">Запрос</a><br><br>\r\n";
					
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=usrs');\">Пользователи</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=kusr');\">Пользователи_</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=q4rn');\">Перешифровка</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=rgac');\">Доступы</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=rgad');\">РегАдмины</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=regs');\">Регионы</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=rtyp');\">Типы&nbsp;ресурсов</a><br>\r\n";
					echo "$tab<a href='#' onclick=\"pg('edit_table.php','main','tbl=rsrs');\">Ресурсы</a><br>\r\n";
				} else {
					echo "$tab"; message('NO MK');# Крокодил не ловится, не растёт кокос
					message('Требуется MasterKey - попросите администратора войти в систему');
				}#end else
			}#end if is_admin
			
			echo "<br>\r\n";
			echo "$tab<a href='#' onclick=\"pg('help.php','main','');\">Справка</a><br>\r\n";
			echo "<br>\r\n";
			echo "$tab<a href='#' onclick=\"pg('logout.php','main','');\">Выход</a>\r\n";
			echo "$tab<!-- END $module -->\r\n";
		} else {
			msg("$module: Логин не задан", $dbg);
		}#end else
		
	}#end func show_menu
	
	
	
	
	
	function htmlfoot() {
		echo "							<!-- START htmlfoot -->
						</TD>
					</TR>
				<TBODY>
			</TABLE>
		</DIV><!-- END DIV body -->
	</BODY>
</HTML>";
	}#end func htmlfoot
	
	
	
	
	
	#Отладочная функция для вывода значения переменной
	function show_var($var, $var_name='var') {
		echo "<b>$var_name</b>=<pre>";
		var_dump($var);
		echo '</pre>';
	}#end func show_var
	
	
	function bold($mess) {
		return "<b>$mess</b>";
	}#end func bold
	
	
	function color($mess, $color) {
		return "<font color=$color>$mess</font>";
	}#end func color
	
	
	function script($scrpt) {
		echo "<script>$scrpt</script>\r\n";
	}#end func script
	
	
	function alert($mess) {
		script("alert(\"$mess\");");
	}#end func alert
	
	
	#Выводим сообщение в текст страницы
	#$mess - текст сообщения
	#$color - цвет текста
	#$bold - жирным шрифтом (true) или нет (false)
	function message($mess, $color='red', $bold=true) {
		$msg=$mess;
		if ($bold) {$msg=bold($mess);}
		echo color($msg, $color)."<br>\r\n";
	}#end func message
	
	
	#Функция выводит сообщение только если $debug=TRUE
	function msg($msg, $debug, $color='red') {
		if ($debug) {message($msg, $color); }
	}#end func msg
	
	
	
	
	
	#Функция делает все буквы до первой точки
	# и первую букву после первой точки заглавными
	# Логин в виде "IO.Familia" или "I.Familia"
	function IOFamilia($login) {
		if (strpos($login, '.')===FALSE) {
			$FIO=strtoupper($login[0]).substr($login, 1);# Familia
		} else {#Если есть точка
			$FIO=explode('.', $login);#Раскукоживаем
			$FIO[0]=strtoupper($FIO[0]);# IO
			$FIO[1]=strtoupper($FIO[1][0]).strtolower(substr($FIO[1], 1));# Familia
			$FIO=implode($FIO, '.'); #Скукоживаем
		}#end if
		
		return $FIO;
	}#end func 
	
	
	# Функция вырезает всё, кроме латинских букв a-z, A-Z, цифр 0-9 и символов _ . - \ @
	function filter_aZ09($val) {
		return preg_replace('/[^a-z0-9_\\.\\-\\\\@]+/i', '', $val);
	}#end func filter_aZ09
	
	
	#Функция вырезает из строки всё кроме цифр и разделителей . / -
	# Нужна для фильтрации принимаемых от юзера данных
	function filter_date($val) {
		return intval(preg_replace('#[^0-9./-]+#', '', $val));
	}#end func filter_date
	
	
	# Функция вырезает из строки всё кроме больших и маленьких букв
	function get_letters($val) {
		return preg_replace('/[^a-z]+/i', '', $val);
	}#end func get_letters
	
	
	# Функция добавляет слеши, заменяет HTML теги кодами и переводит в UTF-8
	# Это нужно для безопасной записи строки в таблицу
	function make_harmless($val) {
		return htmlentities(addslashes($val), ENT_QUOTES, "UTF-8");
	}#end func make_harmless
	
	# Функция убирает все бэкслеши
	function remove_slashes($string){
		$string=implode("",explode("\\",$string));
		return stripslashes(trim($string));
	}#end func remove_slashes
	
	
	#Функция вырезает из строки всё кроме цифр и возвращает целое значение
	# Нужна для фильтрации принимаемых от юзера данных
	# Максимальное значение 2147483647=2^31-1
	function get_int_val($val) {
		return intval(preg_replace('/[^0-9]+/', '', $val));
	}#end func get_int_val
	
	
	
	
	
	#Функция получает строку с номером региона,
	# обшкуривает его до двузначного целого(INT)
	# и проверяет его существование в массиве $region
	# Строка $module с названием вызывающего модуля,
	# чтобы матюгаться где ошибка вылезла
	function get_int_reg($reg, $module, $region) {
		$rg=intval(substr($reg, 0, 2));#Берём только две цифры кода региона и обшкуриваем до целого
		if (!isset($region[$rg])) {#Если региона с таким кодом не существует в массиве $region
			message("$module:get_int_reg: Регион с кодом '$reg' не существует"); #Матюгаемся
			exit; # И упадаем
		}#end if
		
		return $rg;
	}#end func get_int_reg
	
	
	
	
	
	#Функция возвращает логин без домена
	function cut_domain($login){
		$dbg=FALSE; $module='cut_domain';
		
		#Обрезание доменного имени из учетной записи
		#$last_name = preg_replace('/.*?\\\/', '', $login);

		
		$user_name=$login; # Берём логин
		if ($r_pos=strrpos($login, '\\')) { $user_name=substr($login, $r_pos+1);}	# отрезаем всё до бэкслэша (домен)
		msg("$module: login='$login' user_name='$user_name'", $dbg);
		
		return $user_name;
	}#end func cut_domain
	
	
	
	
	
	#Функция возвращает ФИО пользователя из LDAP
	# или пустую строку если не находит
	function get_user_fio($login) {
		$FIO='';
		$login=cut_domain($login);
		
		#Запрашиваем данные из LDAP
		$ldap_info = ldap_user($login);		#Получение данных по пользователю из AD
		$FIO=$ldap_info[0]['cn'][0];
		
		return $FIO;
	}#end func get_user_fio
	
	
	
	
	#Функция ищет юзера по логину в таблице users
	# и возвращает строку инфы в массиве
	# или пустой массив если не находит
	function get_user_info($login) {
		$arr_info=array();
		$login=strtoupper(cut_domain($login));
		
		$STH=search('users', 'UPPER([login])=:login', '', array(':login', $login));	#Ищем юзера по логину
		$arr_info=$STH->fetch();
		
		return $arr_info;
	}#end func get_user_info
	
	
	
	
	# Функция возвращает IDшник юзера из таблицы users
	# по его логину или 0 если не найден
	function get_userID_by($login) {
		$user_id=0;
		$login=cut_domain($login);
		
		$bind=array(':login', strtoupper($login));
		$STH=search('users', 'UPPER([login])=:login', '', $bind);	#Ищем юзера по логину
		if ($row=$STH->fetch()) {										# Если нашли
			$user_id=$row['id'];										# Получаем IDшник
		}# end if
		
		#Можно сделать лучше так:
		#$arr_info=get_user_info($login);
		#$user_id=$arr_info['id'];
		
		return $user_id;
	}#end func get_userID_by
	
	
	
	
	
	
	# Функция возвращает логин юзера из таблицы users
	# по его IDшнику или '' если не найден
	function get_login_by_ID($user_id) {
		$login='';
		
		$bind=array(':id', $user_id);
		$STH=search('users', 'id=:id', '', $bind);	#Ищем юзера по IDшнику
		if ($row=$STH->fetch()) {						# Если нашли
			$login=$row['login'];						# Получаем логин
		}# end if
		
		return $login;
	}#end func get_login_by_ID
	
	
	
	
	
	#Функция возвращает уровень доступа из базы по логину
	function access_level($login) {
		$arr_info=get_user_info($login);
		return $arr_info['supervisior'].$arr_info['admin'];
	}#end func access_level
	
	
	#Функция проверяет признак supervisior в users по логину
	#Возвращает TRUE если supervisior=1
	# и FALSE в остальных случаях
	function is_supervisior($login) {
		$arr_info=get_user_info($login);
		return (1==$arr_info['supervisior']);
	}#end func is_supervisior
	
	
	#Функция проверяет признак admin в users по логину
	#Возвращает TRUE если admin=1
	# и FALSE в остальных случаях
	function is_admin($login) {
		$arr_info=get_user_info($login);
		return (1==$arr_info['admin']);
	}#end func is_admin
	
	
	
	
	
	#Функция заменяет один разделитель на другой
	# И добавляет по бокам каждому элементу дополнительные символы
	# На входе 'elem1,elem2,...'
	# На выходе например '[elem1],[elem2],...'
	# Нужна для insert и send_mail
	function change_delim($left='', $text, $delim_old, $delim_new, $right='') {
		return $left.implode(explode($delim_old, $text), $delim_new).$right; #Раскукоживаем и закукоживаем
	}#end func change_delim
	
	
	
	
	
	
	
	
	#Функция отправляет запрос $text в УЖЕ ОТКРЫТЫЙ файловый указатель $filepointer
	# Получает и возвращает ответ из этого потока
	# Нужна для send_mail
	function fsock_puts_gets($filepointer, $text) {
		fputs($filepointer, $text); #Посылаем запрос
		$res=fgets($filepointer);#Получаем ответ
	}#end func fsock_puts_gets
	
	
	
	
	
	# Функция выполняет cURL запрос и возвращает результат
	function get_curl($login, $passw, $url, $auth_type) {
		$ch = curl_init();#Начинаем cURL
		curl_setopt($ch, CURLOPT_URL, $url); #Адрес на котором авторизуемся
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'); #Прикидываемся фикусом
		curl_setopt($ch, CURLOPT_HTTPAUTH, $auth_type); #Указываем что проходим NTLM, CURLAUTH_ANY не канает
		curl_setopt($ch, CURLOPT_USERPWD, $login.':'.$passw);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); #Указываем что хотим принимать данные
		
		#Количество секунд ожидания при попытке соединения. Используйте 0 для бесконечного ожидания.
		#curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		
		$data = curl_exec($ch);
		$data = iconv('Windows-1251', 'UTF-8', $data);

		curl_close($ch); # завершение сеанса и освобождение ресурсов
		
		return $data;
	} # end func get_curl
	
	
	
	
	
	#Выполнить запрос к базе
	#$select - строка SQL запроса
	#$arr - параметры запроса в (одно)/(дву)мерном массиве [("ключ1","значение1")[,("ключ2","значение2")[,()...]]]
	# если $arr не задано, то выполняется запрос без параметров
	#$query_attr - аттрибуты запроса в массиве вида, например
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY) - делать PDO::Fetch() только вперёд или
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)  - чтобы получить прокручиваемый курсор PDO::fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_*) и
	#  задавать направление выборки через PDO::FETCH_ORI_* :
	#   PDO::FETCH_ORI_NEXT, PDO::FETCH_ORI_PRIOR, PDO::FETCH_ORI_FIRST, PDO::FETCH_ORI_LAST, PDO::FETCH_ORI_ABS, PDO::FETCH_ORI_REL
	
	##################################################################################################
	#  Интересные варианты прибиндить параметры к запросу без $STH->bindValue($key, $val);
	#	$data = array('Cathy', '9 Dark and Twisty Road', 'Cardiff');  
	#	$STH = $DBH->prepare("INSERT INTO folks (name, addr, city) values (?, ?, ?)");  
	#	$STH->execute($data); 
	#
	#	$data = array( 'name' => 'Cathy', 'addr' => '9 Dark and Twisty', 'city' => 'Cardiff' );  
	#	$STH = $DBH->prepare("INSERT INTO folks (name, addr, city) values (:name, :addr, :city)");  
	#	$STH->execute($data);
	##################################################################################################
	function query($select, $arr=array(), $query_attr=array() ) {
		$dbg=FALSE;
		
		if (!empty($select)) { #Если select задан
			$DBH=mssql_conn();#Цепляемся к базе
			$STH = $DBH->prepare($select, $query_attr);#Готовим запрос
			
			if (!empty($arr)) {#Если есть параметры запроса, то привязываем
				$ext_key=''; $ext_val=''; $ext_flag=true;#Флаг внешнего цикла
				foreach($arr as $line) {
					$key=''; $val=''; $flag=true;#Флаг внутреннего цикла
					if (is_array($line)) {#Если $line вложенный массив, тогда
						foreach($line as $info) {#Выковыриваем переменные по одной
							if ($flag) {$key=$info;} else {$val=$info;}
							$flag=! $flag;#Инвертируем флаг для записи во вторую переменную
						}#end foreach $line
					}#end if is_array
					else {#Попытаемся достать данные из первого массива
						if ($ext_flag) {$ext_key=$line;} else {$ext_val=$line;}
						$ext_flag=! $ext_flag;#Инвертируем флаг для записи во вторую переменную
					}#end else
					
					#Теперь на выходе получаем пару key:value
					$key=(!empty($key)) ? $key: $ext_key;
					if ($key[0]!=':') {$key=':'.$key;} #Если вдруг забыл : перед именем параметра
					$val=(!empty($val)) ? $val: $ext_val;
					$STH->bindValue($key, $val);#Добавим параметры в запрос
				}#end foreach $arr
			}#end if !empty $arr
		
			$res=$STH->execute();#Выполняем запрос
			
			if (empty($res)) {
				msg("Query: Error in sql=$select", $dbg);
				if ($dbg) { show_var($arr, 'arr');}
				$STH=FALSE;
			}#end if
			
			unset($DBH); # $DBH = NULL; #Освобождаем дескриптор подключения к базе
			
			return $STH; #Возвращаем ссылку на результат запроса
		}#end if !empty $select
		else {message('query:EMPTY select !!!'); die();} #Матюгаемся на пустой запрос и упадаем
		
	}#end func query



	#Функция выполняет запрос к таблице $table с условием $where
	# упорядоченные по $order с заданными параметрами $bind
	#$query_attr - аттрибуты запроса в массиве вида, например
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY) - делать PDO::Fetch() только вперёд или
	#  array(PDO::ATTR_CURSOR => PDO::CURSOR_SCROLL)  - чтобы получить прокручиваемый курсор
	#  PDO::fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_*)
	function search($table, $where='', $order='', $bind=array(), $query_attr=array() ) {
		if (!empty($table)) {
			$sel="SELECT * FROM [kit_access_request].[dbo].[$table]";
			if (!empty($where)) {$sel.=' WHERE '.$where;}
			if (!empty($order)) {$sel.=' ORDER BY '.$order;}
			$STH=query($sel, $bind, $query_attr);
		} else {message('Search: Имя таблицы не задано !!!'); exit;}
		
		return $STH;
	}#end func search
	
	
	
	
	
	#Функция вставляет в таблицу $table поля $fields='field1,field2...' значения $bind
	#Пример использования:
	#$STH=insert('reg_access', 'user_id,region_id,result,req_date', $bind);
	function insert($table, $fields, $bind) {
		$dbg=FALSE;
		
		#$DBH->lastInsertId(); # Можно узнать последний вставленный IDшник и возвращать его в случае удачи
		
		if (!empty($table)) {
			if (!empty($fields)) {
				$fields_brackets=change_delim('[', $fields, ',', '],[', ']');
				$fields_values=change_delim(':', $fields, ',', ',:', '');
				$query="INSERT INTO [kit_access_request].[dbo].[$table] ($fields_brackets) VALUES ($fields_values)";
				msg("Insert:qry=$query", $dbg);###
				if ($dbg) { show_var($bind, 'bind'); } ###
				$STH=query($query, $bind); # Делаем INSERT
			} else {message("Insert: Поля таблицы '$table' не заданы !!!"); exit;}
		} else {message('Insert: Имя таблицы не задано !!!'); exit;}
		
		return $STH;
	}#end func insert
	
	
	
	
	
	#Функция подготавливает поля текста запроса для функции update
	# На входе  'result,date'
	# На выходе [result]=:result, [req_date]=:req_date
	function prep_fields_for_upd($text) {
		$res='';
		$arr=explode(',', $text);
		foreach($arr as $val) { $res.="[$val]=:$val, "; }
		$res=substr($res, 0, -2); #Отрезаем последние лишние ', '
		
		return $res;
	}#end func prep_fields_for_upd
	
	
	#Функция обновляет в таблице $table поля $fields='field1,field2,...' с условием $where значения $bind
	#Пример использования: update('reg_access', 'result,req_date', 'id=:id', $bind);
	#####################################################################
	#       В массиве $bind названия переменных для бинда :var					#
	#     ДОЛЖНЫ ТОЧНО СООТВЕТСТВОВАТЬ НАЗВАНИЯМ ПОЛЕЙ ТАБЛИЦЫ !!!			#
	#####################################################################
	function update($table, $fields, $where, $bind) {
		$dbg=FALSE; $module='Update';
		
		if (empty($table)) {msg("$module: Имя таблицы не задано !!!", $dbg); exit;}
		if (empty($fields)) {msg("$module: Поля таблицы $table не заданы !!!", $dbg); exit;}
		if (empty($where)) {msg("$module: Условие WHERE для таблицы '$table' не задано !!!", $dbg); exit;}
		$upd_fields=prep_fields_for_upd($fields); #'result,req_date' => '[result]=:result, [req_date]=:req_date'
		$sel="UPDATE [kit_access_request].[dbo].[$table] SET $upd_fields WHERE $where";
		$STH=query($sel, $bind); # Делаем UPDATE
		msg("$module:qry=$sel", $dbg, 'magenta');
		if ($dbg) {show_var($bind, "$module:bind");}
		if ($dbg) {show_var($STH, "$module:STH");}
		
		return $STH;
	}#end func update
	
	
	
	
	
	#Функция удаляет из таблицы $table строки
	#соответствующие условию $where с параметрами $bind
	function delete($table, $where, $bind) {
		$dbg=FALSE; $module='Delete';
		
		if (empty($table)) {msg("$module: Имя таблицы не задано !!!", $dbg); exit;}
		if (empty($where)) {msg("$module: Условие WHERE для таблицы '$table' не задано !!!", $dbg); exit;}
		$sel="DELETE FROM [kit_access_request].[dbo].[$table] WHERE $where;";
		if ($dbg) {show_var($sel, "$module:sel"); show_var($bind, "$module:bind");}###
		$STH=query($sel, $bind);
		
		return $STH;
	}#end func delete
	
	
	
	
	
	
	#Заполним массив $region из таблицы regions
	# для быстрого асинхронного доступа из load_billing.php
	function init_region() {
		$dbg=FALSE; $module='Init_region';
		$region=array();
		
		$STH=search('regions', 'enable=1', 'name');
		if ($STH->rowCount() == NULL) {# Надо как то избавиться от проверки через rowCount
			msg("$module: No regions found !!!", $dbg);###
		} else {
			while ($row = $STH->fetch()) {
				$ID=$row['ID'];
				$name=trim($row['name']);
				$name_rp=trim($row['name_rp']);
				$url=trim($row['url']);
				
				# Это нужно для тех биллингов, где РНР скомпилирован без поддержки libcurl (В частности Н.Новгород)
				# Поэтому они не воспринимают названия констант, им нужно передавать их значения
				$str=trim($row['auth_type']);# Получаем строковое название константы РНР
				$auth_type=$str;
				if (!empty($str) AND 'CURLAUTH_BASIC'!=$str ) { eval("\$auth_type = $str;"); }# Вычисляем её INTEGER значение
				#show_var($auth_type, "auth_type=$str");
				
				$tpl=trim($row['tpl']);
				$region[$ID]=array($name, $name_rp, $url, $auth_type, $tpl);
			}#end while
		}#end if
	
		return $region;
	}#end func init_region
	
	
	
	#$region=init_region();
	
	
	
		#Функция в цикле выводит список регионов из таблицы regions для выбора
	function show_regions() {
		$STH=search('regions', 'enable=1', 'name');
		if ($STH->rowCount() == NULL) {# Надо как то избавиться от проверки через rowCount
			message('Show_regions: No regions found !!!');
		} else {
			echo "\t<TABLE>\r\n\t\t<TR><TH>Регион</TH><TH>Тип</TH></TR>\r\n";
			while ($row = $STH->fetch()) {
				$ID=$row['ID']; $name=trim($row['name']); $tpl=trim($row['tpl']);
				$bgcolor='';
				if ('RK'==$tpl) {$bgcolor=" bgcolor='lime'";}
				#echo "\t\t<TR><TD><input type='checkbox' name='reg[$ID]' id='reg[$ID]' onclick='alert(\"OK \"+this.checked);'>$ID</TD><TD>$name</TD><TD $bgcolor>$tpl</TD></TR>\r\n";
				#echo "\t\t<TR><TD><input type='checkbox' name='reg[$ID]' id='reg[$ID]' onclick='alert(\'OK \'+this.id+\'\'+this.value);'>$ID</TD><TD>$name</TD><TD $bgcolor>$tpl</TD></TR>\r\n";
				#echo "\t\t<TR><TD><input type='checkbox' name='reg[$ID]' id='reg[$ID]' checked>$ID</TD><TD>$name</TD></TR>\r\n";
				#echo "\t\t<TR><TD><input type='checkbox' name='reg[$ID]' id='reg[$ID]'>$ID</TD><TD>$name</TD></TR>\r\n";
				#echo "\t\t<TR><TD><label><input type='checkbox' border='0' name='reg[$ID]' id='reg[$ID]' checked>$ID $name</label></TD><TD $bgcolor>$tpl</TD></TR>\r\n";
				echo "\t\t<TR><TD nowrap>";
				echo "\t\t<label>";
				#echo "\t\t\t<input type='checkbox' name='reg[$ID]' id='reg[$ID]' style='float: left;' checked>";
				echo "\t\t\t<input type='checkbox' name='reg[$ID]' id='reg[$ID]' style='float: left;'>";
				echo "\t\t\t<div style='padding: 3px; float: left;'>$ID $name</div>";
				echo "\t\t</label></TD><TD$bgcolor>$tpl</TD></TR>\r\n";
			}#end while
			echo "\t</TABLE>\r\n";
		}#end if
	}#end func show_regions
	
	
	
	
	
	
	
	
	
	
	
	
	
	# Функция добавляет юзера в users из LDAP по логину
	#  если он там не существует или обновляет если есть
	#  и возвращает его IDшник
	function add_user_from_ldap($login) {
		$user_id=0;
		$login=cut_domain($login);

		#Получаем данные юзера из LDAP
		$ldap_info=ldap_user($login);
		$cn=$ldap_info[0]['cn'][0];
		$mail=$ldap_info[0]['mail'][0];
		$title=$ldap_info[0]['title'][0];
		$department=$ldap_info[0]['department'][0];
		$company=$ldap_info[0]['company'][0];
		$physicaldeliveryofficename=$ldap_info[0]['physicaldeliveryofficename'][0];
		$telephonenumber=$ldap_info[0]['telephonenumber'][0];
		$mobile=$ldap_info[0]['mobile'][0];
		$create_time=date("Y-m-d H:i:s");
		$update_time=date("Y-m-d H:i:s");
	
		$bind=array(array(':login', $login),
					array(':cn', $cn),
					array(':mail', $mail),
					array(':title', $title),
					array(':department', $department),
					array(':company', $company),
					array(':physicaldeliveryofficename', $physicaldeliveryofficename),
					array(':telephonenumber', $telephonenumber),
					array(':mobile', $mobile));
	
		$fields='login,cn,mail,title,department,company,physicaldeliveryofficename,telephonenumber,mobile';
		
		$user_id=get_userID_by($login); #Если не найден, то вернёт 0
		if ($user_id!=0) {# Если юзер найден
			$fields.=',update_time';
			$result = array_merge($bind, array(':update_time', $update_time));#user_id бы ещё в bind добавить
			$STH=update('users', $fields, "id=$user_id", $result); # Тогда надо обновить запись по IDшнику
		} else { # А если НЕ найден
			$fields.=',create_time,update_time';
			$result = array_merge($bind, array(':create_time', $create_time), array(':update_time', $update_time));
			$STH=insert('users', $fields, $result); #Добавляем юзера в таблицу
			$user_id=get_userID_by($login);# Узнаём IDшник только что добавленного юзера
		}#end if
		
		return $user_id;
	}#end func add_user_from_ldap
	
	
	
	
	
	#Функция возвращает массив со списком логинов и ID юзеров,
	# которых добавил в табличку user_box супервизор($login_sv)
	# для отправки запроса на доступ к рег.биллингам
	function get_users_choosed_by($login_sv){
		$login_sv=cut_domain($login_sv);
		
		$sel='SELECT ku.login, ub.* ';
		$sel.='FROM [kit_access_request].[dbo].[users] ku, [kit_access_request].[dbo].[user_box] ub ';
		$sel.='WHERE ub.id_user=ku.id AND UPPER(ub.login_sv)=:login_sv';
		$STH=query($sel, array(':login_sv', strtoupper($login_sv)));
		$arr_user_box=array();
		while($row=$STH->fetch()) {
			$arr_user_box[$row['login']]=array($row['id'], $row['id_user'], $row['login_sv'], $row['mail_sv'], $row['enable']);#Завяжем всё на логин
		}#end while
		
		return $arr_user_box;
	}#end func get_users_choosed_by
	
	
	
	
	
	#Функция возвращает массив инфы о результатах выполнения
	# запросов на доступ юзера к рег.биллингам
	function get_reg_access_info($login) {
		$login=cut_domain($login);
		
		$sel="SELECT * FROM [kit_access_request].[dbo].[regions] reg
				LEFT OUTER JOIN
							[kit_access_request].[dbo].[reg_access] ra
				ON reg.ID=ra.region_id
				AND user_id=(SELECT id FROM [kit_access_request].[dbo].[users] WHERE login=:login)
			ORDER BY name";
		$bind=array(':login', $login);
		$STH=query($sel, $bind);
		
		$access_info=array();# Обнуляем массив инфы по запросам в регионы
		while ($row = $STH->fetch()) { # Заполняем массив access_info
			$ID=$row['ID']; $user_id=trim($row['user_id']); $result=$row['result']; $req_date=$row['req_date'];
			$access_info[$ID]=array($user_id, $result, $req_date);
		}# end while
		
		return $access_info;
	}#end func get_reg_access_info
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	#Функция рассылает админам выбранного региона на e-mail запрос на доступ на нескольких юзеров
	# $sender - логин отправителя
	# $logins - строка логинов разделённых запятыми: login1,login2,...
	# $reg_ID - код региона, куда отправлять
	# $region - массив регионов
	function send_mail($sender, $sender_pass, $logins, $reg_ID, $region, $test=TRUE) { # Отправлять по умолчанию на тестовые e-mail'ы
		$module='send_mail';
		$res=TRUE;		#Результат выполнения функции
		$log=TRUE;		# Выводить отладочные сообщения
		$debug=$test;	# Отправлять на тестовые e-mail'ы
		
		$sender=cut_domain($sender);
		if (empty($sender)) {message("$module:Отправитель не задан"); return FALSE;}#Упадаем
		if (empty($sender_pass)) {message("$module:Пароль отправителя не задан"); return FALSE;}#Упадаем
		if (empty($logins)) {message("$module:Не заданы пользователи для запроса"); return FALSE;}#Упадаем
		
		$smtp_user=$sender;				# $smtp_user должен быть тот, кто отправляет запрос
		$smtp_pass=$sender_pass;	# И он должен вводить свои доменные логин и пароль, чтобы ответы приходили ему на почту
		
		##############################################################
		if ($debug AND $smtp_user=='AS.Loginov') { #Чтобы не отправлять тестовые письма самому себе
			$smtp_user=smtp_user(); $smtp_pass=smtp_pass(); #будем отправлять почту от Литоха
		}#end if ##############################################################
		if ($log) {echo "New message send from: '<b>$smtp_user</b>'<br>\r\n";}# ОТЛАДКА ###
		
		# Узнаём по $reg_ID название региона и шаблон
		$reg_name   =$region[$reg_ID][0];	# Имя региона
		$reg_name_rp=$region[$reg_ID][1];	# Имя региона в РОДИТЕЛЬНОМ падеже
		$tpl        =$region[$reg_ID][4];	# Имя файла шаблона e-maila
		
		# Добываем по   $reg_ID   e-mail'ы админов из reg_admins
		$STH=search('reg_admins', 'region_id=:region_id', '', array(':region_id',$reg_ID));
		if ($STH->rowCount() == NULL) {# Надо как то избавиться от проверки через rowCount
			message("$module: No region admins found !!! Nowhere to send...");
			return FALSE;#Упадаем
		} else {
			$emails=''; $copies='';
			while ($row = $STH->fetch()) {
				#$fio=$row['fio'];
				$email=trim($row['email']);
				$email2=trim($row['email2']);
				$v_kopiyu=trim($row['v_kopiyu']);
				$emails.=(empty($email)? '': $email.',');
				$emails.=(empty($email2)? '': $email2.',');
				if (preg_match('/'.$v_kopiyu.'/', $copies)) {$v_kopiyu='';} # Если такой адрес уже есть, обнуляем
				if (!empty($v_kopiyu)) {$copies.=$v_kopiyu.',';}			# Добавляем адрес "В копию" если не пустой - Так более читаемо
			}#end while
			$emails=substr($emails, 0, -1);#Убираем последнюю лишнюю точку с запятой
		}#end if
		
		$copies.='_ekc_it@skylink.ru'; #Добавляем в копию _ЕКЦ_ИТ для контроля
		
		$mes = file_get_contents ('./tpl/'.$tpl.'.txt');# загружаем содержимое файла шаблона в строку
		
		$arr_logins=explode(',', $logins); #Раскукожим $logins, вдруг их несколько пришло
		$rows=''; $familii='';
		foreach($arr_logins as $login) { #Пробегаемся по всем присланным логинам
			$login=cut_domain($login);
			
			# Хорошо бы ФИО передавать вместе с логинами массивом, а не выковыривать тут в цикле
			$user_fio=get_user_fio($login); #Узнаём ФИО юзера по логину
			
			$email="$login@SKYLINK.RU";
			if (78==$reg_ID) {$email="$sender@SKYLINK.RU";}# Если Питер, то меняем e-mail юзера на отправителя
			
			# И сразу готовим строчки для таблицы запроса
			$rows.="<tr bgcolor=#ffc000><td><b>$user_fio</b></td><td>VLADIMIR\\$login</td><td>$email</td>\r\n</tr>";
			
			$arr_user_fio=explode(' ', $user_fio);#Разбиваем ФИО на Фамилия Имя и Отчество
			$familii.=$arr_user_fio[0].', '; #Добавляем фамилии через запятую для темы
		}# end foreach
		$familii=substr($familii, 0, -2);#Убираем последнюю лишнюю запятую и пробел
		
		$ewbis=''; if ($tpl=='RK') {$ewbis=' EW BiS';}
		$sub = "Запрос доступа на чтение/запись к биллингу$ewbis $reg_name_rp - $familii";
		
		if ($debug) {$sub = 'ТЕСТ:'.$sub;} ###
		if ($log) {echo "<b>Тема</b>:$sub<br>\r\n";}# ОТЛАДКА ###
		
		$mes = str_replace('[rows]', $rows, $mes);# заменяем в шаблоне метку вида [label] на её текст
		
		
		#Готовим подпись сообщения
		$ldap_info = ldap_user($smtp_user); # В подписи будет ФИО того, от кого отправляем письмо
		$sender_fio=$ldap_info[0]['cn'][0];#ФИО get_user_fio($sender);
		$sender_title=$ldap_info[0]['title'][0];#Должность
		$sender_department=$ldap_info[0]['department'][0];# Подразделение
		$sender_telephonenumber=$ldap_info[0]['telephonenumber'][0];# Телефон
		$mobile=$ldap_info[0]['mobile'][0];
		/*
		$signature="\t<table style='font: normal 10pt Arial; color: #000000;' border='0'>
		<tr>
			<td><img src=\"cid:part1.06080204.09060405@ekc.skylink.ru\" style=\"margin: 10px;\"></td>
			<td><font style='font: 12pt;'>$sender_fio</font><br>
				$sender_title<br>
				$sender_department<br>
				Единый контакт-центр<br>
				Макрорегиональный филиал \"Центр\" ОАО \"Ростелеком\"<br>
				Тел.: $sender_telephonenumber<br>
			</td>
		</tr>
	</table>";
	*/
	
		$signature="\t<table style='font: normal 10pt Arial; color: #000000;' border='0'>
		<tr>
			<td><font style='font: 12pt;'>$sender_fio<br>
				$sender_title</font><br>
				$sender_department<br>
				Единый контакт-центр<br>
				Филиал во Владимирской и Ивановской<br>
				областях ЗАО «РТ-Мобайл»<br>
				Тел.: $sender_telephonenumber<br>
				Моб.: $mobile<br>
				Факс: +7 (4922) 37-67-12
			</td>
		</tr>
	</table>";
		$mes = str_replace('[signature]', $signature, $mes);
		
		if ($log) {echo "mess=$mes<br>\r\n";}# ОТЛАДКА ###
		
		#Отправляем письмо через SMTP сервер
		$fp = fsockopen("10.60.7.23", "25", $errno, $errstr, 30);#Цепляемся к SMTP серверу
		if (!$fp) {
			message("$module: $errstr ($errno)");
			$res=FALSE; # НЕ return FALSE - потому что нужно закрыть дескриптор
		} else {
			fsock_puts_gets($fp, "HELO srv-vld-lamp\r\n");
			fsock_puts_gets($fp, "AUTH LOGIN\r\n");
			fsock_puts_gets($fp, base64_encode($smtp_user)."\r\n");
			fsock_puts_gets($fp, base64_encode($smtp_pass)."\r\n");
			fsock_puts_gets($fp, "MAIL FROM: <$smtp_user@skylink.ru>\r\n");
			
			$addr_list='';
			if ($debug) { ##############################################################
				#$addr_list='Dorbah@Rambler.ru,AS.Loginov@SkyLink.ru';	#ОТЛАДКА
				$addr_list='AS.Loginov@SkyLink.ru';	#ОТЛАДКА
				##############################################################
			} else {$addr_list=$emails.','.$copies;} #Адреса Кому разбить на массив строк
			
			if ($log) {echo "addr_list='$addr_list'<br>\r\n";}# ОТЛАДКА ###
			$arr_emails=explode(',', $addr_list);
			foreach ($arr_emails as $val){
				fsock_puts_gets($fp, "RCPT TO: <$val> \r\n");
			}#end foreach
			
			
			fsock_puts_gets($fp, "DATA\r\n");
			fputs($fp, "from: $smtp_user@skylink.ru\r\n");
			$new_emails=change_delim('<', $emails, ',', '>,<', '>');#Раскукоживаем и закукоживаем
			fputs($fp, "to: $new_emails\r\n");
			$new_copies=change_delim('<', $copies, ',', '>,<', '>');#Раскукоживаем и закукоживаем
			fputs($fp, "cc: $new_copies\r\n");
			fputs($fp, "subject: $sub\r\n");
			fputs($fp, "MIME-Version: 1.0\r\n");
			fputs($fp, "Content-Type: multipart/mixed; boundary=\"----------A4D921C2D10D7DB\"\r\n\r\n");
			#Отправка текстовой части сообщения
			fputs($fp, "------------A4D921C2D10D7DB\r\n");
			fputs($fp, "Content-Type: text/html; charset=utf-8\r\n");
			fputs($fp, "Content-Transfer-Encoding: 8bit\r\n\r\n");
			fputs($fp, "$mes\r\n");
			fputs($fp, "\r\n");
			fputs($fp, "------------A4D921C2D10D7DB\r\n");
			#Отправка картинки для подписи
			$fl = fopen("img/logo.jpg", "rb");
			fputs($fp, "Content-Type: image/jpeg; name=\"logo.jpg\"\r\n");
			fputs($fp, "Content-transfer-encoding: base64\r\n");
			fputs($fp, "Content-ID: <part1.06080204.09060405@ekc.skylink.ru>\r\n");
			fputs($fp, "Content-Disposition: inline; filename=\"logo.jpg\"\r\n\r\n");
			fputs($fp, chunk_split(base64_encode(fread($fl, filesize("img/logo.jpg")))));
			fputs($fp, "\r\n");
			fclose($fl);
				
			fsock_puts_gets($fp, ".\r\n");
			fsock_puts_gets($fp, "QUIT");
			
			#fclose($fp);
			
		} #end else if (!$fp)
		
		fclose($fp);# В любом случае надо освободить дескриптор
		
		return $res; #
	}#end func send_mail
	
	
	
	
	
	
	#Функция зашифровывает с помощью пароля $passw текст $txt
	#  $val='0x'.bin2hex(EncryptByPassPhrase($domain_user_pass, $harmless_pass) ); # Если пароль, то зашифруем
	function EncryptByPassPhrase($passw, $txt) {
		$dbg=FALSE; $module='EncryptByPassPhrase';
		
		$passw=htmlspecialchars($passw, ENT_COMPAT | ENT_QUOTES | ENT_HTML401, 'UTF-8');
		
		$sel="SELECT EncryptByPassPhrase(:passw, :txt) AS ENCRYPT";
		msg("$module: sel=$sel", $dbg); ###
		$bind=array(array(':txt', $txt), array(':passw', $passw));
		if ($dbg) { show_var($bind, 'bind'); } ###
		$STH=query($sel, $bind);
		if ($row = $STH->fetch()) {
			$hex=bin2hex($row['0']);  msg("$module: bin2hex=$hex", $dbg); ###
		} else { message("$module: Encrypt error !!!"); }
		
		$str='0x'.$hex;
		
		return $str;
	}#end func EncryptByPassPhrase
	
	
	#Функция расшифровывает текст $bin с помощью пароля $passw
	# Портит русские буквы при расшифровке ???
	function DecryptByPassPhrase($bin, $passw) {# ЧЕМ ЧТО
		$dbg=FALSE; $module='DecryptByPassPhrase';
		
		$hex=$bin; msg("$module: arg bin='$bin'", $dbg);###
		
		#Если пришли двоичные данные, кодим их в hex
		if (substr($bin, 0, 2)!='0x') {
			$hex='0x'.bin2hex($bin);
			msg("$module: var hex='$hex'", $dbg);
		}#end if
		
		# Тут надо как то экранировать \ ' и " в $passw
		# Потому что запрос ломается  | ENT_COMPAT | ENT_HTML401
		$passw=htmlspecialchars($passw, ENT_QUOTES, 'UTF-8');
		#$sel="SELECT DecryptByPassPhrase(\"$passw\", $hex) AS DECRYPT";
		$sel="SELECT CAST(  ( SELECT DecryptByPassPhrase('$passw', $hex)  ) as varchar(max) ) AS PHRASE";
		msg("$module: sel='$sel'", $dbg); ###
		$STH=query($sel);
		if ($row = $STH->fetch()) {
			$bin=$row['0'];
			#if ($dbg) { show_var($bin, "res bin"); }
			msg("$module: res bin='$bin'", $dbg);
		} else { message("$module: DEcrypt error !!!"); }
		
		return $bin;
	}#end func DecryptByPassPhrase
	
	
	
	
	
	
	
	
	
	
	
	
	
	#Функция ставит признак занятости строки запроса на перешифровку
	function SetBusyFlagBy($line_id, $busy) {
		$dbg=FALSE; $module='SetBusyFlagBy';
		
		if (empty($line_id)) {msg("$module: line_id NOT SET, nothing to update", $dbg); return FALSE;}
		$STH_upd=update('queries4reencrypt', 'busy', 'id=:id', array( array(':id', $line_id), array(':busy', $busy) ) );#Ставим признак занятости строки
		if ($STH_upd) {
			msg("$module: Busy flag in line $line_id set to $busy", $dbg, 'lime');
		} else {
			msg("$module: Busy flag of line $line_id NOT UPDATED", $dbg);
		}
		
		return TRUE;
	}#end func SetBusyFlagBy
	
	
	#Удаляем строку $line_id из таблицы $table
	function DelLine($table, $line_id) {
		$dbg=FALSE; $module='DelLine';
		
		$STH=delete($table, 'id=:id', array(':id', $line_id));		#Удалим строку $line_id из таблицы $table
		if ($STH) { msg("$module: Line '$line_id' deleted from '$table'", $dbg, pink); } else {msg("$module: Line '$line_id' NOT DELETED from '$table'", $dbg);} ###
		
		return $STH;
	}#end func DelLine
	
	
	# Функция шифрует новый ДОМпароль юзера с $user_id
	# MasterKey'ем и записывает его в uniq_id
	function SetEncryptedUniqID($user_id, $masterkey, $new_domain_pass) {
		$dbg=FALSE; $module='SetEncryptedUniqID';
		msg("$module START", $dbg);
		
		$update_time=date("Y-m-d H:i:s");
		msg("$module: update_time=$update_time", $dbg, green);
		$uniq_id=EncryptByPassPhrase($masterkey, $new_domain_pass);# ЧЕМ ЧТО #Просто шифруем его новый ДОМпароль
		$bind=array(array(':update_time', $update_time), array(':uniq_id', $uniq_id), array(':id', $user_id) );
		if ($dbg) { show_var($bind, 'bind'); }
		$STH=update('users','update_time,uniq_id', 'id=:id', $bind);#Пишем ДОМпароль в uniq_id
		
		if (empty($STH)) {message("$module: Update ERROR !!!");}
		
		msg("$module END", $dbg, 'lime');
		return $STH;
	}#end if SetEncryptedUniqID
	
	
	# Функция ищет юзера по user_id и возвращает его uniq_id
	# или FALSE если не найдёт
	function Get_uniq_id_by($user_id) {
		$dbg=FALSE; $module='Get_uniq_id_by'; $uniq_id=FALSE;
		msg("$module: START", $dbg);
		
		$STH_usrs=search('users', 'id=:id', '', array(':id', $user_id) ); #У этого юзера есть зашифрованный ДОМпароль в uniq_id ?
		if ($row_usrs=$STH_usrs->fetch()) {
			$user_name=$row_usrs['login'];
			msg("$module: User '$user_name' found", $dbg, 'blue'); ### Если нашли юзера
			$uniq_id=$row_usrs['uniq_id']; #Берём старый ДОМпароль юзера
		}#end if row_usrs
		
		msg("$module: uniq_id='$uniq_id'", $dbg, 'green');###
		
		msg("$module: END", $dbg, 'lime');
		return $uniq_id;
	}#end func Get_uniq_id_by
	
	
	# Функция ищет юзера по login и возвращает его uniq_id
	# или FALSE если не найдёт
	function Get_uniq_id_by_login($login) {
		$dbg=FALSE; $module='Get_uniq_id_by_login'; $uniq_id=FALSE;
		msg("$module: START", $dbg);
		
		$STH_usrs=search('users', 'login=:login', '', array(':login', $login) ); #У этого юзера есть зашифрованный ДОМпароль в uniq_id ?
		if ($row_usrs=$STH_usrs->fetch()) {
			msg("$module: User '$login' with ID='".$row_usrs['id']."' found", $dbg, 'blue'); ### Если нашли юзера
			$uniq_id=$row_usrs['uniq_id']; #Берём старый ДОМпароль юзера
		}#end if row_usrs
		
		msg("$module: uniq_id='$uniq_id'", $dbg, 'green');###
		
		msg("$module: END", $dbg, 'lime');
		return $uniq_id;
	}#end func Get_uniq_id_by_login
	
	
	
	
	# Функция создаёт запрос на перешифровку, если ещё такого нету
	# Параметры:
	# user_id - ID юзера из users
	# res_type_id - ID типа ресурса из res_type
	# new_pass - новый пароль, который нужно зашифровать/перешифровать
	function CreateQuery4Reencrypt($user_id, $res_type_id, $new_pass) {
		$dbg=FALSE; $module='CreateQuery4Reencrypt';
		msg("$module START", $dbg);
		
		######################################################################################
		#  В дальнейшем желательно сделать отправку супервизорам сообщения со ссылкой вида:
		# http://srv-vld-lamp/test/access2/project/approve.php?lg=IO.Familia&hash=bcda4ddc23aeb5ace687f1e4a4acebd2
		# Этот хэш будет расшифровывать новый пароль юзера и он не будет светиться в открытом виде в базе
		######################################################################################
		
		#$new_pass=make_harmless($new_pass);
		$bind=array( array(':user_id', $user_id), array(':res_type_id', $res_type_id) );
		$STH_qry=search('queries4reencrypt', 'user_id=:user_id AND res_type_id=:res_type_id', '', $bind);
		$row_qry = $STH_qry->fetch();
		if (empty($row_qry)) {# Если не нашли
			$bind=array( array(':user_id', $user_id), array(':res_type_id', $res_type_id), array(':new_pass', $new_pass) );
			$STH_ins=insert('queries4reencrypt', 'user_id,res_type_id,new_pass', $bind); # Создаём запрос за зашифровку
			msg("$module: Insert query for UserID=$user_id res=$res_type_id pass=$new_pass", $dbg, 'lime'); ###
		} else {
			msg("$module: Query for UserID=$user_id res=$res_type_id pass=$new_pass already EXISTS", $dbg); ### Уже есть
		}#end if
		
		msg("$module END", $dbg, 'lime');
	}#end func CreateQuery4Reencrypt
	
	
	
	
	
	
	# Функция достаёт и расшифровывает ДОМпаролем MasterKey пароль админа
	# Если не находит MasterKey, создаёт запрос на его зашифровку
	function GetMasterKey($user_id, $pass) {
		$dbg=FALSE; $masterkey=''; $module='GetMasterKey';
		msg("$module START", $dbg);###
		
		$STH=search('resources', 'user_id=:user_id AND res_type_id=9', '', array(':user_id', $user_id) ); #Ищем в ресурсах MasterKey админа
		msg("$module search performed", $dbg);###
		
		$row = $STH->fetch();
		if (empty($row)) { # masterkey НЕ НАЙДЕН
			msg("$module empty(row)", $dbg);###
			
			$user_name=get_login_by_ID($user_id);
			if (!empty($user_name)) {#Тут проверяем, есть в базе юзер с таким $user_id
				msg("$module empty(user_name)", $dbg);###
				
				CreateQuery4Reencrypt($user_id, 9, $pass); # Делаем запрос на ЗА_шифровку ему MasterKey
				msg("$module CreateQuery4Reencrypt", $dbg);###
				
			}#end if
		} else { # NOT empty($row) masterkey НАЙДЕН
			msg("$module masterkey НАЙДЕН", $dbg);###
			$encrypted_pass=$row['pass'];
			
			if ($dbg) {show_var($encrypted_pass, '$enc_pass');}###
			
			$masterkey=DecryptByPassPhrase($encrypted_pass, $pass); #Пытаемся расшифровать MasterKey ДОМпаролем
			
			if ($dbg) {show_var($masterkey, '$masterkey');}###
			
			msg("$module DecryptByPassPhrase", $dbg);###
		}#end else
		msg("$module DecryptByPassPhrase", $dbg);###
		
		msg("$module END", $dbg, 'lime');
		
		return $masterkey;
	}#end func GetMasterKey
	
	
	#Функция зашифровывает MasterKey ДОМпаролем юзера
	function EncryptMasterKeyByDomPass($user_id, $masterkey, $domain_pass) {
		$dbg=FALSE; $module='EncryptMasterKeyByDomPass';
		$res=FALSE;
		msg("$module START", $dbg);
		
		msg("$module: Try to encrypt MasterKey by DomPass '$domain_pass' for UserID='$user_id'", $dbg, 'blue');
		$encr_masterkey=EncryptByPassPhrase($domain_pass, $masterkey);#Зашифруем MasterKey ДОМпаролем юзера ЧЕМ ЧТО
		#$encr_hex_masterkey='0x'.bin2hex($encr_masterkey);#Теперь это делается в самой функции шифрования
		
		if ($dbg) { show_var($encr_masterkey, 'encr_masterkey'); }
		
		if ( empty($encr_masterkey) ) {
			msg("$module: ERROR encrypting MasterKey by DomPass '$domain_pass' for UserID='$user_id'", $dbg);
		}
		
		# Записываем/обновляем шифрованный MasterKey в ресурсы
		$STH_srch=search('resources', 'user_id=:user_id AND res_type_id=9', '', array(':user_id', $user_id) ); # Только сначала поглядим, есть он уже или нет
		if ($row_res=$STH_srch->fetch()) { # Если у этого юзера уже есть MasterKey пароль, обновляем
			$bind=array(  array(':pass', $encr_masterkey), array(':user_id', $user_id), array(':comment', 'MK_auto_update') );
			$STH_upd=update('resources','pass,comment','user_id=:user_id', $bind);
			if ( FALSE!==$STH_upd ) {
				msg("$module: UPDATE UserID=$user_id MasterKey зашифровали c '$domain_pass' в '$encr_masterkey'", $dbg, 'blue'); ###
				$res=TRUE;
			} else {
				message("$module: ERROR updating MasterKey for UserID='$user_id' !!!");
			}
		} else { # иначе добавляем строку
			$login=get_login_by_ID($user_id);#Получаем логин юзера
			$bind=array( array(':user_id', $user_id), array(':res_type_id', 9), array(':login', $login),
									array(':pass', $encr_masterkey), array(':comment', 'MK_auto_insert') );
			$STH_ins=insert('resources', 'user_id,res_type_id,login,pass,comment', $bind); # Создаём запрос за зашифровку
			if ($STH_ins) {
				msg("$module: INSERT UserID='$user_id' Добавили и зашифровали MasterKey c '$domain_pass' в '$encr_masterkey'", $dbg, 'lime'); ###
				$res=TRUE;
			} else {
				message("$module: ERROR inserting MasterKey for UserID='$user_id' !!!");
			}
		}#end if
		msg("$module END", $dbg, 'lime');
		
		return $res;
	}#end func EncryptMasterKeyByDomPass
	
	
	
	
	# Функция удаляет все РЕГпароли юзера по его user_id
	# Это нужно, если невозможно расшифровать ДОМпароль юзера или он пустой 
	function DelAllRegPassesBy($user_id) {
		$dbg=FALSE; $module='DelAllRegPassesBy';
		
		#Удаляем все РЕГпароли этого юзера (кроме 8 и 9 типа ДОМпароль и MasterKey)
		$STH=delete('resources', 'user_id=:user_id AND res_type_id NOT IN (8,9)', array(':user_id', $user_id));
		msg("$module: Del ALL RegPasses of '$user_id'", $dbg);###
		
	}#end func DelAllRegPassesBy
	
	
	# Функция удаляет все запросы на перешифровку юзера по его user_id
	# Это нужно, если невозможно расшифровать ДОМпароль юзера или он пустой 
	function DelAllQrys4ReEncryptBy($user_id) {
		$dbg=FALSE; $module='DelAllQrys4ReEncryptBy';
		
		#Удаляем все запросы на перешифровку РЕГпаролей этого юзера (кроме 8 и 9 типа ДОМпароль и MasterKey)
		msg("$module: Del ALL queries for ReEncrypt '$user_id'", $dbg, pink);###
		$STH=delete('queries4reencrypt', 'user_id=:user_id AND res_type_id NOT IN (8,9)', array(':user_id', $user_id));
		
	}#end func DelAllQrys4ReEncryptBy
	
	
	# Функция расшифровки старого зашифрованного РЕГпароля $encr_res_pass
	# юзера с $user_id расшифрованным старым ДОМпаролем $decr_old_dom_pass
	# и зашифровка РЕГпароля новым ДОМпаролем $new_domain_pass
	# Параметры:	user_id			- ID юзера из users
	#							res_type_id	- ID типа ресурса из res_type
	# Вынесено в функции для усиления абстракции
	function ReCryptResPass($user_id, $res_type_id, $decr_old_dom_pass, $new_domain_pass) {
		$dbg=FALSE; $res=FALSE; $module='ReCryptResPass';
		msg("$module START", $dbg);
		msg("$module user_id=$user_id res_type_id=$res_type_id decr_old_dom_pass=$decr_old_dom_pass new_domain_pass=$new_domain_pass", $dbg, 'green');
		
		$bind=array( array(':user_id', $user_id), array(':res_type_id', $res_type_id) );
		$STH_res=search('resources', 'user_id=:user_id AND res_type_id=:res_type_id', '', $bind); #Ищем РЕГпароль юзера от ресурса res_type_id
		if ($row_res=$STH_res->fetch()) { #Нашли его РЕГпароль
			$encr_res_pass=$row_res['pass']; #Получим старый шифрованный РЕГпароль
			$res_line_id=$row_res['id']; #IDшник строки нужен для апдейта
			msg("$module: Найден пароль в строке $res_line_id от res=$res_type_id", $dbg, pink);###
			$dec_res_pass=DecryptByPassPhrase($encr_res_pass, $decr_old_dom_pass); #Расшифруем РЕГпароль с помощью старого ДОМпароля
			if ($dbg) {show_var($dec_res_pass, "$module: dec_res_pass");}###
			
			if (empty($dec_res_pass)) {#Если РЕГпароль НЕ расшифровался
				msg("$module: Пароль '$encr_res_pass' от res=$res_type_id НЕ расшифровался и НЕ ПЕРЕшифрован", $dbg); ###
			} else {
				msg("$module: Расшифровали пароль '$dec_res_pass' от res=$res_type_id", $dbg, maroon); ###
				$enc_res_pass=EncryptByPassPhrase($new_domain_pass, $dec_res_pass);#Шифруем новым ДОМпаролем расшифрованный РЕГпароль
				#$hex_enc_reg_pass='0x'.bin2hex($enc_res_pass);
				msg("$module: РЕГпароль '$dec_res_pass' зашифровали c '$new_domain_pass' в '$hex_enc_reg_pass'", $dbg, 'blue');###
				
				$STH=update('resources','pass','id=:id', array( array(':id', $res_line_id), array(':pass', $hex_enc_reg_pass) ) );
				if (empty($STH)) {#Не удалось обновить pass
					msg("$module: Update: Ошибка  записи ! Попробуем записать в другой раз", $dbg);
				} else {
					msg("$module: ResPass '$hex_enc_reg_pass' is UPDATED in line $res_line_id", $dbg, green);###
					$res=TRUE;
				}#end else
			}#end else NOT empty $dec_res_pass
		} else { #end if $row_res
			#Если не нашли РЕГпароль юзера, то надо его зашифровать и добавить
			$login=get_login_by_ID($user_id);#Получаем логин юзера
			msg("$module: НЕ найден РЕГпароль юзера user_id=$user_id '$login' в ресурсах", $dbg); ###
			$enc_res_pass=EncryptByPassPhrase($decr_old_dom_pass, $new_domain_pass);#Шифруем старым ДОМпаролем новый РЕГпароль
			#$hex_enc_reg_pass='0x'.bin2hex($enc_res_pass);
			msg("$module: Зашифровали новый РЕГпароль '$new_domain_pass' c паролем '$decr_old_dom_pass' в '$hex_enc_reg_pass'", $dbg, 'blue');###
			$bind=array( array(':user_id', $user_id), array(':res_type_id', $res_type_id), array(':login', $login), array(':pass', $hex_enc_reg_pass), array(':comment', 'ReCryptResPass auto_insert') );
			if ($dbg) {show_var($bind, 'bind');}###
			$STH=insert('resources', 'user_id,res_type_id,login,pass,comment', $bind); #($table, $fields, $bind)
			if (empty($STH) ) {
				msg("$module: Insert: Ошибка записи ! Попробуем записать в другой раз", $dbg);
			} else {
				msg("$module: Добавили новый РЕГпароль '$new_domain_pass' в ресурсы", $dbg, 'lime');
				$res=TRUE;
			} # end else NOT empty($STH)
		}#end else НЕ НАШЛИ РЕГпароль
		msg("$module END", $dbg, 'lime');
		
		return $res;
	}#end func ReCryptResPass
	
	
	#Функция пытается перешифровать РЕГпароль по запросу юзера
	#Входные данные $user_id, $masterkey, $res_type_id, $new_res_pass, $qrs_line_id
	#Возвращает - TRUE если удалось перешифровать и FALSE если нет
	#Вынесено в функции для усиления абстракции
	function TryToReCryptResPass($user_id, $masterkey, $res_type_id, $new_res_pass, $qrs_line_id) {
		$dbg=FALSE; $res=FALSE; $module='TryToReCryptResPass';
		msg("$module START", $dbg);
		
		$STH_srch=search('queries4reencrypt', 'id=:id AND (busy=0 OR busy IS NULL)', '', array(':id', $qrs_line_id) );
		if ( $row_srch=$STH_srch->fetch() ) {#Если строка ещё не занята
			SetBusyFlagBy($qrs_line_id, 1);#Заблочим строку, чтобы никто её не трогал
		} else {
			msg("$module Line '$qrs_line_id' is BUSY", $dbg); #Матюгаемся
			return FALSE;# Возвращаем ошибку
		}#end else
		
		$old_enc_dom_pass=Get_uniq_id_by($user_id); # Получим СТАРЫЙ шифрованный ДОМпароль юзера
		msg("$module uniq_id=$old_enc_dom_pass", $dbg, 'green');
		
		$decr_old_dom_pass=DecryptByPassPhrase($old_enc_dom_pass, $masterkey); # Пробуем расшифровать MasterKey'ем
		msg("$module DomPass=$decr_old_dom_pass", $dbg, 'lime');
		
		if (empty($decr_old_dom_pass)) {# Если ДОМпароль ПУСТОЙ или НЕ РАСШИФРОВАЛСЯ
			msg("UserID=$user_id - EMPTY domain pass or CAN'T DECRYPT", $dbg, 'pink');
			DelAllRegPassesBy($user_id); #Удаляем все РЕГпароли этого юзера
			DelAllQrys4ReEncryptBy($user_id); #Удаляем все запросы на перешифровку РЕГпаролей этого юзера
		} else { # ДОМпароль НЕ пустой или РАСШИФРОВАЛСЯ - всё карашо
			msg("$module: Decrypted OLD pass='$decr_old_dom_pass'", $dbg, 'magenta');###							#Теперь перешифруем РЕГпароль юзера новым ДОМпаролем
			if ( $res=ReCryptResPass($user_id, $res_type_id, $decr_old_dom_pass, $new_res_pass) ) {	# Проверить эту ветку - зашифровать левым доменным паролем РЕГпароли и зашифровать
				msg("$module: РЕГпароль от res='$res_type_id' для UserID='$user_id' перешифрован", $dbg, 'lime');###	# этот левый пароль в uniq_id masterkey'ем, затем попробовать войти этим юзером
				DelLine('queries4reencrypt', $qrs_line_id);		#Удалим строку заявки										# с его нормальным паролем - оно должно перешифровать всё его доменным паролем
			} else {
				msg("$module: РЕГпароль '$new_res_pass' для '$user_id' НЕ ПЕРЕшифрован", $dbg);
			} #end else
		}# end else ДОМпароль НЕ пустой или РАСШИФРОВАЛСЯ
		
		SetBusyFlagBy($qrs_line_id, 0);#Отпускаем строку заявки обратно В ЛЮБОМ СЛУЧАЕ
		msg("$module END", $dbg, lime);
		
		return $res;
	}#end func TryToReCryptResPass
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		/*
	# Функция пробует расшифровать обратно зашифрованный MasterKey'ем
	# ДОМпароль юзера из uniq_id и затем пытается расшифровать этим ДОМпаролем РЕГпароль
	# Если получилось, то удаляет запрос на перешифровку
	# А если не получилось, то снимает флаг занятости busy в строке $line_id таблицы queries4reencrypt
	function CheckPassRecrypt($user_id, $masterkey, $line_id, $dec_reg_pass) {
		$dbg=FALSE; $module='CheckPassRecrypt';
		
		$uniq_id=Get_uniq_id_by($user_id);#Берём шифрованный ДОМпароль
		if (empty($uniq_id)) {#Если uniq_id пустой
			msg("$module: UniqID of user '$user_id' is EMPTY", $dbg);###
		} else {# Если uniq_id НЕ пустой
			msg("$module: Trying to DEcrypt uniq_id='".bin2hex($uniq_id)."' with '$masterkey'", $dbg, purple);###
			$decr_old_dom_pass=DecryptByPassPhrase($uniq_id, $masterkey);#Расшифровываем MasterKey'ем старый ДОМпароль юзера обратно
			if ( empty($decr_old_dom_pass) ) { # Если ДОМпароль НЕ расшифровался
				msg("$module: Domain pass '".bin2hex($uniq_id)."' NOT DECRYPTED", $dbg);###
				SetBusyFlagBy($line_id, 0);#Отпускаем строку обратно
				msg("$module: Query line '$line_id' released", $dbg, 'blue');###
			} else {
				msg("$module: DECRYPTED old domain pass '$decr_old_dom_pass'", $dbg, lime);###
				
				#Теперь нужно проверить, расшифровывается ли РЕГпароль новым ДОМпаролем
				$bind=array( array(':user_id', $user_id), array(':res_type_id', $res_type_id) );
				$STH_srch=search('resources', 'user_id=:user_id AND res_type_id=:res_type_id', $bind);		#Ищем заявку
				if ( $row_srch=$STH_srch->fetch() ) {#Если нашли
					$encr_reg_pass=$row_srch['pass'];#Получим зашифрованный РЕГпароль
					$dec_reg_pass=DecryptByPassPhrase($encr_reg_pass, $decr_old_dom_pass);#Теперь пытаемся расшифровать РЕГпароль ДОМпаролем юзера
					
					if ( empty($dec_reg_pass) ) {#РЕГпароль НЕ расшифровался
						msg("$module: REG pass '".bin2hex($encr_reg_pass)."' NOT DECRYPTED", $dbg);###
					} else { #Расшифровался
						$STH=delete('queries4reencrypt', 'id=:id', array(':id', $line_id));		#Удалим строку заявки
						msg("$module: Query line '$line_id' DELETED", $dbg, 'blue');###
						return TRUE;
					}#end else #Расшифровался
				}#end if   #Если нашли
			}# end else РЕГпароль РАСШИФРОВАЛСЯ
		}#end else # Если uniq_id НЕ пустой
		
		return FALSE;
	}#end func CheckPassRecrypt
	*/
	
	
	
	
	
	
	
	
	
	
	
	
	
?>
