<?php defined("_JEXEC") or die(file_get_contents("index.html"));

/**
 * @package Fox Contact for Joomla
 * @copyright Copyright (c) 2010 - 2014 Demis Palma. All rights reserved.
 * @license Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html
 * @see Documentation: http://www.fox.ra.it/forum/2-documentation.html
 */

function getFoxContactParameters()
{
	static $result = array(
		0 => "view",
		1 => "owner",
		2 => "id",
		3 => "root",
		4 => "filename",
		5 => "type"
	);

	return $result;
}

function FoxContactBuildRoute(&$query)
{
	$segments = array();
	$parameters = getFoxContactParameters();

	foreach ($parameters as $name)
	{
		if (isset($query[$name]))
		{
			$segments[] = $query[$name];
			unset($query[$name]);
		}
		else
		{
			// At the first missing parameter, we can't continue.
			// ParseRoute relies on a specific order of the parameters
			break;
		}
	}

	return $segments;
}

function FoxContactParseRoute($segments)
{
	$vars = array();
	$parameters = getFoxContactParameters();

	foreach ($parameters as $index => $name)
	{
		if (isset($segments[$index]))
		{
			// $segments[$index] should be safe, but we don't trust the user input
			// Ony allows alphanumeric character and underscores. Forbid all the other stuff.
			$vars[$name] = preg_replace('/[^A-Z0-9_]/i', "", $segments[$index]);
		}
		else
		{
			// At the first missing parameter, we can't continue.
			// BuildRoute respect a specific order of the parameters.
			break;
		}
	}

	return $vars;
}
