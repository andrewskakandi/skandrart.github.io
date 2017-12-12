<div id="widgetkit" class="wrap">

	<?php JToolBarHelper::title('Widgetkit', 'widgetkit'); ?>

	<?php if ($this['check']->notices()): ?>
	<div id="wk-systemcheck">
		<strong>Critical Issues</strong>
		<ul>
			<?php foreach($this['check']->get_notices() as $notice): ?>
			<li class="<?php echo $notice['type']; ?>"><?php echo $notice['message']; ?></li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>

	<div class="dashboard">
		<ul id="tabs" data-wkversion="<?php echo $this->widgetkit["version"];?>">
			<?php $this['event']->trigger('dashboard'); ?>
			<li data-name="Settings">
				<form id="form" method="post" action="<?php echo $this['system']->link(array('task' => 'save_settings')); ?>">
					<ul class="properties">
						<li>
							<div class="wlabel">Google Maps API Key</div>
							<div class="field"><input type="text" name="googlemapsapikey" value="<?php echo $this->widgetkit['option']->get('googlemapsapikey'); ?>"></div>
							<div class="description">Custom Google Maps API Key. Visit https://console.developers.google.com/apis to generate your API key.</div>
							<p><input type="submit" value="Save changes" class="button-primary action save"/></p>
						</li>
					</ul>
				</form>
			</li>
		</ul>
	</div>

</div>
