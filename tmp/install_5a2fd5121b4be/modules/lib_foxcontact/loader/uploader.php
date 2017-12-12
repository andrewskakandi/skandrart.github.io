<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

require_once JPATH_COMPONENT . "/helpers/flogger.php";
require_once JPATH_COMPONENT . "/helpers/fmimetype.php";
require_once "loader.php";

define("KB", 1024);
define("MB", 1048576);

// File deletion intentionally uses unlink() because the file has been uploaded by apache therefore JFile::delete() could fail

class uploaderLoader extends UncachableLoader
{
	protected function type()
	{
		return "uploader";
	}


	protected function content_header()
	{
	}


	protected function content_footer()
	{
	}


	protected function load()
	{
		switch (true)
		{
			case isset($_GET['qqfile']):
				$um = new XhrUploadManager();
				break;

			case isset($_FILES['qqfile']):
				$um = new FileFormUploadManager();
				break;

			case JFactory::getApplication()->input->get("action", 0) == "deletefile":
				$um = new XhrDeleteManager();
				break;

			default:
				// Malformed / malicious request, or attachment exceeds server limits
				$result = array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_NO_FILE'));
				exit(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
		}
		$um->Params = & $this->Params;
		$result = $um->HandleUpload(JPATH_COMPONENT . '/uploads/');
		// to pass data through iframe you will need to encode all html tags
		echo(htmlspecialchars(json_encode($result), ENT_NOQUOTES));
	}
}


abstract class FUploadManager
{
	protected $Log;
	protected $session;
	protected $namespace;


	abstract protected function save_file($path);


	abstract protected function get_file_name();


	abstract protected function get_file_size();


	function __construct()
	{
		// Log
		$this->Log = new FLogger();

		// Session
		$this->session = JFactory::getSession();

		// Namespace for session operations
		$owner = JFactory::getApplication()->input->get("owner", null);
		$id = JFactory::getApplication()->input->get("id", null);
		$this->namespace = "foxcontact_" . $owner . "_" . $id;
	}


	public function HandleUpload($uploadDirectory)
	{
		// Security issue: when upload is disabled, stop here
		if (!(bool)$this->Params->get("uploaddisplay", 0))
		{
			return array('error' => " [upload disabled]");
		}

		if (!is_writable($uploadDirectory))
		{
			return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_DIR_NOT_WRITABLE'));
		}

		// Check file size
		$size = $this->get_file_size();
		if ($size == 0) // It must be > 0
		{
			return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_FILE_EMPTY'));
		}

		// uploadmax_file_size defaults to 0 to prevent hack attempts
		$max = $this->Params->get("uploadmax_file_size", 0) * KB; // and < max limit
		if ($size > $max)
		{
			return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_FILE_TOO_LARGE'));
		}

		$realname = $this->get_file_name();

		// Keep the client name untouched, and start working on a server side name
		$filename = JFilterInput::getInstance()->clean($realname, "cmd");

		// Forbid Directory Traversal by removing residual directory
		$filename = basename($filename);

		// Forbid initial and final dots
		$realname = trim($this->get_file_name(), '.');

		// Block scripts based on their extension
		$forbidden_extensions = '/ph(p[345st]?|t|tml|ar)/i'; // php|php3|php4|php5|phps|phpt|pht|phtml|phar
		if (preg_match($forbidden_extensions, $filename))
		{
			$this->Log->Write("Blocked a file upload attempt [forbidden extension]: " . $filename);
			// dangerous file extension
			return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_MIME') . " [forbidden extension]");
		}

		// Assign a random unique id to the file name
		$filename = uniqid() . "-" . $filename;
		$full_filename = $uploadDirectory . $filename;

		if (!$this->save_file($full_filename))
		{
			return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_SAVE_FILE'));
		}

		$mimetype = new FMimeType();
		if (!$mimetype->Check($full_filename, $this->Params))
		{
			// Delete the file uploaded
			unlink($full_filename);
			return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_MIME') . " [" . $mimetype->Mimetype . "]");
		}

		// Read the current file list from the session
		$filelist = $this->session->get("filelist", array(), $this->namespace);

		// Block further uploads once exceeded the max limit
		if (count($filelist) >= $this->Params->get("upload_max_files", 10))
		{
			return array("error" => JText::_("COM_FOXCONTACT_ERR_MAX_UPLOAD_FILES_EXCEEDED"));
		}

		// Block scripts based on their content. To avoid memory limitations which may vary based on the server configuration, analyzes the file in chunks of a reasonable length.
		$chunk_size = 1048576; // 1Mb

		$back_step = -6; // Given by strlen('<script') - 1
		// Open the file
		$handle = fopen($full_filename, "rb");
		do
		{
			// Read a chunk
			$content = fread($handle, $chunk_size);
			// Take back the file pointer in case we are ended just in the middle of the string we have to find
			fseek($handle, $back_step, SEEK_CUR);
			// search for the string
			if (strpos($content, '<?php') !== false || strpos($content, '<script') !== false)
			{
				// contains php directive
				fclose($handle);
				unlink($full_filename);
				$this->Log->Write("Blocked a file upload attempt [forbidden content]: " . $filename);
				return array('error' => JFactory::getLanguage()->_($GLOBALS["COM_NAME"] . '_ERR_MIME') . " [forbidden content]");
			}
			// If we have read exactly it's highly probable that we have not reached the end of file
		} while (strlen($content) == $chunk_size);
		// Close the file
		fclose($handle);

		// Append this file to the file list
		$filelist[] = array(
			"filename" => $filename,
			"realname" => $realname,
			"size" => $size
		);

		// Save the new list to the session
		$this->session->set("filelist", $filelist, $this->namespace);

		// move the internal pointer to the end of the array
		end($filelist);
		// fetches the key of the last element
		$last = key($filelist);

		$this->Log->Write("File " . $filename . " uploaded succesful.");
		return array("success" => true, "index" => $last);
	}
}


// File uploads via XMLHttpRequest
class XhrUploadManager extends FUploadManager
{

