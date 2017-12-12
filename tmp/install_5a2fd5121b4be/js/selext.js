function SelextSelectChange(select, prefix)
{
	// Todo: Validation against invalid data such as 150%, -400px, and so on.
	// ...

	// Search for the associated text field
	var text = document.getElementById(prefix + '_text');

	// In case of "Automatic" selected
	if (select.options[select.selectedIndex].value == 'auto')
	{
		// Clear text value
		text.value = '';

		// Hide it
		//text.style.visibility = 'hidden';
		text.style.display = 'none';
	}
	// Other units selected
	else
	{
		// Show it
		//text.style.visibility = 'visible';
		text.style.display = 'inline';
	}
}

// We need to trig the onchange event on page load.
jQuery(document).ready(function ()
{
	jQuery('select.selext').each(
		function (index, value)
		{
			this.onchange();
		});
});
