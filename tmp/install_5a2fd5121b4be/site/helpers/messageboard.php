<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

class FoxMessageBoard
{
	const success = 0x01;
	const info = 0x02;
	const warning = 0x04;
	const error = 0x08;

	protected $Level = 0;
	protected $Messages = array();
	public static $Levels = array(
		FoxMessageBoard::success => "success",
		FoxMessageBoard::info => "info",
		FoxMessageBoard::warning => "warning",
		FoxMessageBoard::error => "error"
	);


	public function Add($message, $level = 0)
	{
		$this->Messages[] = $message;
		$this->RaiseLevel($level);
	}


	public function Append($messages, $level = 0)
	{
		$this->Messages += $messages;
		$this->RaiseLevel($level);
	}


	public function Clear()
	{
		$this->Messages[] = array();
		$this->Level = 0;
	}


	public function RaiseLevel($level)
	{
		if ($level > $this->Level) $this->Level = $level;
	}


	public function Display()
	{
		echo $this->__toString();
	}


	public function __toString()
	{
		$result = "";
		if (!count($this->Messages)) return $result;

		/* Don't remove the following code, or you will loose system messages too, like
		"Invalid field: email" or "Your messages has been received" and so on.
		If you have problems related to language files, simply fix your language files. */

		$result .= '<div class="alert alert-' . FoxMessageBoard::$Levels[$this->Level] . '">' .
			'<ul class="fox_messages">';

		foreach ($this->Messages as $message)
		{
			$result .= '<li>' . $message . '</li>';
		}

		$result .= '</ul>' .
			'</div>';

		return $result;
	}
}