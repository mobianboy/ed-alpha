<?php

/** 
*	Template for available widgets registered in wordpress
* @uses rem_wp_widgets()->add_action global/jon.func
*	called from balls.list.php
**/

?>
<div class="center" id="available-widgets">
	<div id="widgets-left">
		<div id="available-widgets" class="widgets-holder-wrap">
			<div class="sidebar-name">
				<div class="sidebar-name-arrow"><br /></div>
				<h3>
					<? _e('Available Widgets') ?> <span id="removing-widget"><? _ex('Deactivate', 'removing-widget') ?> <span></span></span>
				</h3>
			</div>
			<div class="widget-holder">
				<p class="description">
					<? _e('Drag widgets from here to a sidebar on the right to activate them. Drag widgets back here to deactivate them and delete their settings.') ?>
				</p>
				<div id="widget-list">
					<? wp_list_widgets() ?>
				</div>
				<br class='clear' />
			</div>
			<br class="clear" />
		</div>
	</div>
</div>

