<?php defined("_JEXEC") or die(file_get_contents("index.html"));

class FoxView extends JViewLegacy
{
	public function display($tpl = null)
	{
		JFactory::getDocument()->addStyleSheet(JUri::base(true) . "/components/com_foxcontact/css/component.css");
		parent::display($tpl);
	}

	/**
	 * Configure the buttons bar
	 *
	 * @param   string $vName The name of the active view
	 * @return  void
	 */
	public function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_("COM_FOXCONTACT_SUBMENU_DASHBOARD"),
			"index.php?option=com_foxcontact&view=dashboard",
			$vName == "dashboard"
		);

		JHtmlSidebar::addEntry(
			JText::_("COM_FOXCONTACT_SUBMENU_ENQUIRIES"),
			"index.php?option=com_foxcontact&view=enquiries",
			$vName == "enquiries"
		);
	}

}
