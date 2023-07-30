<?php

namespace devCommunityTools;

require_once __DIR__ . '/logManagerItem.class.php';

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

		if(is_array($msg)){
			foreach ($msg as $txt){
				$this->addNewLogItem($txt, $type);
			}
			return null;
		}

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
	public function output($print = false, $clear = false){
		$out = '';
		foreach($this->logs as $item){
			$itemOut = $item->output();
			if($print){
				print $itemOut;
			}else{
				$out.= $itemOut;
			}
		}
		if($clear){$this->clear();}
		return $out;
	}

	public function clear(){
		$this->logs = array();
	}
}
