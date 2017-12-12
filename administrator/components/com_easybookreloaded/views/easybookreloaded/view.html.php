<?php
/**
 * EBR - Easybook Reloaded for Joomla! 3
 * License: GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * Author: Viktor Vogel
 * Projectsite: http://joomla-extensions.kubik-rubik.de/ebr-easybook-reloaded
 *
 * @license GNU/GPL
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Restricted access');
jimport('joomla.application.component.view');

class EasybookReloadedViewEasybookReloaded extends JViewLegacy
{
    protected $_state;

    function display($tpl = null)
    {
        JToolBarHelper::title(JText::_('COM_EASYBOOKRELOADED_EASYBOOKRELOADED')." - ".JText::_('COM_EASYBOOKRELOADED_SUBMENU_ENTRIES'), 'easybookreloaded');
        JToolBarHelper::addNew();
        JToolBarHelper::publishList();
        JToolBarHelper::unpublishList();
        JToolBarHelper::deleteList();
        JToolBarHelper::editList();
        JToolBarHelper::preferences('com_easybookreloaded', '500');
        //JHTML::_('stylesheet', 'easybookreloaded.css', 'administrator/components/com_easybookreloaded/css/');

        $items = $this->get('Data');
        $pagination = $this->get('Pagination');
        $this->_state = $this->get('State');

        $this->assignRef('pagination', $pagination);
        $this->assignRef('items', $items);

        $this->sidebar = JHtmlSidebar::render();

        parent::display($tpl);
    }
}
