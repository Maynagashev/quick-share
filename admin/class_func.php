<?php
class func {

// �������� �������������� �����
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


// ����������� �������
function inc_count($d) {  
  global $sv;
  $ret = false;
  
  $t_exp = (TIME_COUNTING == 'upload') ? $d['t_exp'] : $sv->post_time + ($d['days_expired']*24*60*60);
      
  $res = $sv->q("UPDATE ".T_NAME." SET t_last='{$sv->post_time}', t_exp='{$t_exp}', dl=dl+'1' WHERE id='{$d['id']}'");    
  return $ret;
}    

// ������� ���������� �����
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

// ������ �� �������� SQL
function protect($t, $mstrip = 1) {    
  $t = ($mstrip && get_magic_quotes_gpc()) ? stripslashes($t) : $t;
  $t = addslashes($t);    
  return $t;
}


// ���������� �����
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

// ����� ��������
function get_upload_form()
{
  $mfs = round(MAX_FILE_SIZE/1024/1024, 2);
  $de = MAX_DAY_EXPIRED;
  $tc = (TIME_COUNTING == 'upload') ? 
      '����� ������������� � ���� ���������� �����' : '����� ������������� � ���� ���������� ����������';

return <<<EOF
            
<FORM action="index.php" METHOD="POST"  ENCTYPE="multipart/form-data" onsubmit="
    if (document.forms[0].file.value == ''){
  		alert('�� �� ������� ���� ��� �������!');
  		return false;
  	}
  	if (document.forms[0].title.value == '')	{
  		alert('������� �������� �����');
  		return false;
  	}
        
    ">
  <table width=100% cellpadding=10 cellspacing=1 border=0>
    <tr>
      <td colspan="2">�������� ���� ��� �������� (maximum {$mfs} MB)<br/>
        <INPUT style="width: 300px" TYPE="file" NAME="file" VALUE="" size="33" class="file">
      </td>
    </tr>
    <tr>
      <td colspan="2">  			 
        ������� ������� �������� �����</font><br />
  			<input type="text"  style="width:297px;" name="title" maxlength="100"></textarea>  	
	    </td>
    </tr>
    <tr>
      <td width="1%" nowrap><b>�������������� ���������</b></td>
      <tD width="99%"><hr></td>
    </tr>
    <tr>
      <td colspan="2">  	
        ����������� �� ���������� ����������<br/>
  			<select name="max_dl">
  			<option value="0">������������ (�� ���������)</option>
  			<option value="1000">1000</option>
  			<option value="100">100</option>
  			<option value="10">10</option>
  			<option value="5">5</option>
  			</select>
      </td>
    </tr>
	  <tr>
      <td colspan="2">            
  			������� ������� ����? <br/>
  			<select name="days_expired">
    			<option value="{$de}">{$de} ���� (�� ���������)</option>
          <option value="7">������</option>
          <option value="3">��� ���</option>
          <option value="2">��� ���</option>
          <option value="1">24 ����</option>
          <option value="0.5">12 �����</option>
          <option value="0.25">6 �����</option>              
  			</select><br/>
        <small>* {$tc}</small>
      </td>
    </tr>
	  <tr>
      <td colspan="2">      
        <input type="submit" value="���������">
      </td>
    </tr>     
  </table>
	</FORM>      


EOF;
}

// ��������� ��� �������� ��������
function get_uploaded_msg($d)
{

   $fn = stripslashes($d['filename']);
   $sk = round($d['size']/1024, 2);
   $bu = BASE_URL;
   $sn = SITE_NAME;   
   $key = $d['d'];
   $dl_limit = ($d['max_dl'] == 0) ? '������������' : $d['max_dl'];
   $time = $d['days_expired'];
   if ($time>=1) {
      $time.=" ��.";
   }
   else {
      $time = $time*24;
      $time .= ' �.';
   }  
  $tc = (TIME_COUNTING == 'upload') ? 
      '� ���� ���������� �����' : '� ���� ���������� ����������';     
   
return <<<EOF
  
   ���� "{$fn}" ({$sk} KB) ������� ��������.
   <br/><br/>
   ������ ��� ����������:
   <br/><br/>
   <b><a href='{$bu}?d={$key}'>{$bu}?d={$key}</a></b> 
   <br/><br/>
   ����������� �� ���-�� ����������: <b>{$dl_limit}</b>.<br/>
   ������� ���� �� �������: <b>{$time} {$tc}</b>.<br/><br/>
   [ <a href='./'>�������� ��� ���� ����</a> ]
   

EOF;

}


// ����� ���������� �����
function get_download_screen($d) {  
   $fn = stripslashes($d['filename']);
   $sk = round($d['size']/1024, 2);   
   $bu = BASE_URL;
   $sn = SITE_NAME;   
   $key = $d['d'];
   $dl_limit = ($d['max_dl'] == 0) ? '������������' : $d['max_dl'];
   $time = $d['days_expired'];
   if ($time>=1) {
      $time.=" ��.";
   }
   else {
      $time = $time*24;
      $time .= ' �.';
   }
   $mailed = ($d['mailed']===false) ? "" : "<br/><br/> 
      (������ � ������ ������� ���������� �� <a href='mailto:{$d['mailed']}'>{$d['mailed']}</a>)";
   $show_btn = ($d['max_dl']!=0 && $d['dl'] >= $d['max_dl']) ? 'none' : 'block';
   $hide_btn = ($d['max_dl']!=0 && $d['dl'] >= $d['max_dl']) ? 'block' : 'none';
   
   $size = round($d['size']/1024, 2); $suf = "Kb";
      if ($size>1024) { $size = round($size/1024, 2);  $suf = 'Mb';}
   $dl = ($d['max_dl'] == 0 ) ? "{$d['dl']} �� ������������" : " {$d['dl']} �� {$d['max_dl']}";
        
return <<<EOF
<div align=center style='padding:20px;'>    
	<TABLE cellpadding=5 cellspacing=0 style='margin-bottom: 20px;'>
	  <TR>
			<TD width="93" align="left"><b>����:</b></TD>
			<TD width="200" align="left"><div id="filename">{$d['filename']}</div></TD>
		</TR>

		<TR>
			<TD valign="top" align="left"><b>��������:</b></TD>
			<TD><div id="description" align="left">{$d['title']}</div></TD>
		</TR>

		<TR>
			<TD align="left"><b>������:</b></TD>
			<TD align="left"><div id="filesize">{$size}</div></TD>
		</TR>
		<TR>
			<TD align="left"><b>����������:</b></TD>
			<TD align="left"><div id="downloads">{$dl}</div></TD>
		</TR>
	</TABLE>    
  <div style='display: {$show_btn};'>
<form name="download_form" action="index.php?d={$key}&download" method="POST" onsubmit="download_form.download.value='����������� ����'; download_form.download.disabled=true; ">		
	<input type="submit" id="download" class="button"  value=" ��������� ���� ">
</form>
  </div>
  <div align=center style='display: {$hide_btn}; padding:20;' >
      <b style='color: #993333;'>����� ���������� ���������.<br/> ������ ���� ������ �� ����� ���� ������.</b>
  </div>
<br/>
   [ <a href='./'>�� �������</a> ]    
</div>
EOF;

}


// ������ ������� � �������
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

$out_first="<td class=pagelisttd_light><a href={$url}1{$sv->valid_topic} title='c {$pgs[1]['start_title']} �� {$pgs[1]['end_title']} �� {$size}'>������</a></td>";
$out_last="<td class=pagelisttd_light><a href={$url}{$pages}{$sv->valid_topic} title='c {$pgs[$pages]['start_title']} �� {$pgs[$pages]['end_title']} �� {$size}'>���������</a></td>";


// do ==========
$k=0; $do=""; $do_start=$page-3; if ($do_start<1){$do_start=1;};
for ($i=$do_start;$i<$page;$i++)
{
$k++; if ($k>3){break;};
$do.="<td class=pagelisttd_light><a href='{$url}{$i}{$sv->valid_topic}' title='������� c {$pgs[$i]['start_title']} �� {$pgs[$i]['end_title']}, ����� {$size}'>$i</a></td>";

if ($i==1){$out_first="";};
}

// posle ==========
$k=0; $posle="";
for ($i=$page+1;$i<$pages+1;$i++)
{
$k++; if ($k>3){break;};
$posle.="<td class=pagelisttd_light><a href='{$url}{$i}{$sv->valid_topic}' title='������� c {$pgs[$i]['start_title']} �� {$pgs[$i]['end_title']}, ����� {$size}'>$i</a></td>";
if ($i==$pages){$out_last="";};
}


$out_list=$do."<td class=pagelisttd_light style='border: 1px solid blackl'><span title='����� ������� {$size}, �������� c {$pgs[$page]['start_title']} �� {$pgs[$page]['end_title']}'><b style='color:black;'>$page</span></td>".$posle;


if ($page==1){$out_first="";};
if ($page==$pages){$out_last="";};

$prev=$page-1;
$next=$page+1;

if ($prev<1){$out_prev="";} else {$out_prev="<td class=pagelisttd_light><a href={$url}{$prev}{$sv->valid_topic} title='���������� �������� - c {$pgs[$prev]['start_title']} �� {$pgs[$prev]['end_title']} �� {$size}'>&lt;</a></td>";};
if ($next>$pages){$out_next="";} else {$out_next="<td class=pagelisttd_light><a href={$url}{$next}{$sv->valid_topic} title='��������� �������� - c {$pgs[$next]['start_title']} �� {$pgs[$next]['end_title']} �� {$size}'>&gt;</a></td>";};


$out="<table class='pagelist'><tr><td class=pagelisttd>{$sv->valid_ttitle} �������� $page �� $pages</td>".$out_first.$out_prev.$out_list.$out_next.$out_last."</tr></table>";

return array($out," LIMIT {$pgs[$page]['start']},{$limit}",($limit*$page-$limit));
}

