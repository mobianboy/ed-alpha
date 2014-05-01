<?php

// WordPress Administration Bootstrap
require_once(ABSPATH.'wp-admin/admin.php');

// Check permissions
if(!current_user_can('edit_theme_options')) {
  wp_die(__('Cheatin&#8217; uh?'));
}

?>
<div id="balls-positioning" class="wrap">
	<div id="icon-themes" class="icon32">
		<br />
	</div>
	<h2><? _e('Position Maps') ?></h2>
	<div id="ajax-response"></div>
	<br class="clear" />
	<div id="col-container">
		<? // Include map templates ?>
    <? include_once('balls.map.types.php') ?>
		<? if(isset($template_id)): ?>
      <? include_once('balls.map.positions.php') ?>
    <? endif ?>
	</div>
	<form action="" method="post">
		<? wp_nonce_field('save-sidebar-widgets', '_wpnonce_widgets', FALSE) ?>
	</form>
	<br class="clear" />
</div>

