<?php defined("_JEXEC") or die(file_get_contents("index.html"));

	$forum = "http://www.fox.ra.it/forum/1-fox-contact-form.html";
	$rating = 'http://extensions.joomla.org/extensions/contacts-and-feedback/contact-forms/16171';
	$download = "http://www.fox.ra.it/downloads/category/1-fox-contact-form.html";
	$documentation = "http://www.fox.ra.it/forum/2-documentation.html";

	$com_name = JFactory::getApplication()->input->get("option", "");
	$name = substr($com_name, 4);
	$xml = JFactory::getXML(JPATH_ADMINISTRATOR . '/components/' . $com_name . "/" . $name . '.xml');

	$prefix = strtoupper($com_name) . "_";
	$language = JFactory::getLanguage();
	$language->load($com_name . '.sys');  // com_foxcontact.sys.ini
	$language->load("mod_quickicon");  // xx-XX.mod_quickicon.ini
	$freesoftware = str_replace("licenses/gpl-2.0.html", "copyleft/gpl.html", sprintf($language->_('JGLOBAL_ISFREESOFTWARE'), JText::_("COM_FOXCONTACT") . " " . (string)$xml->version));
	$s_description = sprintf($language->_($prefix . 'SHORTDESCRIPTION'),
	"<a href=\"index.php?option=com_menus&view=items\">" . $language->_('MOD_QUICKICON_MENU_MANAGER') . '</a>',
	"<a href=\"index.php?option=com_modules\">" . $language->_('MOD_QUICKICON_MODULE_MANAGER') . '</a>');

	$direction = intval(JFactory::getLanguage()->get('rtl', 0));
	$left  = $direction ? "right" : "left";
	$right = $direction ? "left" : "right";

// Create Transifex tag based on Joomla tag
$tag = str_replace("-", "_", $language->get("tag"));
// Handle exceptions
if ($tag == "sr-YU")
{
	// Serbian (Latin)
	$tag = "sr_RS@latin";
}
$language_url = 'https://www.transifex.com/projects/p/fox-contact/language/' . $tag . '/';
?>

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>

<p><img class="floatleft" src="../media/<?php echo("$com_name/images/$name"); ?>-logo.png"></p>
<p><b><?php echo($s_description); ?></b></p>

<p>
	<a href="<?php echo($documentation); ?>" target="_blank"><?php echo($language->_($prefix . 'DOCUMENTATION')); ?></a> |
	<a href="<?php echo($download); ?>" target="_blank"><?php echo($language->_($prefix . 'DOWNLOAD')); ?></a> |
	<a href="<?php echo($forum); ?>" target="_blank"><?php echo($language->_($prefix . 'FORUM')); ?></a> |
	<?php echo JText::_("JFIELD_LANGUAGE_LABEL") ?>:
	<a href="<?php echo($language_url); ?>" target="_blank"><?php echo($language->get("name")); ?></a> |
	<a href="<?php echo($rating); ?>" target="_blank"><?php echo($language->_($prefix . 'RATING')); ?></a>
</p>

<p><?php echo($freesoftware); ?></p>

</div>