// �������������� ���� � �������
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

	case 1: $output="$a[mday] ".$this->rus_month($a["mon"])." $a[year] ����";break;
	case 2: $output="$a[mday] ".$this->rus_month($a["mon"])." $a[year] ����, $a[hours]:$min";break;
	case 3: $output="$a[mday] ".$this->rus_month($a["mon"])." $a[year] ����, $dnn, $a[hours]:$min";break;
	case 4: 
			$c_h=date('G');
			$b_h=date('G',$time);
			$c_m=date('i');
			$b_m=date('i',$time);
			
			if ($c_h<$b_h){$c_h=$c_h+24;};
			$h=$c_h-$b_h;
			if ($c_m<$b_m){$c_m=$c_m+60;};
			$m=$c_m-$b_m;
			if ($h!=0){$h="$h � ";} else {$h="";};
			if ($m!=0){$m="$m ��� ";} else {$m="";};
			if ($h.$m!=""){$output="$h $m �����";} else {$output="������ �� �����";};
			break;
	case 5: 
			$r=$post_time-$time;	
			$d=floor($r / 86400);
			if ($d > 0){ $dt="$d �� �����";} else {$dt="";};
	
			$c_h=date('G');
			$b_h=date('G',$time);
			$c_m=date('i');
			$b_m=date('i',$time);
			
			if ($c_h<$b_h){$c_h=$c_h+24;};
			$h=$c_h-$b_h;
			if ($c_m<$b_m){$c_m=$c_m+60;$h--;};
			$m=$c_m-$b_m;
			if ($h!=0){$h="$h � ";} else {$h="";};
			if ($m!=0){$m="$m ��� ";} else {$m="";};
			
			$dr_day=$b['yday']-$a['yday'];
			if ($dr_day==1){$dt="�����";};
			if ($dr_day==0){$dt="�������";};
			if ($dr_day==2){$dt="���������";};
			if ($dr_day>2){$dt="$dr_day ��. �����";};

			$output="$dt � $a[hours]:$min";
			break;
	};
	return $output;
}

