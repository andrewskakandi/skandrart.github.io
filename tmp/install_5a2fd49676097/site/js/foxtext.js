if (typeof Fox == 'undefined')
{
	Fox = {};

	Fox.Strings =
	{
		add: function (object)
		{
			jQuery.extend(this, object);
			return this;
		}
	};

	Fox.Options =
	{
		// Fox.Options.add('key', {...});
		add: function (key, data)
		{
			var o = {};
			o[key] = data;
			jQuery.extend(this, o);
			return this;
		},

		// Return by copy: Fox.Options.get('key');
		// Return by reference: Fox.Options['key'];
		get: function (key)
		{
			return jQuery.extend({}, this[key]);
		}
	};
}


(function ()
{
	Fox.Strings.add(
		{
			JCANCEL: "<?php echo JText::_('JCANCEL') ?>",
			COM_FOXCONTACT_BROWSE_FILES: "<?php echo JText::_('COM_FOXCONTACT_BROWSE_FILES') ?>",
			COM_FOXCONTACT_FAILED: "<?php echo JText::_('COM_FOXCONTACT_FAILED') ?>",
			COM_FOXCONTACT_SUCCESS: "<?php echo JText::_('COM_FOXCONTACT_SUCCESS') ?>",
			COM_FOXCONTACT_NO_RESULTS_MATCH: "<?php echo JText::_('COM_FOXCONTACT_NO_RESULTS_MATCH') ?>",
			COM_FOXCONTACT_REMOVE_ALT: "<?php echo JText::_('COM_FOXCONTACT_REMOVE_ALT') ?>",
			COM_FOXCONTACT_REMOVE_TITLE: "<?php echo JText::_('COM_FOXCONTACT_REMOVE_TITLE') ?>"
		}
	);

	Date.monthNames = ["<?php echo JText::_('JANUARY') ?>", "<?php echo JText::_('FEBRUARY') ?>", "<?php echo JText::_('MARCH') ?>", "<?php echo JText::_('APRIL') ?>", "<?php echo JText::_('MAY') ?>", "<?php echo JText::_('JUNE') ?>", "<?php echo JText::_('JULY') ?>", "<?php echo JText::_('AUGUST') ?>", "<?php echo JText::_('SEPTEMBER') ?>", "<?php echo JText::_('OCTOBER') ?>", "<?php echo JText::_('NOVEMBER') ?>", "<?php echo JText::_('DECEMBER') ?>"];
	Date.dayNames = ["<?php echo JText::_('SUNDAY') ?>", "<?php echo JText::_('MONDAY') ?>", "<?php echo JText::_('TUESDAY') ?>", "<?php echo JText::_('WEDNESDAY') ?>", "<?php echo JText::_('THURSDAY') ?>", "<?php echo JText::_('FRIDAY') ?>", "<?php echo JText::_('SATURDAY') ?>"];
	Date.monthNumbers = { "<?php echo JText::_('JANUARY') ?>": 0, "<?php echo JText::_('FEBRUARY') ?>": 1, "<?php echo JText::_('MARCH') ?>": 2, "<?php echo JText::_('APRIL') ?>": 3, "<?php echo JText::_('MAY') ?>": 4, "<?php echo JText::_('JUNE') ?>": 5, "<?php echo JText::_('JULY') ?>": 6, "<?php echo JText::_('AUGUST') ?>": 7, "<?php echo JText::_('SEPTEMBER') ?>": 8, "<?php echo JText::_('OCTOBER') ?>": 9, "<?php echo JText::_('NOVEMBER') ?>": 10, "<?php echo JText::_('DECEMBER') ?>": 11 };

	// Calendar default options
	Fox.Options.add('calendar', {
		dayOfWeekStart: JSON.parse("<?php echo JFactory::getLanguage()->getFirstDay() ?>"),
		lang: 'dynamic',
		step: 60,
		i18n: {
			dynamic: {
				months: Date.monthNames,
				dayOfWeek: ["<?php echo JText::_('SUN') ?>", "<?php echo JText::_('MON') ?>", "<?php echo JText::_('TUE') ?>", "<?php echo JText::_('WED') ?>", "<?php echo JText::_('THU') ?>", "<?php echo JText::_('FRI') ?>", "<?php echo JText::_('SAT') ?>"]
			}
		}
	});

	Fox.Options.add('chosen', {
		disable_search_threshold: 10,
		allow_single_deselect: true,
		no_results_text: Fox.Strings['COM_FOXCONTACT_NO_RESULTS_MATCH']
	});

})();


// Emulate "placeholder" feature on outdated browsers
jQuery(document).ready(function ($)
{
	// Create a new input object, then test if the placeholder attribute is an option inside that object.
	// It will be an option in browsers that support placeholder text, and absent in those that don’t.
	if (!('placeholder' in document.createElement('input')))
	{
		// Avoid the query string ?_=random at the end of the url
		$.ajaxSetup({cache: true });
		$.getScript('<?php echo JUri::root(true) ?>' + '/media/com_foxcontact/js/placeholder.min.js');
	}
});


/* Reset button | Reset input */
jQuery(document).ready(function ($)
{
	if ($('.fox_form .reset-button').length)
	{
		// Avoid the query string ?_=random at the end of the url
		$.ajaxSetup({cache: true });
		$.getScript('<?php echo JUri::root(true) ?>' + '/media/com_foxcontact/js/reset.min.js');
	}
});


/* Captcha begin */
function ReloadFCaptcha(id)
{
	var image = document.getElementById(id);

	// Generates a unique id with an 8 digits fixed length
	var uniqueid = Math.floor(Math.random() * Math.pow(10, 8));
	image.src = image.src.replace(/uniqueid=[0-9]{8}/, "uniqueid=" + uniqueid);
}


function BuildReloadButton(id)
{
	document.getElementById(id).src = document.getElementById(id).src.replace("transparent.gif", "reload-16.png");
}
/* Captcha end */

/* Enable the following function if you want to enable autofocus to the first input of the first form in the page */
/*
 jQuery(document).ready(function ($)
 {
 jQuery('.fox_form').find('input[type=text]').filter(':visible:first').focus();
 });
 */
