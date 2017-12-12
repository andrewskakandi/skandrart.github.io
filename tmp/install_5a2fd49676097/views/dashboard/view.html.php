<?php defined("_JEXEC") or die(file_get_contents("index.html"));

jimport('joomla.application.component.view');

require_once __DIR__ . "/../foxview.html.php";

class FoxContactViewDashboard extends FoxView
{
	protected $e;

	public function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		// Ensure that jQuery framework is loaded
		JHtml::_("jquery.framework");

		// Load the submenu
		$this->addSubmenu("dashboard");
		$this->sidebar = JHtmlSidebar::render();

		// Display the template
		parent::display($tpl);
	}


	protected function addToolBar()
	{
		JToolBarHelper::title(JText::_("COM_FOXCONTACT_SUBMENU_DASHBOARD"), "mail");
		// Options button
		if (JFactory::getUser()->authorise("core.admin", "com_foxcontact"))
		{
			JToolBarHelper::preferences("com_foxcontact");
		}
	}


}
