<?php 
include('../config.php');
$sv->db_connect();

$sv->q("

CREATE TABLE ".T_NAME." (
  id int(11) NOT NULL auto_increment,
    
  d VARCHAR(40) NOT NULL DEFAULT '',

  t_upload int(11) NOT NULL DEFAULT '0',
  t_last int(11) NOT NULL DEFAULT '0',
  t_exp int(11) NOT NULL DEFAULT '0',
  days_expired float NOT NULL DEFAULT '0',
      
  title VARCHAR(250) NOT NULL DEFAULT '',
  email VARCHAR(250) NOT NULL DEFAULT '',
  ip VARCHAR(250) NOT NULL DEFAULT '',
      
  s_filename VARCHAR(250) NOT NULL  DEFAULT '',    
  filename VARCHAR(250) NOT NULL  DEFAULT '',
  size int(11) NOT NULL DEFAULT '0',
  mime VARCHAR(250) NOT NULL DEFAULT '',
  
  max_dl int(11) NOT NULL DEFAULT '0',
  dl int(11) NOT NULL DEFAULT '0',
      
  PRIMARY KEY  (id), 
  KEY (`d`)
) TYPE=MyISAM DEFAULT CHARSET=utf8;
  
");

$sv->q("

CREATE TABLE ".TABLE_ADMIN." (
  id int(11) NOT NULL auto_increment,
  sid varchar(250) NOT NULL default '',
  time int(11) NOT NULL default '0',
  ip varchar(100) NOT NULL default '',
  PRIMARY KEY  (id),
  KEY id (id)
) TYPE=MyISAM DEFAULT CHARSET=utf8;

");

echo "<div style='border:1px dashed black; background-color: #efefef; padding:10px;font-family:verdana;'>
			<b>Script successfully installed.<br><br>
			&raquo; <a href=../index.php style='color:blue;'>Go to upload page</a>

			<br><br>
			&raquo; <a href=../admin/ style='color:blue;'>Go to admin</a>
			
			</div>";

?>