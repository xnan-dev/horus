<?php
namespace xnan\Trurl\Horus\Persistence;
use xnan\Trurl;
use xnan\Trurl\Horus;
use xnan\Trurl\Horus\AssetTradeOperation;
use xnan\Trurl\Horus\AssetTradeStatus;
use xnan\Trurl\Horus\AssetTradeOrder;
use xnan\Trurl\Horus\CryptoCurrency;
use xnan\Trurl\Horus\AssetType;
use xnan\Trurl\Nano\DataSet;
use xnan\Trurl\Nano\TextFormatter;
use xnan\Trurl\Horus\BotWorld;
use xnan\Trurl\Horus\WorldSettings;
use xnan\Trurl\Horus\Portfolio;

//Uses: Start

// Uses: Nano: Shortcuts
use xnan\Trurl\Nano;
Nano\Functions::Load;


class Persistence {
	static $instance;


	static function instance() {
		if (self::$instance==null) {
			self::$instance=new Persistence();
		}
		return self::$instance;
	}


	function pdo(&$pdo=null) {
		if ($pdo!=null) {
			$this->pdo=$pdo;
		} else {
			if ($this->pdo==null) {
				throw new \Exception("pdo not yet setup");
			}
		}

		return $this->pdo;
	}

	function marketBeat($marketId,$beat=null) {		
		if ($beat!=null) {			
			$this->marketFieldSetInt($marketId,"beat",$beat);
		}
		return $this->marketField($marketId,"beat");
	}

	private function marketField($marketId,$field) {
		return $this->marketRow($marketId)[$field];
	}

	private function marketFieldSetInt($marketId,$field,$value) {
		$query=sprintf(
			"UPDATE market SET $field=$value WHERE marketId='%s'",
				$marketId);		

		$this->pdo()->query($query);
	}

	private function marketRow($marketId) {
		$query=sprintf(
		"SELECT * FROM market WHERE marketId='%s'",
			$marketId);		

		$r=$this->pdo()->query($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("marketStats row not found");
		}		
	}

}

?>
