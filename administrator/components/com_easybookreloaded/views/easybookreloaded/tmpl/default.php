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
                                <input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
                            </th>
                            <th width="2%">
                                <?php echo JText::_('COM_EASYBOOKRELOADED_PUBLISHED'); ?>
                            </th>
                            <th width="6%">
                                <?php echo JText::_('COM_EASYBOOKRELOADED_AUTHOR'); ?>
                            </th>
                            <th width="12%">
                                <?php echo JText::_('COM_EASYBOOKRELOADED_TITLE'); ?>
                            </th>
                            <th>
                                <?php echo JText::_('COM_EASYBOOKRELOADED_ENTRY'); ?>
                            </th>
                            <th width="14%">
                                <?php echo JText::_('COM_EASYBOOKRELOADED_DATE'); ?>
                            </th>
                            <th>
                                <?php echo JText::_('COM_EASYBOOKRELOADED_RATING'); ?>
                            </th>
                            <th width="15%">
                                <?php echo JText::_('COM_EASYBOOKRELOADED_COMMENT'); ?>
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
                        $published = JHTML::_('jgrid.published', $row->published, $i);
                        $link = JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=edit&cid[]='.$row->id);
                        ?>
                        <tr class="<?php echo "row$k"; ?>">
                            <td>
                                <?php echo $checked; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php echo $published; ?>
                            </td>
                            <td>
                                <span class="hasTooltip" title="<?php echo $row->gbname ?>">
                                    <?php
                                    if(strlen($row->gbname) > 16) :
                                        echo substr($row->gbname, 0, 16)."...";
                                    else :
                                        echo $row->gbname;
                                    endif;
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="hasTooltip" title="<?php echo $row->gbtitle ?>">
                                    <?php
                                    if(strlen($row->gbtitle) > 30) :
                                        echo substr($row->gbtitle, 0, 30)."...";
                                    else :
                                        echo $row->gbtitle;
                                    endif;
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="hasTooltip" title="<?php echo $row->gbtext ?>">
                                    <a href="<?php echo $link ?>">
                                        <?php
                                        if(strlen(htmlspecialchars_decode($row->gbtext)) > 150) :
                                            echo htmlspecialchars(mb_substr(htmlspecialchars_decode($row->gbtext, ENT_QUOTES), 0, 150))."...";
                                        else :
                                            echo $row->gbtext;
                                        endif;
                                        ?>
                                    </a>
                                </span>
                            </td>
                            <td>
                                <?php echo JHTML::_('date', $row->gbdate, JText::_('DATE_FORMAT_LC2')); ?>
                            </td>
                            <td style="text-align: center;">
                                <?php echo $row->gbvote; ?>
                            </td>
                            <td>
                                <?php if($row->gbcomment) : ?>
                                    <span class="hasTooltip" title="<?php echo $row->gbcomment ?>">
                                        <?php
                                        if(strlen(htmlspecialchars_decode($row->gbcomment)) > 60) :
                                            echo htmlspecialchars(mb_substr(htmlspecialchars_decode($row->gbcomment, ENT_QUOTES), 0, 60))."...";
                                        else:
                                            echo $row->gbcomment;
                                        endif;
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                        $k = 1 - $k;
                    }
                    ?>
                    <tfoot>
                        <tr>
                            <td colspan="8">
                                <?php echo $this->pagination->getListFooter(); ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <input type="hidden" name="option" value="com_easybookreloaded" />
            <input type="hidden" name="task" value="" />
            <input type="hidden" name="boxchecked" value="0" />
            <input type="hidden" name="controller" value="entry" />
            <?php echo JHTML::_('form.token'); ?>
        </div>
</form>
<div style="text-align: center;">
    <p><?php echo JText::sprintf('COM_EASYBOOKRELOADED_VERSION', _EASYBOOK_VERSION) ?></p>
</div>