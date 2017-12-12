var JText = [];
JText['COM_FOXCONTACT_MESSAGE_DELIVERY_LBL'] = '<?php echo JText::_("COM_FOXCONTACT_MESSAGE_DELIVERY_LBL"); ?>';
JText['COM_FOXCONTACT_FIELDS_LBL'] = '<?php echo JText::_("COM_FOXCONTACT_FIELDS_LBL"); ?>';
JText['COM_FOXCONTACT_EVENTS_LBL'] = '<?php echo JText::_("COM_FOXCONTACT_EVENTS_LBL"); ?>';
JText['COM_FOXCONTACT_SECURITY_LBL'] = '<?php echo JText::_("COM_FOXCONTACT_SECURITY_LBL"); ?>';
JText['COM_FOXCONTACT_NEWSLETTER_INTEGRATION_LBL'] = '<?php echo JText::_("COM_FOXCONTACT_NEWSLETTER_INTEGRATION_LBL"); ?>';

/*<?php
$jversion = new JVersion();
// Architecture which works on Joomla 3.0.0 / 3.0.1 / 3.0.2
if (version_compare($jversion->RELEASE . '.' . $jversion->DEV_LEVEL, "3.0.3", "<")) { ?>*/
jQuery(document).ready(function ()
{
	// Only works on Isis template.
	if (!jQuery('div#status').length) return;

	// Count existing tabs
	var tabs = jQuery('ul[class="nav nav-tabs"]').children();

	tabs.each(
		function (index)
		{
			var element = jQuery(this).children('a');
			var text = element.text().trim();
			// Try to translate. Fallback to the current text exactly as it is
			var caption = JText[text] || text;

			// Set the translated text to the child anchor
			element.text(caption);
		});

	// Remove the first 2 elements of the section "Basic"
	var basic = jQuery('div.tab-pane')[1];
	for (var f = 0; f < 2; ++f)
	{
		jQuery(basic.children[0]).remove();
	}
});

/*<?php
// Architecture which works on Joomla 3.0.3 / 3.1.0 > 3.1.5
} else if (version_compare($jversion->RELEASE . '.' . $jversion->DEV_LEVEL, "3.1.5", "<=")) { ?>*/

jQuery(document).ready(function ()
{
	// Only works on Isis template.
	if (!jQuery('div#status').length) return;

	var options = jQuery("#moduleOptions");
	// Move the options
	jQuery("#details").append(options);

	// Remove the useless tab
	jQuery('a[href="\\#options"]').parent().remove();

	// Remove the first 2 elements from Basic Options
	for (var f = 0; f < 2; ++f)
	{
		jQuery(options[0].children[0].children[1].children[0].children[0]).remove();
	}
});

/*<?php
// Architecture which works on Joomla 3.2.0 and newer
} else { ?>*/
// Can't use jQuery(document).ready here
jQuery(window).load(function ()
{
	// Only works on Isis template.
	if (!jQuery('div#status').length) return;

	// Prepare the accordion container
	var $accordion = jQuery('<div />',
		{
			id: 'foxoptions',
			class: 'accordion',
		});
	// Attach the accordion to the insert point on the main panel
	jQuery('div.span9').append($accordion);

	// Count existing tabs
	// This selector includes permissions tab
	//var tabs = jQuery('ul[class="nav nav-tabs"]').children();
	// Equivalent code, but the stupid name (myTabTabs) makes me think it will be changed in future Joomla releases
	var tabs = jQuery('ul#myTabTabs').children();

	tabs.each(
		function (index)
		{
			// Exclude the standard Joomla tabs, and only act on our own tabs
			if (index < 3 || index > 8) return;

			// Read the caption of the tab, we will need it while creating the accordion item
			var caption = jQuery(this).children('a').text().trim();

			// Create the accordion item
			var $accordion_inner;
			$accordion.append(
				jQuery('<div />', {class: 'accordion-group'}).append(
					jQuery('<div />', {class: 'accordion-heading'}).append(
						jQuery('<strong />').append(
							jQuery('<a />', {class: 'accordion-toggle collapsed', 'data-toggle': 'collapse', href: '#collapse' + index, 'data-parent': '#' + $accordion.attr('id'), html: caption})
						)
					),
					jQuery('<div />', {class: 'accordion-body collapse', id: 'collapse' + index}).append(
						$accordion_inner = jQuery('<div />', {class: 'accordion-inner'})
					)
				)
			);

			// Detect the panel associated to this tab
			var panel = jQuery('div#myTabContent > div.tab-pane:eq(' + index + ')');
			// Detect the fields inside this panel
			var fields = panel.find('div.control-group');
			fields.each(
				function ()
				{
					// Skip the void fields (fenvironment)
					if (!jQuery(this).text().length) return;

					// Move this field and populate the accordion item
					$accordion_inner.append(this);
				}
			);

			// Remove the tab
			jQuery(this).remove();
			// Todo: non si possono rimuovere i pannelli perche' contengono ancora i campi hidden. Nello specifico, il secondo pannello (attrib-fields)
		});

});
/*<?php } ?>*/