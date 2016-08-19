<?php

include_once 'procs.php';

$domain='VLADIMIR\\';

###########################################################################
# ЭТО ВАЖНО - НАДО ДОДЕЛАТЬ, потом поменять везде в коде названия таблиц на эти переменные
#Задаём названия таблиц
$cfg_tbl_q4r='queries4reencrypt'; #Таблица для запросов перешифровки
#$cfg_tbl_usrs ='users';



###########################################################################


	# Переменные для отправки почты региональным админам
	#Самому себе лучше не отправлять - заглючит почта
	function smtp_user() {
		#Можно брать сессионный логин $_SESSION['login']
		return "Litoh";
	}# end func get_smtp_user

	function smtp_pass() {
		#Можно брать сессионный пароль $_SESSION['pass']
		return base64_decode('JHRAdGlzdDE=');
	}# end func get_smtp_user


	#Функция тупо подключается к MSSQL базе и возвращает дескриптор подключения
	function mssql_conn() {
		# Переменные для подключения к БД
		$mssqlhost = 'sqlcl';		# Хост
		$mssqlbase = 'kit_access_request';		# БД
		$mssqllogn = 'kitadmin'; 	# Логин 
		$mssqlpass = 'kitadmin'; 	# Пароль
		
		try { # Пытаемся подключиться
			#print_r(PDO::getAvailableDrivers());
			$DBH = new PDO("dblib:host=$mssqlhost;dbname=$mssqlbase", "$mssqllogn", "$mssqlpass");
			return $DBH;# Возвращаем дескриптор
		} catch(PDOException $e) {  
			echo '<font color=red><b>MSSQLERR:</b></font><br>';
			do {
				printf("<b>File</b>: %s, <b>Line</b>: %d, <b>Error</b>: %s, <b>Code</b>:(%d) <b>Class</b>:[%s]\n", $e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), get_class($e));
			} while($e = $e->getPrevious());
			exit;# Упадаем в ужасе
		}
	}#end func mssql_conn


	#Подключение к LDAP
	function ldap_conn() {
	
		$connect = ldap_connect("10.133.1.8", "389") or die("ldap_conn: Could not connect to LDAP server.");
		ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION, 3);  
		ldap_set_option($connect, LDAP_OPT_REFERRALS, 0);  

		return $connect;
	}#end func ldap_conn



	# binding to ldap server
	#function bind_ldap($ldapconn, $ldap_username="VLADIMIR\\LAMP", $ldap_password="h8PYyB7X") {
	function bind_ldap($ldapconn, $ldap_username="VLADIMIR\\LAMP", $ldap_password="h8PYyB7X") {
		
		if (empty($ldapconn)) {$ldapconn=ldap_conn();}
		$res = ldap_bind($ldapconn, $ldap_username, $ldap_password); # or die ("Error trying to bind: ".ldap_error($ldapconn));
		
		return $res;
	}#end func bind_ldap


	function ldap_base() {
		$ldap_base="dc=vld,dc=msk-center,dc=skylink,dc=local";
		
		return $ldap_base;
	}#end func ldap_base



	#Определяем атрибуты юзера из LDAP по логину
	function ldap_user($login) {
		$login=cut_domain($login);
		
		# Переменные для подключения к LDAP
		#$config['ldap_base']="dc=vld,dc=msk-center,dc=skylink,dc=local";
		
		$connect = ldap_conn();#ОТКРЫЛИ
		$bind = bind_ldap($connect);
		#$read = ldap_search($connect, $config['ldap_base'], "(&(sAMAccountName=$login))", array("sAMAccountName","cn","title","department","company","mail","physicaldeliveryofficename","telephonenumber","mobile"));
		$ldap_base=ldap_base();
		$read = ldap_search($connect, $ldap_base, "(&(sAMAccountName=$login))", array("sAMAccountName","cn","title","department","company","mail","physicaldeliveryofficename","telephonenumber","mobile"));
		
		#if ($read==FALSE) { message('ldap_user: Неверно указаны логин или пароль !!!'); exit; }
		$result = ldap_get_entries($connect, $read);
		ldap_close($connect);#ЗАКРЫЛИ
		
		return $result;
	}#end func ldap_user








?>
