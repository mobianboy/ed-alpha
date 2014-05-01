<?php

// WordPress Administration Bootstrap
require_once(ABSPATH.'wp-admin/admin.php');

// WordPress Administration Widgets API
require_once(ABSPATH.'wp-admin/includes/widgets.php');

// Check permissions
if(!current_user_can('edit_theme_options')) {
  wp_die(__('Cheatin&#8217; uh?'));
}

// Load wordpress widget scripts
wp_enqueue_script('admin-widgets');

// These are the widgets grouped by sidebar
$registered_sidebars = wp_get_sidebars_widgets();

?>
<div id="balls-positioning" class="wrap">
	<div id="icon-themes" class="icon32">
		<br />
	</div>
	<h2><? _e('Widget Positioning') ?></h2>
	<div id="ajax-response"></div>
	<br class="clear" />
	<div id="col-container">
		<? // Include list templates ?>
		<? switch($action) {
      case 'position':
        include_once('balls.list.types.php');
        include_once('balls.list.positions.php');
        include_once('balls.list.widgets.php');
        break;
      case 'template':
      default:
        include_once('balls.list.types.php');
        break;
    } ?>
	</div>
	<form action="" method="post">
		<? wp_nonce_field('save-sidebar-widgets', '_wpnonce_widgets', FALSE) ?>
	</form>
	<br class="clear" />
</div>

