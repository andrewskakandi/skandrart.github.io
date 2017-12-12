<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

$wholemenu = $this->Application->getMenu();
$activemenu = $wholemenu->getActive();
$cid = $activemenu->id;

echo
	'<a name="cid_' . $cid . '"></a>' .
	'<div ' .
	'id="foxcontainer_c' . $cid . '" ' .
	'class="foxcontainer' . $this->cparams->get('pageclass_sfx') . '">';

// Page Heading if needed
if ($this->cparams->get('show_page_heading'))
	echo("<h1>" . $this->escape($this->cparams->get('page_heading')) . "</h1>" . PHP_EOL);

// Page Subheading if needed
$page_subheading = $this->cparams->get("page_subheading", "");
if (!empty($page_subheading))
	echo("<h2>" . $page_subheading . "</h2>" . PHP_EOL);

$xml = JFactory::getXML(JPATH_ADMINISTRATOR . "/components/" . $GLOBALS["com_name"] . "/" . $GLOBALS["ext_name"] . ".xml");

$this->MessageBoard->Display();

if (!empty($this->FormText))
{
	?>
	<form enctype="multipart/form-data"
			id="fox_form_c<?php echo $cid; ?>"
			name="fox_form_c<?php echo $cid; ?>"
			class="fox_form foxform-<?php echo $this->cparams->get("form_layout", "extended"); ?>"
			method="post"
			action="<?php echo(JFactory::getApplication()->input->server->get("REQUEST_URI", "", "string") . "#cid_" . $cid); ?>">
		<!-- <?php echo("com_" . $this->_name . " " . (string)$xml->version . " " . (string)$xml->license); ?> -->
		<?php echo($this->FormText); ?>
	</form>
<?php
}
echo('</div>');
?>
