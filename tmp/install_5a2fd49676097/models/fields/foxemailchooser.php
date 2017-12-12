<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

jimport("joomla.form.formfield");

JFormHelper::loadFieldClass("list");

class JFormFieldFoxEmailChooser extends JFormFieldList
{
	protected $type = "FoxEmailChooser";


	public function __construct($form = null)
	{
		parent::__construct($form);

		static $resources = true;
		if ($resources)
		{
			$resources = false;
			$com_name = basename(realpath(__DIR__ . "/../.."));
			$document = JFactory::getDocument();

			$document->addScript(JUri::root(true) . "/media/com_foxcontact/js/placeholder.js");

			$type = strtolower($this->type);
			if (file_exists(JPATH_ADMINISTRATOR . "/components/" . $com_name . "/js/" . $type . ".js"))
			{
				$document->addScript(JUri::base(true) . "/components/" . $com_name . "/js/" . $type . ".js");
			}

			if (file_exists(JPATH_ADMINISTRATOR . "/components/" . $com_name . "/css/" . $type . ".css"))
			{
				$document->addStyleSheet(JUri::base(true) . "/components/" . $com_name . "/css/" . $type . ".css");
			}
		}
	}


	protected function getInput()
	{
		// Initialize variables.
		$html = array();

		// Get the field options.
		$options = (array)$this->getOptions();

		$html[] = '<select onchange="EmailChooserChange(this);" onkeyup="EmailChooserChange(this);" name="' . $this->name . '[select]" id="jform_' . $this->fieldname . '" class="foxemailchooser">';
		foreach ($options as $option)
		{
			$selected = ($option->value == $this->value["select"]) ? ' selected="selected"' : "";
			$html[] = '<option value="' . $option->value . '" class="' . $option->class . '"' . $selected . '>' . $option->text . '</option>';
		}
		$html[] = '</select>';

		$html[] = '<fieldset id="' . $this->id . '_children">';
		// Name
		$html[] = '<input type="text" name="' . $this->name . "[name]" . '" id="' . $this->id . '_name' . '"' .
			' style="display:block; margin:8px 0;"' .
			' placeholder ="' . JText::_("COM_FOXCONTACT_NAME") . '"' .
			' value="' . htmlspecialchars(empty($this->value["name"]) ? "" : $this->value["name"], ENT_COMPAT, 'UTF-8') . '"' .
			'/>';

		// Email
		$html[] = '<input type="text" name="' . $this->name . "[email]" . '" id="' . $this->id . '_email' . '"' .
			' style="display:block; margin:8px 0;"' .
			' placeholder ="' . JText::_("COM_FOXCONTACT_EMAIL_ADDRESS") . '"' .
			' value="' . htmlspecialchars(empty($this->value["email"]) ? "" : $this->value["email"], ENT_COMPAT, 'UTF-8') . '"' .
			'/>';
		$html[] = "</fieldset>";

		return implode($html);
	}

}
