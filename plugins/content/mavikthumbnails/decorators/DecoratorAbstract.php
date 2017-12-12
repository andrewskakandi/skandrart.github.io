<?php
/**
 * @package Joomla
 * @subpackage mavikThumbnails 2
 * @copyright 2014 Vitaliy Marenkov
 * @author Vitaliy Marenkov <admin@mavik.com.ua>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 * 
 * Plugin automatic replaces big images to thumbnails.
 */

namespace Plugin\Content\MavikThumbnails;

defined('_JEXEC') or die();

/**
 * Decoration of img-tag
 */
abstract class DecoratorAbstract
{
    /**
     * @var \plgContentMavikThumbnails
     */
    protected $plugin;

    /**
     * Type of decorator
     * 
     * @var string
     */
    protected $type;
    protected $headerAdded = false;

    public function __construct(&$plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Add code in header of document 
     */
    public function addHeader()
    {
        // Hover-effect can be used in every decorators
        if ($this->hoverAllowed() && $this->plugin->params->get('hover')) {
            $base     = \JUri::base(true);
            \JHtml::_('jquery.framework');
            $document = \JFactory::getDocument();
            $document->addScript($base.'/media/plg_content_mavikthumbnails/js/mavikthumbnails_hover.js');
        }
    }

    /**
     * Action for each item
     */
    public function item()
    {
    }

    /**
     * Decoration of img-tag
     *
     * @return string
     */
    public function decorate()
    {
        if (!$this->headerAdded && $this->plugin->params->get('link_scripts')) {
            $this->addHeader();
            $this->headerAdded = true;
        }

        if ($this->plugin->params->get('move_style', 1) && $this->isThumbnail()) {
            $this->linkStyle = $this->plugin->imgTag->getStyleWithoutSize();
            $this->plugin->imgTag->setStyleOnlySize();
        } else {
            $this->linkStyle = '';
        }

        $path              = \JPluginHelper::getLayoutPath('content',
                'mavikthumbnails', $this->type);
        $this->image       = $this->plugin->imgTag;
        $this->info        = $this->plugin->imageInfo;
        $this->item        = $this->plugin->item;
        $this->params      = $this->plugin->itemParams;
        $this->isThumbnail = $this->isThumbnail();

        ob_start();
        include $path;
        return ob_get_clean();
    }

    public function isThumbnail()
    {
        return !empty($this->plugin->imageInfo->thumbnail->url);
    }

    public function hoverAllowed()
    {
        return true;
    }
}