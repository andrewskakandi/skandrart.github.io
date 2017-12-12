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
jimport('joomla.application.component.model');

class EasybookReloadedModelEasybookReloaded extends JModelLegacy
{
    protected $_data;
    protected $_total;
    protected $_pagination;

    function getData()
    {
        if(empty($this->_data))
        {
            $query = $this->buildQuery();
            $this->_data = $this->_getList($query);
        }

        return $this->_data;
    }

    function getPagination()
    {
        $params = JComponentHelper::getParams('com_easybookreloaded');

        if(empty($this->_pagination))
        {
            // Check whether limit is already set - e.g. from feed function
            $limit = JFactory::getApplication()->input->getInt('limit', 0);

            if(empty($limit))
            {
                $limit = (int)$params->get('entries_perpage', 5);
            }

            jimport('joomla.html.pagination');
            $total = $this->getTotal();
            $this->_pagination = new JPagination($total, JFactory::getApplication()->input->getInt('limitstart', 0), $limit);
        }

        return $this->_pagination;
    }

    /**
     * Build correct query to retrieve all needed entries
     *
     * @return string
     */
    private function buildQuery()
    {
        $params = JComponentHelper::getParams('com_easybookreloaded');

        // If type is feed, then the order has to be DESC to get the latest entries in the feed reader
        $document = JFactory::getDocument();

        if($document->getType() == 'feed')
        {
            $order = 'DESC';
        }
        else
        {
            $order = $params->get('entries_order', 'DESC');
        }

        // Check whether limit is already set - e.g. from feed function
        $limit = JFactory::getApplication()->input->getInt('limit', 0);

        if(empty($limit))
        {
            $limit = (int)$params->get('entries_perpage', 5);
        }

        $start = JFactory::getApplication()->input->getInt('limitstart', 0);

        if(_EASYBOOK_CANEDIT)
        {
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook')." ORDER BY ".$this->_db->quoteName('gbdate')." ".$order." LIMIT ".$start.", ".$limit;
        }
        else
        {
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook')." WHERE ".$this->_db->quoteName('published')." = 1 ORDER BY ".$this->_db->quoteName('gbdate')." ".$order." LIMIT ".$start.", ".$limit;
        }

        return $query;
    }

    private function buildCountQuery()
    {
        if(_EASYBOOK_CANEDIT)
        {
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook');
        }
        else
        {
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook')." WHERE ".$this->_db->quoteName('published')." = 1";
        }

        return $query;
    }

    function getTotal()
    {
        if(empty($this->_total))
        {
            $query = $this->buildCountQuery();
            $this->_total = $this->_getListCount($query);
        }

        return $this->_total;
    }

}
