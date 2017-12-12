<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

abstract class FDataPump
{
	/**
	 * Parameters of the running component or module
	 *
	 * @var Joomla\Registry\Registry
	 */
	public $Params;
	public $Application;
	public $Name;
	public $Fields = array();
	public $Style = array();
	protected $Submitted;

	/**
	 * @var FLogger
	 */
	protected $Logger;

	protected $isvalid;

	/**
	 * @var FoxMessageBoard
	 */
	protected $MessageBoard;
	protected $JInput;

	// Label contruction
	protected $LabelHtmlCode;
	protected $placeholder;

	// Session (captcha, file list, ...)
	protected $session;
	protected $namespace;

	abstract protected function LoadFields();


	public function __construct(&$params, FoxMessageBoard &$messageboard)
	{
		$this->Params = & $params;
		$this->MessageBoard = & $messageboard;
		$this->Application = JFactory::getApplication();
		$this->Submitted = (bool)count($_POST) && isset($_POST[$this->GetId()]);
		$this->JInput = JFactory::getApplication()->input;

		// Session
		$this->session = JFactory::getSession();

		// Namespace for session operations
		$this->namespace = "foxcontact_" . $this->Application->owner . "_" . $this->Application->oid;

		$this->LoadFields();
	}


	public function IsValid()
	{
		return $this->isvalid;
	}


	protected function LoadField($type, $name) // Example: 'text', 'text0'
	{
		$enabled = intval($this->Params->get($name . "display", "0"));
		// If not to be displayed, it's useless to continue reading other values
		if (!$enabled) return false;

		$this->Fields[$name]["Display"] = intval($this->Params->get($name . "display", "0"));
		$this->Fields[$name]["Type"] = $type;
		$this->Fields[$name]["Name"] = $this->Params->get($name, "");
		$this->Fields[$name]["PostName"] = $this->SafeName($this->Fields[$name]["Name"] . $this->Application->cid . $this->Application->mid);
		$this->Fields[$name]["Values"] = $this->Params->get($name . "values", "");
		$this->Fields[$name]["Width"] = (int)$this->Params->get($type . "width", "");
		$this->Fields[$name]["Height"] = (int)$this->Params->get($type . "height", "");
		$this->Fields[$name]["Unit"] = $this->Params->get($type . "unit", "");
		$this->Fields[$name]["Order"] = (int)$this->Params->get($name . "order", 0);
		$this->Fields[$name]["Mode"] = (int)$this->Params->get($name . "mode", 0);
		return true;
	}


	protected function MakeText($key)
	{
		$text = $this->Params->get($key, "");
		if (empty($text)) return "";
		return
			'<div class="foxmessage" style="clear:both;">' .
			$text .
			'</div>';
	}


	protected function AdditionalDescription($display)
	{
		return ($display == 2) ? ("<span class=\"required\"></span>") : "";
	}


	protected function SafeName($name)
	{
		// In $_POST[names], spaces are replaced with underscores. The reason is that PHP used to create a local variable
		// for each form value (now it's optional an deprecated) and you can't have a variable with spaces on its name.
		// Other characters than spaces, are not invalid. So, it's better replace all of them

		// In addition, a valid variable name starts with a letter or underscore, followed by any number of letters, numbers, or underscores.
		// As a regular expression, it would be expressed thus: '[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*
		// In case field name starts with a number, it's better to put an underscore before it
		// "/[^a-zA-Z0-9\s]/" this allows spaces
		// "/[^a-zA-Z0-9]/" this doesn't allow spaces
		// This code doesn't work for non latin charsets, because builds a name with only underscores
		//$name = "_" . preg_replace("/[^a-zA-Z0-9]/", "_", $name);
		// Truncate to 64 char
		//$name = substr($name, 0, 64);
		//return $name;
		return "_" . md5($name);
	}


	protected function GetComponentId()
	{
		global $app;
		if (strpos($app->scope, "com_") !== 0) return 0;

		$wholemenu = $this->Application->getMenu();
		$targetmenu = $wholemenu->getActive();
		return $targetmenu->id;
	}


	protected function GetId($separator = "_")
	{
		$id = substr($this->Application->scope, 0, 1); // can be "c" or "m"
		switch ($id)
		{
			case "c":
				$wholemenu = $this->Application->getMenu();
				$activemenu = $wholemenu->getActive();
				$id .= "id" . $separator . $activemenu->id;
				break;

			case "m":
				//$id .= "id_" . $module->id;
				$id .= "id" . $separator . $this->Application->mid;
				break;

			default:
				$id = "";
		}

		return $id;
	}


	protected function CreateStandardLabel($field)
	{
		// This is a standard label, such as in Text or Textarea fields
		// It doesn't depend on the layout, but it only depends on the "labelsdisplay" parameter
		// When labelsdisplay is "beside fields" the label has the field name
		// When labelsdisplay is "inside fields" the label isn't drawn at all

		if ((bool)$this->Params->get("labelsdisplay"))
		{
			// Labels beside
			$this->LabelHtmlCode = '<label class="control-label">' . $field["Name"] . $this->AdditionalDescription($field["Display"]) . '</label>';
			$this->placeholder = "";
		}
		else
		{
			// Labels inside
			$this->LabelHtmlCode = "";
			$this->placeholder = 'placeholder="' . $field['Name'] . '" ';
		}
	}


	protected function CreateSpacerLabel()
	{
		// This is a spacer label, such as in Checkbox fields or submit button
		// It depends on the "labelsdisplay" parameter, which need to be "beside fields" for this spacer to be drown,
		// but in addition it depends on the layout
		// When layout is compact or extended the label assumes a blank but valid value in order to align fields even if it isn't visible.
		//
		// Text |________|
		// .... [] Checbox
		//
		// When layout is stacked or inline, the label is useless and annoying.
		//
		// == Inline ==
		// Text |________| .... [] Checkbox
		//
		// == Stacked ==
		// Text
		// |________|
		// ....
		// [] Checkbox
		//
		// In no cases the label assumes the field name. It is only a spacer.

		$layout = $this->Params->get("form_layout", "extended");

		if ((bool)$this->Params->get("labelsdisplay") && ($layout == "compact" || $layout == "extended"))
		{
			$this->LabelHtmlCode = '<label class="control-label">&nbsp;</label>';
		}
		else
		{
			$this->LabelHtmlCode = "";
		}

	}

}
