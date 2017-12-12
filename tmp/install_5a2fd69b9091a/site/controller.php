<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

jimport('joomla.application.component.controller');

class FoxContactController extends JControllerLegacy
{
	public function display($cachable = false, $urlparams = false)
	{
		$application = JFactory::getApplication("site");
		$menu = $application->getMenu();
		$activemenu = $menu->getActive();
		$view = $application->input->get("view", $this->default_view);

		// When called without a valid menu id, hijack to the invalid view
		if ($view == "foxcontact" && !$activemenu)
		{
			// My old hijack code {
			// $_GET["view"] = "invalid";
			// $_REQUEST["view"] = "invalid";
			// $GLOBALS['_JREQUEST']["view"]["DEFAULTCMD0"] = "invalid";
			// }

			// The standard Joomla redirect (http://en.wikipedia.org/wiki/HTTP_303)
			// is a better way and avoids confusion to 3rd party SEO/SEF components
			JFactory::getApplication()->redirect(JRoute::_("index.php?option=com_foxcontact&view=invalid"));

			//$this->setRedirect(JRoute::_("index.php?option=com_foxcontact&view=invalid", false));
			//return $this;

		}

		return parent::display($cachable, $urlparams);
	}
}
