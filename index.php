<?php
include('config.php');
include('admin/class_func.php');
$std = new func;
$sv->db_connect();

// удаление файлов у которых истек срок хранения
$std->delete_expired(); 

// если задан верный идентификатор файла то показываем инфо либо скачиваем его
if (isset($sv->_get['d']) && $std->check_key($sv->_get['d']) ) {

  // если задан параметр для начала скачивания, то начинаем скачивать
  if (isset($sv->_get['download'])) {    
  
    // проверям лимит скачиваний
    if ($sv->file['max_dl'] != 0 && $sv->file['dl'] >= $sv->file['max_dl']) {
      $err = true;    
      $sv->msgs[] = "Лимит скачиваний достигнут. Данный файл больше не может быть скачан.";
    }
    // если все ок то скачиваем
    else {
      $std->inc_count($sv->file); // увеличиваем счетчик
      $std->download_file($sv->file, true, (1024*1024*5));  // скачиваем, максимальная скорость скачики 5МБ в сек
    }
    
  }
  // иначе показываем информацию
  else {
    $html = $std->get_download_screen($sv->file);
    include("main.html");    
  }
}
// если идентификатор не указан, показываем главную или загружаем файл
else {

    
  // если получен файл то делаем проверки и добавляем, иначе показываем форму загрузки
  if (isset($sv->_post['title']) && isset($sv->_files['file']['error']) && $sv->_files['file']['error'] == 0) {
    
    // проверки при загрузке файла
    $err = false; $msgs = array(); $d=array();

    // 2.1.1 проверяем название
    $d['title'] = trim ($std->protect($sv->_post['title'], 'cut_html'));
    if ($d['title'] == "") {
      $err = true;
      $msgs[] = "не заполнено обязательное поле - краткое описание файла";
    }
    
    // 2.1.2 проверяем введенный лимит скачиваний
    $d['max_dl'] = intval($sv->_post['max_dl']);   
    if ($d['max_dl']<1 || $d['max_dl']>1000){
      $d['max_dl'] = 0;
    }

    // 2.1.3 проверям максимальное время хранения файла
    $d['days_expired'] = trim ($std->protect($sv->_post['days_expired']));
    $d['days_expired'] = (is_scalar($d['days_expired'])) ? doubleval($d['days_expired']) : MAX_DAY_EXPIRED;
    if ($d['days_expired']<=0 || $d['days_expired']>MAX_DAY_EXPIRED){
      $d['days_expired'] = MAX_DAY_EXPIRED;
    }
    $d['t_exp'] = $sv->post_time + ($d['days_expired']*24*60*60);

    // 2.1.4 проверям расширение
    $d['filename'] = $sv->_files['file']['name'];
    $ext = $std->file_extension($d['filename']);
    if (in_array($ext, $not_allowed_ext)) {
      $err = true;
      $msgs[] = "расширение файла входит в список запрещенных для закачки (".implode(', ', $not_allowed_ext).")";
    } 
    $d['filename'] = $std->protect($d['filename']);
        
    // 2.1.5 mime тип файла
    $d['mime'] = trim ($std->protect($sv->_files['file']['type']));
    
    // 2.1.6 проверям размер файла 
    $d['size'] = trim ($std->protect($sv->_files['file']['size']));
    if ($d['size']>MAX_FILE_SIZE) {
      $err = true;
      $size = round($d['size']/1024, 2); $suf = "Kb";
      if ($size>1024) { $size = round($size/1024, 2);  $suf = 'Mb';}
      $limit = round(MAX_FILE_SIZE/1024/1024,2);
      $msgs[] = "Размер файла ({$size} {$suf}) больше разрешенного [{$limit} MB].";
    }     
    
    // 2.1.7 генерирурем имя файла для сохранения
    $d['s_filename'] = uniqid(rand()).".tmp";
    while(file_exists(UPLOAD_DIR.$d['s_filename'])){
      $d['s_filename'] = uniqid(rand()).".tmp";
    }
    
    // 2.1.8 генерируем уникальный идентификатор
    $d['d'] = uniqid(rand());
   
    
    // если нет ошибок премещаем файл из темпа
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
        $msgs[] = "данные не добавлены в базу";             
      }
    }
    else {
      if (!$err) {
        $msgs[] = "файл не перемещен из темпа {$sv->_files['file']['tmp_name']} => ".UPLOAD_DIR.$d['s_filename'];
      }
      $err = true;
    }
  }
  // показываем форму загрузки
  else {
    $html = $std->get_upload_form();
    include("main.html");    
  }
  
  if ($err) {
    $html = "<META HTTP-EQUIV=\"Refresh\" CONTENT=\"5; URL=index.php\"><center>".implode("<br/>", $msgs).
    "<br><br>Сейчас вы будете <a href='index.php'>перемещены</a>.";
    include("main.html");
  }
}    
    
?>