<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

class FoxConflicting
{
	protected $messages = array();


	function __construct()
	{
		$this->check();
	}


	public function HasMessages()
	{
		return (bool)count($this->messages);
	}


	public function GetMessages()
	{
		return $this->messages;
	}


	protected function check()
	{
		$extensions = array();
		$extensions[] = new FoxConflictingYoujoomlaTemplates();

		foreach ($extensions as $extension)
		{
			if ($extension->{"Detect"}())
			{
				if (!$extension->{"Patch"}())
				{
					// See the documentation string
					JFactory::getLanguage()->load("com_foxcontact", JPATH_ADMINISTRATOR);

					$this->messages[] = JText::_("COM_FOXCONTACT_ERR_CONFLICTING_EXTENSION") .
						' <a href="' . $extension->{"Link"}() . '">' .
						JText::_("COM_FOXCONTACT_DOCUMENTATION") .
						'</a>.';
				}
			}
		}
	}

}


class FoxConflictingYoujoomlaTemplates
{
	public function Detect()
	{
		// Detect the conflicting (unpatched) extension
		return (function_exists("yjsg_validate_data") && !defined("yjsg_validate_data_fixed"));
	}


	public function Patch()
	{
		$patch = '<?php defined("_JEXEC") or die(); define("yjsg_validate_data_fixed", 1); function yjsg_validate_data($array) {} ';
		$function = new ReflectionFunction("yjsg_validate_data");
		$filename = $function->getFileName();
		$result = file_put_contents($filename, $patch);
		return (bool)$result;
	}


	public function Link()
	{
		// Related manual page
		return "http://www.fox.ra.it/forum/24-troubleshooting/8760-youjoomla-template-removes-special-and-non-latin-characters.html";
	}

}

?>