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
        public static function init()
            {
                if (is_null(self::$instance))
                    {
                        self::$instance=new DB();
                    }
                return self::$instance;
            }

        private function __construct()
            {
                if (DB_PERSISTENT)
                    {
                        $link=mysql_pconnect(DB_HOST, DB_LOGIN, DB_PASSWORD);
                    }
                else
                    {
                        $link=mysql_connect(DB_HOST, DB_LOGIN, DB_PASSWORD);
                    }

                if (mysql_ping($link))
                    {
                        if (mysql_select_db(DB_DATABASE, $link))
                            {
                                $this->lnk=$link;
                                mysql_set_charset(DB_CHARSET, $this->lnk);
                            }
                        else
                            {
                                trigger_error(__FILE__.'>>'.__METHOD__.' No database!');
                            }
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

                $db->res=mysql_query($mysql_query, $db->lnk);
                if (gettype($db->res)!='boolean')
                    {
                        if (gettype($db->res)=='resource')
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
                                        while ($a=mysql_fetch_object($db->res))
                                            {
                                                $ans[]=$a;
                                            }

                                    }
                                else
                                    {
                                        while ($a=mysql_fetch_assoc($db->res))
                                            {
                                                $ans[]=$a;
                                            }
                                    }
                                $type='SELECT';
                                $rows=mysql_num_rows($db->res);
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
                                $rows=mysql_affected_rows($db->lnk);

                            }
                        elseif (preg_match('~^update~i', $mysql_query)) //Edit
                            {
                                $type='UPDATE';
                                $ans=$db->res;
                                $rows=mysql_affected_rows($db->lnk);
                            }
                        elseif (preg_match('~^delete~i', $mysql_query)) //DELETE
                            {
                                $type='DELETE';
                                $ans=$db->res;
                                $rows=mysql_affected_rows($db->lnk);
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
                                           'status'        =>(mysql_error($db->lnk)=="") ? 'OK' : 'MySQL error: '.mysql_error($db->lnk),
                                           'affected rows' =>$rows

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
                return mysql_insert_id($d->lnk);
            }

        public static function getError()
            {
                $d=DB::init();
                return (mysql_error($d->lnk)!="") ? mysql_error($d->lnk) : false;
            }

        public static function s()
            {
                $a=DB::init()->stats;
                return $a;
            }

        public static function f($string_to_escape)
            {
                $a=DB::init();
                return mysql_real_escape_string($string_to_escape, $a->lnk);
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


        public function __destruct()
            {
                if ($this->lnk)
                    {
                        mysql_close($this->lnk);
                    }
            }
    }