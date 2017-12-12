<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 * Contributions by Graeme Moffat
 */

$inc_dir = realpath(dirname(__FILE__));
require_once($inc_dir . '/fdispatcher.php');


class FAdminMailer extends FDispatcher
{
	public function Process()
	{
		$mail = JFactory::getMailer();

		$this->set_from($mail);
		$this->set_to($mail, "to_address", "addRecipient");
		$this->set_to($mail, "cc_address", "addCC");
		$this->set_to($mail, "bcc_address", "addBCC");

		$mail->setSubject($this->subject());

		$body = $this->body();
		$body .= $this->attachments($mail);
		$body .= PHP_EOL;

		// Info about url
		$body .= JFactory::getConfig()->get("sitename") . " - " . $this->CurrentURL() . PHP_EOL;

		// Info about client
		$body .= "Client: " . $this->ClientIPaddress() . " - " . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;

		$body = JMailHelper::cleanBody($body);
		$mail->setBody($body);

		$sent = $this->send($mail);
		if ($sent)
		{
			// Notify email send success
			$this->MessageBoard->Add($this->Params->get("email_sent_text"), FoxMessageBoard::success);
			$this->Logger->Write("Notification email sent.");
		}

		return $sent;
	}


	/**
	 * @param JMail $mail
	 */
	private function set_from(&$mail)
	{
		$emailhelper = new FoxEmailHelper($this->Params);

		/** @var Joomla\Registry\Registry $config */
		$config = JComponentHelper::getParams("com_foxcontact");

		// Set a default value
		$default = (object)array(
			"select" => "submitter",
			"email" => "",
			"name" => ""
		);

		$adminemailfrom = $config->get("adminemailfrom", $default);
		$from = $emailhelper->convert($adminemailfrom);

		$mail->setSender($from);

		$adminemailreplyto = $config->get("adminemailreplyto", $default);
		$replyto = $emailhelper->convert($adminemailreplyto);

		// In Joomla 1.7 From and Reply-to fields is set by default to the Global admin email
		// but a call to setSender() won't change the Reply-to field
		$mail->ClearReplyTos();
		// addReplyTo() function expects an array(address, name) in Joomla 2, and two strings (address, name) in Joomla 3
		$mail->addReplyTo($replyto[0], $replyto[1]);
	}


	// $param_name | $method
	// ------------+-------------
	// to_address  | addRecipient
	// cc_address  | addCC
	// bcc_address | addBCC
	private function set_to(&$mail, $param_name, $method)
	{
		if ($this->Params->get($param_name, null))
			$recipients = explode(",", $this->Params->get($param_name, ""));
		else
			$recipients = array();

		// http://docs.joomla.org/How_to_send_email_from_components
		foreach ($recipients as $recipient)
		{
			// Avoid to call $mail->add..() with an empty string, since explode(",", $string) returns al least 1 item, even if $string is empty
			if (empty($recipient)) continue;
			$mail->$method($recipient);
		}
	}


	protected function attachments(&$mail)
	{
		$result = "";
		// this email is for the webmaster
		$uploadmethod = intval($this->Params->get("uploadmethod", "1")); // How the webmaster wants to receive attachments

		if (count($this->FileList) && ($uploadmethod & 1))
		{
			$result .= JText::_($GLOBALS["COM_NAME"] . "_ATTACHMENTS") . PHP_EOL;
		}

		foreach ($this->FileList as $file)
		{
			// binary 01: http link, binary 10: attach, binary 11: both
			$filename = 'components/' . $GLOBALS["com_name"] . '/uploads/' . $file["filename"];
			if ($uploadmethod & 1) $result .= JUri::base() . $filename . PHP_EOL;
			if ($uploadmethod & 2) $mail->addAttachment(JPATH_SITE . "/" . $filename);
		}

		return $result;
	}

}
