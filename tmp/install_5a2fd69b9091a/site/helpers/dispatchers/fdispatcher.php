<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 * Contributions by Graeme Moffat
 */

$inc_dir = realpath(__DIR__ . "/..");
require_once($inc_dir . "/fdatapump.php");
require_once($inc_dir . "/flogger.php");
require_once($inc_dir . "/emailhelper.php");

// JMailHelper provides security functions
jimport("joomla.mail.helper");


// Todo: Dispatchers don't need FDataPump, except for FSubmitterMailer::Process() which uses SafeName() and GetId()
abstract class FDispatcher extends FDataPump
{
	protected $FieldsBuilder;
	protected $FileList;

	/**
	 * Parameters of the running component or module
	 *
	 * @var JRegistry
	 */
	//protected $Params;

	/**
	 * @var FoxMessageBoard
	 */
	//protected $MessageBoard;

	abstract public function Process();


	protected function LoadFields()
	{
	}


	public function __construct(&$params, FoxMessageBoard &$messageboard, &$fieldsbuilder)
	{
		//$this->Params = & $params;
		//$this->MessageBoard = & $messageboard;

		parent::__construct($params, $messageboard);

		/*
		JLog::addLogger(array(
		'text_file' => 'foxcontact.log.php',
		"text_entry_format" => "{DATE}\t{TIME}\t{PRIORITY}\t{CATEGORY}\t{MESSAGE}"
		));
		*/
		$this->FieldsBuilder = $fieldsbuilder;
		$this->Logger = new FLogger();

		// Read attachments file list from the session
		$this->FileList = $this->session->get("filelist", array(), $this->namespace);
	}


	protected function submittername()
	{
		// Uses the user sender name. If the field is disabled, uses Joomla admin name
		return
			isset($this->FieldsBuilder->Fields['sender0']) ?
				$this->FieldsBuilder->Fields['sender0']['Value'] :
				JFactory::getConfig()->get("fromname");
	}


	protected function submitteraddress()
	{
		// Bug: http://www.fox.ra.it/forum/3-bugs/2399-error-when-email-is-optional-and-field-is-left-empty.html
		// $from = isset($this->FieldsBuilder->Fields['sender1']['Value']) ? $this->FieldsBuilder->Fields['sender1']['Value'] : JFactory::getApplication()->getCfg("mailfrom");

		// If submitter address is present and not empty, we can use it
		// otherwise system global address will be used
		$addr =
			isset($this->FieldsBuilder->Fields['sender1']['Value']) &&
			!empty($this->FieldsBuilder->Fields['sender1']['Value']) ?
				$this->FieldsBuilder->Fields['sender1']['Value'] :
				JFactory::getConfig()->get("mailfrom");

		return JMailHelper::cleanAddress($addr);
	}


	protected function body()
	{
		$result = "";
		foreach ($this->FieldsBuilder->Fields as $key => $field)
		{
			switch ($field['Type'])
			{
				case 'sender':
				case 'text':
				case 'textarea':
				case 'dropdown':
				case 'checkbox':
				case 'calendar':
					$result .= $this->AddToBody($field);
				// default:
				// do nothing;
			}
		}

		// a blank line
		$result .= PHP_EOL;
		return $result;
	}


	protected function AddToBody(&$field)
	{
		if (!$field['Display']) return "";
		//return $field["Name"] . ": " . $field["Value"] . PHP_EOL;
		return "*" . JFilterInput::getInstance()->clean($field["Name"], "") . "*" . PHP_EOL . JFilterInput::getInstance()->clean($field["Value"], "") . PHP_EOL . PHP_EOL;
	}


	protected function CurrentURL()
	{
		$url = 'http';
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") $url .= "s";
		$url .= "://";
		$url .= $_SERVER["SERVER_NAME"];
		if ($_SERVER["SERVER_PORT"] != "80") $url .= ":" . $_SERVER["SERVER_PORT"];
		$url .= $_SERVER["REQUEST_URI"];
		return $url;
	}


	protected function ClientIPaddress()
	{
		if (isset($_SERVER["REMOTE_ADDR"])) return $_SERVER["REMOTE_ADDR"];
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) return $_SERVER["HTTP_X_FORWARDED_FOR"];
		if (isset($_SERVER["HTTP_CLIENT_IP"])) return $_SERVER["HTTP_CLIENT_IP"];
		return "?";
	}


	/**
	 * Send the email
	 *
	 * @param JMail $mail Joomla mailer
	 * @return bool true on success
	 */
	protected function send(&$mail)
	{
		if (($error = $mail->Send()) !== true)
		{
			//$info = empty($mail->ErrorInfo) ? $error->getMessage() : $mail->ErrorInfo;
			// Obtaining the problem information from Joomla mailer is a nightmare
			if (is_object($error))
			{
				// It is an instance of JError. Calls the getMessage() method
				$info = $error->getMessage();
			}
			else if (!empty($mail->ErrorInfo))
			{
				// Send() returned false. If a $mail->ErrorInfo property is set, this is the cause
				$info = $mail->ErrorInfo;
			}
			else
			{
				// Send() returned false, but $mail->ErrorInfo is empty. The only reasonable cause can be $mailonline = 0
				$info = JText::_("JLIB_MAIL_FUNCTION_OFFLINE");
			}

			$msg = JText::_($GLOBALS["COM_NAME"] . "_ERR_SENDING_MAIL") . ". " . $info;
			$this->MessageBoard->Add($msg, FoxMessageBoard::error);
			$this->Logger->Write($msg);
			//JLog::add($msg, JLog::ERROR, get_class($this));
			return false;
		}

		//JLog::add("Email sent.", JLog::INFO, get_class($this));
		return true;
	}


	/**
	 * Substitute keywords in email subject
	 *
	 * @return string Email subject
	 */
	protected function subject()
	{
		// Read the subject from the form configuration
		$subject = JMailHelper::cleanSubject($this->Params->get("email_subject"));

		// Call the replacement engine for each variable found
		$expanded = preg_replace_callback('/{(.*?)}/', array("FDispatcher", "expand_variables"), $subject);

		return $expanded;
	}


	/**
	 * Variable replacement engine
	 *
	 * Keywords which are delimited by brackets [] are not case-sensitive,
	 * and are replaced with either form fields or Joomla properties
	 *
	 * @param $matches
	 * @return string
	 */
	protected function expand_variables($matches)
	{
		// We can't expect that the user respect the upper and lower case in variable names
		$keyword = strtolower($matches[1]);
		// Ignore & remove if unknown or unwanted
		$replace = "";

		switch ($keyword)
		{
			case "user" : // Registered user name
				$replace = JFactory::getUser()->get("username") or $replace = JText::_("COM_FOXCONTACT_NOT_LOGGED_IN");
				break;

			case "name" : // Submitter's supplied name
				$replace = "'" . $this->submittername() . "'";
				break;

			case "email" : // Submitter's email
				$replace = $this->submitteraddress();
				break;

			case "date" : // Local time of server
				$replace = JHtml::date('now', 'Y-m-d', false);
				break;

			case "time" :
				$replace = JHtml::date('now', 'H:i', false);
				break;

			case "date-utc" : // UTC times
				$replace = JHtml::date('now', 'Y-m-d', 'UTC');
				break;

			case "time-utc" :
				$replace = JHtml::date('now', 'H:i', 'UTC') . ' UTC';
				break;

			default : // Not a property. Is it a field name?
				if (isset($this->FieldsBuilder->Fields[$keyword]))
				{
					$replace = $this->FieldsBuilder->Fields[$keyword]["Value"];
				}
		}

		return $replace;
	}
}
