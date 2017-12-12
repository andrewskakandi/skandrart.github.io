<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

jimport("joomla.application.component.modellist");
// include required files
JLoader::register("FoxContactModelEnquiries", JPATH_COMPONENT . "/models/enquiries.php");

class FoxContactModelExport extends FoxContactModelEnquiries
{
	/**
	 * Method to get an array of data items. Taken from JModelList::getItems()
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// Filter and order the export using the same values that have been set in the enquiries manager list

		// Set the same context as enquiries list, otherwise it would be "com_foxcontact.export"
		$this->context = "com_foxcontact.enquiries";
		// Load filter and order values
		$this->populateState();

		// Load the list items
		$query = $this->_getListQuery();

		$this->_db->setQuery($query);
		$items = $this->_db->loadAssocList();

		foreach ($items as &$item)
		{
			$fields = json_decode($item["fields"]);

			foreach ($fields as $field)
			{
				// $field[0]: field name
				// $field[1]: field description
				// $field[2]: field value
				// While collecting the values of the field, removes newlines
				$item[$field[1]] = str_replace(array("\r", "\n"), " ", $field[2]);
			}

			// Remove useless properties
			unset($item["exported"]);
			unset($item["fields"]);
		}

		return $items;
	}


	/**
	 * Mark an array of items as exported
	 *
	 * @param array $items Items exported
	 * @return null
	 */
	public function mark($items)
	{
		// Collect the item ids as a separate array
		$ids = array();
		foreach ($items as $item)
		{
			$ids[] = $item["id"];
		}

		// Ensure that the query has a valid syntax at "where id IN ( ... )"
		if (!empty($ids))
		{
			$query = $this->_db->getQuery(true);
			$query->update("#__foxcontact_enquiries");
			$query->set("exported = 1");
			$query->where("id IN (" . implode(",", $ids) . ")");
			$this->_db->setQuery($query);
			$this->_db->execute();
		}
	}

}