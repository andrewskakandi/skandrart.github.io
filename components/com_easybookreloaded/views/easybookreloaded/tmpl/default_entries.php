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

<div class="easy_entries">
    <?php foreach($this->entries as $entry) : ?>
        <div class="easy_frame" <?php if(!$entry->published) : ?>style="background-color: #fffefd; border: #ffb39b solid 1px;"<?php endif; ?>>
            <div class="easy_top" <?php if(!$entry->published) : ?>style="background-color: #FFE7D7;"<?php endif; ?>>
                <div class="easy_top_left">
                    <strong class="easy_big" id="gbentry_<?php echo $entry->id; ?>"><?php echo $entry->gbname ?></strong>
                    <strong class="easy_small">
                        <?php if($this->params->get('date_format') == 0) : ?>
                            <?php echo JHTML::_('date', $entry->gbdate, JText::_('DATE_FORMAT_LC2')); ?>
                        <?php else : ?>
                            <?php echo JHTML::_('date', $entry->gbdate, JText::_('DATE_FORMAT_LC1')); ?>
                        <?php endif; ?>
                        <?php if($entry->gbloca) : ?>
                            <?php echo ' | '.$entry->gbloca; ?>
                        <?php endif; ?>
                        <?php if(!$entry->published) : ?>| <strong class="easy_small_red"><?php echo JText::_('COM_EASYBOOKRELOADED_ENTRY_OFFLINE'); ?></strong><?php endif; ?>
                    </strong>
                </div>
                <div class="easy_top_right">
                    <?php if($this->params->get('show_rating', true) AND $entry->gbvote !== '0') : ?>
                        <?php for($start = 1; $start <= $this->params->get('rating_max', 5); $start++) : ?>
                            <?php if($this->params->get('show_rating_type') == 0) : ?>
                                <?php $ratimg = $entry->gbvote >= $start ? 'sun_full.png' : 'sun_empty.png'; ?>
                            <?php elseif($this->params->get('show_rating_type') == 1) : ?>
                                <?php $ratimg = $entry->gbvote >= $start ? 'star_full.png' : 'star_empty.png'; ?>
                            <?php elseif($this->params->get('show_rating_type') == 2) : ?>
                                <?php $ratimg = $entry->gbvote >= $start ? 'star_boxed_full.png' : 'star_boxed_empty.png'; ?>
                            <?php endif; ?>
                            <?php $ratimg_alt = $start.' '.ucwords(str_replace('_', ' ', substr($ratimg, 0, -4))); ?>
                            <?php echo JHTML::_('image', 'components/com_easybookreloaded/images/'.$ratimg, JText::_('COM_EASYBOOKRELOADED_RATING').' - '.$ratimg_alt, 'class="easy_align_middle"'); ?>
                        <?php endfor; ?>
                    <?php endif; ?>
                    <?php if(_EASYBOOK_CANEDIT) : ?>
                        <div class="easy_top_right_admin">
                            <a href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=edit&cid='.(int)$entry->id); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_EDIT_ENTRY'); ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/edit.png', JText::_('COM_EASYBOOKRELOADED_EDIT_ENTRY'), 'class="easy_align_middle"'); ?></a>
                            <a href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=remove&cid='.(int)$entry->id); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_DELETE_ENTRY'); ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/delete.png', JText::_('COM_EASYBOOKRELOADED_DELETE_ENTRY'), 'class="easy_align_middle"'); ?></a>
                            <?php if(empty($entry->gbcomment)) : ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=comment&cid='.(int)$entry->id); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_ADD_COMMENT'); ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/comment.png', JText::_('COM_EASYBOOKRELOADED_ADD_COMMENT'), 'class="easy_align_middle"'); ?></a>
                            <?php else : ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=comment&cid='.(int)$entry->id); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_EDIT_COMMENT'); ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/comment_edit.png', JText::_('COM_EASYBOOKRELOADED_EDIT_COMMENT'), 'class="easy_align_middle"'); ?></a>
                            <?php endif; ?>
                            <?php if(empty($entry->published)) : ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=publish&cid='.(int)$entry->id); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_PUBLISH_ENTRY'); ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/offline.png', JText::_('COM_EASYBOOKRELOADED_PUBLISH_ENTRY'), 'class="easy_align_middle"'); ?></a>
                            <?php else : ?>
                                <a href="<?php echo JRoute::_('index.php?option=com_easybookreloaded&controller=entry&task=publish&cid='.(int)$entry->id); ?>" title="<?php echo JText::_('COM_EASYBOOKRELOADED_UNPUBLISH_ENTRY'); ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/online.png', JText::_('COM_EASYBOOKRELOADED_UNPUBLISH_ENTRY'), 'class="easy_align_middle"'); ?></a>
                        <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div style="clear: both;"></div>
            </div>
            <?php if((!empty($entry->gbmail) AND $entry->gbmailshow AND $this->params->get('show_mail', true)) OR (!empty($entry->gbpage) AND $this->params->get('show_home', true)) OR (!empty($entry->gbicq) AND $this->params->get('show_icq', true)) OR (!empty($entry->gbaim) AND $this->params->get('show_aim', true)) OR (!empty($entry->gbmsn) AND $this->params->get('show_msn', true)) OR (!empty($entry->gbyah) AND $this->params->get('show_yah', true)) OR (!empty($entry->gbskype) AND $this->params->get('show_skype', true))) : ?>
                <div class='easy_contact'>
                    <?php if(!empty($entry->gbmail) AND $entry->gbmailshow AND $this->params->get('show_mail', true)) : ?>
                        <?php $image = JHTML::_('image', 'components/com_easybookreloaded/images/email.png', '', 'height="16" width="16" hspace="3"'); ?>
                        <?php echo JHTML::_('email.cloak', $entry->gbmail, true, $image, false); ?>
                    <?php endif; ?>
                    <?php if(!empty($entry->gbpage) AND $this->params->get('show_home', true)) : ?>
                        <?php if(substr($entry->gbpage, 0, 7) != "http://") : ?>
                            <?php $entry->gbpage = 'http://'.$entry->gbpage; ?>
                        <?php endif; ?>
                        <a href="<?php echo $entry->gbpage; ?>" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_HOMEPAGE').' - '.$entry->gbpage; ?>" <?php if($this->params->get('nofollow_home', true)) : ?>rel="nofollow" <?php endif; ?>target="_blank"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/world.png', $entry->gbpage, 'height="16" width="16" hspace="3"'); ?></a>
                    <?php endif; ?>
                    <?php if(!empty($entry->gbicq) AND $this->params->get('show_icq', true)) : ?>
                        <a href="mailto:<?php echo $entry->gbicq; ?>@pager.icq.com"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/im-icq.png', $entry->gbicq, 'title="'.JTEXT::_('COM_EASYBOOKRELOADED_ICQ_NUMBER').' - '.$entry->gbicq.'" height="16" width="16" hspace="3"'); ?></a>
                    <?php endif; ?>
                    <?php if(!empty($entry->gbaim) AND $this->params->get('show_aim', true)) : ?>
                        <a href="aim:goim?screenname=<?php echo $entry->gbaim; ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/im-aim.png', $entry->gbaim, 'title="'.JTEXT::_('COM_EASYBOOKRELOADED_AIM_NICKNAME').' - '.$entry->gbaim.'" height="16" width="16" hspace="3"'); ?></a>
                    <?php endif; ?>
                    <?php if(!empty($entry->gbmsn) AND $this->params->get('show_msn', true)) : ?>
                        <a href="mailto:<?php echo $entry->gbmsn; ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/im-msn.png', $entry->gbmsn, 'title="'.JTEXT::_('COM_EASYBOOKRELOADED_MSN_MESSENGER').' - '.$entry->gbmsn.'" height="16" width="16" hspace="3"'); ?></a>
                    <?php endif; ?>
                    <?php if(!empty($entry->gbyah) AND $this->params->get('show_yah', true)) : ?>
                        <a href="ymsgr:sendIM?<?php echo $entry->gbyah; ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/im-yahoo.png', $entry->gbyah, 'title="'.JTEXT::_('COM_EASYBOOKRELOADED_YAHOO_MESSENGER').' - '.$entry->gbyah.'" height="16" width="16" hspace="3"'); ?></a>
                    <?php endif; ?>
                    <?php if(!empty($entry->gbskype) AND $this->params->get('show_skype', true)) : ?>
                        <a href="skype:<?php echo $entry->gbskype; ?>?call"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/im-skype.png', $entry->gbskype, 'title="'.JTEXT::_('COM_EASYBOOKRELOADED_SKYPE_NICKNAME').' - '.$entry->gbskype.'" height="16" width="16" hspace="3"'); ?></a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if(!empty($entry->gbtitle) AND $this->params->get('show_title', true)) : ?>
                <div class="easy_title"><?php echo $entry->gbtitle; ?></div>
            <?php endif; ?>
            <div class="easy_content"><?php echo EasybookReloadedHelperContent::parse($entry->gbtext) ?></div>
            <?php if($entry->gbcomment) : ?>
                <div class="easy_admincomment"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/admin.png', JText::_('COM_EASYBOOKRELOADED_ADMIN_COMMENT:'), 'class="easy_align_middle"'); ?><strong>
                        <?php if($this->params->get('admin_name')) : ?>
                            <?php echo $this->params->get('admin_name'); ?>
                        <?php else : ?>
                            <?php echo JText::_('COM_EASYBOOKRELOADED_ADMIN_COMMENT'); ?>
                    <?php endif; ?>
                    </strong><br />
                    <?php echo EasybookReloadedHelperContent::parse($entry->gbcomment) ?>
                </div>
            <?php endif; ?>
        </div>
        <p class="clr"></p>
    <?php endforeach; ?>
</div>