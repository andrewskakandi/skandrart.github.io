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
jimport('joomla.application.component.view');

class EasybookReloadedViewEasybookReloaded extends JViewLegacy
{
    function display($tpl = null)
    {
        $document = JFactory::getDocument();
        $menu = new JSite();
        $menus = $menu->getMenu();
        $params = JComponentHelper::getParams('com_easybookreloaded');

        // Set CSS File
        if($params->get('template') == 0)
        {
            $document->addStyleSheet('components/com_easybookreloaded/css/easybookreloaded.css');
        }
        elseif($params->get('template') == 1)
        {
            $document->addStyleSheet('components/com_easybookreloaded/css/easybookreloadeddark.css');
        }
        elseif($params->get('template') == 2)
        {
            $document->addStyleSheet('components/com_easybookreloaded/css/easybookreloadedtransparent.css');
        }

        $document->addCustomTag('
		<!--[if IE 6]>
    		<style type="text/css">
                    .easy_align_middle { behavior: url('.JURI::base().'components/com_easybookreloaded/scripts/pngbehavior.htc); }
                    .png { behavior: url('.JURI::base().'components/com_easybookreloaded/scripts/pngbehavior.htc); }
    		</style>
  		<![endif]-->');

        // Get neede data
        $entries = $this->get('Data');
        $count = $this->get('Total');
        $pagination = $this->get('Pagination');

        // Show RSS Feed
        $link = '&format=feed&limitstart=';
        $attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
        $document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
        $attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
        $document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);

        // Add meta data from menu link
        $menu = $menus->getActive();

        if($menu->params->get('menu-meta_description'))
        {
            $document->setDescription($menu->params->get('menu-meta_description'));
        }

        if($menu->params->get('menu-meta_keywords'))
        {
            $document->setMetadata('keywords', $menu->params->get('menu-meta_keywords'));
        }

        if($menu->params->get('robots'))
        {
            $document->setMetadata('robots', $menu->params->get('robots'));
        }

        $heading = $document->getTitle();

        // Assign Data to template
        $this->assignRef('heading', $heading);
        $this->assignRef('entries', $entries);
        $this->assignRef('count', $count);
        $this->assignRef('pagination', $pagination);
        $this->assignRef('params', $params);

        // Add HTML Head Link
        if(method_exists($document, 'addHeadLink'))
        {
            $paginationdata = $pagination->getData();

            if($paginationdata->start->link)
            {
                $document->addHeadLink($paginationdata->start->link, 'first');
            }

            if($paginationdata->previous->link)
            {
                $document->addHeadLink($paginationdata->previous->link, 'prev');
            }

            if($paginationdata->next->link)
            {
                $document->addHeadLink($paginationdata->next->link, 'next');
            }

            if($paginationdata->end->link)
            {
                $document->addHeadLink($paginationdata->end->link, 'last');
            }
        }

        parent::display($tpl);
    }
}
