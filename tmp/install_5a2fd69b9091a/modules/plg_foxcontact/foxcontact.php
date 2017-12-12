<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

//jimport('joomla.event.plugin');

class plgContentfoxcontact extends JPlugin
{
	function onContentPrepareForm($form, $data)
	{
	/*
		// Only works on JForms...
		if (!($form instanceof JForm)) return true;

		// ...which belong to the following components
		$components_list = array(
			"com_menus.item",
			"com_modules.module",
			"com_advancedmodules.module"
		);
		if (!in_array($form->getName(), $components_list)) return true;
	*/

		// Detect whether we are editing a Fox Contact module or a Fox Contact menu item. Components like Nonumber Advanced module manager are considered to be editing module.
		$is_module = is_object($data) && isset($data->module) && $data->module == "mod_foxcontact";
		$is_menu_item = is_array($data) && isset($data["request"]["option"]) && $data["request"]["option"] == "com_foxcontact";

		$language = JFactory::getLanguage();
		$enGB = $language->get("tag") == $language->getDefault();

		// On the module, we always need to load the admin language, because the module hasn't its own language files
		// On the menu item, we only need to act when the current language is different from enGB. We need to load the enGB language as fallback values, then (re)load the current language
		if ($is_module || ($is_menu_item && !$enGB))
		{
			// Using less CPU power reduces the world carbon dioxide emissions.
			$component_name = "com_" . basename(realpath(dirname(__FILE__)));

			// The current language is already been loaded, so it is important for the following workaround to work, that the parameter $reload is set to true
			// Reload the default language (en-GB)
			$language->load($component_name, JPATH_ADMINISTRATOR, $language->getDefault(), true);
			// Reload current language, overwriting nearly all the strings, but keeping the english version for untranslated strings
			$language->load($component_name, JPATH_ADMINISTRATOR, null, true);
		}

		return true;
	}
}
