<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

jimport('joomla.application.component.view');

class FoxContactViewLoader extends JViewLegacy
{
	protected $Input;

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->Input = JFactory::getApplication()->input;
	}


	function display($tpl = null)
	{
		// Load component || module parameters. Defaults to component
		$owner = $this->Input->get("owner", "");
		// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
		preg_match('/^[a-z_-]+$/', $owner) or $owner = "";

		$method = "_get_" . $owner . "_params_";
		$params = $this->$method();

		$type = $this->Input->get("type", "");
		// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
		preg_match('/^[a-z_-]+$/', $type) or $type = "";

		$root = $this->Input->get("root", "");
		// Only admit "components" and "media"
		preg_match('/^components|media$/', $root) or $root = "components";

		$option = $this->Input->get("option", "");
		// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
		preg_match('/^[a-z_-]+$/', $option) or $option = "";

		$view = $this->Input->get("v", "");
		// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
		preg_match('/^[a-z_-]+$/', $view) or $view = "";
		$view = $view ? "/views/" . $view : "";

		// Import appropriate library
		jimport("foxcontact.loader." . $type) or die(JText::_("JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND"));

		// Instantiate the loader
		$classname = $type . "Loader";
		$loader = new $classname();
		$loader->IncludePath = JPATH_SITE . "/$root/$option" . $view;
		$loader->Params = & $params;
		$loader->Show();
	}


	// Owner is empty. No component or module parameters are required
	private function _get__params_()
	{
		// Do nothing
		return new JRegistry;
	}


	private function _get_component_params_()
	{
		// @ avoids Warning: ini_set() has been disabled for security reasons in /var/www/libraries/joomla/[...]
		$application = @JFactory::getApplication('site'); // Needed to get the correct session with JFactory::getSession() below
		$menu = @$application->getMenu();
		$params = $menu->getParams(intval($this->Input->get("id", 0)));
		return $params;
	}


	private function _get_module_params_()
	{
		$db = JFactory::getDbo();
		jimport("joomla.database.databasequery");
		$query = $db->getQuery(true);
		$query->select($db->quoteName("params"));
		$query->from($db->quoteName("#__modules"));
		$query->where($db->quoteName("id") . "=" . intval($this->Input->get("id", 0)));
		$query->where($db->quoteName("module") . "=" . $db->quote("mod_foxcontact"));
		$db->setQuery($query);

		// Load parameters from database
		$json = $db->loadResult();
		// Convert to JRegistry
		$params = new JRegistry($json);
		return $params;
	}
}