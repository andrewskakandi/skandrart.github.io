<?php defined("_JEXEC") or die(file_get_contents("index.html"));


/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
class FLangHandler
{
	protected $messages = array();


	function __construct()
	{
		$lang = JFactory::getLanguage();
		$ini = JPATH_SITE . "/language/" . $lang->get("tag") . "/" . $lang->get("tag") . "." . $GLOBALS["com_name"] . ".ini";

		if (!file_exists($ini))
		{
			$xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/' . $GLOBALS["com_name"] . "/" . $GLOBALS["ext_name"] . '.xml');
			foreach ($xml->languages->language as $l)
			{
				if (strpos((string)$l, $lang->get("tag")) === 0)
				{
					$this->messages = array($lang->get("name") . " translation has not been installed, but <strong>is available</strong>. To fix this problem simply install this extension once again, without uninstalling it. <a href=\"http://www.fox.ra.it/forum/19-languages-and-translations/2886-my-language-is-available-but-it-hasnt-been-installed.html\">Learn more</a>.");
					break;
				}
			}

		}
	}


	public function HasMessages()
	{
		return (bool)count($this->messages);
	}


	public function GetMessages()
	{
		return $this->messages;
	}
}
