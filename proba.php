<?php

include_once 'procs.php';

#$dbg=FALSE;

#msg('START', $dbg);
#msg('ВНИМАНИЕ ! E-MAIL\'ы БУДУТ ОТПРАВЛЕНЫ НА РЕАЛЬНЫЕ АДРЕСА', !$dbg);
#msg('END', $dbg, blue);

/*
$auth=CURLAUTH_BASIC;					echo "auth BASIC=$auth<br>";
$auth=CURLAUTH_DIGEST;				echo "auth DIGEST=$auth<br>";
$auth=CURLAUTH_GSSNEGOTIATE;	echo "auth GSSNEGOTIATE=$auth<br>";
$auth=CURLAUTH_NTLM;					echo "auth NTLM=$auth<br>";
$auth=CURLAUTH_ANY;						$auth=dechex($auth); echo "auth ANY=$auth<br>"; var_dump($auth);
$auth=CURLAUTH_ANYSAFE;				echo "<br>auth ANYSAFE=".dechex($auth)."<br>";
*/


#$passw='';
#$txt='WanTu WanTu Check Da Microphone !!!';
#$encr=EncryptByPassPhrase($passw, $txt);
#$bin=$encr;
#echo "encr=0x".bin2hex($encr).'<br>';
#$passw='';
#$bin='';
#$decr=DecryptByPassPhrase($bin, $passw);
#show_var($decr, 'decr');


#$user_id=76;
#$masterkey='';
#$new_domain_pass='';
#$res=SetEncryptedUniqID($user_id, $masterkey, $new_domain_pass);
#show_var($res, 'res');

/*
$utf8string = "cakeæøå";
echo substr($utf8string,0,5);
// output cake#
echo mb_substr($utf8string,0,5,'UTF-8');
//output cakeæ
*/

/*
# Проверка функции поиска только если строка НЕ пустая
echo "
<HTML>
<Head><Title>Proba</Title>
	<script type='text/javascript'>
		function search(){
			var sh_val= document.getElementById('sech').value;
			var sh = encodeURIComponent(sh_val);
			alert('sh_val=:'+sh_val+':');
			if (sh_val!=='') {
				alert('sh_val NOT EMPTY');
			} //Ищем только если строка не пустая
		}
	</script>
</Head>

<Body>
	<input id='sech' type='text' style='width: 99%;' onKeyUp='search();'>
</Body>
</HTML>
";
#	<input id='sech' type='text' style='width: 99%;' onKeyUp='search();'>
#	<input id='sech' type='text' style='width: 99%;' onKeyUp='alert(\"OK :\"+this.value+\":\");'>
*/

/*$position = array();
$position[] = 1;
$position[] = 2;
$position[] = 3;

show_var($position, 'position');
echo "count=".count($position)."<br>\r\n";
*/

/*
	#Функция запрета кеширования скриптов и стилей
	#Список подключаемых файлов с относительными путями
	#$arr_files=array('js/ajax.js', 'styles.css');
	function deny_cache($arr_files){
		$module='deny_cache';
		
		if (empty($arr_files) ) {message("$module: Список файлов пустой"); return false;}
		
		$str='';#Вернём HTML в строке
		foreach($arr_files as $filename) {
			clearstatcache(TRUE, $filename);
			if (file_exists($filename)) {
				$mod_time=filemtime($filename);
				
				$ext = substr($filename, strrpos($filename, '.')+1);
				switch ($ext) {
					case 'js':
						$str.="<script type='text/javascript' src='$filename?$mod_time'></script>";
						break;
						
					case 'css':
						$str.="<link rel='stylesheet' type='text/css' href='$filename?$mod_time' />";
						break;
				}#end switch
				
				$str.="\r\n";
			}#end if
		}#end foreach
		
		return $str;
	}#end func deny_cache
		
	#$arr_files=array('js/ajax.js', 'styles.css');#Список подключаемых файлов с относительными путями
	echo deny_cache(array('js/ajax.js', 'styles.css'));#$arr_files);
		*/
		
		
	#echo '<td>В определенный день:<input type="checkbox" id="dial" name="dial" value="1" onclick="alert("OK");" disabled></td>';
	
	
	#echo "<TABLE border=1><TR onClick='this.innerHTML=\"<TD bgcolor=lime>OK</TD>\";'><TD bgcolor=red>Жмякай !!!</TD></TR></TABLE>";
	
	
	
	
	
