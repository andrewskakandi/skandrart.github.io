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
 * Decorator for Slimbox (on Mootools)
 */
class DecoratorHighslide extends DecoratorAbstract
{
    public function __construct(&$plugin)
    {
        $this->type = 'highslide';
        parent::__construct($plugin);
    }
    
    public function addHeader()
    {
        parent::addHeader();
        
        $base = \JUri::base(true);
        $document = \JFactory::getDocument();
        $document->addScript($base.'/media/plg_content_mavikthumbnails/highslide/highslide-with-gallery.packed.js');
        $document->addStyleSheet($base.'/media/plg_content_mavikthumbnails/highslide/highslide.css');
		
		$document->addScriptDeclaration('
				hs.graphicsDir = "'.$this->plugin->url.'/mavikthumbnails/highslide/graphics/"
				hs.align = "center";
				hs.transitions = ["expand", "crossfade"];
				hs.lang = {
				  loadingText :     "Загрузка...",
				  playTitle :       "Просмотр слайдшоу",
				  pauseTitle:       "Пауза",
				  previousTitle :   "Предыдущее изображение",
				  nextTitle :       "Следующее изображение",
				  moveTitle :       "Переместить",
				  closeTitle :      "Закрыть (Esc)",
				  fullExpandTitle : "Развернуть до полного размера",
				  restoreTitle :    "Кликните для закрытия картинки, нажмите и удерживайте для перемещения",
				  focusTitle :      "Сфокусировать",
				  loadingTitle :    "Нажмите для отмены"
			  };
				hs.outlineType = "rounded-white";
				hs.fadeInOut = true;
				hs.showCredits = false;
				//hs.dimmingOpacity = 0.75;
			');
		
        $document->addScriptDeclaration('
           hs.graphicsDir = "'.$base.'/media/plg_content_mavikthumbnails/highslide/graphics/";
           hs.align = "center";
           hs.transitions = ["expand", "crossfade"];
				hs.outlineType = "rounded-white";
				hs.showCredits = false;           
        ');
        
    }
    
    public function item()
    {
        if(!$this->headerAdded) { return; }

        if (!isset($this->plugin->item->id)) {
            $this->plugin->item->id = uniqid();
        }
        $document = \JFactory::getDocument();
        $document->addScriptDeclaration('
            hs.addSlideshow({
               slideshowGroup: "'.$this->plugin->item->id.'",
               interval: 5000,
               repeat: false,
               useControls: true,
               fixedControls: true,
               overlayOptions: {
                  opacity: .6,
                  position: "top center",
                  hideOnMouseOut: true
               }
            });            
        ');
    }
}