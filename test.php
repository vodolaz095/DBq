<?php
require_once 'DB.php';

DB::q('SET NAMES utf8');
$query = 'SHOW DATABASES';

$array = DB::q($query);
print_r($array);

define('DB_FETCH_ROW_AS_OBJECT', true);
$obj = DB::q($query);

print_r($obj);
print_r(DB::s());
