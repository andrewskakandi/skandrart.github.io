<?php defined("_JEXEC") or die(file_get_contents("index.html"));


/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */
class FoxEmailHelper
{
	/** @var Joomla\Registry\Registry $Params */
	protected $Params;


	public function __construct(&$params)
	{
		$this->Params = $params;
	}


	public function convert(stdClass $data)
	{
		return $this->{$data->select}($data);
	}


	public function submitter( /*$data*/)
	{
		$application = JFactory::getApplication();
		$input = $application->input;

		$name = $input->post->get("_" . md5($this->Params->get("sender0") . $application->cid . $application->mid), "", "string");
		$address = $input->post->get("_" . md5($this->Params->get("sender1") . $application->cid . $application->mid), "", "string");

		return array($address, $name);
	}


	public function admin( /*$data*/)
	{
		/** @var Joomla\Registry\Registry $config */
		$config = JFactory::getConfig();
		$name = $config->get("fromname");
		$address = $config->get("mailfrom");
		return array($address, $name);
	}


	public function custom(stdClass $data)
	{
		return array($data->email, $data->name);
	}
}


