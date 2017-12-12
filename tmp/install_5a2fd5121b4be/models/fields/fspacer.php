<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

jimport('joomla.form.formfield');

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldFSpacer extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'FSpacer';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		return ' ';
	}

	/**
	 * Method to get the field label markup.
	 *
	 * @return	string	The field label markup.
	 * @since	1.6
	 */
	protected function getLabel()
	{
		$html = array();
		$class = $this->element['class'] ? (string) $this->element['class'] : '';

		$html[] = '<span class="spacer">';
		$html[] = '<span class="before"></span>';
		$html[] = '<span class="'.$class.'">';
		if ((string) $this->element['hr'] == 'true') {
			$html[] = '<hr class="'.$class.'" />';
		}
		else {
			$label = '';
			// Get the label text from the XML element, defaulting to the element name.
			$text = $this->element['label'] ? (string) $this->element['label'] : (string) $this->element['name'];
			$text = $this->translateLabel ? JText::_($text) : $text;

			// Build the class for the label.
			$class = !empty($this->description) ? 'hasTip' : '';
			$class = $this->required == true ? $class.' required' : $class;

			$url = (string)$this->element['url'];
			if (!empty($url))
				{
				$label .= '<a target="_blank" href="' . $this->element['url'] . '">';
				}

			// Add the opening label tag and main attributes attributes.
			$label .= '<span class="'.$class.'"';

			// If a description is specified, use it to build a tooltip.
			if (!empty($this->description)) {
				$label .= ' title="'.htmlspecialchars(trim($text, ':').'::' .
							($this->translateDescription ? JText::_($this->description) : $this->description), ENT_COMPAT, 'UTF-8').'"';
			}

			// Add the label text and closing tag.
			$label .= '>'.$text.'</span>';

			if (!empty($url)) $label .= '</a>';

			$html[] = $label;
		}
		$html[] = '</span>';
		$html[] = '<span class="after"></span>';
		$html[] = '</span>';
		return implode('',$html);
	}
	/**
	 * Method to get the field title.
	 *
	 * @return	string	The field title.
	 * @since	1.6
	 */
	protected function getTitle()
	{
		return $this->getLabel();
	}
}
