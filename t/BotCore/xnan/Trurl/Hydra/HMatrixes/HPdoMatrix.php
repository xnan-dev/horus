<?php
namespace xnan\Trurl\Hydra\HMatrixes;
//Uses: Start

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
//use xnan\Trurl\Hydra\HMatrixes;

//Uses: End

class HPdoMatrix implements  \Serializable {
	var $name=null;
	var $id;
	var $changed;
	var $dimensions;
	var $dataValueSize;
	var $data=null;
	var $pdo=null;
	var $isNew=true;

	function __construct($pdo,$name,$dimensions=[2,2]) {
		if ($pdo==null) Nano\nanoCheck()->checkFailed("pdo cannot be null");
		if ($dimensions==null) Nano\nanoCheck()->checkFailed("dimensions cannot be null");
		if ($dimensions==null) Nano\nanoCheck()->checkFailed("dimensions cannot be null");
		$this->pdo=$pdo;
		$this->changed=true;
		if ($name!=null) $this->name=$name;
		if ($dimensions!=null) $this->dimensions=$dimensions;
	}

	private function pdo() {
		return $this->pdo;
	}

	function id() {
		return $this->id;
	}

	function name() {
		return $this->name;
	}
	
	function isHydrated() {
		return !($this->data===null);
	}

	function initData() {		
		$length=1;
		$strZero=pack("d",0.0);
		//print "INITDATA strZero:'$strZero'<br>\n";
		$this->dataValueSize=strlen($strZero);
		$this->data=b"";
		for ($d=0;$d<count($this->dimensions);$d++) {
			$length=$length*$this->dimensions[$d];
		}		

		for($i=0;$i<$length;$i++) $this->data.=$strZero;	
	}

	function get($coordinates) {		
		$this->hydrateIfReq();
		$this->checkCoordinates($coordinates);

		$strZero=pack("d",0.0);
		$this->dataValueSize=strlen($strZero);

		$offset=$this->offset($coordinates);				
		$v=$this->getValueLinear($offset);
//		print "get $coord offset:$offset retValue:$v<br>";
		return $v;
	}

	function lastDimension() {
		return $this->dimensions[count($this->dimensions)-1];
	}

	function checkCoordinates($coordinates) {
		for ($d=0;$d<count($coordinates)-1;$d++) {
			if ($this->dimensions[$d]<$coordinates[$d]) Nano\nanoCheck()->checkFailed("dimensions out of range");
		}
		return true;
	}
	
	function shift($coordinates) { //1 dimension menos que la matriz
		$this->hydrateIfReq();
		$this->checkCoordinates($coordinates);

		$lastDim=$this->lastDimension();		
		for($i=0;$i<$lastDim-1;$i++) {
			$coord=$coordinates;
			$coord[count($coordinates)]=$i;
			$coordNext=$coord;
			$coordNext[count($coordinates)]=$i+1;
			$this->set($coord,$this->get($coordNext));
		}
		$this->set($coordNext,0);
	}

	function set($coordinates,$value) {				
		$this->hydrateIfReq();
		$this->checkCoordinates($coordinates);

		$strZero=pack("d",0.0);
		$this->dataValueSize=strlen($strZero);

		$offset=$this->offset($coordinates);
		//$coord=sprintf("[%s %s %s]",$coordinates[0],$coordinates[1],$coordinates[2]);		
		//print "set $coord offset:$offset value:$value<br>";
		$this->setValueLinear($offset,$value);		
		$this->changed=true;
	}

	function checkOffset($offset) {
		$maxOffset=$this->dataValueSize*$this->dimensionMul(count($this->dimensions));
		$maxOffsetIndex=$maxOffset/$this->dataValueSize;
		$offsetIndex=$offset/$this->dataValueSize;
		if ( $offset>$maxOffset) Nano\nanoCheck()->checkFailed("checkOffset: offset: $offset maxOffset:$maxOffset offsetIndex:$offsetIndex maxOffsetIndex:$maxOffsetIndex msg: out of range");
	}

	function getValueLinear($offset) {		
		$this->checkOffset($offset);		
		$value=unpack("d",$this->data,$offset)[1];
		return $value;
	}

