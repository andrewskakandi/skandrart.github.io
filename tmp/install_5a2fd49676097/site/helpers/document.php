<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

abstract class FoxDocument
{
	protected $Document;
	protected $Prefix;


	abstract protected function addCss($slug);


	abstract protected function addJs($slug);


	abstract public function addStyleSheet($url);


	abstract public function addScript($url);


	/**
	 * @return FoxDocument
	 */
	static public function getInstance()
	{
		// Read the global configuration
		$config = JComponentHelper::getParams("com_foxcontact");
		// Read the current mode. Defaults to best performances.
		$mode = $config->get("resources_loading", "Performance");
		// Determine the class full name
		$class = "FoxDocument" . $mode;
		// Create an instance of the class
		return new $class;
	}


	public function __construct()
	{
		$application = JFactory::getApplication();
		$this->Document = JFactory::getDocument();

		$this->Prefix = "index.php?option=" . $GLOBALS["com_name"] .
			"&view=loader" .
			"&owner=" . $application->owner .
			"&id=" . $application->oid;
	}


	public function addResource(array $values)
	{
		$slug = "";
		foreach ($values as $key => $value)
		{
			$slug .= "&" . $key . "=" . $value;
		}

		$method = "add" . ucwords($values["type"]);
		$this->{$method}($slug);
	}
}


class FoxDocumentPerformance extends FoxDocument
{
	protected function addCss($slug)
	{
		$this->Document->addStyleSheet(JRoute::_($this->Prefix . $slug));
	}


	protected function addJs($slug)
	{
		$this->Document->addScript(JRoute::_($this->Prefix . $slug));
	}


	public function addStyleSheet($url)
	{
		$this->Document->addStyleSheet($url);
	}


	public function addScript($url)
	{
		$this->Document->addScript($url);
	}
}


class FoxDocumentCompatibility extends FoxDocument
{
	protected function addCss($slug)
	{
		$this->Document->addCustomTag('<link rel="stylesheet" href="' . JRoute::_($this->Prefix . $slug) . '" type="text/css" />');
	}


	protected function addJs($slug)
	{
		$this->Document->addCustomTag('<script src="' . JRoute::_($this->Prefix . $slug) . '" type="text/javascript"></script>');
	}


	public function addStyleSheet($url)
	{
		$this->Document->addCustomTag('<link rel="stylesheet" href="' . $url . '" type="text/css" />');
	}


	public function addScript($url)
	{
		$this->Document->addCustomTag('<script src="' . $url . '" type="text/javascript"></script>');
	}
}