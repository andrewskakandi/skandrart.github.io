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
		$type = $this->Input->get("type", "");
		// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
		preg_match('/^[a-z_-]+$/', $type) or $type = "";

		// Import appropriate library
		jimport("foxcontact.loader." . $type) or die(JText::_("JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND"));

		$view = $this->Input->get("v", "");
		// Only admit lowercase a-z, underscore and minus. Forbid numbers, symbols, slashes and other stuff.
		preg_match('/^[a-z_-]+$/', $view) or $view = "";

		$view = $view ? "/views/" . $view : "";
		$option = $this->Input->get("option", "");

		// Instantiate the loader
		$classname = $type . "Loader";
		$loader = new $classname();
		$loader->IncludePath = JPATH_ADMINISTRATOR . "/components/$option" . $view;
		$loader->Show();
	}
}