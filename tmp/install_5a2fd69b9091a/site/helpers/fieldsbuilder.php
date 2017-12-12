<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

$inc_dir = realpath(dirname(__FILE__));
require_once($inc_dir . '/fdatapump.php');
require_once($inc_dir . '/flanghandler.php');
require_once($inc_dir . '/conflicting.php');
require_once($inc_dir . '/flogger.php');

class FieldsBuilder extends FDataPump
{
	public function __construct(&$params, FoxMessageBoard &$messageboard)
	{
		parent::__construct($params, $messageboard);

		$this->ValidateEmail(); // email can have text without being valid

		// Load resources only once
		static $resources = true;
		if ($resources)
		{
			// The easy way to include chosen. It taunts the page source with tons of embedded javascripts.
			// JHtml::_("jquery.framework"); // jquery
			// JHtml::_('behavior.framework', true); // mootools
			// JHtml::_('formbehavior.chosen', 'select'); // chosen

			// We need jQuery to be loaded before our scripts
			JHtml::_("jquery.framework");

			$foxDocument = FoxDocument::getInstance();
			$foxDocument->addResource(array("root" => "components", "filename" => "foxtext", "type" => "js"));

			// Prevent further load of the same resources
			$resources = false;
		}

		$this->isvalid = intval($this->ValidateForm()); // Are all fields valid?

		$lang_handler = new FLangHandler();
		if ($lang_handler->HasMessages())
		{
			$messageboard->Append($lang_handler->GetMessages(), FoxMessageBoard::warning);
		}

		$conflict = new FoxConflicting();
		if ($conflict->HasMessages())
		{
			$messageboard->Append($conflict->GetMessages(), FoxMessageBoard::warning);
		}

	}


	public function Show()
	{
		$result = "";
		uasort($this->Fields, "sort_fields");

		foreach ($this->Fields as $key => $field)
		{
			switch ($field['Type'])
			{
				case 'customhtml':
					$result .= $this->BuildCustomHtmlField($key, $field);
					break;
				case 'sender':
				case 'text':
					$result .= $this->BuildTextField($key, $field); //Example: $this->BuildTextField('sender0', $field)
					break;
				case 'dropdown':
					$result .= $this->BuildDropdownField($key, $field); //Example: $this->BuildTextField('dropdown0', $field)
					break;
				case 'textarea':
					$result .= $this->BuildTextareaField($key, $field); //Example: $this->BuildTextField('textarea0', $field)
					break;
				case 'checkbox':
					$result .= $this->BuildCheckboxField($key, $field); //Example: $this->BuildTextField('checkbox0', $field)
					break;
				case 'calendar':
					$result .= $this->BuildCalendarField($key, $field);
					break;
			}

			if (!$field["IsValid"]) $this->MessageBoard->Add(JText::sprintf($GLOBALS["COM_NAME"] . '_ERR_INVALID_VALUE', $field["Name"]), FoxMessageBoard::error);
		}

		return $result;
	}


