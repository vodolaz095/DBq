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
        if ( is_null(self::$instance) )
        {
            self::$instance = new DB();
        }
        return self::$instance;
    }

    private function __construct()
    {
        if(DB_PERSISTENT)
            $link=mysql_pconnect(DB_HOST,DB_LOGIN,DB_PASSWORD);
        else
            $link=mysql_connect(DB_HOST,DB_LOGIN,DB_PASSWORD);

        if(mysql_ping($link))
        {
            if(mysql_select_db(DB_DATABASE,$link))
            {
                $this->lnk=$link;
                mysql_set_charset(DB_CHARSET,$this->lnk);
            }
            else
                trigger_error(__FILE__.'>>'.__METHOD__.' No database!');
        }
        else
            trigger_error(__FILE__.'>>'.__METHOD__.' error connecting to db!');
    }

////////////////////////////////////////
    public function query($mysql_query)
    {
        $now=microtime(true);

        $this->res=false;
        $mysql_query=trim($mysql_query);
        $this->res=mysql_query($mysql_query,$this->lnk);
        if(gettype($this->res)=='resource')
        {
            $ans=array();
            while($a=mysql_fetch_assoc($this->res))
            {
                $ans[]=$a;
            }
            $type='SELECT';
            $rows=mysql_num_rows($this->res);
        }
        else
        {
            if(preg_match('~^insert~i',$mysql_query)) //Create
            {
                $type='INSERT';
                $ans=$this->res;
                $rows=mysql_affected_rows($this->lnk);

            }
            elseif(preg_match('~^update~i',$mysql_query))//Edit
            {
                $type='UPDATE';
                $ans=$this->res;
                $rows=mysql_affected_rows($this->lnk);
            }
            elseif(preg_match('~^delete~i',$mysql_query))//DELETE
            {
                $type='DELETE';
                $ans=$this->res;
                $rows=mysql_affected_rows($this->lnk);
            }
            else
            {
                $type='UNKNOWN';
                $ans=$this->res;
                $rows=false;
            }

        }

        $exectime=microtime(true)-$now;

        if(DB_QUERY_LOGGING)
        {
            $this->stats[]=array(
                'type'=>$type,
                'query'=>$mysql_query,
                'time'=>round((1000*$exectime),2),
                'status'=>(mysql_error($this->lnk)=="") ? 'OK' : 'MySQL error: '.mysql_error($this->lnk),
                'affected rows'=>$rows
            );
        }
        return $ans;
    }

/////////////////////////// MISC
    public function getLink()
    {
        return $this->lnk;
    }

    public function getLastInsertId()
    {
        return mysql_insert_id($this->lnk);
    }

    public static function filter($a)
    {
        $a=trim($a);
        $lnk=DB::init()->getLink();
        $a=mysql_real_escape_string($a,$lnk);
        return $a;
    }

    public function getRes()
    {
        return $this->res;
    }

    public function getError()
    {
        return (mysql_error($this->lnk)!="") ? mysql_error($this->lnk) : false;
    }

    public function getNumberOfQueries()
    {
        return count($this->stats);
    }

    public function getStats()
    {
        return $this->stats;
    }
    /*
short
    */

    public static function q($query)
    {
        $a=DB::init()->query($query);
        return $a;
    }

    public static function f($string_to_escape)
    {
	    return DB::filter($string_to_escape);    
	  }
    
    public static function r()
    {
	    $a=DB::init()->getRes();
      return $a;    
	  }
	
    public static function err()
    {
	    $a=DB::init()->getError();
      return $a;    
  	}

    public static function s()
    {
	    $a=DB::init()->getStats();
        return $a;    
	  }

	
    /*
end_short
    */
    public static function insert($table_name,$assosiated_array_of_values)
    {

        $columns='`'.implode('`,`',array_keys($assosiated_array_of_values)).'`';
        $vals=array();
        foreach($assosiated_array_of_values as $val)
        {
            $vals[]=DB::f($val);
        }
        $values='"'.implode('","',$vals).'"';
        $q='INSERT INTO `'.$table_name.'`('.$columns.') VALUES ('.$values.')';
        $a=DB::q($q);
        return $a;
    }

    public static function update($table_name,$assosiated_array_of_values,$string_where)
    {
        $columns=array_keys($assosiated_array_of_values);
        $vals=array();
        foreach($columns as $column)
        {
            $vals[]='`'.$column.'`="'.DB::f($assosiated_array_of_values[$column]).'"';
        }
        $values=implode(',',$vals);
        $q='UPDATE `'.$table_name.'` SET '.$values.' WHERE '.$string_where;
        $a=DB::q($q);
        return $a;
    }


    public function __destruct()
    {
        mysql_close($this->lnk);
    }
}
