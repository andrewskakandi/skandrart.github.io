<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

// Access check
if (!JFactory::getUser()->authorise("core.manage", "com_foxcontact"))
{
	JFactory::getApplication()->enqueueMessage(JText::_("JERROR_ALERTNOAUTHOR"), "error");
	return;
}

$language = JFactory::getLanguage();
// Don't waste time reloading the same English language two more times. Using less CPU power reduces the world carbon dioxide emissions.
if ($language->get("tag") != $language->getDefault())
{
    // The current language is already been loaded, so it is important for the following workaround to work, that the parameter $reload is set to true
    $GLOBALS["com_name"] = basename(dirname(__FILE__));
    // Reload the default language (en-GB)
    $language->load($GLOBALS["com_name"], JPATH_ADMINISTRATOR, $language->getDefault(), true);
    // Reload current language, overwriting nearly all the strings, but keeping the english version for untranslated strings
    $language->load($GLOBALS["com_name"], JPATH_ADMINISTRATOR, null, true);
}

jimport('joomla.application.component.controller');
$controller = JControllerLegacy::getInstance("FoxContact");
$controller->execute(JFactory::getApplication()->input->get("task", "display"));
$controller->redirect();
