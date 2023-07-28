<?php

namespace devCommunityTools;

class LogManager {

	/**
	 * @var LogManagerItem[]
	 */
	public $logs = array();


	/**
	 * @param string $msg
	 * @param int $type
	 * @return bool
	 */
	public function addNewLogItem($msg, $type = 0){

		$item = new LogManagerItem();
		if($item->setLog($msg, $type)){
			$this->logs[] = $item;
			return true;
		}

		return false;
	}

	/**
	 * @param string $msg
	 * @return bool
	 */
	public function addError($msg){
		return $this->addNewLogItem($msg, LogManagerItem::TYPE_ERROR);
	}

	/**
	 * @param string $msg
	 * @return bool
	 */
	public function addSuccess($msg){
		return $this->addNewLogItem($msg, LogManagerItem::TYPE_SUCCESS);
	}

	/**
	 * @param string $msg
	 * @return bool
	 */
	public function addLog($msg){
		return $this->addNewLogItem($msg, LogManagerItem::TYPE_LOG);
	}

	/**
	 * @return bool
	 */
	public function output($print = false){
		$out = '';
		foreach($this->logs as $item){
			$itemOut = $item->output();
			if($print){
				print $itemOut;
			}else{
				$out.= $itemOut;
			}
		}
		return $out;
	}
}


class LogManagerItem {


	/**
	 * @var int see const self::TYPE_*
	 */
	public int $type = 0;

	const TYPE_LOG = 0;
	const TYPE_ERROR = 1;
	const TYPE_SUCCESS = 2;

	public string $msg = '';

	/**
	 * @param string $msg
	 * @param int $type
	 * @return bool
	 */
	public function setLog($msg, $type = 0){

		if(empty($msg) || !is_string($msg)){
			return false;
		}

		if(in_array($type, array(static::TYPE_LOG, static::TYPE_ERROR, static::TYPE_SUCCESS))){
			$this->type = $type;
		}

		$this->msg = $msg;
		return true;
	}


	/**
	 * check if current output is bash
	 * @return bool
	 */
	public static function isBash()
	{
		// Use only on command line
		$isBash = true;
		$sapi_type = php_sapi_name();
		if (substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
			$isBash = false;
		}

		return $isBash;
	}


	/**
	 *
	 * @param string $msg
	 * @return void
	 */
	public function output(){
		if(static::isBash()){
			$bashColor = '0;37';
			if($this->type == static::TYPE_ERROR ){
				$bashColor = '1;37;41';
			}elseif($this->type == static::TYPE_SUCCESS){
				$bashColor = '0;32';
			}
			return "\e[".$bashColor."m".$this->msg."\e[0m\n";
		}else{
			return '<p log-type="'.$this->type.'" >'.$this->msg.'</p>';
		}
	}
}