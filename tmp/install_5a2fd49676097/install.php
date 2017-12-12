<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

require_once(realpath(dirname(__FILE__) . '/foxinstall.php'));

class com_foxcontactInstallerScript extends FoxInstaller
{
	function update($parent)
	{
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fsession.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fdispatcher.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fadminmailer.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fsubmittermailer.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fjmessenger.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/helpers/fuploader.php');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/js/jtext.js');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/js/fileuploader.js');
		@unlink(JPATH_ROOT . '/components/com_foxcontact/js/fileuploader.min.js');
		@unlink(JPATH_ROOT . '/media/com_foxcontact/css/chosen.css');
		@unlink(JPATH_ROOT . '/media/com_foxcontact/css/bootstrap.css');
		parent::install($parent);
	}
}