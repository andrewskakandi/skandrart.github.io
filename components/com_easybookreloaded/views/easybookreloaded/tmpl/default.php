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
echo '<!-- Easybook Reloaded - Kubik-Rubik Joomla! Extensions by Viktor Vogel -->';
?>
<div id="easybook">
    <?php if($this->params->get('show_page_title', 1)) : ?>
        <h2 class="componentheading"><?php echo $this->heading ?></h2>
    <?php endif; ?>
    <div class="easy_entrylink">
        <?php if($this->params->get('offline')) : ?>
            <?php echo $this->loadTemplate('header'); ?>
            <?php echo JText::_('COM_EASYBOOKRELOADED_GUESTBOOK_OFFLINE_FRONTEND'); ?>
            <?php echo $this->loadTemplate('footer'); ?>
        <?php else : ?>
            <?php echo $this->loadTemplate('header'); ?>
            <?php echo $this->loadTemplate('entries'); ?>
            <?php if($this->params->get('show_count_entries')) : ?>
                <div>
                    <strong class='easy_pagination'><?php echo $this->count ?>
                        <?php if($this->count == 1) : ?>
                            <?php echo JText::_('COM_EASYBOOKRELOADED_ENTRY_IN_THE_GUESTBOOK'); ?>
                        <?php else : ?>
                            <?php echo JText::_('COM_EASYBOOKRELOADED_ENTRIES_IN_THE_GUESTBOOK'); ?>
                        <?php endif; ?>
                    </strong>
                </div>
            <?php endif; ?>
            <?php if($this->pagination->total > $this->pagination->limit) : ?>
                <div class="easy_pagination">
                    <?php echo $this->pagination->getPagesLinks(); ?>
                </div>
            <?php endif; ?>
            <?php echo $this->loadTemplate('footer'); ?>
        <?php endif; ?>
    </div>
</div>