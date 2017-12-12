<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

$inc_dir = realpath(dirname(__FILE__));
require_once($inc_dir . '/fnewsletter.php');

class FAcyMailing extends FNewsletter
{
	const subscribe = 1;
	const unsubscribe = -1;

	public function __construct(&$params, FoxMessageBoard &$messageboard, &$fieldsbuilder)
	{
		parent::__construct($params, $messageboard, $fieldsbuilder);
		$this->Name = "FAcyMailing";
		$this->prefix = "acymailing";
	}


	public function Process()
	{
		// Newsletter component disabled or not found. Aborting.
		if (!$this->enabled) return true;

		//$config = acymailing_config();

		// Lists
		$cumulative = $this->JInput->post->get("acymailing_subscribe_cumulative", NULL, "int");

		$checkboxes = array(FAcyMailing::subscribe => $this->JInput->post->get("acymailing_subscribe", array(), "array"));
		$lists = $cumulative ? $checkboxes : array();

		// When subscription requires confirmation (double opt-in) AcyMailing sends a confirmation request to the user as soon as the user himself is saved. $userClass->save($subscriber)
		// Even in case of no list selected the user will be annoyed with a confirmation email
		// The confirmation status doesn't depend on the lists, which will be passed to AcyMailing only a few lines later. $userClass->saveSubscription($sub_id, $newSubscription)
		if (empty($lists[FAcyMailing::subscribe])) return true;

		// Build subscriber object
		$subscriber = new stdClass;

		// Name field may be absent. AcyMailing will guess the user's name from his email address
		$subscriber->name = isset($this->FieldsBuilder->Fields['sender0']) ? $this->FieldsBuilder->Fields['sender0']['Value'] : "";

		// AcyMailing refuses to save the user (return false) if the email address is empty, so we don't care to check it
		$subscriber->email = empty($this->FieldsBuilder->Fields['sender1']['Value']) ? NULL : JMailHelper::cleanAddress($this->FieldsBuilder->Fields['sender1']['Value']);

		$userClass = acymailing_get('class.subscriber');
		$userClass->checkVisitor = false;

		// Add or update the user
		$sub_id = $userClass->save($subscriber);

		if (empty($sub_id))
		{
			// User save failed. Probably email address is empty or invalid
			$this->logger->Write(get_class($this) . " Process(): User save failed");
			return true;
		}

		// When in mode "one checkbox for each list" and no lists selected the code above produce an SQL error because passes an empty array to saveSubscription()
		$newSubscription = array();
		foreach($lists[FAcyMailing::subscribe] as $listId)
		{
			$newList = array();
			$newList['status'] = FAcyMailing::subscribe;
			$newSubscription[$listId] = $newList;
		}
		if (!empty($newSubscription))
		{
			$userClass->saveSubscription($sub_id, $newSubscription);
		}

		// implode() doesn't accept NULL values :(
		@$lists[FAcyMailing::subscribe] or $lists[FAcyMailing::subscribe] = array();

		// Log
		$this->logger->Write(get_class($this) . " Process(): subscribed "
		. $this->FieldsBuilder->Fields['sender0']['Value'] . " (". $this->FieldsBuilder->Fields ['sender1']['Value']
		. ") to lists " . implode(",", $lists[FAcyMailing::subscribe]));

		return true;
	}


	protected function load_newsletter_config()
	{
		if (!(bool)$this->Params->get("acymailing")) return $this->enabled = false;

		$include = JPATH_ADMINISTRATOR . '/components/com_acymailing/helpers/helper.php';
		$this->enabled = (bool)@include_once($include);

		$found = $this->enabled ? " " : " not ";
		$this->logger->Write(get_class($this) . " Newsletter component" . $found . "found");
	}


	protected function load_newsletter_lists()
	{
		// Prepare at least an empty array
		$this->lists = array();

		// Is this newsletter integration enabled in the parameters?
		if (!$this->enabled) return;

		// Ensure that the table exists, otherwise an sql error will be raised: #1146 - Table 'db.prefix_newsletter' doesn't exist
		if (!$this->extension_exists("acymailing")) return;

		// Get the lists selected to be shown. Defaults to a null array
		$lists = $this->Params->get("acymailing_lists", array("NULL"));

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select($db->quoteName("listid") . " as " . $db->quoteName("id") . "," . $db->quoteName("visible") . "," . $db->quoteName("name"));

		$query->from($db->quoteName("#__acymailing_list"));

		// Condition: Published
		$query->where($db->quoteName("published") . "=" . $db->quote("1"));
		// Do not use Visible as condition, so that invisible lists are hidden but usable

		// Condition: List selected to be shown
		$query->where($db->quoteName("listid") . " IN (" . implode(',', $lists) .")");

		// Condition: current language or "all" languages
		$query->where("(" . $db->quoteName("languages") . " LIKE " . $db->quote("%" . JFactory::getLanguage()->getTag() . "%") . " OR " . $db->quoteName("languages") . " LIKE " . $db->quote("%all%") . ")");

		// (Suggested by ADRIEN) Condition: only standard lists
		$query->where($db->quoteName("type") . "=" . $db->quote("list"));

		$query->order($db->quoteName("ordering"));

		$db->setQuery($query);

		// Get the definitive lists to be shown. Defaults to an empty array
		$this->lists = $db->loadAssocList() or $this->lists = array();
	}
}
