<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

require_once "fdispatcher.php";

class DatabaseDispatcher extends FDispatcher
{
	public function Process()
	{
		// Negative values indicate that the id belongs to a module
		$prefix = (JFactory::getApplication()->owner == "module") ? "-" : "";
		$oid = $prefix . JFactory::getApplication()->oid;

		$body = $this->body();

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->insert($db->quoteName("#__foxcontact_enquiries"));
		$query->set($db->quoteName("form_id") . "=" . $db->quote($oid));
		$query->set($db->quoteName("date") . "=" . $db->quote(JFactory::getDate()->toSql()));
		$query->set($db->quoteName("ip") . "=" . $db->quote($this->ClientIPaddress()));
		$query->set($db->quoteName("url") . "=" . $db->quote($this->CurrentURL()));
		$query->set($db->quoteName("fields") . "=" . $db->quote($body));

		$db->setQuery((string)$query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			// Show a generic database error
			$this->MessageBoard->Add(JText::_("COM_FOXCONTACT_ERR_DATABASE"), FoxMessageBoard::error);
			// Log the details which may contain sensitive data
			$this->Logger->Write($e->getMessage());

			// Database problems. Return error.
			return false;
		}

		// Log the successful event to the database. Intentionally not in the user's language.
		$this->Logger->Write("Enquiry saved to the database.");
		return true;
	}


	protected function body()
	{
		$body = array();

		foreach ($this->FieldsBuilder->Fields as $field)
		{
			switch ($field['Type'])
			{
				case 'sender':
				case 'text':
				case 'textarea':
				case 'dropdown':
				case 'checkbox':
				case 'calendar':
					$body[] = array(
						$field["Type"],
						$field["Name"],
						$field["Value"]
					);
				// default:
				// do nothing;
			}
		}

		return json_encode($body);
	}
}
