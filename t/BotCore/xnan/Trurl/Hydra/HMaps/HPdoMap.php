<?php

namespace xnan\Trurl\Hydra\HMaps;

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;

// Uses: Nano
use xnan\Trurl;
//use xnan\Trurl\Nano;
use xnan\Trurl\Nano\Log;
use xnan\Trurl\Nano\Performance;
use xnan\Trurl\Nano\Lock;
Trurl\Functions::Load;
Log\Functions::Load;

// Uses: Hydra
use xnan\Trurl\Hydra;
use xnan\Trurl\Hydra\HMaps;
use xnan\Trurl\Hydra\HRefs;
use xnan\Trurl\Hydra\HMatrix;

//Uses: End

class HUnsupportedType extends \Exception {};

class HPdoMap {

	var $name=null;
	var $id;
	var $values;	
	var $changed;
	var $pdo;

	function __construct($id,$name=null) {		
		if ($id===null) Nano\nanoCheck()->checkFailed("id cannot be null");
		$this->id=$id;
		$this->changed=true;
		$this->name=$name;
		$this->connect();
	}


	private function connect() {
		$this->pdo = new \PDO(
		    sprintf('mysql:host=%s;dbname=%s',
		    	Hydra\hydra()->settings()->hostname(),
		    	Hydra\hydra()->settings()->database()),
		    Hydra\hydra()->settings()->user(),
		    Hydra\hydra()->settings()->password());		
	}

	function id() {
		return $this->id;
	}

	function name() {
		return $this->name;
	}
	function isHydrated() {
		return !($this->values===null);
	}
	
	function mapExists() {
		return $this->pdoMapExists();
	}

	function hydrate() {
		Hydra\hydra()->performance()->track("HPdoMap.hydrate");			
		if ($this->mapExists()) {						
			$this->pdoLoad();
			if ($this->values===null) $this->values=[];
			$this->changed=false;
		} else {			
			$this->values=[];
			$this->changed=true;			
		}
		(HMaps\HMaps::instance())->notifyHydrated($this);
		Hydra\hydra()->performance()->track("HPdoMap.hydrate");
	}

	function dehydrate() {		
		if ($this->changed) {
			Hydra\hydra()->performance()->track("HPdoMap.dehydrate");

			if (!$this->mapExists()) {
				$this->pdoMapCreate();
			}
			$this->pdoStore();
			
			Hydra\hydra()->performance()->track("HPdoMap.dehydrate");
		}
		$this->values=null;		
	}

	function hydrateIfReq() {
		if (!$this->isHydrated()) {
			$this->hydrate();
		}
	}

	function dehydrateIfReq() {
		if ($this->isHydrated()) {
			$this->dehydrate();
		}
	}

	function markChanged() {
		$this->hydrateIfReq();
		$this->changed=true;
	}
	

	function hasKey($key) {
		$this->hydrateIfReq();
		if ($this->values===NULL) Nano\nanoCheck()->checkFailed("values cannot be null");
		return array_key_exists($key,$this->values);
	}


	function contains(&$value) {
		$this->pdoCheckType($value);
		$this->hydrateIfReq();
		return in_array($value,$this->values);
	}

	function values($values=null) {
		$this->hydrateIfReq();
		if ($values!=null) {
			$this->pdoCheckTypes($values);
			$this->values=$values;
			$this->changed=true;
		} 		
		return array_values($this->values);
	}

	function keys($keys=null) {
		$this->hydrateIfReq();
		if ($keys!=null) {
			$this->values=array_flip($keys);
		}
		return array_keys($this->values);
	}

	function get($key) {
		$this->hydrateIfReq();
		return $this->values[$key];
	}

	function reset() {
		$this->hydrateIfReq();
		$this->changed=true;
		return $this->values=[];
	}

	function count() {
		$this->hydrateIfReq();
		return count($this->values);
	}

	function shift() {
		$this->hydrateIfReq();
		array_shift($this->values);
		$this->changed=true;
	}

	function set($key,&$value) {
		$this->pdoCheckType($value);
		$this->hydrateIfReq();
		$this->values[$key]=$value;
		$this->changed=true;
	}


	function insert(&$value) {
		$this->pdoCheckType($value);
		$this->hydrateIfReq();
		$this->values[]=$value;
		$this->changed=true;
	}

	function kill() {
		if ($this->mapExists()) {
			$delQuery=sprintf("DROP TABLE %s",$this->table());		
			$this->pdo->query($delQuery);			
		}
	}

	function pdoCheckTypes(&$values) {
		foreach($values as $value) {
			pdoCheckType($value);
		}
		return true;
	}

	function pdoCheckType(&$value) {
		if (is_object($value) || is_array($value)) throw new HUnsupportedType();
		return true;
	}

	function pdoMapCreate() {
		$query=sprintf("CREATE TABLE %s (
		    map_key varchar(255),
		    map_value varchar(255))",$this->table());
		$this->pdo->query($query);
	}

	private function pdoKeyStore($key,$value) {
		$delQuery=sprintf("DELETE FROM hmap_%s WHERE map_key='%s'",
			$this->name(),
			$key);

		$query=sprintf("INSERT INTO hmap_%s (map_key,map_value) VALUES ('%s','%s')",
			$this->name(),
			$key,
			$value);
		
		$this->pdo->query($delQuery);
		$this->pdo->query($query);
	}

	private function pdoStore() {
			Hydra\hydra()->performance()->track("HPdoMap.pdoStore");
			$this->pdoKeyStore("_id",$this->id);
			$this->pdoKeyStore("_name",$this->name);

			foreach($this->values as $key=>$value) {
				$this->pdoKeyStore($key,$value);
			}
			Hydra\hydra()->performance()->track("HPdoMap.pdoStore");
	}

	private function pdoLoad() {
		Hydra\hydra()->performance()->track("HPdoMap.pdoLoad");
		$this->pdoKeyStore("_id",$this->id);
		$this->pdoKeyStore("_name",$this->name);
		
		$query=sprintf("SELECT * FROM %s",$this->pdoTable());
		
		$st=$this->pdo->query($query);

		$this->values=[];

		while($row=$st->fetch()) {
			$key=$row["map_key"];
			$value=$row["map_value"];

			if ($key=="_id") {
				$this->id=$value;
			} else if ($key=="_name") {
				$this->name=$value;
			} else {
				$this->values[$key]=$value;
			}
		}
		Hydra\hydra()->performance()->track("HPdoMap.pdoLoad");

	}

	function pdoTable() {
		return sprintf("hmap_%s",$this->name);
	}

	private function pdoQuery($query) {
		$s = $this->pdo->query($query);
		return $s;
	}

	private function pdoMapExists() {
		$query=sprintf("SELECT * 
				FROM information_schema.tables
				WHERE table_schema = '%s'
				    AND table_name = '%s'
				LIMIT 1",
			Hydra\hydra()->settings()->database(),
			$this->pdoTable());

		$s=$this->pdoQuery($query);
		
		$r=$s->fetch();
		$ret=!($r==FALSE);
		
		return $ret;
	}

}

?>