/*<?php
 $jversion = new JVersion();
 // Architecture which works on Joomla 3.0.0 > 3.1.5
 if (version_compare($jversion->RELEASE . '.' . $jversion->DEV_LEVEL, "3.1.5", "<=")) { ?>*/

jQuery(document).ready(function ()
{
	// Only works on Isis template.
	if (!jQuery('div#status').length) return;

	// Check for component view
	// if (jQuery('input[type="hidden"][name="jform\\[type\\]"][value="component"]').length)

	var options = jQuery("#menuOptions");
	// Move the options
	jQuery("#details").append(options);

	// Remove the useless tab
	jQuery('a[href="\\#options"]').parent().remove();

	// Remove the first 2 elements from Basic Options
	// Better code, but by far slower
	//var container = options.children('div:first').children('div:nth-child(2)').children('div:first');
	//for (var f = 0; f < 2; ++f)
	//{
	//	container.children('div:first').remove();
	//}
	// Optimized code
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
	var tabs = jQuery('ul[class="nav nav-tabs"]').children();
	// Equivalent code, but the stupid name (myTabTabs) makes me think it will be changed in future Joomla releases
	//var tabs = jQuery('ul[id="myTabTabs"]').children();

	tabs.each(
		function (index)
		{
			// Exclude the standard Joomla tabs, and only act on our own tabs
			if (index < 1 || index > 6) return;

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
			var panel = jQuery('div.tab-pane:eq(' + index + ')');
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