<?php

namespace xnan\Trurl\Horus\PdoSettings;

class PdoSettings {
	var $hostname,$database,$user,$password;

	function hostname() {
		return $this->hostname;
	}

	function database() {
		return $this->database;
	}

	function user() {
		return $this->user;
	}

	function password() {
		return $this->password;
	}

	function withHostname($hostname) {
		$s=clone $this;
		$s->hostname=$hostname;
		return $s;
	}

	function withDatabase($database) {
		$s=clone $this;
		$s->database=$database;
		return $s;
	}

	function withUser($user) {
		$s=clone $this;
		$s->user=$user;
		return $s;
	}

	function withPassword($password) {
		$s=clone $this;
		$s->password=$password;
		return $s;
	}
}

?>