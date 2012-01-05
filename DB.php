<?php
/*
 * enter settings for connection to mysql database
 */

define('DB_HOST','localhost');
define('DB_LOGIN','root');
define('DB_PASSWORD','SeCReT');
define('DB_DATABASE','MyDatabase');
define('DB_PERSISTENT',false);
define('DB_QUERY_LOGGING',true);
define('DB_CHARSET','utf8');

/*
 * Uncomment ONE driver which you want to use
 */

require_once 'dbdrv/MySQLi.php';
//require_once 'dbdrv/MySQL.php'; //old, not recommended!




//EXAMPLE
/*
DB::q('INSERT into users(login) VALUES ("Vodolaz095")');
print_r(DB::q('SELECT * FROM users'));
$a=DB::init();
print_r($a->getStats());
*/

/*
 * enjoy
 */