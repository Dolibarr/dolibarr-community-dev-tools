<?php

namespace devCommunityTools;

require_once __DIR__ . '/errors.trait.php';


class ModulesManager {

	use \devCommunityTools\errors;


	/**
	 * @var DolibarrModules[]
	 */
	public $modules = array();

	public $db;

	public function __construct($db){
		$this->db = $db;
	}


	public function fetchAll($type = 'all'){

// Search modules dirs
		$modulesdir = dolGetModulesDirs();

		foreach ($modulesdir as $dir) {
			// Load modules attributes in arrays (name, numero, orders) from dir directory
			$handle = @opendir($dir);
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					//print "$i ".$file."\n<br>";
					if (is_readable($dir.$file) && substr($file, 0, 3) == 'mod' && substr($file, dol_strlen($file) - 10) == '.class.php') {
						$modName = substr($file, 0, dol_strlen($file) - 10);
						if ($modName) {
							if (!empty($modNameLoaded[$modName])) {   // In cache of already loaded modules ?
								$this->setError("Error: Module ".$modName." was found twice: Into ".$modNameLoaded[$modName]." and ".$dir.". You probably have an old file on your disk.");
								continue;
							}

							try {
								$res = include_once $dir.$file; // A class already exists in a different file will send a non catchable fatal error.
								if (class_exists($modName)) {
									try {
										$objMod = new $modName($this->db);
										$modType = $objMod->isCoreOrExternalModule();
										if ($type == 'all' || (!is_array($type) && $modType == $type)  || (is_array($type) && in_array($modType, $type)) ) {
											$this->modules[$modName] = $objMod;
										}
									} catch (Exception $e) {
										$this->setError("Failed to load ".$dir.$file." ".$e->getMessage());
									}
								} else {
									$this->setError("admin/modules.php Warning bad descriptor file : ".$dir.$file." (Class ".$modName." not found into file)");
								}
							} catch (Exception $e) {
								$this->setError("Failed to load ".$dir.$file." ".$e->getMessage());
							}
						}
					}
				}
				closedir($handle);
			} else {
				$this->setError("htdocs/admin/modules.php: Failed to open directory ".$dir.". See permission and open_basedir option.");
			}
		}
	}

}
