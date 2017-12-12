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

class EasybookReloadedModelBadword extends JModelLegacy
{
    protected $_data = null;
    protected $_id = null;
    protected $_input;

    function __construct()
    {
        parent::__construct();

        $this->_input = JFactory::getApplication()->input;

        $array = $this->_input->get('cid', 0, 'ARRAY');
        $this->setId((int)$array[0]);
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
            $query = "SELECT * FROM ".$this->_db->quoteName('#__easybook_badwords')." WHERE ".$this->_db->quoteName('id')." = ".$this->_db->quote($this->_id);
            $this->_db->setQuery($query);
            $this->_data = $this->_db->loadObject();
        }

        if(!$this->_data)
        {
            $this->_data = $this->getTable('badword', 'EasybookReloadedTable');
            $this->_data->id = 0;
        }

        return $this->_data;
    }

    function store()
    {
        $row = $this->getTable('badword', 'EasybookReloadedTable');

        // Load all request variable - becaus JInput doesn't allow to load the whole data at once, a workaround
        //is used. This was easily possible with the deprecated JRequest (e.g. JRequest::get('post');)
        $data = $_REQUEST;
        array_walk($data, create_function('&$data', '$data = htmlspecialchars(strip_tags(trim($data)));'));

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
        $cids = $this->_input->get('cid', array(0), 'ARRAY');
        $row = $this->getTable('badword', 'EasybookReloadedTable');

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
}