// ����� �� ������, ����������� �����
function rus_month($month){
 if ($month=="1"){$month="������";};
 if ($month=="2"){$month="�������";};
 if ($month=="3"){$month="�����";};
 if ($month=="4"){$month="������";};
 if ($month=="5"){$month="���";};
 if ($month=="6"){$month="����";};
 if ($month=="7"){$month="����";}; 
 if ($month=="8"){$month="�������";};
 if ($month=="9"){$month="��������";};
 if ($month=="10"){$month="�������";};
 if ($month=="11"){$month="������";};
 if ($month=="12"){$month="�������";};
 return $month;
}

// ���� ������ �� ������
function dntorus($dn){
 if ($dn=="1"){$dn="�����������";};
 if ($dn=="2"){$dn="�������";};
 if ($dn=="3"){$dn="�����";};
 if ($dn=="4"){$dn="�������";};
 if ($dn=="5"){$dn="�������";};
 if ($dn=="6"){$dn="�������";};
 if ($dn=="7"){$dn="�����������";};
 if ($dn=="0"){$dn="�����������";};
return $dn;
}

// ����� �� ������ - ������������ �����
function monthtorus($month){
 if ($month=="1"){$month="������";};
 if ($month=="2"){$month="�������";};
 if ($month=="3"){$month="����";};
 if ($month=="4"){$month="������";};
 if ($month=="5"){$month="���";};
 if ($month=="6"){$month="����";};
 if ($month=="7"){$month="����";}; 
 if ($month=="8"){$month="������";};
 if ($month=="9"){$month="��������";};
 if ($month=="10"){$month="�������";};
 if ($month=="11"){$month="������";};
 if ($month=="12"){$month="�������";};
 return $month;
}  
  
// ���������� �����
function download_file($d, $resume = true, $speed_limit = 1048576) {
  global $sv;
  
  $filename = $d['filename'];
  $s_filename = $d['s_filename'];
  
  $file = UPLOAD_DIR.$s_filename;
  $running_time = 0; 
  $begin_time = time(); 
  set_time_limit( 300 ); 
  if (!file_exists($file)) {
    $sv->msgs[] = "���� {$file} - �� ����������";   
    return false;
  }
  
  $f = fopen( $file, 'rb' ); 
  if ( !$f ) { 
    $sv->msgs[] = "�� �������� ������� ���� {$file} ��� ������";   
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
    // ��������� � ������ ��������� ����� �����
    print fread( $f, $speed_limit );   
    
    //flush(); 
    sleep(0.5); 
    $running_time = time() - $begin_time; 
    
    // ����������� ����� ������� �� ��������� ������� ���� �������� � �����
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