	public function __construct()
	{
		parent::__construct();
	}


	protected function save_file($path)
	{
		$input = fopen("php://input", "r");
		$target = fopen($path, "w");

		// Todo: Check they are both valid strams before using them
		$realSize = stream_copy_to_stream($input, $target);

		fclose($input);
		fclose($target);

		return ($realSize == $this->get_file_size());
	}


	protected function get_file_name()
	{
		// Todo: usare il wrapper di Joomla per le get
		return $_GET['qqfile'];
	}


	protected function get_file_size()
	{
		if (isset($_SERVER["CONTENT_LENGTH"])) return (int)$_SERVER["CONTENT_LENGTH"];
		//else throw new Exception('Getting content length is not supported.');
		return 0;
	}

}


// File uploads via regular form post (uses the $_FILES array)
class FileFormUploadManager extends FUploadManager
{
	public function __construct()
	{
		parent::__construct();
	}


	protected function save_file($path)
	{
		return move_uploaded_file($_FILES['qqfile']['tmp_name'], $path);
	}


	protected function get_file_name()
	{
		return $_FILES['qqfile']['name'];
	}


	protected function get_file_size()
	{
		return $_FILES['qqfile']['size'];
	}

}


class XhrDeleteManager
{
	public function HandleUpload($uploadDirectory)
	{
		$fileindex = JFactory::getApplication()->input->get("fileindex", 0);
		$owner = JFactory::getApplication()->input->get("owner", null);
		$id = JFactory::getApplication()->input->get("id", null);
		$namespace = "foxcontact_" . $owner . "_" . $id;

		// Retrieve the file list from the session
		$jsession = JFactory::getSession();
		$filelist = $jsession->get("filelist", array(), $namespace);

		if (!isset($filelist[$fileindex]))
		{
			return array("error" => "Index not found");
		}

		// Delete the file from the filesystem
		$deleted = @unlink($uploadDirectory . $filelist[$fileindex]["filename"]);
		if (!$deleted)
		{
			return array("error" => "Unable to delete the file");
		}

		// and again from the list
		unset($filelist[$fileindex]);

		// Save the new list to the session
		$jsession->set("filelist", $filelist, $namespace);

		return array("success" => true);
	}
}

