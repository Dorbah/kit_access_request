<?php
	$curr_dir=getcwd();  $session_name='';
	if ( FALSE!==strpos($curr_dir, 'test') ) {$session_name='test-';}
	session_name($session_name."KIT"); # Подключаем сессию test-KIT, если сидим в тесте
	session_start();#Header передали - а дальше хоть трава не расти
	
	include_once 'procs.php';
	$module='get_ldap_users';
	# Этот модуль выводит список для выбора активных пользователей из таблицы USERS
	#  и при запросе ищет пользователей в LDAP, у которых стоит "Специалист" в title
	# Тут же можно делать первичную авторизацию и проверку доступа конкретных пользователей
	# При выборе пользователя он добавляется в таблицу выбранных пользователей этого супервизора
	#   и одновременно добавляется в основную таблицу пользователей, если его там нет
	
	
	if ( !isset($_SESSION['auth']) ) { # Попытка обхода авторизации
		$redirect='login.php';
		message("$module: Время сессии истекло");
		script("setTimeout(\"pg('$redirect', 'main', '');\", 5000);");
		$mess=color( bold('Авторизация не пройдена, нажмите сюда для входа'), 'lime');
		echo "<noscript><a href='$redirect'>$mess</a></noscript>";
		exit;# Обязательно выходим !!!!!!!!
	}#end if
	
	if (isset($_SESSION['login'])) {$supervisior = $_SESSION['login'];} else {message("$module: Login NOT DEFINED !!!"); exit;}
	if (!isset($_SESSION['pass'])) {message("$module: Password NOT DEFINED !!!"); exit;}
	
	$al=access_level($supervisior);# Получим права доступа $al[0]-супервизор='1'  $al[1]-админ='1'     Пустим только супервизоров и админов
	if ( !('1'==$al[0] OR '1'==$al[1]) ) {message("$module: You DON'T HAVE sufficient privilegies to view this page ($al) !!!"); exit;}
	
	
	
	function print_row($id, $cn, $login, $mail, $checked='', $color='white' ) {
		#Доработать чтобы при установке галочки цвет строки тоже сразу менялся
		# Видимо событие onClick надо повесить на TR, а результат кидать в TD и менять цвет строки
		#echo "<tr id='tr_$login' bgcolor='$color' onclick='document.getElementById(\"tr_$login\").innerHTML.style=\"color: red;\";'>";
		#echo "<tr id='tr_$login' bgcolor='$color' onclick='document.getElementById(\"tr_$login\").style.backgroundColor = red;'>";
		# Разобраться с this.style="color: red;"
		#onclick=\"this.style.backgroundColor='#FF0000';\" - РАБОТАЕТ
		
		$bgcolor='';
		if ( 'white'!==$color ) { $bgcolor="bgcolor='$color'"; }
		
		#echo "<tr id='tr_$id' $bgcolor>";
		#echo "<tr id='tr_$id' $bgcolor onClick=\"clr=this.style.backgroundColor; this.style.backgroundColor='red'; alert('OK $login clr='+clr);\">";
		#echo "<tr id='tr_$id' $bgcolor onClick=\"this.style.backgroundColor='lime'; alert('OK :-) id='+this.id); alert('OK chkd='+chkd); \">";
		echo "<tr id='tr_$id' $bgcolor onClick=\"cb=document.getElementById('cb_$id'); clr='white'; chkd=cb.checked; cb.checked=!chkd; if (cb.checked) {clr='lime';} this.style.backgroundColor=clr; pg('add_user.php', 'td_$id', 'lg=$login&id=$id');\">";
		echo "	<td id='td_$id'>";
		#echo "		<input id='cb_$id' type=checkbox $checked onClick='pg(\"add_user.php\", \"td_$id\", \"lg=$login&id=$id\");'>";
		echo "		<input id='cb_$id' type=checkbox $checked>";
		echo "	</td>";
		echo "	<td>$cn</td>";
		echo "	<td>$login</td>";
		echo "	<td>$mail</td>";
		echo "	<td><input type=button class='button' onclick=\"pg('load_billing.php', 'main', 'lg=$login');\" value='Авторизация'></td>";
		echo "	<td><input type=button class='button' onclick=\"pg('check_billing.php', 'main', 'lg=$login');\" value='Проверка'></td>";
		echo "</tr>";
	}#end func print_row
	
	
	
	
	
	
	if (!$ldapconn = ldap_conn()) { echo "LDAP connect failed..."; exit; } #verify connecting       $ldapconn НУЖЕН ДАЛЬШЕ !!! НЕ ТРОГАТЬ ЭТУ СТРОКУ !!!
	if (!$ldapbind = bind_ldap($ldapconn)) { echo "LDAP bind failed..."; exit;} # verify binding
	
	$login_search='';
	if ( isset($_REQUEST['login_search']) ) {
		$login_search=$_REQUEST['login_search'];
	}#end if isset
	
	
	echo '<div id="result_search"><div class="area" align="center"><b>Выберите пользователей для отправки запроса</b></div>';
	echo "<div class='area' align='center'><div id='form'><b>Введите ФИО:</b>\r\n";
	$page_get='pg("get_ldap_users.php", "result_search", "login_search="+this.value )';
	echo "<input type='text' size=30 name='login_search' id='login_search' value='$login_search' onchange='$page_get'>"; # В IE событие onchange срабатывает только после выхода курсора из формы !!!!!!!!
	echo "<input type=button class='button' onClick='$page_get' value='Искать'>";
	echo "\t\t</div>\r\n";
	
	
	$arr_user_box=get_users_choosed_by($supervisior); # Получим массив логинов юзеров, которых выбрал супервизор
	
	
	
	#Вывести список выбранных/всех операторов ЕКЦ из LDAP для отправки запроса
	#<caption><H2>Выбор пользователей для отправки запроса</H2></caption>
	echo "\t\t\t\t<table border=1 align='center'>
		<th>+</th><th>ФИО</th><th>Логин</th><th>e-mail</th><th>Авторизация</th><th>Проверка</th>";
	
	
	if ( !empty($login_search) ) { #Если есть кого искать, то
		$search_req="(&(cn=*$login_search*))";
		
		$ldap_base=ldap_base();
		$result = ldap_search($ldapconn, $ldap_base, $search_req, array("samaccountname","cn","title","mail"))
				or die ("Error in search query: '$search_req' :".ldap_error($ldapconn));
		$data = ldap_get_entries($ldapconn, $result);
		
		
		# Можно будет сделать разбивку таблички на странички по 10-20 строк
		$count=$data["count"];
		for ($i=0; $i<$count; $i++) {
			$title=(isset($data[$i]["title"][0])) ? $data[$i]["title"][0] : "&nbsp;";
			if ( $title!="Специалист" ) {continue;} #Пропускаем не 'Специалист'ов  
			
			$dn=(isset($data[$i]["dn"])) ? $data[$i]["dn"] : "&nbsp;";
			if (preg_match("/заблокированные/i", $dn)==true) {continue;} #Пропускаем заблокированных
			
			$cn		=(isset($data[$i]["cn"][0])							) ? $data[$i]["cn"][0] 							: "&nbsp;"; #ФИО
			$login=(isset($data[$i]["samaccountname"][0])	) ? $data[$i]["samaccountname"][0]	: "&nbsp;";
			$mail	=(isset($data[$i]["mail"][0])						) ? $data[$i]["mail"][0]						: "&nbsp;";
			
			$checked=''; $color='white';
			if (isset($arr_user_box[$login])) {$checked='checked'; $color='lime';}
			print_row($i, $cn, $login, $mail, $checked, $color);
		}# for
		
		
	} else {
	#	$search_req='(&(objectclass=user)(!(objectclass=computer))(objectCategory=person))'; # Иначе выводим форму ввода для поиска и всю таблицу целиком
		#$STH=search('users', "title='Специалист' AND enabled=1", 'cn'); # Выведем всех активных пользователей 'Специалист' из таблицы users
		$STH=search('users', "enabled=1", 'cn'); # Выведем всех активных пользователей из таблицы users
		$i=0;
		while ($row = $STH->fetch()) {
			$cn=$row['cn']; $login=$row['login']; $mail=$row['mail'];
			$checked=''; $color='white';
			if (isset($arr_user_box[$login])) {$checked='checked'; $color='lime';}
			print_row($i, $cn, $login, $mail, $checked, $color);
			$i++;
		}#end while
		
	}#end else
	
	
	
	
	echo "\t\t\t\t</table>";
	
	echo '</div>
		</div>';
	
	ldap_close($ldapconn);	# all done? clean up
	
	
	
?>