<?php

$admin_login='agr';
$admin_pass='agr';

DEFINE ('SITE_NAME', "QuickLOAD");
DEFINE ('BASE_URL', "http://84.254.239.106/"); // ���������� url �������
DEFINE ('UPLOAD_DIR', "C:/WebServers/home/84.254.239.106/admin/files/"); // ����� �� ������� � ������� ����� ����������� ����� (������������ ��������)
DEFINE ('UPLOAD_URL', BASE_URL."admin/files/"); // ���������� url ����� ��� ���������� ������

DEFINE ('MAX_FILE_SIZE',1024*1024*2); // 2 Mb
DEFINE ('MAX_DAY_EXPIRED', 30); // ������������ ���� ��������
DEFINE ('TIME_COUNTING', 'upload');  // last|upload - ��� ������� ���� �������� �� ������� ���������� ����������|� ���� ����������


// ������������ ���� ������
$db_vars['host']='localhost';
$db_vars['user']='root';
$db_vars['pass']='';
$db_vars['name']='share';

DEFINE ('T_NAME','share_files'); // ��� ������� � �������
DEFINE ('TABLE_ADMIN','share_admin_sessions');  // ��� ������� � ����� ��������


// =====================================
// ������ ���������
// ===================================== 
DEFINE('ADMIN_LOGIN',$admin_login);
DEFINE('ADMIN_PASSWORD',$admin_pass);
error_reporting(1);

// ����� ����� � ��������� ����������� � ��������� ��� ������ � ����� ������
class sitevars {
	var $query_count=0;
	var $last_res='';

function sitevars() {
	GLOBAL $sponsor_dir,$db_prefix;
	$this->t=array();
	$this->dir = getcwd();			
	$this->ip = getenv('REMOTE_ADDR');	
	$this->script_name = getenv('SCRIPT_NAME');
  $this->host = getenv('HTTP_HOST');
  $this->domain = (preg_match("/[^\.\/]+\.[^\.\/]+$/msi", $this->host, $m)) ? $m[0] : $this->host;    
  $this->cookie_time	= 60*60*24*3;        
  $this->post_time=time();	
  $this->_post = $_POST;
  $this->_get = $_GET;
  $this->_files = $_FILES;
}

function db_connect() {
	GLOBAL $db_vars;
	
	if(!mysql_connect($db_vars['host'],$db_vars['user'],$db_vars['pass'])) { 
echo "<div style='border:1px dashed black; background-color: #efefef; padding:10px;font-family:verdana;'>
<b>�� ���� ����������� � ����, �������� ����������� ������� ��������� ����������� � ���� ������ - � ����� <span style='color:blue;'>config.php</span>: </b><br><br>
		".convert_cyr_string(mysql_error(),'k','w')."</div>";
		exit; 
		
	}
	if (!mysql_select_db($db_vars['name']))	{
		echo "<div style='border:1px dashed black; background-color: #efefef; padding:10px;font-family:verdana;'>
		<b>�� ������� ����  <span style='color:blue;'>{$db_vars['name']}</span>, �������� ����������� ������� ��������� ����������� � ���� ������ - � ����� <span style='color:blue;'>config.php</span>: </b><br><br>
		".convert_cyr_string(mysql_error(),'k','w')."</div>";		
		exit();
	}
}

function q($q) {
	$this->query_count++;
	$res=@mysql_query($q);
	if (!$res) 	{ 
		echo "<div style='border:1px dashed black; background-color: #efefef; padding:10px;font-family:verdana;'>".convert_cyr_string(mysql_error(),'k','w')."</div>";
	}
	$this->last_res=$res;
	return $res;
}

function f($res="") {		
	$qid = ($res=='') ? $this->last_res : $res;
	$data = mysql_fetch_array($qid, MYSQL_ASSOC);
	return $data;
}

function nr($res="") {		
	if ($res==''){$qid=$this->last_res;} else {$qid=$res;};
	$data=mysql_num_rows($qid);
	return $data;
}

function af($res="") {				
	$data=mysql_affected_rows();
	return $data;
}
  	
//eoc
}

$sv = new sitevars;

?>