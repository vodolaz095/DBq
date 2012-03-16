<?php
class DB
    {
        protected static $instance;
        private $lnk;
        private $res;
        private $stats=array();

/*
* Singleton get instanse
*/
        private  function init()
            {
                if (is_null(self::$instance))
                    {
                        self::$instance=new DB();
                    }
                return self::$instance;
            }

        private function __construct()
            {
                $db=new SQLite3(DB_PATH_TO_SQLITE_FILE);
                if (get_class($db)=='SQLite3')
                    {
                        $this->lnk=$db;
                    }
                else
                    {
                        trigger_error(__FILE__.'>>'.__METHOD__.' error connecting to db!');
                    }
            }

////////////////////////////////////////
        public static function q($mysql_query)
            {
                $db=DB::init();
                $now=microtime(true);

                $db->res=false;

                $mysql_query=trim($mysql_query);

                $db->res=$db->lnk->query($mysql_query);
                if (gettype($db->res)!='boolean')
                    {
                        if (get_class($db->res)=='SQLite3Result')
                            {
                                $ans=array();

                                $fetch_objects=false;
                                if (defined('DB_FETCH_ROW_AS_OBJECT')) {
                                    if (DB_FETCH_ROW_AS_OBJECT==true)
                                        {
                                            $fetch_objects=true;
                                        }
                                }


                                if ($fetch_objects)
                                    {
                                        while ($a=$db->res->fetchArray(SQLITE3_ASSOC))
                                            {
                                                $ans[]=(object)$a;
                                            }

                                    }
                                else
                                    {
                                        while ($a=$db->res->fetchArray(SQLITE3_ASSOC))
                                            {
                                                $ans[]=$a;
                                            }
                                    }
                                $type='SELECT';
                                $rows=$db->res->numColumns();

                                $db->res->finalize();
                            }
                        else
                            {
                                $type='UNKNOWN';
                                $ans=false;
                                $rows=0;
                            }

                    }
                else
                    {
                        if (preg_match('~^insert~i', $mysql_query)) //Create
                            {
                                $type='INSERT';
                                $ans=$db->res;
                                $rows=$db->lnk->changes();

                            }
                        elseif (preg_match('~^update~i', $mysql_query)) //Edit
                            {
                                $type='UPDATE';
                                $ans=$db->res;
                                $rows=$db->lnk->changes();
                            }
                        elseif (preg_match('~^delete~i', $mysql_query)) //DELETE
                            {
                                $type='DELETE';
                                $ans=$db->res;
                                $rows=$db->lnk->changes();
                            }
                        else
                            {
                                $type='UNKNOWN';
                                $ans=$db->res;
                                $rows=false;
                            }

                    }

                $exectime=microtime(true)-$now;
                if (DB_QUERY_LOGGING)
                    {
                        $db->stats[]=array('type'          =>$type,
                                           'query'         =>$mysql_query,
                                           'time'          =>round((1000*$exectime), 2),
                                           'status'        =>($db->lnk->lastErrorCode()==0 or $db->lnk->lastErrorCode()==101) ? 'OK' : 'SQLite3 error: '.$db->lnk->lastErrorMsg(),
                                           'affected rows' =>$rows,

                        );
                    }

                return $ans;
            }

        public static function getLink()
            {
                $d=DB::init();
                return $d->lnk;
            }

        public static function getRes()
            {
                $d=DB::init();
                return $d->res;
            }

        public static function getLastInsertId()
            {
                $d=DB::init();
                return $d->lnk->lastInsertRowID();
            }

        public static function getError()
            {
                $db=DB::init();
                return ($db->lnk->lastErrorMsg()!="") ? $db->lnk->lastErrorMsg() : false;
            }

        public static function s()
            {
                $a=DB::init()->stats;
                return $a;
            }

        public static function f($string_to_escape)
            {
                $a=DB::init();
                return $a->lnk->escapeString($string_to_escape);
            }


        public static function insert($table_name, $assosiated_array_of_values)
            {

                $columns='`'.implode('`,`', array_keys($assosiated_array_of_values)).'`';
                $vals=array();
                foreach ($assosiated_array_of_values as $val)
                    {
                        $vals[]=DB::f($val);
                    }
                $values='"'.implode('","', $vals).'"';
                $q='INSERT INTO `'.$table_name.'`('.$columns.') VALUES ('.$values.')';
                $a=DB::q($q);
                return $a;
            }

        public static function update($table_name, $assosiated_array_of_values, $string_where)
            {
                $columns=array_keys($assosiated_array_of_values);
                $vals=array();
                foreach ($columns as $column)
                    {
                        $vals[]='`'.$column.'`="'.DB::f($assosiated_array_of_values[$column]).'"';
                    }
                $values=implode(',', $vals);
                $q='UPDATE `'.$table_name.'` SET '.$values.' WHERE '.$string_where;
                $a=DB::q($q);
                return $a;
            }

        public static function GetDriver()
            {
                return 'SQLite3';
            }

        public   function __destruct()
            {
                if (get_class($this->lnk)=='SQLite3')
                    {
                        $this->lnk->close();
                    }
            }
    }