	protected function LoadFields()
	{
		// Load all the component | module parameters
		$all_params = $this->Params->toArray();

		foreach($all_params as $name => $value)
		{
			// Try to detect the parameters which actually correspond to fields
			if (array_key_exists($name . "order", $all_params))
			{
				// Extract type and number from the name. Example: text0 -> text | 0
				list($type, $number) = preg_split('/([a-z]+)/i', $name, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

				// Load the other field properties and the POST value
				$this->LoadField($type, $number);
			}
		}
	}


	protected function LoadField($type, $number) // Example: 'text', '0'
	{
		// Load component parameters
		$name = $type . (string)$number; // Example: 'text0'
		// If not to be displayed, it's useless to continue reading other values
		if (!parent::LoadField($type, $name)) return false;
		// Load data
		$this->Fields[$name]['Value'] = $this->JInput->post->get($this->Fields[$name]['PostName'], NULL, "string");

		// Additional manipulations
		if ($this->Fields[$name]['Value'] == $this->Fields[$name]['Name']) // Example: Field='Your name' Value='Your name'
		{
			// Seems like a submission from the module without filling the field, so let's invalidate the value!
			$this->Fields[$name]['Value'] = "";
		}

		// Validation after *all* fields are loaded and manipulated
		$this->Fields[$name]['IsValid'] = intval($this->ValidateField($this->Fields[$name]['Value'], $this->Fields[$name]['Display']));

		// Checkboxes need to be manipulated after validation, otherwise a JNO value will be considered valid
		// Checkboxes have only JYES or empty values. Translate empty to JNO
		if ($type == "checkbox" && $this->Fields[$name]['Value'] == "") $this->Fields[$name]['Value'] = JText::_('JNO');

		return true;
	}


	private function BuildCustomHtmlField($key, &$field)
	{
		// When the field has an empty text to display, do not insert the container neither
		if (empty($field['Name'])) return "";

		$result = '<div ' .
			'id="' . $this->GetId() . "-" . $key . '" ' .
			'class="control-group" ' .
			'>' . PHP_EOL .
			'<div class="controls">' .
			'<div>' . PHP_EOL .
			$field['Name'] . PHP_EOL .
			"</div>" .
			"</div>" .
			"</div>" . PHP_EOL .
			PHP_EOL;

		return $result;
	}


	// Build a single Text field
	private function BuildTextField($key, &$field)
	{
		$this->CreateStandardLabel($field);

		$result = '<div ' .
			'id="' . $this->GetId() . "-" . $key . '" ' .
			'class="control-group' . $this->TextStyleByValidation($field) . '" ' .
			'>' . PHP_EOL .
			$this->LabelHtmlCode . PHP_EOL .
			'<div class="controls">' .
			'<input ' .
			'type="text" ' .
			'value="' . $field['Value'] . '" ' .
			'title="' . $field['Name'] . '" ' .
			$this->placeholder .
			'name="' . $field['PostName'] . '" ' .
			'/>' .
			$this->DescriptionByValidation($field) . // Example: *
			'</div>' . PHP_EOL . // controls
			'</div>' . PHP_EOL . // control-group
			PHP_EOL;

		return $result;
	}

	private function BuildCalendarField($key, &$field)
	{
		// Load resources only once
		static $resources = true;
		if ($resources)
		{
			// We need to load the resources
			$document = JFactory::getDocument();
			$document->addScript(JUri::base(true) . "/media/com_foxcontact/js/calendar.min.js");
			$document->addStyleSheet(JUri::base(true) . "/media/com_foxcontact/css/calendar.css");

			// Prevent further load of the same resources
			$resources = false;
		}

		$mode = (int)$field["Mode"];
		$have_date = (bool)($mode & 2);
		$have_time = (bool)($mode & 1);
		$have_both = $have_date && $have_time;
		$format = ($have_date ? JText::_("DATE_FORMAT_LC") : "") . ($have_time ? " H:i" : "");

		$this->CreateStandardLabel($field);

		$result = '<div ' .
			'id="' . $this->GetId() . "-" . $key . '" ' .
			'class="control-group' . $this->TextStyleByValidation($field) . '" ' .
			'>' . PHP_EOL .

			$this->LabelHtmlCode . PHP_EOL .

			'<div class="controls">' .
			'<input ' .
			'type="text" ' .
			'class="foxcalendar" ' .
			'value="' . $field['Value'] . '" ' .
			'title="' . $field['Name'] . '" ' .
			$this->placeholder .
			'name="' . $field['PostName'] . '" ' .

			'data-datepicker="' . ($have_date ? "true" : "false") . '" ' .
			'data-timepicker="' . ($have_time ? "true" : "false") . '" ' .
			'data-close-on-date-select="' . (!$have_both ? "true" : "false") . '" ' .
			'data-format="' . $format . '" ' .

			'readonly="" ' .
			'/>' .

			$this->DescriptionByValidation($field) . // Example: *

			'</div>' . PHP_EOL . // controls
			'</div>' . PHP_EOL . // control-group
			PHP_EOL;

		return $result;
	}

	// Build a single Dropdown box field
	private function BuildDropdownField($key, &$field)
	{
		// Load resources only once
		static $resources = true;
		if ($resources)
		{
			// We need to load the resources

			/** @var Joomla\Registry\Registry $config */
			$config = JFactory::getConfig();

			// chosen.jquery requires core for Joomla.JText
			$uncompressed = $config->get("debug") ? "-uncompressed" : "";
			$min = $config->get("debug") ? "" : ".min";

			$document = JFactory::getDocument();
			$document->addScript(JUri::base(true) . "/media/system/js/core" . $uncompressed . ".js");
			$document->addScript(JUri::base(true) . "/media/jui/js/chosen.jquery" . $min . ".js");
			$document->addScript(JUri::base(true) . "/media/com_foxcontact/js/chosen.min.js");
			$document->addStyleSheet(JUri::root(true) . "/media/jui/css/chosen.css");

			// Prevent further load of the same resources
			$resources = false;
		}

		$this->CreateStandardLabel($field);

		$placeholder = $this->Params->get("labelsdisplay") ? " " : $field['Name'];
		$result = '<div ' .
			'id="' . $this->GetId() . "-" . $key . '" ' .
			'class="control-group' . $this->TextStyleByValidation($field) . '" ' .
			'>' . PHP_EOL .
			$this->LabelHtmlCode . PHP_EOL .
			'<div class="controls">' .
			'<select ' .
			'class="fox_select" ' .
			'data-placeholder="' . $placeholder . '" ' .
			'name="' . $field['PostName'] . '" ' .
			'>' . PHP_EOL;

		// Insert an empty option
		$result .= '<option value=""></option>';

		// and the actual options
		$options = explode(",", $field['Values']);
		foreach ($options as $option)
		{
			$result .= "<option value=\"" . $option . "\"";
			if ($field['Value'] === $option && !empty($option))
			{
				$result .= " selected ";
			}
			$result .= ">" . $option . "</option>";
		}
		$result .= PHP_EOL . "</select>" .
			$this->DescriptionByValidation($field) .
			'</div>' . PHP_EOL . // controls
			"</div>" . PHP_EOL . // control-group
			PHP_EOL;

		return $result;
	}


	// Build a single Check Box field
	private function BuildCheckboxField($key, &$field)
	{
		// Here, validation will be successful, because there aren't post data, but it isn't a good right to activate che checkbox with the check
		// if (intval($this->FieldsBuilder->Fields[$index]['Value'])) $this->msg .= "checked=\"\"";
		if ($field['Value'] == JText::_('JYES')) $checked = 'checked="" ';
		else $checked = "";

		$this->CreateSpacerLabel();

		$result = '<div ' .
			'id="' . $this->GetId() . "-" . $key . '" ' .
			'class="control-group' . $this->TextStyleByValidation($field) . '" ' .
			'>' . PHP_EOL .
			$this->LabelHtmlCode . PHP_EOL .
			'<div class="controls">' .
			'<label class="checkbox">' .
			'<input ' .
			'type="checkbox" ' .
			"value=\"" . JText::_('JYES') . "\" " .
			$checked .
			'name="' . $field['PostName'] . '" ' .
			'id="c' . $field['PostName'] . '" ' .
			'/>' .
			$this->AdditionalDescription($field['Display']) . // Asterisk
			$field['Name'] .
			$this->DescriptionByValidation($field) . // Nested span with validation red asterisk
			'</label>' .
			'</div>' . PHP_EOL .
			'</div>' . PHP_EOL .
			PHP_EOL;

		return $result;
	}


	// Build a Textarea field
	private function BuildTextareaField($key, &$field)
	{
		$this->CreateStandardLabel($field);

		$result = '<div ' .
			'id="' . $this->GetId() . "-" . $key . '" ' .
			'class="control-group' . $this->TextStyleByValidation($field) . '" ' .
			'>' . PHP_EOL .
			$this->LabelHtmlCode . PHP_EOL .
			'<div class="controls">' .
			"<textarea " .
			'rows="10" ' .
			'cols="30" ' .
			'name="' . $field['PostName'] . '" ' .
			'title="' . $field['Name'] . '" ' .
			$this->placeholder .
			">" .
			$field['Value'] . // Inner Text
			"</textarea>" .
			$this->DescriptionByValidation($field) .
			'</div>' . PHP_EOL . // controls
			'</div>' . PHP_EOL . // control-group
			PHP_EOL;

		return $result;
	}


	// Check a single field and return a string good for html output
	function DescriptionByValidation(&$field)
	{
		return $field['IsValid'] ? "" : (" <span class=\"asterisk\"></span>");
	}


	// Check a single field and return a string good for html output
	function CheckboxStyleByValidation(&$field)
	{
		if (!$this->Submitted) return "foxcheckbox";
		// Return a green or red border
		return $field['IsValid'] ? "validcheckbox" : "invalidcheckbox";
	}


	// Check a single field and return a string good for html output
	protected function TextStyleByValidation(&$field)
	{
		// No post data = first time here. return a grey border
		if (!$this->Submitted) return "";
		// Return a green or red border
		return $field['IsValid'] ? " success" : " error";
	}


	function ValidateForm()
	{
		$result = true;

		// Validate default fields
		$result &= $this->ValidateGroup("sender");
		// Validate Text fields
		$result &= $this->ValidateGroup("text");
		// Validate Dropdown fields
		$result &= $this->ValidateGroup("dropdown");
		// Validate Check Boxes
		$result &= $this->ValidateGroup("checkbox");
		// Validate text areas
		$result &= $this->ValidateGroup("textarea");
		// Validate calendars
		$result &= $this->ValidateGroup("calendar");

		return $result;
	}


	// $family can be 'text', 'dropdown', 'textarea' or 'checkbox'
	function ValidateGroup($family)
	{
		$result = true;

		for ($l = 0; $l < 10; ++$l)
		{
			if (isset($this->Fields[$family . $l]) && $this->Fields[$family . $l]['Display'])
			{
				$result &= $this->Fields[$family . $l]['IsValid'];
			}
		}

		return $result;
	}


	// Check a single field and return a boolean value
	function ValidateField($fieldvalue, $fieldtype)
	{
		// Params:
		// $fieldvalue is a string with the text filled by user
		// $fieldtype can be 0 = unused, 1 = optional, 2 = required
		// S | R | F | V   (Submitted | Required | Filled | Valid)
		// 0 | 0 | 0 | 1
		// 0 | 0 | 1 | 1
		// 0 | 1 | 0 | 1
		// 0 | 1 | 1 | 1
		// 1 | 0 | 0 | 1
		// 1 | 0 | 1 | 1
		// 1 | 1 | 0 | 0
		// 1 | 1 | 1 | 1
		return !($this->Submitted && ($fieldtype == 2) && empty($fieldvalue));
	}


	function ValidateEmail()
	{
		// data aren't destinated to this form
		if (!isset($_POST[$this->GetId()])) return;

		// email field is disabled
		if (!isset($this->Fields['sender1'])) return;

		// email field is empty and optional
		if (empty($this->Fields['sender1']['Value']) && $this->Fields['sender1']['Display'] == 1) return;

		if (!isset($this->Fields['sender1']['Value'])) return;

		// Some nowadays servers doesn't have the function filter_var(). We need to use a regular expression.
		// $this->Fields['sender1']['IsValid'] &= (bool)strlen(filter_var($this->Fields['sender1']['Value'], FILTER_VALIDATE_EMAIL));

		// Check the email syntax (http://www.regular-expressions.info/email.html)
		$this->Fields['sender1']['IsValid'] &= (preg_match('/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/', strtolower($this->Fields['sender1']['Value'])) == 1);

		// Check mx record
		$config = JComponentHelper::getParams("com_foxcontact");
		$mode = $config->get("use_dns", "disabled");
		if (method_exists("FieldsBuilder", $mode))
		{
			$this->$mode();
		}
	}


	function dns_check()
	{
		// Check mx record
		if (empty($this->Fields['sender1']['Value'])) return;

		$parts = explode("@", $this->Fields['sender1']['Value']);
		$domain = array_pop($parts);
		if (!empty($domain))
			$this->Fields['sender1']['IsValid'] &= checkdnsrr($domain, "MX");
	}
}


function sort_fields($a, $b)
{
	return $a["Order"] - $b["Order"];
}


class fieldsbuilderCheckEnvironment
{
	protected $InstallLog;


