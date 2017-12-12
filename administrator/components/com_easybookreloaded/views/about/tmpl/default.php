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
?>

<?php if(!empty($this->sidebar)): ?>
    <div id="j-sidebar-container" class="span2">
        <?php echo $this->sidebar; ?>
    </div>
    <div id="j-main-container" class="span10">
    <?php else : ?>
        <div id="j-main-container">
        <?php endif; ?>

        <h1>Easybook Reloaded</h1>
        <?php echo JTEXT::_('COM_EASYBOOKRELOADED_ABOUT_EASYBOOK_RELOADED'); ?>
        <?php echo JTEXT::_('COM_EASYBOOKRELOADED_JED'); ?>
        <p><strong>Easybook Reloaded Version: <?php echo _EASYBOOK_VERSION; ?></strong></p>
        <p><?php echo JText::_('COM_EASYBOOKRELOADED_SUPPORT_THE_FURTHER_DEVELOPMENT_AND_FREE_AVAILABILITY_OF_THE_EASYBOOK_WITH_A_SMALL_DONATION_THANK_YOU'); ?></p>
    </div>
    <div style="text-align: center;">
        <p><?php echo JText::sprintf('COM_EASYBOOKRELOADED_VERSION', _EASYBOOK_VERSION) ?></p>
    </div>