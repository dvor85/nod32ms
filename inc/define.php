<?php
define("AUTHOR_NAME",   "Pavel A.");
define("AUTHOR_ALIAS",  "AlexCo"); 
define("AUTHOR_MAIL",   "alexco_admin@mail.ru");  

define("MASTERHOST",    "213.141.141.87"); 
 
define("DS",        DIRECTORY_SEPARATOR);
define('SELF',      substr(dirname(__FILE__),0,-3)); 
define('SYSTEM',    SELF."system".DS);
define('PATTERN',   SELF."system".DS."pattern".DS);
define('TOOLS',     SELF."system".DS."tools".DS);
define('TEMP_DIR',  SELF."tmp".DS); 

define('INC',       dirname(__FILE__));
define('CLASSES',   dirname(__FILE__).DS."classes".DS); 
?>
