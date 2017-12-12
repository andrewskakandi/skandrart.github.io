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

/**
 * This code segment sets the correct permission rights of the user
 */
$can_add = false;
$can_edit = false;

// Get all groups which are associated with the user
$user = JFactory::getUser();
$usergroup = JAccess::getGroupsByUser($user->id);

// Get the specified groups from the parameters
$params = JComponentHelper::getParams('com_easybookreloaded');
$add_acl_array = $params->get('add_acl', array(1));
$admin_acl_array = $params->get('admin_acl', array(8));


foreach($usergroup as $value)
{
    foreach($add_acl_array as $add_acl_value)
    {
        if($value == $add_acl_value)
        {
            $can_add = true;
            break;
        }
    }

    foreach($admin_acl_array as $admin_acl_value)
    {
        if($value == $admin_acl_value)
        {
            $can_edit = true;
            break;
        }
    }
}

// Defines constants for adding and editing permission rights
define('_EASYBOOK_CANADD', $can_add);
define('_EASYBOOK_CANEDIT', $can_edit);
