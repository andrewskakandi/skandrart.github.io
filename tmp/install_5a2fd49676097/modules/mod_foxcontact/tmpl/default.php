<?php defined("_JEXEC") or die(file_get_contents("index.html"));

// Program: Fox Contact for Joomla
// Copyright (C): 2011 Demis Palma
// Documentation: http://www.fox.ra.it/forum/2-documentation.html
// License: Distributed under the terms of the GNU General Public License GNU/GPL v3 http://www.gnu.org/licenses/gpl-3.0.html

?>
<a name="<?php echo("mid_" . $module->id); ?>"></a>

<div
	id="foxcontainer_m<?php echo $module->id; ?>"
	class="foxcontainer<?php echo $params->get("moduleclass_sfx"); ?>">

	<?php
	// Page Subheading if needed
	if (!empty($page_subheading))
		echo("<h2>" . $page_subheading . "</h2>" . PHP_EOL);

	$messageboard->Display();
	?>

	<?php if (!empty($form_text)) { ?>
	<form enctype="multipart/form-data"
			id="fox_form_m<?php echo $module->id; ?>"
			name="fox_form_m<?php echo $module->id; ?>"
			class="fox_form foxform-<?php echo $params->get("form_layout", "extended"); ?>"
			method="post"
			action="<?php echo($action); ?>">
		<!-- <?php echo($app->scope . " " . (string)$xml->version . " " . (string)$xml->license); ?> -->
		<?php echo($form_text); ?>
	</form>
	<?php } ?>

</div>

