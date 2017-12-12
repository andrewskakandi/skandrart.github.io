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
jimport('joomla.application.component.helper');

class EasybookReloadedHelperRoute
{
    /**
     * Creates correct URL to the entry
     *
     * @param int $id
     * @return string
     */
    static function getEasybookReloadedRoute($id)
    {
        $item_id = EasybookReloadedHelperRoute::getItemId();
        $limit = EasybookReloadedHelperRoute::getLimitstart($id);

        $link = 'index.php?option=com_easybookreloaded&view=easybookreloaded';
        $link .= '&Itemid='.$item_id;

        if(!empty($limit))
        {
            $link .= '&limitstart='.$limit;
        }

        $link .= '#gbentry_'.$id;

        return $link;
    }

    /**
     * Creates correct URL with the task for the hash link in the notification mail
     *
     * @param string $task
     * @return string
     */
    static function getEasybookReloadedRouteHash($task)
    {
        $item_id = EasybookReloadedHelperRoute::getItemId();

        $link = 'index.php?option=com_easybookreloaded&task=';

        // Add the task to the URL
        $link .= $task;

        // Add the Item ID to the URL
        $link .= '&Itemid='.$item_id;
        $link .= '&hash=';

        return $link;
    }

    /**
     * Gets limitstart to set the correct page with the entry
     *
     * @param int $id
     * @return int
     */
    static function getLimitstart($id)
    {
        $params = JComponentHelper::getParams('com_easybookreloaded');
        $entries_per_page = (int)$params->get('entries_perpage', 5);
        $order = $params->get('entries_order', 'DESC');

        $db = JFactory::getDBO();
        $query = "SELECT * FROM ".$db->quoteName('#__easybook')." WHERE ".$db->quoteName('published')." = 1 ORDER BY ".$db->quoteName('id')." ".$order;
        $db->setQuery($query);
        $db->query();
        $result = $db->loadRowList();

        foreach($result as $key => $value)
        {
            if($value[0] == $id)
            {
                break;
            }
        }

        $limit = $entries_per_page * intval($key / $entries_per_page);

        return (int)$limit;
    }

    /**
     * Gets the Item ID of the component - the Item ID is the ID from the menu entry
     *
     * @return int The Item ID of the menu entry of the component
     */
    static function getItemId()
    {
        $db = JFactory::getDBO();
        $query = "SELECT ".$db->quoteName('id')." FROM ".$db->quoteName('#__menu')." WHERE ".$db->quoteName('link')." = 'index.php?option=com_easybookreloaded&view=easybookreloaded' AND ".$db->quoteName('published')." = 1";
        $db->setQuery($query);
        $item_id = $db->loadResult();

        if(empty($item_id))
        {
            $item_id = '';
        }

        return (int)$item_id;
    }
}
