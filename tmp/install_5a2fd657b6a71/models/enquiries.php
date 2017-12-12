<?php defined("_JEXEC") or die(file_get_contents("index.html"));
/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

class FoxContactModelEnquiries extends JModelList
{
	/**
	 * Constructor
	 *
	 * @param   array $config An optional associative array of configuration settings.
	 */
	public function __construct($config = array())
	{
		if (empty($config["filter_fields"]))
		{
			$config["filter_fields"] = array(
				"id", "a.id",
				"date", "a.date",
				"exported", "a.exported"
			);
		}

		parent::__construct($config);
	}


	/**
	 * Build the appropriate SQL query to load the items
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				"list.select",
				"a.id AS id," .
				"a.form_id AS form_id," .
				"a.date AS date," .
				"a.exported AS exported," .
				"a.ip AS ip," .
				"a.url AS url," .
				"a.fields fields"
			)
		);
		$query->from($db->quoteName("#__foxcontact_enquiries") . " AS a");

		// Filter by search in title
		$search = $this->getState("filter.search");
		if (!empty($search))
		{
			$query->where("a.fields LIKE " . $db->quote("%" . $db->escape($search, true) . "%"));
		}

		// Filter by exported state
		$exported = (int)$this->getState("filter.exported");
		// With checkbox disabled (0) loads unexported records only (0). With checkbox enabled (1) loads in addition exported records (1)
		$query->where("a.exported <= " . $exported);

		$initial_date = $this->getState("filter.initial_date");
		if (!empty($initial_date))
		{
			$query->where("a.date >= " . $db->quote($initial_date));
		}

		$final_date = $this->getState("filter.final_date");
		if (!empty($final_date))
		{
			$query->where("a.date <= " . $db->quote($final_date));
		}

		$forms = $this->getState("filter.forms");
		// In case of empty selection, don't apply this filter
		if (!empty($forms))
		{
			// Quote all the elements of the array before the implosion
			$forms = implode(",", $db->quote($forms));
			$query->where("form_id IN (" . $forms . ")");
		}

		// Add the list ordering clause.
		$order = $this->state->get("list.fullordering", "a.date DESC");
		$query->order($db->escape($order));

		return $query;
	}


	/**
	 * Method to get an array of data items. Taken from JModelList::getItems()
	 * @return  mixed  An array of data items on success, false on failure.
	 */
	public function getItems()
	{
		// Load the list items
		$query = $this->_getListQuery();

		$items = $this->_getList($query, $this->getStart(), (int)$this->getState("list.limit"));

		// Complete with additional data

		// Form description lookup data
		$db = JFactory::getDbo();

		$query->clear();
		$query->select("id as value, title as text");
		$query->from("#__menu");
		$query->where("link LIKE " . $db->quote("%option=com_foxcontact&view=foxcontact%"));

		$db->setQuery($query);
		$components = $db->loadAssocList("value");

		$query->clear();
		// Use negatives modules id to distinghush from menu items in filter dropdown
		$query->select("-id as value, title as text");
		$query->from("#__modules");
		$query->where("module = " . $db->quote("mod_foxcontact"));

		$db->setQuery($query);
		$modules = $db->loadAssocList("value");

		// Merge the arrays
		$forms = $components + $modules;

		foreach ($items as &$item)
		{
			// Extract sender data from the form fields
			$item->sender_data = array();
			$fields = json_decode($item->fields);
			foreach ($fields as $field)
			{
				if ($field[0] == "sender")
				{
					$item->sender_data[] = $field[2];
				}
			}

			// exported css class
			$item->class = $item->exported ? " exported" : "";

			// Form description
			if (isset($forms[$item->form_id]))
			{
				$item->form = $forms[$item->form_id]["text"];
			}
			else
			{
				$item->form = JText::_("JLIB_UNKNOWN");
			}
		}

		return $items;
	}
}
