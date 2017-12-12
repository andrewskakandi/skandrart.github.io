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

class EasybookReloadedModelEntry extends JModelLegacy
{
    protected $_data = null;
    protected $_id = null;
    protected $_input;

    function __construct()
    {
        parent::__construct();

        $this->_input = JFactory::getApplication()->input;

        $array = $this->_input->get('cid', 0, 'ARRAY');
        $this->setId((int) $array[0]);
    }

    function setId($id)
    {
        $this->_id = $id;
        $this->_data = null;
    }

    function getData()
    {
        if(empty($this->_data))
        {
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook')." WHERE ".$this->_db->quoteName('id')." = ".$this->_db->quote($this->_id);
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }

        if(!$this->_data)
        {
            $this->_data = $this->getTable('entry', 'EasybookReloadedTable');
            $this->_data->id = 0;
        }

        // Set correct date with the selected timezone from the configuration
        $this->_data->gbdate = JHTML::_('date', $this->_data->gbdate, 'Y-m-d H:i:s');

        return $this->_data;
    }

    function store()
    {
        $mainframe = JFactory::getApplication();
        $row = $this->getTable('entry', 'EasybookReloadedTable');

        // Load all request variable - becaus JInput doesn't allow to load the whole data at once, a workaround
        //is used. This was easily possible with the deprecated JRequest (e.g. JRequest::get('post');)
        $data = $_REQUEST;
        array_walk($data, create_function('&$data', '$data = htmlspecialchars(strip_tags(trim($data)));'));

        // Get unfiltered request variable is only with a trick with JInput possible, so direct access is used instead
        // Possible solution: list($gbtext) = ($this->_input->get('gbtext', array(0), 'array') - use the filter array
        // With JRequest one could use - JRequest::getVar('gbtext', NULL, 'post', 'none', JREQUEST_ALLOWRAW)
        $data['gbtext'] = htmlspecialchars($_REQUEST['gbtext'], ENT_QUOTES);
        $data['gbcomment'] = htmlspecialchars($_REQUEST['gbcomment'], ENT_QUOTES);

        $date = JFactory::getDate($data['gbdate'], $mainframe->getCfg('offset'));
        $data['gbdate'] = $date->toSql();

        if(!$row->bind($data))
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        if(!$row->check())
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        if(!$row->store())
        {
            $this->setError($this->_db->getErrorMsg());

            return false;
        }

        return true;
    }

    function delete()
    {
        $cids = $this->_input->get('cid', 0, 'ARRAY');
        $row = $this->getTable('entry', 'EasybookReloadedTable');

        foreach($cids as $cid)
        {
            if(!$row->delete($cid))
            {
                $this->setError($row->_db->getErrorMsg());
                return false;
            }
        }

        return true;
    }

    function publish($state)
    {
        $cids = $this->_input->get('cid', 0, 'ARRAY');
        $row = $this->getTable('entry', 'EasybookReloadedTable');

        if(!$row->publish($cids, $state))
        {
            $this->setError($row->getError());

            return false;
        }

        return true;
    }
}
