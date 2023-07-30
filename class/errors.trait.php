<?php

namespace devCommunityTools;

trait errors {

	public $errors = array();


	/**
	 * Add new error
	 * @param $msg
	 * @return bool
	 */
	function setError($msg){

		if(empty($msg) || !is_string($msg)){
			return false;
		}

		$this->errors[] = $msg;
		return true;
	}


	/**
	 * Return last error string or false if no error set
	 * @return string | false
	 */
	function getLastError(){
		return end($this->errors);
	}

}
