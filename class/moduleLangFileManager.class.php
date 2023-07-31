<?php

namespace devCommunityTools;
require_once __DIR__ . '/errors.trait.php';
require_once __DIR__ . '/modulesManager.class.php';



class ModuleLangFileManager{

	use \devCommunityTools\errors;

	/**
	 * @var \Translate $langs
	 */
	public $langs;

	/**
	 * @var \DolibarrModules $module
	 */
	public $module;

	/**
	 * @var string
	 */
	public $langDir;

	public $translations = array();
	public $langsAvailables = array();


	/**
	 * Use only for external modules
	 * @param  \Translate $langs
	 * @param \DolibarrModules $module
	 */
	public function __construct($module, $langs)
	{
		$this->module = $module;
		$this->langs = $langs;
		if(ModulesManager::getModulePath($this->module)){
			$this->langDir = ModulesManager::getModulePath($this->module).'/langs';
			$this->loadAvailableTranslations();
		}
	}


	/**
	 * @return false|void
	 */
	public function loadAvailableTranslations(){
		if(!$this->langDir){
			$this->setError('No translation folder defined');
			return false;
		}

		$this->langsAvailables = array();

		$useLang = file_exists($this->langDir);
		if($useLang) {
			$langsAvailables = $this->langs->get_available_languages(ModulesManager::getModulePath($this->module), 0, 0, 0);
			if(!empty($langsAvailables)){
				$this->langsAvailables = $langsAvailables;
				return $this->langsAvailables;
			}
		}

		return false;
	}


	/**
	 * @return false|void
	 */
	public function loadTranslations(){

		if(empty($this->langsAvailables)){
			$this->setError('No translation availables');
			return false;
		}


		foreach ($this->langsAvailables as $langKey => $langCode){

			$langDir = $this->langDir . '/'.$langKey;
			$scanDir = scandir($langDir);
			if(!empty($scanDir) && is_array($scanDir)) {
				foreach ($scanDir as $filename) {

					// skip folders
					if ($filename == "." || $filename == "..") {
						continue;
					}

					// check is a lang file
					if (preg_match('/^[a-zA-Z0-9]+(\.lang)$/', $filename)) {
						// Load "from" translations
						$path_parts = pathinfo($langDir . '/' . $filename);
						$this->translations[$langKey][$path_parts['filename']] = static::loadFileTranslation($langDir . '/' . $filename);
					}
				}
			}
		}
	}


	/**
	 * @param $filename
	 * @return array|bool
	 */
	public static function loadFileTranslation($filename){
		$tab_translate = array();

		if(!is_file($filename)){
			return false;
		}

		/**
		 * Read each lines until a '=' (with any combination of spaces around it)
		 * and split the rest until a line feed.
		 * This is more efficient than fgets + explode + trim by a factor of ~2.
		 */
		if ($fp = @fopen($filename,"rt")) {
			while ($line = fscanf($fp, "%[^= ]%*[ =]%[^\n]")) {
				if (isset($line[1])) {
					[$key, $value] = $line;
					//if ($domain == 'orders') print "Domain=$domain, found a string for $tab[0] with value $tab[1]. Currently in cache ".$this->tab_translate[$key]."<br>";
					//if ($key == 'Order') print "Domain=$domain, found a string for key=$key=$tab[0] with value $tab[1]. Currently in cache ".$this->tab_translate[$key]."<br>";
					if (empty($tab_translate[$key])) { // If translation was already found, we must not continue, even if MAIN_FORCELANGDIR is set (MAIN_FORCELANGDIR is to replace lang dir, not to overwrite entries)
						$value = preg_replace('/\\n/', "\n", $value); // Parse and render carriage returns
						if ($key == 'DIRECTION') { // This is to declare direction of language
							// TODO
							continue;
						} elseif ($key[0] == '#') {
							continue;
						} else {
							$tab_translate[$key] = $value;
						}
					}
				}
			}
			fclose($fp);
		}

		return $tab_translate;
	}


	/**
	 * @param $langCode
	 * @return mixed|string
	 */
	public static function getFlag($langCode, $codeIfNoImage = true){
		$langCodeArr = explode('_', $langCode);
		$countryCode = strtolower(end($langCodeArr));

		$flag = $codeIfNoImage?$langCode:'';
		if (file_exists(DOL_DOCUMENT_ROOT.'/theme/common/flags/'.$countryCode.'.png')) {
			$flag = img_picto($countryCode, DOL_URL_ROOT.'/theme/common/flags/'.$countryCode.'.png', '', 1, 0, 1);
		}

		return $flag;
	}
}
