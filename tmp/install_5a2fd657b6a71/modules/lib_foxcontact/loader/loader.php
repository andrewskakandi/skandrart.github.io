<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

abstract class Loader
{
	public $headers = array();

	abstract protected function type();
	abstract protected function content_header();
	abstract protected function content_footer();


	public function Show()
	{
		$this->headers();
		$this->content_header();
		$this->load();
		$this->content_footer();

		//die();
		JFactory::getApplication()->close();
	}


	private function headers()
	{
		// Rattle off all the headers set at different levels
		foreach ($this->headers as $header)
		{
			header($header);
		}
	}


	protected function load()
	{
		// Prepares the unique id value ($uid) to be used by the script
		$input = JFactory::getApplication()->input;
		$owner = $input->get("owner", "component");
		$id = $input->get("id", "0");
		// Variables usable by the resource files
		// Unique id.
		$uid = "_" . $owner[0] . $id;
		// $left and $right directions
		$language = JFactory::getLanguage();
		$direction = intval($language->get('rtl', 0));
		$left = $direction ? "right" : "left";
		$right = $direction ? "left" : "right";
		$juri_root = JURI::root(true);

		// Read the file name of the resource
		$filename = $input->get("filename", "");
		// accept letters, numbers, dashes, underscores
		preg_match('/^[A-Z0-9-_]+$/i', $filename) or $filename = "invalid";

		// Complete the script name with its own path
		$filename = $this->IncludePath . "/" . $this->type() . "/" . $filename . "." . $this->type();

		// Avoid warnings which may cause information disclousure
		if (is_file($filename) && is_readable($filename))
		{
			require_once $filename;
		}
		else
		{
			echo JText::_("JERROR_LAYOUT_REQUESTED_RESOURCE_WAS_NOT_FOUND");
		}
	}

}


abstract class CachableLoader extends Loader
{
	public function __construct()
	{
		// Headers which generates cachable resources. It works, but it needs 'ExpiresActive On' in .htaccess
		$this->headers[] = "Cache-Control: max-age=604800, public";
		$this->headers[] = "Connection: keep-alive, Keep-Alive";
		$this->headers[] = "Date: " . gmdate("D, d M Y H:i:s") . " GMT";
		$this->headers[] = "Expires: " . gmdate("D, d M Y H:i:s", time() + 604800) . " GMT"; // 604800 = 7 days
		// ETag resists to a forced reload, such as F5 key. It should be calculated based on the file modification timestamp
		// but we need to parse the header of the http request, which could contain 'If-None-Match: \"123456\-gzip"'
		// and reply 'header('HTTP/1.1 304 Not Modified');' if the file has not been modified in the meantime.
		// In addition, when gzip compression is enabled, the suffix '\-gzip' is added to the ETag,
		// When parsing back the ETag from the client, we should take in consideration this possible suffix
		// http://stackoverflow.com/questions/1971721/how-to-use-http-cache-headers-with-php
		//$this->headers[] = 'ETag: \"$unique_hash\"';
	}

}


abstract class UncachableLoader extends Loader
{
	public function __construct()
	{
		// http://stackoverflow.com/questions/9884513/avoid-caching-of-the-http-responses
		// Must not be cached neither by client browsers or proxies
		$this->headers[] = "Expires: " . gmdate("D, d M Y H:i:s") . " GMT";
		$this->headers[] = "Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT";
		// no-cache: cacheable, but mustn't use the response without first checking with the originating server
		// private: is intended for a single user and MUST NOT be cached by a shared cache
		// must-revalidate: MUST NOT use the entry after it becomes stale without first revalidating it with the origin server. In all circumstances an HTTP/1.1 cache MUST obey the must-revalidate directive; in particular, if the cache cannot reach the origin server for any reason, it MUST generate a 504 (Gateway Timeout) response.
		// max-age=0: The content is stale and should be validated before use.
		$this->headers[] = "Cache-Control: no-cache, private, must-revalidate, max-age=0";
		$this->headers[] = "Pragma: no-cache";
	}
}