<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

jimport('joomla.application.component.view');

require_once JPATH_COMPONENT . "/lib/functions.php";

class FoxContactViewInvalid extends JViewLegacy
{
	function display($tpl = null)
	{
		echo("<h2>" . JText::_($GLOBALS["COM_NAME"] . "_ERR_PROVIDE_VALID_URL") . "</h2>");

		$application = JFactory::getApplication("site");
		$menu = $application->getMenu();
		$valid_items = $menu->getItems("component", $GLOBALS["com_name"]);

		echo("<ul>");
		foreach ($valid_items as $valid_item)
		{
			echo('<li><a href="' . FGetLink($valid_item->id) . '">' . $valid_item->title . '</a></li>');
		}
		echo("</ul>");

		// See the documentation string
		JFactory::getLanguage()->load("com_foxcontact", JPATH_ADMINISTRATOR);
		echo('<p><a href="http://www.fox.ra.it/forum/22-how-to/1574-hide-the-contact-page-menu-item.html">' . JText::_($GLOBALS["COM_NAME"] . "_DOCUMENTATION") . "</a></p>");
	}
}