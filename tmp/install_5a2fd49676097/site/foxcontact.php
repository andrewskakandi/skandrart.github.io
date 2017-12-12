<?php defined("_JEXEC") or die(file_get_contents("index.html"));

/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

$GLOBALS["ext_name"] = basename(__FILE__);
$GLOBALS["com_name"] = dirname(__FILE__);
$GLOBALS["mod_name"] = realpath(dirname(__FILE__) . "/../../modules");
$GLOBALS["EXT_NAME"] = strtoupper($GLOBALS["ext_name"]);
$GLOBALS["COM_NAME"] = strtoupper($GLOBALS["com_name"]);
$GLOBALS["MOD_NAME"] = strtoupper($GLOBALS["mod_name"]);
$GLOBALS["left"] = false;
$GLOBALS["right"] = true;

$application = JFactory::getApplication('site');
$menu = $application->getMenu();
// JMenu::getActive() is inconsistent. It doesn't return an object everytime.
$activemenu = $menu->getActive() or $activemenu = new stdClass();
$application->owner = "component";
$application->oid = isset($activemenu->id) ? $activemenu->id : 0;
$application->cid = isset($activemenu->id) ? $activemenu->id : 0;
$application->mid = 0;
$application->submitted = (bool)count($_POST) && isset($_POST["cid_$application->cid"]);
$me = basename(__FILE__);
$name = substr($me, 0, strrpos($me, '.'));
include(realpath(dirname(__FILE__) . "/" . $name . ".inc"));

$language = JFactory::getLanguage();
// Don't waste time reloading the same English language two more times. Using less CPU power reduces the world carbon dioxide emissions.
if ($language->get("tag") != $language->getDefault())
{
    // The current language is already been loaded, so it is important for the following workaround to work, that the parameter $reload is set to true
    // Reload the default language (en-GB)
    $language->load($GLOBALS["com_name"], JPATH_SITE, $language->getDefault(), true);
    // Reload current language, overwriting nearly all the strings, but keeping the english version for untranslated strings
    $language->load($GLOBALS["com_name"], JPATH_SITE, null, true);
}

jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance('FoxContact');
$controller->execute(JFactory::getApplication()->input->get("task", "display"));
$controller->redirect();