	function setValueLinear($offset,$value) {		
		$packed=pack("d",$value);
		for($i=0;$i<strlen($packed);$i++)  {
			$this->data[$offset+$i]=$packed[$i];
		}
	}

	function dimensionMul($dimension,$show=false) {
		$m=1;
		for($d=0;$d<=$dimension-1;$d++) {
			$m=$m*$this->dimensions[$d];
			if ($show) print "INMUL d:$d, m:$m<br>";
		}
		return $m;
	}

	function offset($coordinates) {		
		$this->hydrateIfReq();

		if (!is_array($coordinates)) throw new \Exception("coordinates should be an array");
		$offset=$coordinates[0];
		for($d=1;$d<=count($this->dimensions)-1;$d++) {
			$coef=$coordinates[$d];
			$mul=$this->dimensionMul($d,false);
			$offset+=($coef*$mul);
			//print "c[$d]: $coef * mul:$mul => $offset\n<br>";
		}		
		
		$ret=$offset*$this->dataValueSize;		
		//$coord=print_r($coordinates,true);
		//print "RET: $coord: off ".($ret/4)."<br>";
		return $ret;
	}


	private function createTableIfReq() {

		$sql="CREATE TABLE `hmatrix` (
			`id` INT(11) NULL DEFAULT NULL,
			`name` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
			`dimensions` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
			`dataValueSize` INT(11) NULL DEFAULT NULL,
			`data` INT(11) NULL DEFAULT NULL
		)
		COLLATE='utf8mb4_general_ci'
		ENGINE=InnoDB"
		;

		$this->pdo()->query($sql);
	}

	private function matrixRow() {
		$sql=sprintf("SELECT * FROM hmatrix WHERE name='%s'",$this->name());		
		$r=$this->pdo()->query($sql);
		return $r->fetch();
	}

	function isNew() {
		$this->hydrateIfReq();
		return $this->isNew;
	}

	function hydrate() {
		Hydra\hydra()->performance()->track("HMatrix.hydrate");
		
		$this->createTableIfReq();

		if ($row=$this->matrixRow() ) {
			$this->id=$row["id"];
			$this->name=$row["name"];	
			$this->dimensions=unserialize($row["dimensions"]);	
			$this->dataValueSize=$row["dataValueSize"];
			$this->data=$row["data"];			
			if ($this->data===null) $this->initData();
			$this->changed=false;
			$this->isNew=false;			
		} else {
			$this->initData();			
			$this->changed=true;
			$this->isNew=true;
		}
		
		//(HMatrixes::instance())->notifyHMatrixHydrated($this);

		Hydra\hydra()->performance()->track("HMatrix.hydrate");		
	}


	function dehydrate() {				
		if ($this->changed) {
			Hydra\hydra()->performance()->track("HMatrix.dehydrate");


			if ($row=$this->matrixRow()) {

			} else {

				$query=sprintf("
						INSERT INTO hmatrix(name,dimensions,dataValueSize) 
							VALUES ('%s','%s',%s)
							",$this->name,\serialize($this->dimensions),$this->dataValueSize);

				$this->pdo()->query($query);
			}

			
			$query=sprintf("UPDATE hmatrix SET data = :data  
				WHERE name LIKE '%s' ",$this->name);

			$st = $this->pdo()->prepare($query);

			$st->bindParam(':data', $this->data, \PDO::PARAM_LOB);
			$r=$st->execute();			

//			print_r([strlen($this->data),"r"=>$r,"query"=>$query,"errorInfo:",$st->errorInfo()]);

			Hydra\hydra()->performance()->track("HMatrix.dehydrate");	
		}
		$this->data=null;		
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

	function dimensions() {
		return $this->dimensions;
	}

	function serialize() {
		return \serialize([$this->id,$this->name,$this->dimensions,$this->dataValueSize]);
	}
	
	function unserialize($serialized) {
		$arr=\unserialize($serialized);
		$this->id=$arr[0];
		$this->name=$arr[1];
		$this->dimensions=$arr[2];
		$this->dataValueSize=$arr[3];
	}
}

?>