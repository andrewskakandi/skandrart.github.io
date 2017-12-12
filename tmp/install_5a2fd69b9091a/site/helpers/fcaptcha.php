<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

$inc_dir = realpath(dirname(__FILE__));
require_once($inc_dir . '/fdatapump.php');
include_once(realpath(dirname(__FILE__) . "/../" . substr(basename(realpath(dirname(__FILE__) . "/..")), 4) . ".inc"));

class FCaptcha extends FDataPump
{
	public function __construct(&$params, FoxMessageBoard &$messageboard)
	{
		parent::__construct($params, $messageboard);

		$this->Name = "FCaptcha";

		// Read captcha value submitted
		$this->Fields['Value'] = $this->FaultTolerance(JRequest::getVar("fcaptcha", NULL, 'POST'));
		// Read from the session
		$this->Fields['Secret'] = $this->FaultTolerance($this->session->get("captcha_answer", "", $this->namespace));

		// Check if the answer if correct
		$this->isvalid = intval($this->Validate());
	}


	protected function LoadFields()
	{
	}


	protected function LoadField($type, $number) // Example: 'text', '0'
	{
	}


	function OverrideFields()
	{
	}


	function OverrideField($type, $number)
	{
	}


	public function Show()
	{
		if (!(bool)$this->Params->get("stdcaptchadisplay")) return "";
		$captcha_width = (int)$this->Params->get("stdcaptchawidth", "");
		$captcha_height = (int)$this->Params->get("stdcaptchaheight", "");

		$valid = (!empty($this->Fields['Secret']) && $this->Fields['Value'] == $this->Fields['Secret']);

		// FDataPump::CreateStandardLabel() creates external labels based on the field["Name"] property, while
		// captcha field has no name, but standard description instead: params->get("stdcaptcha", "")
		// So create a fake "Name" property based on the captcha description
		$this->Fields["Name"] = $this->Params->get("stdcaptcha", "");

		// CreateStandardLabel() may use $field['Display'], and it needs to be a valid value
		$this->Fields["Display"] = 2;
		$this->CreateStandardLabel($this->Fields);

		$result =
			'<div class="control-group' . $this->TextStyleByValidation() . '"';
		if ($valid) $result .= ' style="display:none !important;"';
		$result .= '>' . PHP_EOL .
			$this->LabelHtmlCode .

			'<div ' .
			'class="controls" ' .
			'>' . PHP_EOL;

		if (!$valid)
		{
			$src = JRoute::_('index.php?option=' . $GLOBALS["com_name"] .
						"&view=loader" .
						"&owner=" . $this->Application->owner .
						"&id=" . $this->Application->oid .
						"&root=none" .
						"&filename=none" .
			'&type=captcha');

			// Adds a unique id used by the refresh javascript
			if (strpos($src, "?") === false)
			{
				$src .= "?";
			}
			else
			{
				$src .= "&";
			}
			$src .= "uniqueid=00000000";

			$result .=
				// Captcha image
				'<div class="fcaptchafieldcontainer">' .
					'<img src="' . $src .
					'" ' .
					'class="fox_captcha_img" ' .
					'alt="captcha" ' . // w3c validation
					'id="fcaptcha_' . $this->GetId() . '" width="' . $captcha_width . '" height="' . $captcha_height . '"/>' .
					'</div>'; // fcaptchafieldcontainer
		}

		$result .=
			// Input for answer
			'<div class="fcaptchainputcontainer">' .

				$this->DescriptionByValidation() . // Example: *

				'<input ' .
				//'class="' . $this->TextStyleByValidation() . '" ' .
				'type="text" ' .
				'name="' . "fcaptcha" . '" ' .
				'style="width:' . ($captcha_width - 40) . 'px !important;" ' .
				'value="' . $this->Fields['Value'] . '" ' .
				// captcha field has no "Name" property, but standard description instead
				'title="' . $this->Params->get("stdcaptcha", "") . '" ' .
				$this->placeholder;

		if ($valid)
		{
			$result .=
				/*'value="' . $this->Fields['Value'] . '" ' .*/
				'readonly="readonly" ';
		}

		$result .=
			'/>' .
				'</div>'; // fcaptchainputcontainer

		if (!$valid)
		{
			$result .=

				// Reload button
				'<div class="fcaptcha-reload-container">' .
					// Show a transparent dummy image
					'<img src="' . JUri::base(true) . '/media/' . $GLOBALS["com_name"] . '/images/transparent.gif" ' .
					'id="reloadbtn_' . $this->GetId() . '" ' .
					'alt="' . JTEXT::_($GLOBALS["COM_NAME"] . '_RELOAD_ALT') . '" ' .
					'title="' . JTEXT::_($GLOBALS["COM_NAME"] . '_RELOAD_TITLE') . '" ' .
					'width="16" height="16" ' .
					"onclick=\"javascript:ReloadFCaptcha('fcaptcha_" . $this->GetId() . "')\" />" .
					'</div>' . // fcaptchafieldcontainer
					// Without javascript enable, you will not be able to click reload button, so let's show it only if javascript is enabled
					"<script language=\"javascript\" type=\"text/javascript\">BuildReloadButton('reloadbtn_" . $this->GetId() . "');</script>";
		}

		$result .=
			'</div>' . // fcaptchacontainer
				'</div>' . // Row div
				PHP_EOL;

		if (!$this->isvalid)
		{
			$this->MessageBoard->Add(JText::sprintf($GLOBALS["COM_NAME"] . '_ERR_INVALID_VALUE', JText::_($GLOBALS["COM_NAME"] . '_SECURITY_CODE')), FoxMessageBoard::error);
		}
		return $result;
	}


