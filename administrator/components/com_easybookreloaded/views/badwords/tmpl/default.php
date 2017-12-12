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
JHtml::_('bootstrap.tooltip');
?>

<form action="<?php echo JRoute::_('index.php?option=com_easybookreloaded'); ?>" method="post" name="adminForm" id="adminForm">
    <?php if(!empty($this->sidebar)): ?>
        <div id="j-sidebar-container" class="span2">
            <?php echo $this->sidebar; ?>
        </div>
        <div id="j-main-container" class="span10">
        <?php else : ?>
            <div id="j-main-container">
            <?php endif; ?>
            <div id="filter-bar" class="btn-toolbar">
                <div class="filter-search btn-group pull-left">
                    <label for="filter_search" class="element-invisible"><?php echo JText::_('COM_EASYBOOKRELOADED_FILTERSEARCH'); ?></label>
                    <input type="text" name="filter_search" placeholder="<?php echo JText::_('COM_EASYBOOKRELOADED_FILTERSEARCH'); ?>" id="filter_search" value="<?php echo $this->escape($this->_state->get('filter.search')); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_FILTERSEARCH'); ?>" />
                </div>
                <div class="btn-group pull-left">
                    <button class="btn tip hasTooltip" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
                    <button class="btn tip hasTooltip" type="button" onclick="document.id('filter_search').value='';this.form.submit();" title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"><i class="icon-remove"></i></button>
                </div>
                <div class="btn-group pull-right">
                    <label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC'); ?></label>
                    <?php echo $this->pagination->getLimitBox(); ?>
                </div>
            </div>
            <div class="clearfix"> </div>
            <div id="editcell">
                <table id="articleList" class="table table-striped">
                    <thead>
                        <tr>
                            <th width="20">
                                <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->items); ?>);" />
                            </th>
                            <th width="5%">
                                <?php echo JText::_('COM_EASYBOOKRELOADED_ID'); ?>
                            </th>
                            <th>
                                <?php echo JText::_('COM_EASYBOOKRELOADED_WORD'); ?>
                            </th>
                        </tr>
                    </thead>
                    <?php
                    $k = 0;
                    $n = count($this->items);

                    for($i = 0; $i < $n; $i++)
                    {
                        $row = $this->items[$i];
                        $checked = JHTML::_('grid.id', $i, $row->id);
                        $link = JRoute::_('index.php?option=com_easybookreloaded&controller=badwords&task=edit&cid[]='.$row->id);
                        ?>
                        <tr class="<?php echo "row$k"; ?>">
                            <td>
                                <?php echo $checked; ?>
                            </td>
                            <td>
                                <?php echo $row->id; ?>
                            </td>
                            <td>
                                <a href="<?php echo $link ?>"><?php echo $row->word; ?></a>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                    <tfoot>
                        <tr>
                            <td colspan="7">
                                <?php echo $this->pagination->getListFooter(); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="option" value="com_easybookreloaded" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="controller" value="badwords" />
            <?php echo JHTML::_('form.token'); ?>
        </div>
</form>
<div style="text-align: center;">
    <p><?php echo JText::sprintf('COM_EASYBOOKRELOADED_VERSION', _EASYBOOK_VERSION) ?></p>
</div>