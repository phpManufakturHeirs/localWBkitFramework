<?php
/**
 *
 * @category        frontend
 * @package         account
 * @author          WebsiteBaker Project
 * @copyright       2004-2009, Ryan Djurovich
 * @copyright       2009-2011, Website Baker Org. e.V.
 * @link			http://www.websitebaker2.org/
 * @license         http://www.gnu.org/licenses/gpl.html
 * @platform        WebsiteBaker 2.8.x
 * @requirements    PHP 5.2.2 and higher
 * @version         $Id: signup.php 1599 2012-02-06 15:59:24Z Luisehahne $
 * @filesource		$HeadURL: svn://isteam.dynxs.de/wb_svn/wb280/tags/2.8.3/wb/account/signup.php $
 * @lastmodified    $Date: 2012-02-06 16:59:24 +0100 (Mo, 06. Feb 2012) $
 *
 */

require_once('../config.php');

if(!( intval(FRONTEND_SIGNUP) && (  0 == (isset($_SESSION['USER_ID']) ? intval($_SESSION['USER_ID']) : 0) )))
{
	if(INTRO_PAGE) {
		header('Location: '.WB_URL.PAGES_DIRECTORY.'/index.php');
		exit(0);
	} else {
		header('Location: '.WB_URL.'/index.php');
		exit(0);
	}
}

if(ENABLED_ASP && isset($_POST['username']) && ( // form faked? Check the honeypot-fields.
	(!isset($_POST['submitted_when']) OR !isset($_SESSION['submitted_when'])) OR 
	($_POST['submitted_when'] != $_SESSION['submitted_when']) OR
	(!isset($_POST['email-address']) OR $_POST['email-address']) OR
	(!isset($_POST['name']) OR $_POST['name']) OR
	(!isset($_POST['full_name']) OR $_POST['full_name'])
)) {
	exit(header("Location: ".WB_URL.PAGES_DIRECTORY.""));
}

// Load the language file
if(!file_exists(WB_PATH.'/languages/'.DEFAULT_LANGUAGE.'.php')) {
	exit('Error loading language file '.DEFAULT_LANGUAGE.', please check configuration');
} else {
	require_once(WB_PATH.'/languages/'.DEFAULT_LANGUAGE.'.php');
	$load_language = false;
}

$page_id = (isset($_SESSION['PAGE_ID']) && ($_SESSION['PAGE_ID']!='') ? $_SESSION['PAGE_ID'] : 0);

// Required page details
// $page_id = 0;
$page_description = '';
$page_keywords = '';
define('PAGE_ID', $page_id);
define('ROOT_PARENT', 0);
define('PARENT', 0);
define('LEVEL', 0);
define('PAGE_TITLE', $TEXT['SIGNUP']);
define('MENU_TITLE', $TEXT['SIGNUP']);
define('MODULE', '');
define('VISIBILITY', 'public');

// Set the page content include file
if(isset($_POST['username'])) {
	define('PAGE_CONTENT', WB_PATH.'/account/signup2.php');
} else {
	define('PAGE_CONTENT', WB_PATH.'/account/signup_form.php');
}

// Set auto authentication to false
$auto_auth = false;

// Include the index (wrapper) file
require(WB_PATH.'/index.php');
