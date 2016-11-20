<?php

require_once("../config.php");
require_once("class_func.php");

$std = new func;

$sv->db_connect();

$cookie_time	= 60*60*24*3;
session_set_cookie_params($cookie_time);
session_start();

$sv->sid = session_id();

// �������� ���� � �������
if (isset($sv->_post['new_pass'])  && isset($sv->_post['new_login'])) {
	$new_pass  = $std->protect($sv->_post['new_pass']);
	$new_login = $std->protect($sv->_post['new_login']);
	
	if ($new_pass==ADMIN_PASSWORD && $new_login==ADMIN_LOGIN) {
		$sv->q("DELETE FROM  ".TABLE_ADMIN." WHERE sid='{$sv->sid}'");    
		$sv->q("INSERT INTO ".TABLE_ADMIN." (sid,time,ip) vALUES ('{$sv->sid}','{$sv->post_time}','{$sv->ip}')");	
	}
}


// �������� session
$sv->q("SELECT * FROM  ".TABLE_ADMIN." WHERE sid='{$sv->sid}'");

if ($sv->nr()!=1){
  echo authform();
  die('������� � �������....');
}

$act = (!isset($sv->_get['act'])) ? 'default' : $sv->_get['act'];
$id = (!isset($sv->_get['id'])) ? 0 : intval($sv->_get['id']);

switch ($act){
  
	case "del": 
	  if (isset($sv->_get['accepted']) && $sv->_get['accepted']=='yes') {
    	this_del();	
    	this_default();
    }
    else {
      this_del_confirm();
    }  
  break;	
  
	case "exit":
	  $sv->q("DELETE FROM ".TABLE_ADMIN.""); session_destroy(); die('buy buy...'); 	
  break;	
  
	default:		
	  this_default();
	break;
}


// �������� ��������������
$sv->nav=" :: 	
	<a href=../index.php>����� ��������</a> ::
	<a href=index.php?act=exit>�����</a> ::
	";

$sv->msg = implode("<br/>", $sv->msgs);
$sv->out="
<title>�������� ��������������</title>
<LINK REL='StyleSheet' HREF='style.css' TYPE='text/css'>
<table width=100% cellpadding=5>
<tr><td bgcolor=#eeeeee>{$sv->nav}</td></tr>
<tr><td><b>{$sv->title}</td></tr>
<tr><td>{$sv->msg}</td></tr>
<tr><td>{$sv->html}</td></tr>
</table>
";
print ($sv->out);


// =============================
// �������
// =============================
function this_default()
{
	GLOBAL $sv, $std;
    $res=$sv->q("SELECT 0 FROM ".T_NAME." ORDER BY id DESC");
    $size = $sv->nr();
    $res=$sv->q("SELECT * FROM ".T_NAME." ORDER BY id DESC {$qlimit}");
    $bg = '#ffffff';
    while($d=$sv->f()) {
    	$k++;

    	$bg = ($bg == '#ffffff') ? '#dddddd' : '#ffffff';
    	$size = round($d['size']/1024, 2); $suf = "Kb";
      if ($size>1024) { $size = round($size/1024, 2);  $suf = 'Mb';}
      
      $t_upload = $std->gettime($d['t_upload'], 0);
      $t_exp = $std->gettime($d['t_exp'], 0);
      $t_del = round(($d['t_exp'] - $sv->post_time)/60/60, 2);
      
      $dl = ($d['max_dl'] == 0 ) ? "{$d['dl']} �� ������������" : " {$d['dl']} �� {$d['max_dl']}";
      $uu = UPLOAD_URL;      

    	$list.="<tr bgcolor='$bg'>
    			<td valign=top align=center>{$d['id']}</td>
    			<td valign=top>
              ���: <b>{$d['filename']}</b> <br/>
              ������: <b>{$size} {$suf}</b><br/>
              ���: <b>{$d['mime']}</b><br/>
              �������� ���: <a href='{$uu}{$d['s_filename']}'>{$d['s_filename']}</a> <br/>
              ��������: {$d['title']}
             </td>
    			<td valign=top>
              ���-�� ����.: {$dl}<br/>
              IP: {$d['ip']}<br/>
              �����: {$d['days_expired']} ��.<br/>              
             </td>
    			<td valign=top>
             {$t_upload} (c�����)  <br/>
             {$t_exp} (�������)<br/>
             �� ��������: {$t_del} �.
            </td>
    			<td valign=top >
              <a href='index.php?act=del&id={$d['id']}'>�������</a> <br/><br/>
              <a href='../index.php?d={$d['d']}'>�������� �����</a> <br/>    
            </td>
    		</tr>";
    };

// ���������� �������
    $sv->html="
    <table width=100% cellpadding=3>
    <tr>
    <td style='border:2px solid black;'><b>No.</td>
    <td style='border:2px solid black;'><b>����</td>
    <td style='border:2px solid black;'><b>������������</td>
    <td style='border:2px solid black;'><b>�����</td>
    <td style='border:2px solid black;'><b>�����</td>
    </tr>
    ".$list."
     <tr><td colspan=6 style='border-top:2px solid black;'>&nbsp;</td> </tr>
    <table>
    ";
};

// ������� ����
function this_del() {
	GLOBAL $sv, $std;
	
    $id = intval($sv->_get['id']);
    $sv->q("SELECT * FROM ".T_NAME." WHERE id='{$id}'");
    if ($sv->nr()>0) {
      $d = $sv->f();
      unlink(UPLOAD_DIR.$d['s_filename']);
      $sv->q("DELETE FROM ".T_NAME." WHERE id='{$id}'");
      $ret = ($sv->af() == 1) ? true : false;
    }
    return $ret;
}
           
// ���� �������� �����
function this_del_confirm()
{
  global $sv;
  $sv->html = "�� ������������� ������ ������� ���� (id: {$sv->_get['id']})?
  <br/><br/>
  [ <a href='index.php?act=del&id={$sv->_get['id']}&accepted=yes'>��</a> ] 
  &nbsp;  &nbsp;  &nbsp;  &nbsp;
  [ <a href='index.php'>���</a> ]  
  ";
};

// ���� ����� � �������    
function authform()
{
return "
<title>���� � �������</title>
<LINK REL='StyleSheet' HREF='style.css' TYPE='text/css'>
<form action=index.php method=post>
��� ������������: <br><input type=text name=new_login><br><br>
������: <br><input type=password name=new_pass><br><br>
<input type=submit><br><br>
</form>";
};

?>