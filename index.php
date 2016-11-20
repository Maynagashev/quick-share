<?php
include('config.php');
include('admin/class_func.php');
$std = new func;
$sv->db_connect();

// �������� ������ � ������� ����� ���� ��������
$std->delete_expired(); 

// ���� ����� ������ ������������� ����� �� ���������� ���� ���� ��������� ���
if (isset($sv->_get['d']) && $std->check_key($sv->_get['d']) ) {

  // ���� ����� �������� ��� ������ ����������, �� �������� ���������
  if (isset($sv->_get['download'])) {    
  
    // �������� ����� ����������
    if ($sv->file['max_dl'] != 0 && $sv->file['dl'] >= $sv->file['max_dl']) {
      $err = true;    
      $sv->msgs[] = "����� ���������� ���������. ������ ���� ������ �� ����� ���� ������.";
    }
    // ���� ��� �� �� ���������
    else {
      $std->inc_count($sv->file); // ����������� �������
      $std->download_file($sv->file, true, (1024*1024*5));  // ���������, ������������ �������� ������� 5�� � ���
    }
    
  }
  // ����� ���������� ����������
  else {
    $html = $std->get_download_screen($sv->file);
    include("main.html");    
  }
}
// ���� ������������� �� ������, ���������� ������� ��� ��������� ����
else {

    
  // ���� ������� ���� �� ������ �������� � ���������, ����� ���������� ����� ��������
  if (isset($sv->_post['title']) && isset($sv->_files['file']['error']) && $sv->_files['file']['error'] == 0) {
    
    // �������� ��� �������� �����
    $err = false; $msgs = array(); $d=array();

    // 2.1.1 ��������� ��������
    $d['title'] = trim ($std->protect($sv->_post['title'], 'cut_html'));
    if ($d['title'] == "") {
      $err = true;
      $msgs[] = "�� ��������� ������������ ���� - ������� �������� �����";
    }
    
    // 2.1.2 ��������� ��������� ����� ����������
    $d['max_dl'] = intval($sv->_post['max_dl']);   
    if ($d['max_dl']<1 || $d['max_dl']>1000){
      $d['max_dl'] = 0;
    }

    // 2.1.3 �������� ������������ ����� �������� �����
    $d['days_expired'] = trim ($std->protect($sv->_post['days_expired']));
    $d['days_expired'] = (is_scalar($d['days_expired'])) ? doubleval($d['days_expired']) : MAX_DAY_EXPIRED;
    if ($d['days_expired']<=0 || $d['days_expired']>MAX_DAY_EXPIRED){
      $d['days_expired'] = MAX_DAY_EXPIRED;
    }
    $d['t_exp'] = $sv->post_time + ($d['days_expired']*24*60*60);

    // 2.1.4 �������� ����������
    $d['filename'] = $sv->_files['file']['name'];
    $ext = $std->file_extension($d['filename']);
    if (in_array($ext, $not_allowed_ext)) {
      $err = true;
      $msgs[] = "���������� ����� ������ � ������ ����������� ��� ������� (".implode(', ', $not_allowed_ext).")";
    } 
    $d['filename'] = $std->protect($d['filename']);
        
    // 2.1.5 mime ��� �����
    $d['mime'] = trim ($std->protect($sv->_files['file']['type']));
    
    // 2.1.6 �������� ������ ����� 
    $d['size'] = trim ($std->protect($sv->_files['file']['size']));
    if ($d['size']>MAX_FILE_SIZE) {
      $err = true;
      $size = round($d['size']/1024, 2); $suf = "Kb";
      if ($size>1024) { $size = round($size/1024, 2);  $suf = 'Mb';}
      $limit = round(MAX_FILE_SIZE/1024/1024,2);
      $msgs[] = "������ ����� ({$size} {$suf}) ������ ������������ [{$limit} MB].";
    }     
    
    // 2.1.7 ����������� ��� ����� ��� ����������
    $d['s_filename'] = uniqid(rand()).".tmp";
    while(file_exists(UPLOAD_DIR.$d['s_filename'])){
      $d['s_filename'] = uniqid(rand()).".tmp";
    }
    
    // 2.1.8 ���������� ���������� �������������
    $d['d'] = uniqid(rand());
   
    
    // ���� ��� ������ ��������� ���� �� �����
    if (!$err && move_uploaded_file($sv->_files['file']['tmp_name'], UPLOAD_DIR.$d['s_filename'])){
      
      $sql = "INSERT INTO ".T_NAME."(d, t_upload, t_last, t_exp, 
                                     title, s_filename, filename, size, mime, max_dl, dl, ip, days_expired)
              VALUES ('".$d['d']."',
                      '".$sv->post_time."', '".$sv->post_time."', '".$d['t_exp']."', 
                      '".$d['title']."', '".$d['s_filename']."', '".$d['filename']."',  
                      '".$d['size']."', '".$d['mime']."', '".$d['max_dl']."', '0', '". $sv->ip ."', '".$d['days_expired']."')";
      $sv->q($sql);               
      if ($sv->af()>0) {                
        $html = $std->get_uploaded_msg($d);
        include("main.html");        
      }
      else {
        $err = true;
        $msgs[] = "������ �� ��������� � ����";             
      }
    }
    else {
      if (!$err) {
        $msgs[] = "���� �� ��������� �� ����� {$sv->_files['file']['tmp_name']} => ".UPLOAD_DIR.$d['s_filename'];
      }
      $err = true;
    }
  }
  // ���������� ����� ��������
  else {
    $html = $std->get_upload_form();
    include("main.html");    
  }
  
  if ($err) {
    $html = "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"5; URL=index.php\"><center>".implode("<br/>", $msgs).
    "<br><br>������ �� ������ <a href='index.php'>����������</a>.";
    include("main.html");
  }
}    
    
?>