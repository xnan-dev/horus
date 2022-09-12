<?php
namespace xnan\Trurl\Horus\WorldSettings;
use xnan\CryptoBot;

class WorldSettings {
	var $listeners=[];

	function registerChangeListener($key,&$obj) {		
		if (!array_key_exists($key,$this->listeners)) {
			$this->listeners[$key]=[];
		}
		if (!in_array($obj,$this->listeners[$key])) {
			$this->listeners[$key][]=$obj;
		}
	}

	function settingsChange($key,$params=array()) {		
		//print_r(array_keys($this->listeners));
		if (array_key_exists($key,$this->listeners) )  {						
			foreach($this->listeners[$key] as $listener) {
				$listener->onSettingsChange($key,$params);
				$listener->markChanged();
			}			
		} 
	}

	function settingsChangeKeys() {
		return array_keys($this->listeners);
	}
}
?>