/*
	#Функция возвращает логин без домена
	function cut_domain_old($login){
		$user_name=$login; # Берём логин
		while (strpos($user_name, '\\')!==FALSE) {#Заманаем в доску
			$pos=strpos($user_name, '\\');
			$user_name=substr($user_name, $pos+1);	# отрезаем всё до бэкслэша (домен)
		}#end while
		return $user_name;
	}#end func cut_domain_old
	
	function cut_domain_new($login){
		$dbg=FALSE; $module='cut_domain';
		
		$user_name=$login; # Берём логин
		if ($r_pos=strrpos($login, '\\')) { $user_name=substr($login, $r_pos+1);}	# отрезаем всё до бэкслэша (домен)
		msg("$module: login='$login' user_name='$user_name'", $dbg);
		
		return $user_name;
	}#end func cut_domain_new
	
		
	$login="VLADIMIR\\VS.Pupikyan";
	$user_name_old=cut_domain_old($login);
	echo "cut_domain_old($login)='$user_name_old'<br>";
	echo '$user_name_old=<pre>';
	var_dump($user_name_old);
	echo '</pre><br>';
	
	$user_name_new=cut_domain_new($login);
	echo "cut_domain_new($login)='$user_name_new'<br>";
	echo '$user_name_new=<pre>';
	var_dump($user_name_new);
	echo '</pre><br>';
	
	if ($user_name_old===$user_name_new) {echo "NEW = = = OLD<br>";}
*/
	
	#$module='proba';
	#alert("$module: Line_ID не задан");
	
	
	
	#echo 'digits only='.preg_replace('/[^0-9]+/', '', '012345 6789+-*/,.').'<br>';
	
	#echo 'int_val(10)='.get_int_val( substr('1234567890123456', 0, 10) ).'<br>';
	#echo 'int_val='.get_int_val('1234567890123456').'<br>';
	#echo '2^31='.pow(2, 31).'<br>';
	
	
	#$val='<html>\' " OR 1=1; <script>alert("CRACKED");</script> ';
	#echo 'val='.make_harmless($val).'<br>';
	
	
	#$val='0123456789abcABC-=+/.,/?';
	#echo 'val='.get_letters($val).'<br>';
	
	
	#$val=(1==2);
	#show_var($val, 'val');
	
	
	
	/*
	session_start(); message("session_start");
	$_SESSION['var']=123; show_var($_SESSION, 'SESSION');
	unset($_SESSION['var']); message("unset var");
	show_var($_SESSION, 'SESSION');
	session_unset(); message("session_unset");
	show_var($_SESSION, 'SESSION');
	session_destroy(); message("session_destroy");
	show_var($_SESSION, 'SESSION');
	*/
	
	
#косвенный вызов функции
# $a = "var_dump";
# $b = "Test";
#    $a($b);
	
	
	/*
	# Проверим, портит шифрование русские буквы или нет
	$txt='123йцу.?./|\ASDzxc-_';
	$passw='12345678';
	echo "txt='$txt'<br>";
	$bin=EncryptByPassPhrase($passw, $txt);
	echo "bin='$bin'<br>";
	$dec=DecryptByPassPhrase($bin, $passw);
	echo "dec='$dec'<br>";
	#  ВЫВОД: Русские буквы ПОРТЯТСЯ
	*/
	
	
	/*
	#Сделаем экранирование апострофа ' кавычек " и бэкслеша \
	#  Это нужно для тех, у кого в пароле есть спецсимволы
	$txt='This is a test string';
	$passw="1234'\\\"/5678<>";
	show_var($passw, 'passw');
	$passw=htmlspecialchars($passw, ENT_COMPAT | ENT_HTML401, 'UTF-8');
	show_var($passw, 'spec_passw');
	$bin=EncryptByPassPhrase($passw, $txt);
	show_var($bin, 'bin');
	$decr_pass=DecryptByPassPhrase($bin, $passw);# {# ЧТО ЧЕМ
	show_var($decr_pass, 'decr_pass');
	#ВЫВОД: С экранированием шифруется и расшифровывается нормально
	*/
	
	#echo phpinfo();
	
	/*
	$L2=135*741;
	$res=gmdate('H:i:s', 135*741);
	echo "L2=$L2=gmdate(";
	echo '<pre>';
	var_dump($res);
	echo '</pre>)';
	*/
	
	
	/*
	#Вычисляем разницу в днях между датами в виде строк
	$date0 = date ('d.m.Y', mktime(0,0,0,date('m'),date('d')-1,date('Y')) );
	$date1 = date ('d.m.Y', time() ); #'18.04.2014';
	
	echo "date0=$date0<br>";
	echo "date1=$date1<br>";
	
	$diff = intval(abs(strtotime($date0) - strtotime($date1)) / 86400);
	echo "diff=$diff<br>";
	*/
	
	/*
	for ($i=' A'; $i<'AZ'; $i++) {
		echo "i=$i<br>";
		
	}#end for
	*/
	
	
	/*
	$date0 = mktime(0,0,0,date('m'),date('d')-4,date('Y'));
	echo "date0=$date0";
	echo "date=".date('Y-m-d', $date0);
	*/
	
	/*
	echo "<script type='text/javascript'>
function highlight(item_name, formId){
	var arr = document.getElementById(formId).getElementsByTagName('a');
	var len = arr.length;
	for (var i = 0; i < len; i++){// Перебираем все ссылки
		arr[i].style.color='blue';
	}
	
	item = document.getElementById(item_name);
	item.style.color='red';
}
</script>";
	*/
	
	echo "<script type='text/javascript'>
