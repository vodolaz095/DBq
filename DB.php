<?php
/*
 * enter settings for connection to mysql database
 */

define('DB_HOST', 'localhost');
define('DB_LOGIN', 'root');
define('DB_PASSWORD', 'SeCReT');
define('DB_DATABASE', 'MyDatabase');
define('DB_PERSISTENT', false);
define('DB_QUERY_LOGGING', true);
define('DB_CHARSET', 'utf8');
/*
 * If you uncomment this line, DB::q($query) will return results as array of objects
 */
//define('DB_FETCH_ROW_AS_OBJECT',true);


/*
* Uncomment ONE driver which you want to use
*/

require_once 'dbdrv/MySQLi.php';
//require_once 'dbdrv/MySQL.php'; //old, not recommended!


/*
 * Quick reference


-------------------
|DB::q($sql_query)|
-------------------
perform $sql_query with active database. If query returns rows, return an associated arrayes of rows,
where rows are depicted as assotiated arrays or as objects if constant DB_FETCH_ROW_AS_OBJECT is true

Example

<?php
$databases=DB::q('SHOW DATABASES');
foreach($databases as $database)
    {
    echo $database['Database'].PHP_EOL;

// or when DB_FETCH_ROW_AS_OBJECT is TRUE
//    echo $database->Database.PHP_EOL;
    }


------------------
|DB::f(string $a)|
------------------

Return escaped version of the string.
See
http://php.net/manual/en/function.mysql-real-escape-string.php
http://php.net/manual/en/mysqli.real-escape-string.php
for details.

---------------------------------------------------------------------------
|DB::update($table_name,$assosiated_array_of_parameters,$where_condition);|
---------------------------------------------------------------------------
Update table.
For example

$arr=array('name'=>'Jon');
DB::update('USERS',$arr,'`id`=1');

Will execute query

UPDATE `USERS` SET `name`='Jon' WHERE `id`=1

NOTE: statistical data on query will be accaunted!

----------------------------------------------------------
|DB::insert($table_name,$assosiated_array_of_parameters);|
----------------------------------------------------------

Insert data table.
For example

$arr=array('name'=>'Jon');
DB::insert('USERS',$arr);

Will execute query

INSERT INTO `USERS`(`name`) VALUES ('Jon')

NOTE: statistical data on query will be accaunted!

----------
|DB::s() |
----------
Returns information about all queries executed during this script running.
This function returns array describing query information
For example

PRINT_R(DB::s())


array(
[0]=>array(
    'type'=>'SELECT',			//type of query
    'query'=>'SELECT * FROM `users`',	//query string
    'time'=>20,				//duration of execution of query in miliseconds
    'status'=>'OK',				//status of query
    'affected rows'=>451,			//number of rows affected
         );

[1]=>array(
    'type'=>'UPDATE',
    'query'=>'UPDATE `users` SET `name`="Pasha" WHERE id=2',
    'time'=>32,
    'status'=>'OK',
    'affected rows'=>1,
         );

[2]=>array(
    'type'=>'INSERT',
    'query'=>'INSERT INTO `users`(`name`) VALUES ("Pasha")',
    'time'=>33,
    'status'=>'MySQL error: duplicate key `name` for value "Pasha"',
    'affected rows'=>0
         );
)

---------------
|DB::getLink()|
---------------
Returns mysql link descriptor
for exapmle for use with mysql_query(' ,,, ',DB::getLink());
The data type of return value depends on active driver!

--------------
|DB::getRes()|
--------------
Returns current resource descriptor for
example for use with mysql_num_rows(DB::getRes());
The data type of return value depends on active driver!

-----------------------
|DB::getLastInsertId()|
-----------------------
Returns the ID of last inserted value, where id - is Auto Incrementing key of table;

----------------
|DB::getError()|
----------------
Returns the string with description of last mysql error


*/
