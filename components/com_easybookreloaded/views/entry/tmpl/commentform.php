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
echo '<!-- Easybook Reloaded - Joomla! 3.x - Kubik-Rubik Joomla! Extensions -->';
?>
<div id="easybook">
    <?php if($this->params->get('show_page_title', 1))
    {
        ?>
        <h2 class="componentheading"><?php echo $this->heading ?></h2>
<?php } ?>
    <div class="easy_entrylink">
        <strong><a class="view" href="<?php echo JRoute::_('index.php?option=com_easybookreloaded'); ?>" style="text-decoration: none !important;"><?php echo JText::_('COM_EASYBOOKRELOADED_READ_GUESTBOOK'); ?><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/book.png', JText::_('COM_EASYBOOKRELOADED_READ_GUESTBOOK').":", 'height="16" border="0" width="16" class="png" style="vertical-align: middle; padding-left: 3px;"'); ?></a></strong>
        <br /><br />
        <form class="form-horizontal" name="gbookForm" action="<?php JRoute::_('index.php'); ?>" target="_top" method="post">
           <?php if($this->params->get('support_bbcode', false)) : ?>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="btn-toolbar">
                        <div class="btn-group">
                            <?php if($this->params->get('support_link', false)) : ?>
                                <a href="javascript:x()" onclick="DoPrompt('url');"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/world_link.png" hspace="3" border="0" alt="" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_WEB_ADDRESS'); ?>" height="16" width="16" /></a>
                            <?php endif; ?>
                            <?php if($this->params->get('support_mail', true)) : ?>
                                <a href="javascript:x()" onclick="DoPrompt('email');"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/email_link.png" hspace="3" border="0" alt="" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_EMAIL_ADDRESS'); ?>" height="16" width="16" /></a>
                            <?php endif; ?>
                            <?php if($this->params->get('support_pic', false)): ?>
                                <a href="javascript:x()" onclick="DoPrompt('image_link');"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/picture_link.png" hspace="3" border="0" alt="" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_SHOW_IMAGE_WITH_A_LINK'); ?>" height="16" width="16" /></a>
                            <?php endif; ?>
                            <?php if($this->params->get('support_pic', false)) : ?>
                                <a href="javascript:x()" onclick="DoPrompt('image');"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/picture.png" hspace="3" border="0" alt="" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_SHOWS_IMAGE_FROM_AN_URL'); ?>" height="16" width="16" /></a>
                            <?php endif; ?>
                            <?php if($this->params->get('support_code', false)) : ?>
                                <a href="javascript:x()" onclick="DoPrompt('code');"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/code.png" hspace="3" border="0" alt="" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_ENTER_CODE'); ?>" height="16" width="16" /></a>
                            <?php endif; ?>
                            <?php if($this->params->get('support_youtube', false)) : ?>
                                <a href="javascript:x()" onclick="DoPrompt('youtube');"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/youtube.png" hspace="3" border="0" alt="" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_YOUTUBE'); ?>" height="16" width="16" /></a>
                            <?php endif; ?>
                            <a href="javascript:x()" onclick="insert('[B]', '[/B]')"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_bold.png" hspace="3" border="0" alt="Bold" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_BOLD'); ?>" height="16" width="16" /></a>
                            <a href="javascript:x()" onclick="insert('[I]', '[/I]')"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_italic.png" hspace="3" border="0" alt="Italic" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_ITALIC'); ?>" height="16" width="16" /></a>
                            <a href="javascript:x()" onclick="insert('[U]', '[/U]')"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_underline.png" hspace="3" border="0" alt="Underline" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_UNDERLINE'); ?>" height="16" width="16" /></a>
                            <a href="javascript:x()" onclick="insert('[CENTER]', '[/CENTER]')"><img src="<?php echo $this->baseurl ?>/components/com_easybookreloaded/images/text_align_center.png" hspace="3" border="0" alt="Center" title="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_CENTER'); ?>" height="16" width="16" /></a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="control-group">
                <label class="control-label" for="gbcomment"><?php echo JTEXT::_('COM_EASYBOOKRELOADED_ADMIN_COMMENT'); ?></label>
                <div class="controls">
                    <textarea name="gbcomment" id="gbcomment" rows="10" placeholder="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_ADMIN_COMMENT'); ?>"><?php echo $this->entry->gbcomment; ?></textarea>
                </div>
            </div>
            <?php if($this->params->get('support_smilie', true)) : ?>
                <div class="control-group">
                    <label class="control-label"></label>
                    <div class="btn-toolbar">
                        <div class="btn-group">
                            <?php $count = 1; ?>
                            <?php $smiley = EasybookReloadedHelperSmilie::getSmilies(); ?>
                            <?php foreach($smiley as $i => $sm) : ?>
                                <?php if($this->params->get('smilie_set') == 0) : ?>
                                    <a href="javascript:insertsmilie('<?php echo $i; ?>')" title="<?php echo $i; ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/smilies/'.$sm, $sm, 'border="0"'); ?></a>
                                <?php else : ?>
                                    <a href="javascript:insertsmilie('<?php echo $i; ?>')" title="<?php echo $i; ?>"><?php echo JHTML::_('image', 'components/com_easybookreloaded/images/smilies2/'.$sm, $sm, 'border="0"'); ?> </a>
                                <?php endif; ?>
                                <?php $count++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            <div class="control-group">
                <label class="control-label" for="inform"><?php echo JTEXT::_('COM_EASYBOOKRELOADED_INFORM'); ?></label>
                <div class="controls">
                    <input type="checkbox" name="inform" id="inform" value="1" />
                </div>
            </div>
            <div class="control-group">
                <label class="control-label"></label>
                <div class="controls">
                    <p id="easysubmit">
                        <input type="submit" name="send" value="<?php echo JTEXT::_('COM_EASYBOOKRELOADED_SUBMIT_ENTRY'); ?>" class="btn" />
                    </p>
                </div>
            </div>
            <input type="hidden" name="option" value="com_easybookreloaded" />
            <input type="hidden" name="task" value="savecomment"/>
            <input type="hidden" name="controller" value="entry" />
            <input type="hidden" name="id" value="<?php echo $this->entry->id; ?>" />
        </form>
        <?php if($this->params->get('show_logo', true) == 1) : ?>
            <p id="easyfooter">
                <a href="http://joomla-extensions.kubik-rubik.de" title="Easybook Reloaded - Kubik-Rubik Joomla! Extension by Viktor Vogel" target="_blank"><img src="<?php echo JURI::base(); ?>components/com_easybookreloaded/images/logo.png" class="png" alt="EasyBook Reloaded - Logo" title="Easybook Reloaded - Kubik-Rubik Joomla! Extension by Viktor Vogel" border="0" width="80" height="36" /></a>
            </p>
        <?php elseif($this->params->get('show_logo', true) == 2) : ?>
            <p id="easyfooter">
                <a href="http://joomla-extensions.kubik-rubik.de" title="Easybook Reloaded - Kubik-Rubik Joomla! Extension by Viktor Vogel" target="_blank">Easybook Reloaded</a>
            </p>
        <?php elseif($this->params->get('show_logo', true) == 3) : ?>
            <p id="easyfooterinv">
                <a href="http://joomla-extensions.kubik-rubik.de" title="Easybook Reloaded - Kubik-Rubik Joomla! Extension by Viktor Vogel" target="_blank">Easybook Reloaded</a>
            </p>
        <?php endif; ?>
    </div>
</div>