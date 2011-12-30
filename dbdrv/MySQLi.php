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
            $link=mysqli_connect('p:'.DB_HOST, DB_LOGIN, DB_PASSWORD, DB_DATABASE);
        else
            $link=mysqli_connect(DB_HOST, DB_LOGIN, DB_PASSWORD, DB_DATABASE);

        if(mysqli_ping($link))
        {
            $this->lnk=$link;
            mysqli_set_charset($this->lnk,DB_CHARSET);
        }
        else
            trigger_error(__FILE__.'>>'.__METHOD__.' error connecting to db!');
    }

////////////////////////////////////////
    public function query($mysql_query)
    {
        $now=microtime(true);

        $this->res=false;

        $this->res=mysqli_query($this->lnk,$mysql_query);
        if(gettype($this->res)!='boolean')
        {
            if(get_class($this->res)=='mysqli_result')
            {
                $ans=array();
                while($a=mysqli_fetch_assoc($this->res))
                {
                    $ans[]=$a;
                }
                $type='SELECT';
                $rows=mysqli_num_rows($this->res);
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
            if(preg_match('~^insert~i',$mysql_query)) //Create
            {
                $type='INSERT';
                $ans=$this->res;
                $rows=mysqli_affected_rows($this->lnk);

            }
            elseif(preg_match('~^update~i',$mysql_query))//Edit
            {
                $type='UPDATE';
                $ans=$this->res;
                $rows=mysqli_affected_rows($this->lnk);
            }
            elseif(preg_match('~^delete~i',$mysql_query))//DELETE
            {
                $type='DELETE';
                $ans=$this->res;
                $rows=mysqli_affected_rows($this->lnk);
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
                'status'=>(mysqli_error($this->lnk)=="") ? 'OK' : 'MySQL error: '.mysqli_error($this->lnk),
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
        return mysqli_insert_id($this->lnk);
    }

    public static function filter($a)
    {
        $a=trim($a);
        $a=mysqli_real_escape_string($a);
        return $a;
    }

    public function getRes()
    {
        return $this->res;
    }

    public function getError()
    {
        return (mysqli_error($this->lnk)!="") ? mysqli_error($this->lnk) : false;
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
    public function __destruct()
    {
        mysqli_close($this->lnk);
    }
}