	private function build_label(&$field)
	{
		// Label
		return '<label ' .
			'class="control-label"' .
			//'style="' .
			//'width:' . $this->Params->get('labelswidth') . $this->Params->get('labelsunit') . ' !important;"' .
			'>' .
			// Unlike other fields, captcha can have an empty description
			// "&nbsp;" default value avoids a misaligned visualization
			$this->Params->get("stdcaptcha", "&nbsp;") .
			'</label>' . PHP_EOL;
	}


	// Check a single field and return a string good for html output
	protected function TextStyleByValidation()
	{
		// No post data = first time here. return a grey border
		//if (!$this->Submitted) return "foxtext";
		if (!$this->Submitted) return "";
		// Return a green or red border
		//return $field['IsValid'] ? "validfoxtext" : "invalidfoxtext";
		return $this->isvalid ? " success" : " error";
	}


	// Check a single field and return a boolean value
	function Validate()
	{
		$isrequired = (bool)$this->Params->get("stdcaptchadisplay");

		// Value == Secret == NULL is not a valid condition
		$this->isvalid = (!empty($this->Fields['Secret']) && $this->Fields['Value'] == $this->Fields['Secret']);
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
		// $this->isvalid now stores the state of the uploaded file only...
		return !($this->Submitted && $isrequired && !$this->isvalid);
		// ..but after returning it will consider the submitted and required state too
	}


	private function DescriptionByValidation()
	{
		return $this->isvalid ? "" : (" <span class=\"asterisk\"></span>");
	}


	private function FaultTolerance($string)
	{
		// Same content as the label
		if ($string == $this->Params->get("stdcaptcha", "")) return $string;

		// Convert in lower case
		$string = strtolower($string);
		// correct common mistakes
		$string = preg_replace("/[l1]/", "i", $string); // I i l 1 -> i
		$string = preg_replace("/[0]/", "o", $string); // O o 0 -> o
		$string = preg_replace("/[q9]/", "g", $string); // g q 9 -> g
		$string = preg_replace("/[5]/", "s", $string); // S s 5 -> s
		$string = preg_replace("/[8]/", "b", $string); // B 8 -> b

		return $string;
	}

}


class fcaptchaCheckEnvironment
{
	protected $InstallLog;


	public function __construct()
	{
		$this->InstallLog = new FLogger("fcaptchaimage", "install");
		$this->InstallLog->Write("--- Determining if this system is able to draw captcha images ---");

		switch (true)
		{
			case $this->gd_usable():
				$value = "use_gd";
				break;
			// No way to draw images
			default:
				$value = "disabled";
		}

		$db = JFactory::getDBO();
		$sql = "REPLACE INTO #__" . $GLOBALS["ext_name"] . "_settings (name, value) VALUES ('captchadrawer', '$value');";
		$db->setQuery($sql);
		$result = $db->query();

		$this->InstallLog->Write("--- Method choosen to draw captcha images is [$value] ---");
		return $result;
	}


	private function gd_usable()
	{
		if (!extension_loaded("gd") || !function_exists("gd_info"))
		{
			$this->InstallLog->Write("gd extension not found");
			return false;
		}

		$this->InstallLog->Write("gd extension found. Let's see if it works.");

		$gdinfo = gd_info();
		foreach ($gdinfo as $key => $line) $this->InstallLog->Write($key . "... [" . $line . "]");

		$result = true;
		$result &= $this->testfunction("imagecreate");
		$result &= $this->testfunction("imagecolorallocate");
		$result &= $this->testfunction("imagefill");
		$result &= $this->testfunction("imageline");
		$result &= $this->testfunction("imagettftext");
		$result &= $this->testfunction("imagejpeg");
		$result &= $this->testfunction("imagedestroy");

		return $result;
	}


	private function testfunction($function)
	{
		$result = function_exists($function);
		$this->InstallLog->Write("testing function [$function]... [" . intval($result) . "]");
		return $result;
	}

}
