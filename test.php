<?php

require_once 'DB.php';

DB::q('SET NAMES utf8');
DB::q('CREATE TABLE IF NOT EXISTS `foo` (`bar` CHAR(63))');
DB::q('DELETE FROM `foo` WHERE 1');
DB::q('INSERT INTO foo (bar) VALUES ("This is a test performed on '.date('Y-m-d H:i:s').'")');
DB::q('INSERT INTO foo (bar) VALUES ("This is a test performed on '.date('Y-m-d H:i:s').'")');
DB::q('INSERT INTO foo (bar) VALUES ("This is a test performed on '.date('Y-m-d H:i:s').'")');
$query='SELECT bar FROM foo';

$array=DB::q($query);
print_r($array);

define('DB_FETCH_ROW_AS_OBJECT', true);
$obj=DB::q($query);

print_r($obj);
print_r(DB::s());
