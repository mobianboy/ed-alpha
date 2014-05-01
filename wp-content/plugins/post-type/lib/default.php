<?php

/* needs to be thrown into a controler and specified through post type 
/* administration system, loop through available post type and available enabled meta boxes
/* should loop through active post type, then selectevly activate related "add_meta_box" and 
/* corresponding tpl/"post_type".meta.html.php (or whatever)
/* need class entire thing shoudl be dynamic and loaded via control
/* Define the custom box */
add_action('add_meta_boxes', 'post_images_add_box');
add_action('save_post', 'post_images_save_meta_data');

/* Adds a box to the main column on the Post and Page edit screens */
function post_images_add_box() {
	add_meta_box( 
		'post_images',
		__('Images for post', 'post_images_nonce'),
		'post_images_inner_custom_box',
		'post' 
	);
}

/* Prints the box content */
function post_images_inner_custom_box($post) {
  // Use nonce for verification
	wp_nonce_field(plugin_basename(__FILE__), 'post_images_nonce');
  // needs breakout in tpl files and stucture
	include_once('tpl/post.meta.html.php');
}

/* When the post is saved, saves our custom data */
function post_images_save_meta_data($post_id) {
	if(defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
    return;
  }
	if(!wp_verify_nonce($_POST['post_images_nonce'], plugin_basename(__FILE__))) {
    return;
  }
  // Check permissions
	check_post_perms();
	// save meta keys and values in array
	$post_updates = array(
    'test2' => $_POST['test2'],
  	'test3' => $_POST['test3'],
  	'test4' => $_POST['test4'],
	);
	// loop through and update post meta updates
	if(count($post_updates)) {
    foreach($post_updates as $post_update => $value) {
		  update_post_meta($post_id, $post_update, $value);
	  }
  }
}