	public function __construct()
	{
		$this->InstallLog = new FLogger("fieldsbuilder", "install");

		// Load the global configuration
		$params = JComponentHelper::getParams("com_foxcontact")->toObject();

		// Test environment and update the configuration
		$this->test_addresses($params);
		$this->test_dns($params);

		// Save the configuration
		// Improve the performance by referencing the record using the index "element + client_id"
		// UPDATE #__extensions SET params = json_encode($params) WHERE element = com_foxcontact AND client_id = 1
		$table = JTable::getInstance("extension");
		$table->load(array("element" => "com_foxcontact", "client_id" => 1));
		$table->bind(array("params" => json_encode($params)));
		$result = $table->check() && $table->store();

		return $result;
	}


	private function test_dns($params)
	{
		$this->InstallLog->Write("--- Determining if this system is able to query DNS records ---");

		if (!function_exists("checkdnsrr"))
		{
			$this->InstallLog->Write("checkdnsrr function doesn't exist.");
			$params->use_dns = "0";
			return;
		}
		$this->InstallLog->Write("checkdnsrr function found. Let's see if it works.");

		// Check mx record
		$record_found = checkdnsrr("fox.ra.it", "MX");
		$this->InstallLog->Write("testing function [checkdnsrr]... [" . intval($record_found) . "]");
		$result = $record_found ? "dns_check" : "0";
		$this->InstallLog->Write("--- Method choosen to query DNS records is [$result] ---");

		$params->use_dns = $result;
	}


	private function test_addresses(&$params)
	{
		// Todo: Attention, this test runs on every update

		/** @var Joomla\Registry\Registry $config */
		$config = JFactory::getConfig();
		// SMTP authentication may require that the sender address is the same that the auth username
		if ($config->get("mailer") == "smtp" && (bool)$config->get("smtpauth") && strpos($config->get("smtpuser"), "@") !== false)
		{
			isset($params->adminemailfrom) or $params->adminemailfrom = new stdClass();
			$params->adminemailfrom->select = "custom";
			$params->adminemailfrom->name = $config->get("fromname");
			$params->adminemailfrom->email = $config->get("smtpuser");

			isset($params->submitteremailfrom) or $params->submitteremailfrom = new stdClass();
			$params->submitteremailfrom->select = "custom";
			$params->submitteremailfrom->name = $config->get("fromname");
			$params->submitteremailfrom->email = $config->get("smtpuser");
		}
	}

}
