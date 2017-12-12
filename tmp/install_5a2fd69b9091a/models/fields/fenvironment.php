<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

jimport('joomla.form.formfield');

class JFormFieldFEnvironment extends JFormField
{
	protected $type = 'FEnvironment';


	public function __construct(JForm $form = null)
	{
		parent::__construct($form);

		static $resources = true;
		if ($resources)
		{
			$resources = false;
			$name = basename(realpath(dirname(__FILE__) . "/../.."));
			$document = JFactory::getDocument();

			// $this->element is not ready on the constructor
			//$type = (string)$this->element["type"];
			$type = strtolower($this->type);
			if (file_exists(JPATH_ADMINISTRATOR . "/components/" . $name . "/js/" . $type . ".js"))
			{
				$document->addScript(JUri::current() . "?option=" . $name . "&amp;view=loader&amp;filename=" . $type . "&amp;type=js");
			}

			if (file_exists(JPATH_ADMINISTRATOR . "/components/" . $name . "/css/" . $type . ".css"))
			{
				$document->addStyleSheet(JUri::base(true) . "/components/" . $name . "/css/" . $type . ".css");
			}

			$scope = JFactory::getApplication()->scope;
			if (file_exists(JPATH_ADMINISTRATOR . "/components/" . $name . "/js/" . $scope . ".js"))
			{
				$document->addScript(JUri::current() . "?option=" . $name . "&amp;view=loader&amp;filename=" . $scope . "&amp;type=js");
			}

			if (file_exists(JPATH_ADMINISTRATOR . "/components/" . $name . "/css/" . $scope . ".css"))
			{
				$document->addStyleSheet(JUri::base(true) . "/components/" . $name . "/css/" . $scope . ".css");
			}
		}

	}


	protected function getInput()
	{
		return "";
	}


	protected function getLabel()
	{
		return "";
	}
}
