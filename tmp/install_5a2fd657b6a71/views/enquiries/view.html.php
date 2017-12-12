<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

require_once __DIR__ . "/../foxview.html.php";


/**
 * View class for a list of enquiries
 *
 */
class FoxContactViewEnquiries extends FoxView
{
	protected $items;
	protected $pagination;
	protected $state;


	/**
	 * Method to display the view.
	 *
	 * @param   string $tpl A template file to load. [optional]
	 * @return  mixed  A string if successful, otherwise a JError object.
	 */
	public function display($tpl = null)
	{
		$this->items = $this->get("Items");
		$this->pagination = $this->get("Pagination");
		$this->state = $this->get("State");

		$this->filterForm = $this->get("FilterForm");
		$this->activeFilters = $this->get("ActiveFilters");

		// Load the submenu
		$this->addSubmenu("enquiries");
		$this->sidebar = JHtmlSidebar::render();
		$this->addToolbar();

		// We need jQuery to be loaded before our scripts
		JHtml::_("jquery.framework");

		$document = JFactory::getDocument();
		$document->addScript(JUri::base(true) . "/components/com_foxcontact/js/enquiries.min.js");

		parent::display($tpl);
	}


	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		// Todo: ACL

		// Get the toolbar object instance
		$bar = JToolBar::getInstance("toolbar");

		JToolbarHelper::title(JText::_("COM_FOXCONTACT_SUBMENU_ENQUIRIES"), "list-2");

		// Export button
		//JToolbarHelper::custom("enquiries.export", "cog", "", "JTOOLBAR_EXPORT", false);
		// Avoid that a filter change fires another export
		$bar->appendButton("Link", "cog", "JTOOLBAR_EXPORT", "index.php?option=com_foxcontact&task=enquiries.export");

		// Delete button
		JToolbarHelper::deleteList(JText::_("COM_FOXCONTACT_ARE_YOU_SURE"), "enquiries.delete", "JACTION_DELETE");

		// Options button
		if (JFactory::getUser()->authorise("core.admin", "com_foxcontact"))
		{
			JToolBarHelper::preferences("com_foxcontact");
		}
	}

}
