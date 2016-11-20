<?php
class func {

// проверка идентификатора файла
function check_key($key) {  
  global $sv;
  $ret = false;
  
  $key = $this->protect($key);
  $sql = "SELECT * FROM ".T_NAME." WHERE `d`='{$key}'";
  $res = $sv->q($sql);
  if ($sv->nr()==1) {
    $ret = true;    
    $sv->file = $sv->f();  
  }
  else {
    $ret = false;
    unset($sv->file);
  }  
  return $ret;
}    


// увеличиваем счетчик
function inc_count($d) {  
  global $sv;
  $ret = false;
  
  $t_exp = (TIME_COUNTING == 'upload') ? $d['t_exp'] : $sv->post_time + ($d['days_expired']*24*60*60);
      
  $res = $sv->q("UPDATE ".T_NAME." SET t_last='{$sv->post_time}', t_exp='{$t_exp}', dl=dl+'1' WHERE id='{$d['id']}'");    
  return $ret;
}    

// удаляем устарвешие файлы
function delete_expired() {  
  global $sv;    
  
  $sv->q("SELECT * FROM ".T_NAME." WHERE t_exp<'{$sv->post_time}'");
  while ($d = $sv->f()) {
    $s_filename = stripslashes($d['s_filename']);
    if (file_exists(UPLOAD_DIR.$s_filename)) {
      unlink(UPLOAD_DIR.$s_filename);
    }    
  }
  $sv->q("DELETE FROM ".T_NAME." WHERE t_exp<'{$sv->post_time}'");        
}    

// защита от инъекций SQL
function protect($t, $mstrip = 1) {    
  $t = ($mstrip && get_magic_quotes_gpc()) ? stripslashes($t) : $t;
  $t = addslashes($t);    
  return $t;
}


// расширение файла
function file_extension($filename)
{

	$filename=basename($filename);
	$ex=explode(".",$filename);
	if (is_array($ex))
	{
		$i=sizeof($ex)-1;
		$def=strtolower($ex[$i]);
	} else {$def="";};

	
return $def;	
}  

// форма загрузки
function get_upload_form()
{
  $mfs = round(MAX_FILE_SIZE/1024/1024, 2);
  $de = MAX_DAY_EXPIRED;
  $tc = (TIME_COUNTING == 'upload') ? 
      'время отсчитывается с даты добавления файла' : 'время отсчитывается с даты последнего скачивания';

return <<<EOF
            
<FORM action="index.php" METHOD="POST"  ENCTYPE="multipart/form-data" onsubmit="
    if (document.forms[0].file.value == ''){
  		alert('Вы не выбрали файл для закачки!');
  		return false;
  	}
  	if (document.forms[0].title.value == '')	{
  		alert('Введите описание файла');
  		return false;
  	}
        
    ">
  <table width=100% cellpadding=10 cellspacing=1 border=0>
    <tr>
      <td colspan="2">Выберите файл для отправки (maximum {$mfs} MB)<br/>
        <INPUT style="width: 300px" TYPE="file" NAME="file" VALUE="" size="33" class="file">
      </td>
    </tr>
    <tr>
      <td colspan="2">  			 
        Введите краткое описание файла</font><br />
  			<input type="text"  style="width:297px;" name="title" maxlength="100"></textarea>  	
	    </td>
    </tr>
    <tr>
      <td width="1%" nowrap><b>Необязательные параметры</b></td>
      <tD width="99%"><hr></td>
    </tr>
    <tr>
      <td colspan="2">  	
        Ограничение на количество скачиваний<br/>
  			<select name="max_dl">
  			<option value="0">неограничено (по умолчанию)</option>
  			<option value="1000">1000</option>
  			<option value="100">100</option>
  			<option value="10">10</option>
  			<option value="5">5</option>
  			</select>
      </td>
    </tr>
	  <tr>
      <td colspan="2">            
  			Сколько хранить файл? <br/>
  			<select name="days_expired">
    			<option value="{$de}">{$de} дней (по умолчанию)</option>
          <option value="7">неделю</option>
          <option value="3">три дня</option>
          <option value="2">два дня</option>
          <option value="1">24 часа</option>
          <option value="0.5">12 часов</option>
          <option value="0.25">6 часов</option>              
  			</select><br/>
        <small>* {$tc}</small>
      </td>
    </tr>
	  <tr>
      <td colspan="2">      
        <input type="submit" value="Отправить">
      </td>
    </tr>     
  </table>
	</FORM>      


EOF;
}

// сообщение при успешной загрузке
function get_uploaded_msg($d)
{

   $fn = stripslashes($d['filename']);
   $sk = round($d['size']/1024, 2);
   $bu = BASE_URL;
   $sn = SITE_NAME;   
   $key = $d['d'];
   $dl_limit = ($d['max_dl'] == 0) ? 'неограничено' : $d['max_dl'];
   $time = $d['days_expired'];
   if ($time>=1) {
      $time.=" дн.";
   }
   else {
      $time = $time*24;
      $time .= ' ч.';
   }  
  $tc = (TIME_COUNTING == 'upload') ? 
      'с даты добавления файла' : 'с даты последнего скачивания';     
   
return <<<EOF
  
   Файл "{$fn}" ({$sk} KB) успешно загружен.
   <br/><br/>
   Ссылка для скачивания:
   <br/><br/>
   <b><a href='{$bu}?d={$key}'>{$bu}?d={$key}</a></b> 
   <br/><br/>
   Ограничение по кол-ву скачиваний: <b>{$dl_limit}</b>.<br/>
   Хранить файл на сервере: <b>{$time} {$tc}</b>.<br/><br/>
   [ <a href='./'>закачать еще один файл</a> ]
   

EOF;

}


// экран скачивания файла
function get_download_screen($d) {  
   $fn = stripslashes($d['filename']);
   $sk = round($d['size']/1024, 2);   
   $bu = BASE_URL;
   $sn = SITE_NAME;   
   $key = $d['d'];
   $dl_limit = ($d['max_dl'] == 0) ? 'неограничено' : $d['max_dl'];
   $time = $d['days_expired'];
   if ($time>=1) {
      $time.=" дн.";
   }
   else {
      $time = $time*24;
      $time .= ' ч.';
   }
   $mailed = ($d['mailed']===false) ? "" : "<br/><br/> 
      (письмо с данной ссылкой отправлено на <a href='mailto:{$d['mailed']}'>{$d['mailed']}</a>)";
   $show_btn = ($d['max_dl']!=0 && $d['dl'] >= $d['max_dl']) ? 'none' : 'block';
   $hide_btn = ($d['max_dl']!=0 && $d['dl'] >= $d['max_dl']) ? 'block' : 'none';
   
   $size = round($d['size']/1024, 2); $suf = "Kb";
      if ($size>1024) { $size = round($size/1024, 2);  $suf = 'Mb';}
   $dl = ($d['max_dl'] == 0 ) ? "{$d['dl']} из неограничено" : " {$d['dl']} из {$d['max_dl']}";
        
return <<<EOF
<div align=center style='padding:20px;'>    
	<TABLE cellpadding=5 cellspacing=0 style='margin-bottom: 20px;'>
	  <TR>
			<TD width="93" align="left"><b>Файл:</b></TD>
			<TD width="200" align="left"><div id="filename">{$d['filename']}</div></TD>
		</TR>

		<TR>
			<TD valign="top" align="left"><b>Описание:</b></TD>
			<TD><div id="description" align="left">{$d['title']}</div></TD>
		</TR>

		<TR>
			<TD align="left"><b>Размер:</b></TD>
			<TD align="left"><div id="filesize">{$size}</div></TD>
		</TR>
		<TR>
			<TD align="left"><b>Скачиваний:</b></TD>
			<TD align="left"><div id="downloads">{$dl}</div></TD>
		</TR>
	</TABLE>    
  <div style='display: {$show_btn};'>
<form name="download_form" action="index.php?d={$key}&download" method="POST" onsubmit="download_form.download.value='Загружается файл'; download_form.download.disabled=true; ">		
	<input type="submit" id="download" class="button"  value=" Сохранить файл ">
</form>
  </div>
  <div align=center style='display: {$hide_btn}; padding:20;' >
      <b style='color: #993333;'>Лимит скачиваний достигнут.<br/> Данный файл больше не может быть скачан.</b>
  </div>
<br/>
   [ <a href='./'>на главную</a> ]    
</div>
EOF;

}


// список страниц в админке
function pagelist($size, $limit, $page, $url = "index.php?page=")
{
GLOBAL $sv;

if ($sv->valid_ttitle!=""){$sv->valid_ttitle=ucfirst($sv->valid_ttitle).".";};
if ($size<=$limit){return array("","");};

$page=floor($page);
$ost=$size%$limit; 

if ($ost!=0){$pages=(($size-$ost)/$limit)+1;} else {$pages=$size/$limit;};
if ($page<1 || $page>$pages){$page=1;} else {$sv->valid_page="&page=".$page;};
	


$pgs=array();
for ($i=1;$i<$pages+1;$i++)  {
  $pgs[$i]['end_title']=$i*$limit;
  $pgs[$i]['end']=$pgs[$i]['end_title']-1;
  $pgs[$i]['start']=$pgs[$i]['end_title']-$limit;
  $pgs[$i]['start_title']=$pgs[$i]['start']+1;

  if ($pgs[$i]['end_title']>$size){
    $pgs[$i]['end_title']=$size;
  }
}

$out_first="<td class=pagelisttd_light><a href={$url}1{$sv->valid_topic} title='c {$pgs[1]['start_title']} по {$pgs[1]['end_title']} из {$size}'>первая</a></td>";
$out_last="<td class=pagelisttd_light><a href={$url}{$pages}{$sv->valid_topic} title='c {$pgs[$pages]['start_title']} по {$pgs[$pages]['end_title']} из {$size}'>последняя</a></td>";


// do ==========
$k=0; $do=""; $do_start=$page-3; if ($do_start<1){$do_start=1;};
for ($i=$do_start;$i<$page;$i++)
{
$k++; if ($k>3){break;};
$do.="<td class=pagelisttd_light><a href='{$url}{$i}{$sv->valid_topic}' title='Открыть c {$pgs[$i]['start_title']} по {$pgs[$i]['end_title']}, всего {$size}'>$i</a></td>";

if ($i==1){$out_first="";};
}

// posle ==========
$k=0; $posle="";
for ($i=$page+1;$i<$pages+1;$i++)
{
$k++; if ($k>3){break;};
$posle.="<td class=pagelisttd_light><a href='{$url}{$i}{$sv->valid_topic}' title='Открыть c {$pgs[$i]['start_title']} по {$pgs[$i]['end_title']}, всего {$size}'>$i</a></td>";
if ($i==$pages){$out_last="";};
}


$out_list=$do."<td class=pagelisttd_light style='border: 1px solid blackl'><span title='Всего найдено {$size}, показано c {$pgs[$page]['start_title']} по {$pgs[$page]['end_title']}'><b style='color:black;'>$page</span></td>".$posle;


if ($page==1){$out_first="";};
if ($page==$pages){$out_last="";};

$prev=$page-1;
$next=$page+1;

if ($prev<1){$out_prev="";} else {$out_prev="<td class=pagelisttd_light><a href={$url}{$prev}{$sv->valid_topic} title='Предыдущая страница - c {$pgs[$prev]['start_title']} по {$pgs[$prev]['end_title']} из {$size}'>&lt;</a></td>";};
if ($next>$pages){$out_next="";} else {$out_next="<td class=pagelisttd_light><a href={$url}{$next}{$sv->valid_topic} title='Следующая страница - c {$pgs[$next]['start_title']} по {$pgs[$next]['end_title']} из {$size}'>&gt;</a></td>";};


$out="<table class='pagelist'><tr><td class=pagelisttd>{$sv->valid_ttitle} Страница $page из $pages</td>".$out_first.$out_prev.$out_list.$out_next.$out_last."</tr></table>";

return array($out," LIMIT {$pgs[$page]['start']},{$limit}",($limit*$page-$limit));
}

// форматирование даты и времени
function getTime($time,$vid) {	 
	GLOBAL $sv;

	$post_time=$sv->post_time;

	 $a = getdate($time);
     $b = getdate($post_time);
	 $min=$a["minutes"];
     if ($min<10){$min="0".$min;};
	 $dnn=$this->dntorus($a['wday']);
switch ($vid)
	{
	case 0:	$output="$a[mday].$a[mon].$a[year] $a[hours]:$min"; break;
	case 7:	$output="$a[hours]:$min"; break;
	case 6:	 
			if ($a['mon']<10){$a['mon']='0'.$a['mon'];}
			$output="$a[mday].$a[mon].$a[year]"; 
			
			break;

	case 1: $output="$a[mday] ".$this->rus_month($a["mon"])." $a[year] года";break;
	case 2: $output="$a[mday] ".$this->rus_month($a["mon"])." $a[year] года, $a[hours]:$min";break;
	case 3: $output="$a[mday] ".$this->rus_month($a["mon"])." $a[year] года, $dnn, $a[hours]:$min";break;
	case 4: 
			$c_h=date('G');
			$b_h=date('G',$time);
			$c_m=date('i');
			$b_m=date('i',$time);
			
			if ($c_h<$b_h){$c_h=$c_h+24;};
			$h=$c_h-$b_h;
			if ($c_m<$b_m){$c_m=$c_m+60;};
			$m=$c_m-$b_m;
			if ($h!=0){$h="$h ч ";} else {$h="";};
			if ($m!=0){$m="$m мин ";} else {$m="";};
			if ($h.$m!=""){$output="$h $m назад";} else {$output="сейчас на сайте";};
			break;
	case 5: 
			$r=$post_time-$time;	
			$d=floor($r / 86400);
			if ($d > 0){ $dt="$d дн назад";} else {$dt="";};
	
			$c_h=date('G');
			$b_h=date('G',$time);
			$c_m=date('i');
			$b_m=date('i',$time);
			
			if ($c_h<$b_h){$c_h=$c_h+24;};
			$h=$c_h-$b_h;
			if ($c_m<$b_m){$c_m=$c_m+60;$h--;};
			$m=$c_m-$b_m;
			if ($h!=0){$h="$h ч ";} else {$h="";};
			if ($m!=0){$m="$m мин ";} else {$m="";};
			
			$dr_day=$b['yday']-$a['yday'];
			if ($dr_day==1){$dt="вчера";};
			if ($dr_day==0){$dt="сегодня";};
			if ($dr_day==2){$dt="позавчера";};
			if ($dr_day>2){$dt="$dr_day дн. назад";};

			$output="$dt в $a[hours]:$min";
			break;
	};
	return $output;
}

// месяц по русски, родительный падеж
function rus_month($month){
 if ($month=="1"){$month="января";};
 if ($month=="2"){$month="февраля";};
 if ($month=="3"){$month="марта";};
 if ($month=="4"){$month="апреля";};
 if ($month=="5"){$month="мая";};
 if ($month=="6"){$month="июня";};
 if ($month=="7"){$month="июля";}; 
 if ($month=="8"){$month="августа";};
 if ($month=="9"){$month="сентября";};
 if ($month=="10"){$month="октября";};
 if ($month=="11"){$month="ноября";};
 if ($month=="12"){$month="декабря";};
 return $month;
}

// день недели по русски
function dntorus($dn){
 if ($dn=="1"){$dn="понедельник";};
 if ($dn=="2"){$dn="вторник";};
 if ($dn=="3"){$dn="среда";};
 if ($dn=="4"){$dn="четверг";};
 if ($dn=="5"){$dn="пятница";};
 if ($dn=="6"){$dn="суббота";};
 if ($dn=="7"){$dn="воскресенье";};
 if ($dn=="0"){$dn="воскресенье";};
return $dn;
}

// месяц по русски - именительный падеж
function monthtorus($month){
 if ($month=="1"){$month="январь";};
 if ($month=="2"){$month="февраль";};
 if ($month=="3"){$month="март";};
 if ($month=="4"){$month="апрель";};
 if ($month=="5"){$month="май";};
 if ($month=="6"){$month="июнь";};
 if ($month=="7"){$month="июль";}; 
 if ($month=="8"){$month="август";};
 if ($month=="9"){$month="сентябрь";};
 if ($month=="10"){$month="октябрь";};
 if ($month=="11"){$month="ноябрь";};
 if ($month=="12"){$month="декабрь";};
 return $month;
}  
  
// скачивание файла
function download_file($d, $resume = true, $speed_limit = 1048576) {
  global $sv;
  
  $filename = $d['filename'];
  $s_filename = $d['s_filename'];
  
  $file = UPLOAD_DIR.$s_filename;
  $running_time = 0; 
  $begin_time = time(); 
  set_time_limit( 300 ); 
  if (!file_exists($file)) {
    $sv->msgs[] = "файл {$file} - не существует";   
    return false;
  }
  
  $f = fopen( $file, 'rb' ); 
  if ( !$f ) { 
    $sv->msgs[] = "не возможно открыть файл {$file} для чтения";   
    return false;
  } 
  
  $file_size = filesize( $file ); 
  $file_date = date( "D, d M Y H:i:s T", filemtime( $file ) ); 
  $offset = 0; 

  header( "HTTP/1.1 200 OK" ); 
  
  $data_start  = $offset; 
  $data_end    = $file_size - 1; 
  $etag        = md5( $file . $file_size . $file_date ); 
  fseek( $f, $data_start ); 
  header( "Content-Disposition: attachment; filename=".$filename ); 
  header( "Last-Modified: ".$file_date ); 
  header( "Cache-Control: private");
  header( "Content-Transfer-Encoding: binary");
  header( "ETag: \"".$etag."\"" ); 
  header( "Content-Length: ".( $file_size-$data_start ) );
  header( "Content-type: {$d['mime']}" ); 

  
  while( !feof( $f ) && ( connection_status() == 0 ) )  {     
    // считываем и отдаем очередную часть файла
    print fread( $f, $speed_limit );   
    
    //flush(); 
    sleep(0.5); 
    $running_time = time() - $begin_time; 
    
    // увеличиваем лимит времени на исполение скрипта если подходит к концу
    if( $running_time > 240 ) { 
        set_time_limit(300); 
        $begin_time = time(); 
    } 
  } 
  fclose( $f );      
    
  return true;
}  

  
  //eoc
}
?>