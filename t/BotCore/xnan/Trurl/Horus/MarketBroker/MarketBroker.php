<?php

 class MarketBroker {	
	var $homeUrl="";
	var $allowDispathLogin=false;
	var $accountLoginUser="";
	var $accountLoginPassword="";

	
	function homeUrl($homeUrl=null) {
		if ($homeUrl!=null) $this->homeUrl=$homeUrl;
			return $this->homeUrl;			
	}

	function allowDispathLogin($allowDispathLogin=null) {
		if ($allowDispathLogin!=null) $this->allowDispathLogin=$allowDispathLogin;
			return $this->allowDispathLogin;			
	}

	function accountLoginUser($accountLoginUser=null) {
		if ($accountLoginUser!=null) $this->accountLoginUser=$accountLoginUser;
		return $this->accountLoginUser;			
	}

	function accountLoginPassword($accountLoginPassword=null) {
			if ($accountLoginPassword!=null) $this->accountLoginPassword=$accountLoginPassword;
				return $this->accountLoginPassword;			
	}

}

?>