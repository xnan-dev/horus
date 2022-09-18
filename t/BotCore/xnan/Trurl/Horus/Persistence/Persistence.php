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

	private function dsTraderFieldInt($botArenaId,$traderId,$field,$value=null) {
		if ($value!=null) {

			$query=sprintf(
				"UPDATE divideAndScaleMarketTrader 
					SET $field=$value 
					WHERE
					 botArenaId='%s' AND
					 traderId='%s'
				",
					$botArenaId,
					$traderId);		

			$this->pdo()->query($query);

			return null;
		} else {
			return $this->dsTraderRow($botArenaId,$traderId)[$field];
		}
	}


	private function marketBotArenaId($marketId) {

		$query=sprintf(
			"SELECT botArenaId FROM botArena
				WHERE
				 marketId='%s'
			",
				$marketId
			);		

		$r=$this->pdo()->query($query);

		if ($row=$r->fetch()) {
			return $row["botArenaId"];
		}

		Nano\nanoCheck()->checkFailed("botArena not found");
	}

	private function dsTraderFieldDouble($botArenaId,$traderId,$field,$value=null) {
		if ($value!=null) {

			$value=round($value, 10);

			$query=sprintf(
				"UPDATE divideAndScaleMarketTrader 
					SET $field=$value 
					WHERE
					 botArenaId='%s' AND
					 traderId='%s'
				",
					$botArenaId,
					$traderId);		

			$this->pdo()->query($query);

			return null;
		} else {
			return $this->dsTraderRow($botArenaId,$traderId)[$field];
		}
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


	private function dsTraderRow($botArenaId,$traderId) {
		$query=sprintf(
		"SELECT * 
			FROM marketTrader t
			INNER JOIN divideAndScaleMarketTrader ds 
				ON t.botArenaId=ds.botArenaId AND t.traderId=ds.traderId
			WHERE 
				t.traderId='%s' AND
				t.botArenaId='%s'
		"
		,$traderId
		,$botArenaId);		

//		print $query;

		$r=$this->pdo()->query($query);
		
		if ($row=$r->fetch()) {
			return $row;
		} else {
			Nano\nanoCheck()->checkFailed("dsTrader row not found");
		}		
	}


	function dsTraderWaitBeats($botArenaId,$marketId,$waitBeats=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"waitBeats",$waitBeats);
	}

	function dsTraderPhase($botArenaId,$marketId,$phase=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"phase",$phase);
	}

	function dsTraderStartBeat($botArenaId,$marketId,$startBeat=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"startBeat",$startBeat);	
	}

	function dsTraderMaxAssetPercentage($botArenaId,$marketId,$maxAssetPercentage=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"maxAssetPercentage",$maxAssetPercentage);	
	}

	function dsTraderWaitBeatsForAssetRepeat($botArenaId,$marketId,$waitBeatsForAssetRepeat=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"waitBeatsForAssetRepeat",$waitBeatsForAssetRepeat);	
	}

	function dsTraderBuyCicleCut($botArenaId,$marketId,$buyCicleCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"buyCicleCut",$buyCicleCut);	
	}

	function dsTraderSellCicleCut($botArenaId,$marketId,$sellCicleCut=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"sellCicleCut",$sellCicleCut);	
	}

	function dsTraderMaxBuySuggestions($botArenaId,$marketId,$maxBuySuggestions=null) {
		return $this->dsTraderFieldInt($botArenaId,$marketId,"maxBuySuggestions",$maxBuySuggestions);	
	}

	function dsTraderBuyLimitFactor($botArenaId,$marketId,$buyLimitFactor=null) {
		return $this->dsTraderFieldDouble($botArenaId,$marketId,"buyLimitFactor",$buyLimitFactor);	
	}
}

?>
