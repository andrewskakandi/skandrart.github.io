<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

require_once "fdispatcher.php";

class FJMessenger extends FDispatcher
{
	public function Process()
	{
		$uid = $this->Params->get("jmessenger_user", NULL);
		// No user selected for Joomla messenger
		if (!$uid)
		{
			//JLog::add("No recipient selected in Joomla Messenger dispatcher. Private message was not send.", JLog::INFO, get_class($this));
			// It's not a problem. Maybe it's even wanted. Return succesful.
			return true;
		}

		$body = $this->body();
		$body .= $this->attachments();
		$body .= PHP_EOL;
		// Info about url
		$body .= JFactory::getConfig()->get("sitename") . " - " . $this->CurrentURL() . PHP_EOL;
		// Info about client
		$body .= "Client: " . $this->ClientIPaddress() . " - " . $_SERVER['HTTP_USER_AGENT'] . PHP_EOL;

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->insert($db->quoteName("#__messages"));
		$query->set($db->quoteName("user_id_from") . "=" . $db->quote($uid));
		$query->set($db->quoteName("user_id_to") . "=" . $db->quote($uid));
		$query->set($db->quoteName("date_time") . "=" . $db->quote(JFactory::getDate()->toSql()));
		$query->set($db->quoteName("subject") . "=" . $db->quote($this->submittername() . " (" . $this->submitteraddress() . ")"));
		$query->set($db->quoteName("message") . "=" . $db->quote(JMailHelper::cleanBody($body)));

		$db->setQuery((string)$query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Show a generic database error
			$this->MessageBoard->Add(JText::_("COM_FOXCONTACT_ERR_SENDING_MESSAGE"), FoxMessageBoard::error);
			// Log the details which may contain sensitive data
			$this->Logger->Write($e->getMessage());

			// Database problems. Return error.
			return false;
		}

		// Log the successful event to the database. Intentionally not in the user's language.
		$this->Logger->Write("Private message sent to Joomla messenger.");
		//JLog::add("Private message sent to Joomla messenger.", JLog::INFO, get_class($this));
		return true;

	}


	protected function attachments()
	{
		$result = "";
		// this message is for the webmaster
		if (count($this->FileList)) $result .= JText::_($GLOBALS["COM_NAME"] . "_ATTACHMENTS") . PHP_EOL;
		foreach ($this->FileList as &$file)
		{
			$filename = 'components/' . $GLOBALS["com_name"] . '/uploads/' . $file["filename"];
			$result .= JUri::base() . $filename . PHP_EOL;
		}

		return $result;
	}

}
