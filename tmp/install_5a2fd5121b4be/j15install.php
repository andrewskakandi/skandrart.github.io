<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

if (version_compare(JVERSION, "3.0.0", "<"))
{
	JError::raiseWarning(500, 'This extension requires Joomla 3.0 or newer. See the <a href="http://www.fox.ra.it/forum/15-installation/77-joomla-compatibility-list.html">compatibility list</a>.');
}
