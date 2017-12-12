<?php
/**
 * Library for Joomla for creating thumbnails of images
 * 
 * @package Mavik Thumb
 * @version 1.0
 * @author Vitaliy Marenkov <admin@mavik.com.ua>
 * @copyright 2012 Vitaliy Marenkov
 * @license GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

/**
 * Strategy of resizing
 * Parent class
 */
abstract class MavikThumbResizeType
{

    /**
     * Set thumnail size
     * 
     * @param MavikThumbInfo $info
     * @param int $width
     * @param int $height
     */
    public function setSize(MavikThumbInfo $info, $width, $height, &$options)
    {
        $defaultDimension = $this->getDefaultDimension($info, $width, $height);
        $defaultSize      = $this->setDefaultSize(
            $info, $width, $height, $options['defaultSize'],
            $options['defaultWidth'], $options['defaultHeight'],
            $defaultDimension
        );

        if (!$defaultSize) {
            switch ($defaultDimension) {
                case 'w':
                    $info->thumbnail->width  = $width;
                    $info->thumbnail->height = round($info->original->height * $width
                        / $info->original->width);
                    break;
                case 'h':
                    $info->thumbnail->height = $height;
                    $info->thumbnail->width  = round($info->original->width * $height
                        / $info->original->height);
                    break;
                default:
                    $info->thumbnail->width  = $width;
                    $info->thumbnail->height = $height;
            }
        }
    }

    /**
     * Coordinates and size of area in the original image
     * 
     * @return array
     */
    public function getArea(MavikThumbInfo $info)
    {
        return array(0, 0, $info->original->width, $info->original->height);
    }

    /**
     * Which dimension to use: width or heigth or width and heigth?
     * 
     * @return string
     */
    protected function getDefaultDimension(MavikThumbInfo $info, $width, $height)
    {
        return 'wh';
    }

    /**
     * Set default size
     * 
     * @param MavikThumbInfo $info
     * @param int $thumbWidth
     * @param int $thumbHeight
     * @param string $defSize
     * @param int $defWidth
     * @param int $defHeight
     * @param string $defDimension
     */
    protected function setDefaultSize(MavikThumbInfo $info, $thumbWidth,
                                      $thumbHeight, $defSize, $defWidth,
                                      $defHeight, $defDimension)
    {
        $setDimension = '';
        $origWidth    = $info->original->width;
        $origHeight   = $info->original->height;
        if (
            ($defSize == 'all' && ($defHeight || $defWidth)) ||
            ($defSize == 'not_resized' && ((!$thumbWidth || $thumbWidth == $origWidth)
            && (!$thumbHeight || $thumbHeight == $origHeight)))
        ) {
            // Use defauult width or height
            if (!$defHeight && $defWidth && $defWidth < $origWidth) {
                // Only default width is setted
                $setDimension = 'w';
            } elseif (!$defWidth && $defHeight && $defHeight < $origHeight) {
                // Only default height is setted
                $setDimension = 'h';
            } elseif ($defWidth && $defHeight && ($defWidth < $origWidth || $defHeight
                < $origHeight)) {
                // Both default sizes are setted
                $setDimension = $defDimension;
            }

            // Set size
            switch ($setDimension) {
                case 'w':
                    $info->thumbnail->width  = $defWidth;
                    $info->thumbnail->height = round($origHeight * $defWidth / $origWidth);
                    break;
                case 'h':
                    $info->thumbnail->height = $defHeight;
                    $info->thumbnail->width  = round($origWidth * $defHeight / $origHeight);
                    break;
                case 'wh':
                    $info->thumbnail->height = $defHeight;
                    $info->thumbnail->width  = $defWidth;
                    break;
            }
        }

        return $setDimension;
    }
}
?>
