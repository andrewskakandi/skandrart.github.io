<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

$inc_dir = realpath(dirname(__FILE__));
require_once($inc_dir . '/fdatapump.php');
require_once(JPATH_SITE . "/components/com_foxcontact/lib/functions.php");


class FAjaxUploader extends FDataPump
{
	public function __construct(&$params, FoxMessageBoard &$messageboard)
	{
		parent::__construct($params, $messageboard);

		$this->Name = "FAjaxFilePump";
		$this->isvalid = intval($this->Validate());
	}


	protected function LoadFields()
	{
		// Nothing to load for the moment
	}


	// Build a multiple upload field
	public function Show()
	{
		if (!(bool)$this->Params->get("uploaddisplay")) return "";

		// Load resources only once
		static $resources = true;
		if ($resources)
		{
			// We need to load the resources
			$document = JFactory::getDocument();
			$document->addScript(JUri::base(true) . "/media/com_foxcontact/js/upload.min.js");

			// Prevent further load of the same resources
			$resources = false;
		}

		$id = $this->GetId();

		JFactory::getDocument()->addScriptDeclaration(
				"jQuery(document).ready(function () {" .
					"CreateUploadButton('foxupload_$id'," .
					"'" . $this->Application->owner . "'," .
					$this->Application->oid . "," .
					"'" . JRoute::_("index.php?option=com_foxcontact&view=loader&root=none&filename=none&type=uploader&owner=" . $this->Application->owner . "&id=" . $this->Application->oid) . "');" .
				"});" . PHP_EOL
		);

		$label = "";
		$span = "";
		// Label beside: generates a label
		if ((bool)$this->Params->get("labelsdisplay"))
		{
			$label =
				'<label class="control-label">' .
					$this->Params->get('upload') .
					'</label>';
		}
		// Label inside: generates a little span vertical aligned to the button
		else
		{
			$span =
				'<span class="help-block">' .
					$this->Params->get('upload') .
					'</span>';
		}

		$result =
			// Open row container
			'<div class="control-group">' .
				$label .

				'<div class="controls">' .
				$span .
				// Upload button and list container
				'<div id="foxupload_' . $id . '"></div>' . // foxupload
			   '<span class="help-block">' . JText::_($GLOBALS["COM_NAME"] . '_FILE_SIZE_LIMIT') . " " . HumanReadable($this->Params->get("uploadmax_file_size") * 1024) . '</span>' .
				// Javascript support missing
				'<span><noscript>' . JText::_("COM_FOXCONTACT_JAVASCRIPT_REQUIRED") . '</noscript></span>' .
				'</div>' . PHP_EOL . // controls
				"</div>" . PHP_EOL; // control-group

		$filelist = $this->session->get("filelist", array(), $this->namespace);

		// List of files
		$result .= '<div class="control-group">' .
			'<div class="controls">';

			// Previously completed uploads
		$result .= '<ul id="uploadlist-' . $this->Application->owner . $this->Application->oid . '" class="qq-upload-list">';
		foreach ($filelist as $index => $file)
			{
				$result .=
					'<li class="qq-upload-success">' .
				'<span class="qq-upload-file">' . $this->format_filename($file["realname"]) . '</span>' .
				'<span class="qq-upload-size">' . HumanReadable($file["size"]) . '</span>' .
						'<span class="qq-upload-success-text">' . JTEXT::_($GLOBALS["COM_NAME"] . '_SUCCESS') . '</span>' .
				'<span class="qq-upload-remove" title="' . JTEXT::_("COM_FOXCONTACT_REMOVE_TITLE") . '" onclick="deletefile(this,' . $index . ',\'' . JRoute::_("index.php?option=com_foxcontact&view=loader&root=none&filename=none&type=uploader&owner=" . $this->Application->owner . "&id=" . $this->Application->oid) . '\')">' . JTEXT::_("COM_FOXCONTACT_REMOVE_ALT") . '</span>' .
						'</li>';
			}
			$result .= '</ul>' . PHP_EOL;

		$result .= '</div>' . // controls
			'</div>' . PHP_EOL; // control-group

		if (!$this->isvalid)
		{
			$this->MessageBoard->Add(JText::sprintf("COM_FOXCONTACT_ERR_MIN_UPLOAD_FILES_NOT_REACHED", $this->Params->get("upload_min_files", 0)), FoxMessageBoard::error);
		}

		return $result;
	}


	protected function format_filename($value)
	{
		if (strlen($value) > 33)
		{
			// make safe for utf8 file names
			if (function_exists("mb_substr")) $substr = "mb_substr";
			// fallback to standard string function
			else $substr = "substr";

			$value = $substr($value, 0, 19) . '...' . $substr($value, -13);
		}
		return $value;
	}


	protected function Validate()
	{
		// Get by the configuration the minimum number of files allowed
		$min_files = $this->Params->get("upload_min_files", 0);

		// This field is required if minimum number of uploads is greater than 0
		$isrequired = (bool)$min_files;

		$filelist = $this->session->get("filelist", array(), $this->namespace);

		// To be valid, it requires that the uploaded files are equal or greater than the minimum
		$this->isvalid = count($filelist) >= $min_files;

		return !($this->Submitted && $isrequired && !$this->isvalid);
	}

}
