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
        try
        {
            $db = new PDO(DB_PDO_DSN, DB_LOGIN, DB_PASSWORD);
            if($db)
            {
                $this->lnk=$db;
            }
        }
        catch (PDOException $e)
        {
            trigger_error(__FILE__.'>>'.__METHOD__.' error connecting to db!'.$e->getMessage());
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

        if (preg_match('~^insert~i', $mysql_query)) //Create
        {
            $type='INSERT';
            $ans=$db->res;
            $rows=$db->res->rowCount();
        }
        elseif (preg_match('~^update~i', $mysql_query)) //Edit
        {
            $type='UPDATE';
            $ans=$db->res;
            $rows=$db->res->rowCount();
        }
        elseif (preg_match('~^delete~i', $mysql_query)) //DELETE
        {
            $type='DELETE';
            $ans=$db->res;
            $rows=$db->res->rowCount();
        }
        elseif(@get_class($db->res)=='PDOStatement')
        {
            $ans=array();
            $fetch_objects=false;
            if (defined('DB_FETCH_ROW_AS_OBJECT'))
            {
                if (DB_FETCH_ROW_AS_OBJECT==true)
                {
                    $fetch_objects=true;
                }
            }


            if ($fetch_objects)
            {
                while ($a=$db->res->fetchObject())
                {
                    $ans[]=$a;
                }

            }
            else
            {
                while ($a=$db->res->fetch(PDO::FETCH_ASSOC))
                {
                    $ans[]=$a;
                }
            }

            $type='SELECT';
            $rows=$db->res->rowCount();
        }
        else
        {
            $type='UNKNOWN';
            $ans=$db->res;
            $rows=false;
        }



        $exectime=microtime(true)-$now;
        if (DB_QUERY_LOGGING)
        {
            $db->stats[]=array('type'          =>$type,
                'query'         =>$mysql_query,
                'time'          =>round((1000*$exectime), 2),
                'status'        =>($db->lnk->errorInfo()) ? 'OK' : implode($db->lnk->errorInfo(),', '),
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
        return $d->lnk->lastInsertId();
    }

    public static function getError()
    {
        $db=DB::init();
        return ($db->lnk->errorInfo()) ? 'OK' : implode($db->lnk->errorInfo(),', ');
    }

    public static function s()
    {
        $a=DB::init()->stats;
        return $a;
    }

    public static function f($string_to_escape)
    {
        $db=DB::init();
        $tmp=$db->lnk->quote($string_to_escape);
        $tmp=substr($tmp, 1);//убираем кавычку слева
        $tmp=substr($tmp, 0, strlen($tmp)-1);//убираем кавычку справа
        //preg_match('~(*UTF8)^\'(.+)\'$~',$tmp,$a);
        return($tmp);
    }


    public static function insert($table_name, $assosiated_array_of_values)
    {

        $columns='`'.implode('`,`', array_keys($assosiated_array_of_values)).'`';
        $vals=array();
        foreach ($assosiated_array_of_values as $val)
        {
            $vals[]=DB::f($val);
        }
        $values=implode(",", $vals);
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
            unset($this->lnk);
        }
    }
}