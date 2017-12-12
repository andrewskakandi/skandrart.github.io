<?php
/**
 * @package Joomla
 * @subpackage mavikThumbnails 2
 * @copyright 2014 Vitaliy Marenkov
 * @author Vitaliy Marenkov <admin@mavik.com.ua>
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Plugin\Content\MavikThumbnails;

defined('_JEXEC') or die();

/**
 * Decorator for Category Blog of Content Component
 */
class DecoratorComContentCategory extends DecoratorAbstract
{
   
    public function __construct(&$plugin)
    {
        $this->type = 'com_content.category';
        parent::__construct($plugin);
    }
    
    public function hoverAllowed()
    {
        return false;
    }    
}
