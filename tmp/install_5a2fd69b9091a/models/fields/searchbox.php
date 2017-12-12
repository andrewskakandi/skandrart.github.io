<?php defined("_JEXEC") or die;

/**
 * Implements a text search box with a submit button beside it
 * @package Fox Contact for Joomla
 * @author Demis Palma
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class JFormFieldSearchBox extends JFormField
{
	/** @var string */
	protected $type = "SearchBox";


	/**
	 * Method to get the field input markup
	 * @return string The field input markup
	 */
	protected function getInput()
	{
		$html = '<div class="input-append">';

		// We have to read some additional attributes which our parent class ignores
		$maxlength = (string)$this->element["maxlength"];
		$placeholder = (string)$this->element["placeholder"];

		$attributes =

			// Standard attributes read by the parent class
			(!empty($this->class) ? 'class="' . $this->class . '" ' : '') .

			// Our own attributes
			(!empty($maxlength) ? 'maxlength="' . $maxlength . '" ' : '') .
			(!empty($placeholder) ? 'placeholder="' . JText::_($placeholder) . '" ' : '');

		$html .=
			'<input ' .

			// Fixed attributes
			'type="text" ' .

			// Standard attributes read by the parent class
			'name="' . $this->name . '" ' .
			'id="' . $this->id . '" ' .
			'value="' . htmlspecialchars($this->value, ENT_COMPAT, "UTF-8") . '" ' .

			$attributes .
			'/>';

		$html .= '<button type="submit" class="btn hasTooltip" title=""><i class="icon-search"></i></button></div>';

		return $html;
	}
}
