<?php


//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
if (! defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
if (! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification


$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

// Include and load Dolibarr environment variables
$res = 0;
if (!$res && file_exists($path . "main.inc.php")) $res = @include($path . "main.inc.php");
if (!$res && file_exists($path . "../main.inc.php")) $res = @include($path . "../main.inc.php");
if (!$res && file_exists($path . "../../main.inc.php")) $res = @include($path . "../../main.inc.php");
if (!$res && file_exists($path . "../../../main.inc.php")) $res = @include($path . "../../../main.inc.php");
if (!$res) die("Include of master fails");

require_once __DIR__ . '/class/jsonResponse.class.php';

$jsonResponse = new \devCommunityTools\JsonResponse();

// Security check
if (empty($conf->devcommunitytools->enabled)) $jsonResponse->exitError('Module not enabled');
if (empty($user->admin)) $jsonResponse->exitError('Not enough rights');


$action = GETPOST('action');



if($action == 'deepl-translate'){
	__getTranslationFromDeeplAPI();
}
else{
	$jsonResponse->msg = 'Action not found';
}

print $jsonResponse->getJsonResponse();

$db->close();    // Close $db database opened handler



function __getTranslationFromDeeplAPI(){
	global $conf, $jsonResponse;

	if(empty($conf->global->DEVCOMMUNITYTOOLS_DEEPL_API_KEY)){
		$jsonResponse->msg = 'DeepL API Key missing';
		return false;
	}

	$data = GETPOST('data', 'array:restricthtml');

	if(empty($data['sourceTxt'])){
		$jsonResponse->msg = 'Source text empty';
		return false;
	}

	if(empty($data['langDest'])){
		$jsonResponse->msg = 'Target lang missing';
		return false;
	}

	$url = 'https://api-free.deepl.com';
	if(!empty($conf->global->DEVCOMMUNITYTOOLS_DEEPL_USE_PRO)){
		$url = 'https://api.deepl.com';
	}

	$postRequest = array(
		'text' => $data['sourceTxt'],
		'target_lang' =>  $data['langDest'],
		'auth_key' => $conf->global->DEVCOMMUNITYTOOLS_DEEPL_API_KEY
	);

	if(!empty($data['langSrc'])){
		$postRequest['source_lang'] = $data['langSrc'];
	}

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded',
	));

	curl_setopt($ch, CURLOPT_URL, $url.'/v2/translate');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postRequest));

	// Receive server response ...
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);


	// Further processing ...
	if ($httpCode == "200") {
		$jsonResponse->data = json_decode($server_output);
		$jsonResponse->result = 1;
		return true;
	} else {
		$jsonResponse->msg = 'Server response error code '.$httpCode;
		return false;
	}
}
