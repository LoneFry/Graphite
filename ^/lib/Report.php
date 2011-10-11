<?php
/*****************************************************************************
 * Project     :
 *
 * Created By  : Tyler Uebele
 *
 * License     :
 *
 * File        : /^/lib/Report.php
 *                Base Class for Report Models
 ****************************************************************************/

//CORE should be defined as evidence we are not requested directly
if(!defined('CORE')){header("Location: /");exit;}

require_once LIB.'/DataModel.php';

abstract class Report extends DataModel {
    protected $_data=array();
    protected $_start=0;
    protected $_count=10000;
	protected $_order=null;
	protected $_orders=array();

    public function __construct($a=null,$b=null){
        if(!isset(static::$query) || ''==static::$query){
            throw new Exception('Report class defined with no query.');
        }
		parent::__construct($a,$b);
    }
    public function load(){
        $this->_data=array();
        $query=array();
        foreach(static::$vars as $k =>$v){
			if(isset($this->vals[$k]) && null!=$this->vals[$k]){
	            $query[]=sprintf($v['sql'],G::$m->escape_string($this->vals[$k]));
			}
        }
        if(count($query) == 0){
	        $query=sprintf(static::$query,'1');
	    }else{
    	    $query=sprintf(static::$query,implode(' AND ',$query));
    	}

		//if an order has been set, add it to the query
		if(null!==$this->_order){
			$query.=' ORDER BY `'.$this->_order.'`';
		}

    	//add limits also
    	$query.=' LIMIT '.$this->_start.', '.$this->_count;

        if(false===$result=G::$m->query($query)){
            return false;
        }
        if(0==$result->num_rows){
            $result->close();
            return $this->_data;
        }
        while($row=$result->fetch_assoc()){
            $this->_data[]=$row;
        }
		$result->close();
		$this->onload();
    }
    public function toArray(){
        return $this->_data;
    }
    public function toJSON(){
        return json_encode($this->_data);
    }
    public function __set($k,$v){
    	if('_start'==$k && is_numeric($v)){
    		return $this->_start=(int)$v;
    	}
    	if('_count'==$k && is_numeric($v)){
    		return $this->_count=(int)$v;
    	}
    	if('_order'==$k && in_array($v,$this->_orders)){
    		return $this->_count=$v;
    	}

		return parent::__set($k,$v);
	}
}
