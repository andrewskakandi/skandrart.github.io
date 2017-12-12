<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Thanks to: Lorenzo Milesi (YetOpen S.r.l. maxxer@yetopen.it http://www.yetopen.it/) for his great contribution
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

$inc_dir = realpath(dirname(__FILE__));
require_once($inc_dir . '/fnewsletter.php');

class FJNewsSubscriber extends FNewsletter
{
	public function __construct(&$params, FoxMessageBoard &$messageboard, &$fieldsbuilder)
	{
		parent::__construct($params, $messageboard, $fieldsbuilder);
		$this->Name = "FJNews";
		$this->prefix = "jnews";
	}


	public function Process()
	{
		// Newsletter component disabled or not found. Aborting.
		if (!$this->enabled) return true;

		$config = new jNews_Config();

		// Build subscriber object
		$subscriber = new stdClass;

		// Lists
		$cumulative = $this->JInput->post->get("jnews_subscribe_cumulative", NULL, "int");
		$checkboxes = $this->JInput->post->get("jnews_subscribe", array(), "array");
		$subscriber->list_id = $cumulative ? $checkboxes : array();

		// No lists selected. Skip here to avoid annoying the user with email confirmation. It is useless to confirm a subscription to no lists.
		if (empty($subscriber->list_id)) return true;

		// Name field may be absent. JNews will assign an empty name to the user.
		$subscriber->name = isset($this->FieldsBuilder->Fields['sender0']) ? $this->FieldsBuilder->Fields['sender0']['Value'] : "";

		$subscriber->email = empty($this->FieldsBuilder->Fields['sender1']['Value']) ? NULL : JMailHelper::cleanAddress($this->FieldsBuilder->Fields['sender1']['Value']);
		// JNews saves users with empty email address, so we have to check it
		if (empty($subscriber->email))
		{
			$this->logger->Write(get_class($this) . " Process(): Email address empty. User save aborted.");
			return true;
		}

		// It seems that $subscriber->confirmed defaults to unconfirmed if unset, so we need to read and pass the actual value from the configuration
		$subscriber->confirmed = !(bool)$config->get('require_confirmation');

		$subscriber->receive_html = 1;
		// Avoid Notice: Undefined property while JNews libraries access undefined properties
		$subscriber->ip = jNews_Subscribers::getIP();
		$subscriber->subscribe_date = jnews::getNow();
		$subscriber->language_iso = "eng";
		$subscriber->timezone = "00:00:00";
		$subscriber->blacklist = 0;
		$subscriber->user_id = JFactory::getUser()->id;

		// Subscription
		$sub_id = null;
		jNews_Subscribers::saveSubscriber($subscriber, $sub_id, true);

		if (empty($sub_id))
		{
			// User save failed. Probably email address is empty or invalid
			$this->logger->Write(get_class($this) . " Process(): User save failed");
			return true;
		}

		// Subscribe $subscriber to $subscriber->list_id
		//$subscriber->id = $sub_id;

		// jNews_ListsSubs::saveToListSubscribers() doesn't work well. When only one list is passed to, it reads the value $listids[0],
		// but the element 0 is not always the first element of the array. In our case is $listids[1]
		//jNews_ListsSubs::saveToListSubscribers($subscriber);
		$this->SaveSubscription($subscriber);

		// Log
		$this->logger->Write(get_class($this) . " Process(): subscribed "
		. $this->FieldsBuilder->Fields['sender0']['Value'] . " (". $this->FieldsBuilder->Fields ['sender1']['Value']
		. ") to lists " . implode(",", $subscriber->list_id));

		return true;
	}


	protected function load_newsletter_config()
	{
		if (!(bool)$this->Params->get("jnews")) return $this->enabled = false;

		// Load JNews classes
		defined("JNEWS_JPATH_ROOT") or define("JNEWS_JPATH_ROOT", JPATH_ROOT);

		$mainAdminPathDefined = JPATH_ROOT . '/components/com_jnews/defines.php';
		$this->enabled = (bool)@include_once($mainAdminPathDefined);
		$jnews_include = JNEWS_JPATH_ROOT . '/administrator/components/' . JNEWS_OPTION . '/classes/class.jnews.php';
		$this->enabled &= (bool)@include_once($jnews_include);

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
		if (!$this->extension_exists("jnews")) return;

		// Get the lists selected to be shown. Defaults to a null array
		$lists = $this->Params->get("jnews_lists", array("NULL"));

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select($db->quoteName("id") . "," . $db->quoteName("hidden") . " as " . $db->quoteName("visible") . "," . $db->quoteName("list_name") . " as " . $db->quoteName("name"));  // JNews "hidden" means "visible"

		$query->from($db->quoteName("#__jnews_lists"));

		// Condition: Published
		$query->where($db->quoteName("published") . "=" . $db->quote("1"));
		// Do not use Visible as condition, so that invisible lists are hidden but usable

		// Condition: List selected to be shown
		$query->where($db->quoteName("id") . " IN (" . implode(',', $lists) .")");

		$db->setQuery($query);

		// Get the definitive lists to be shown. Defaults to an empty array
		$this->lists = $db->loadAssocList() or $this->lists = array();
	}


	function SaveSubscription($subscriber)
	{
		if (empty($subscriber->list_id) || empty($subscriber->id)) return false;

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		foreach ($subscriber->list_id as $listid)
		{
			$query->clear();
			$query->update($db->quoteName("#__jnews_listssubscribers"));

			$query->set($db->quoteName("subdate") . "=" . time());
			$query->set($db->quoteName("unsubdate") . "= 0");
			$query->set($db->quoteName("unsubscribe") . "= 0");
			$query->where($db->quoteName("list_id") . "=" . (int)$listid);
			$query->where($db->quoteName("subscriber_id") . "=" . (int)$subscriber->id);
			$db->setQuery($query);
			try
			{
				$result = $db->execute();
				$affected = $db->getAffectedRows();
				switch ($affected)
				{
					case -1:
						// Yes, JDatabase::getErrorMsg() is deprecated (use exception handling instead)
						// but if JDatabase::execute() raised an exception we weren't here :)
						JFactory::getApplication()->enqueueMessage($db->getErrorMsg(true), "error");
						break;

					case 0:
						// No records updated. Need to be inserted.
						$query->clear();
						$query->insert($db->quoteName("#__jnews_listssubscribers"));

						$query->set($db->quoteName("list_id") . "=" . (int)$listid);
						$query->set($db->quoteName("subscriber_id") . "=" . (int)$subscriber->id);
						$query->set($db->quoteName("subdate") . "=" . time());
						$query->set($db->quoteName("unsubdate") . "= 0");
						$query->set($db->quoteName("unsubscribe") . "= 0");
						$db->setQuery($query);
						try
						{
							$result = $db->execute();
							if (!$result)
							{
								// Yes, JDatabase::getErrorMsg() is deprecated (use exception handling instead)
								// but if JDatabase::execute() raised an exception we weren't here :)
								JFactory::getApplication()->enqueueMessage($db->getErrorMsg(true), "error");
							}
						}
						catch (RuntimeException $e)
						{
							JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
						}

					break;

					//default:
					// One (or more) records updated. Do nothing.
				}
			}
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage($e->getMessage(), "error");
			}

		}

		return true;
	}

}