function highlight(formId) {
	var arr = document.getElementById(formId).getElementsByTagName('a');
	var len = arr.length;
	var color='';
	
	alert('len='+len);
	
	for (var i = 0; i < len; i++){// Перебираем все ссылки
		color=arr[i].style.color;
		alert('color='+color);
	}
	
}
</script>";
	
	
/*	
	echo "<DIV id='menu'>\r\n";
			#echo "$tab<a href='#' onclick=\"highlight(this, 'menu'); pg('load_billing.php','main','');\">Авторизация</a><br>\r\n";
			#echo "$tab<a href='#' onclick=\"pg('check_billing.php','main','');\">Проверка</a><br>\r\n";
			#echo "$tab<a href='#' onclick=\"pg('user_pass.php','main','');\">Пароли</a><br>";
			echo "$tab<a href='#' name='a_1' onclick=\"highlight('menu');\">Авторизация</a><br>\r\n";
			echo "$tab<a href='#' name='a_2' onclick=\"alert('name '+this);\">Проверка</a><br>\r\n";
			echo "$tab<a href='#' name='a_3' onclick=\"alert('name '+this);\">Пароли</a><br>";
	echo "</DIV>\r\n";
*/	
	
	
/*
	$cmd='svn --config-dir /tmp --version';
	
	$descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));

	$resource = proc_open($cmd, $descriptorspec, $pipes);

	if (!is_resource($resource)) {
		echo '<p>Error running this command: <code>'.$cmd.'</code></p>';
		echo "resource=<pre>";
		var_dump($resource);
		echo "</pre>";
		exit;
	}	
*/	





$cmd='php';

$descriptorspec = array(
   0 => array("pipe", "r"),  // stdin - канал, из которого дочерний процесс будет читать
   1 => array("pipe", "w"),  // stdout - канал, в который дочерний процесс будет записывать 
   2 => array("file", "/tmp/error-output.txt", "a") // stderr - файл для записи
);

$cwd = '/tmp';
$env = array('some_option' => 'aeiou');

$process = proc_open($cmd, $descriptorspec, $pipes, $cwd, $env);



if (is_resource($process)) {
    // $pipes теперь выглядит так:
    // 0 => записывающий обработчик, подключенный к дочернему stdin
    // 1 => читающий обработчик, подключенный к дочернему stdout
    // Вывод сообщений об ошибках будет добавляться в /tmp/error-output.txt

    fwrite($pipes[0], '<?php print_r($_ENV); ?>');
    fclose($pipes[0]);

    echo stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    // Важно закрывать все каналы перед вызовом
    // proc_close во избежание мертвой блокировки
    $return_value = proc_close($process);

    echo "команда вернула $return_value\n";
} else {
		echo '<p>Error running this command: <code>'.$cmd.'</code></p>';
		echo "resource=<pre>";
		var_dump($process);
		echo "</pre>";

}


	
	
	
	
	
echo 'OK';
?>
