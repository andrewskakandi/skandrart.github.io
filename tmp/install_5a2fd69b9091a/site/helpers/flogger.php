<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class FLogger
{
	protected $Handle = null;
	protected $Prefix = "";


	public function __construct($prefix = null, $suffix = null)
	{
		$this->open($suffix);
		if ($prefix) $this->Prefix = "[" . $prefix . "] ";
	}


	function __destruct()
	{
		if ($this->Handle) fclose($this->Handle);
	}


	public function Write($buffer)
	{
		if (!$this->Handle) return false;

		// Remove newlines
		$buffer = str_replace(array("\r", "\n"), " ", $buffer);

		// Go to the end of file just in case another instance has written something
		fseek($this->Handle, 0, SEEK_END);
		return fwrite($this->Handle, JFactory::getDate()->format("Y-m-d H:i:s") . " " . $this->Prefix . $buffer . PHP_EOL);
	}


	protected function open($suffix = null)
	{
		if (!$suffix) $suffix = md5(JFactory::getConfig()->get("secret"));
		$this->Handle = @fopen(JFactory::getConfig()->get("log_path") . "/" . substr(basename(realpath(dirname(__FILE__) . '/..')), 4) . "-" . $suffix . ".txt", 'a+');
	}
}

