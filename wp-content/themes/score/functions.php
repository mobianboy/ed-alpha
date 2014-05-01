<?php

// Register/Enqueue Stylesheets within BALLS theme
function theme_styles() {
  global $stylesheets, $edhost;
  $sheets = array();
  if(count($stylesheets)) {
    foreach($stylesheets as $key => $stylesheet) {
      wp_register_style($key, '//'.$edhost.'/wp-content/themes/score/css/'.$key.'/desktop.min.css', $stylesheet['dep'], $stylesheet['ver'], $stylesheet['med']);
      $sheets[] = $key;
    }
  }
  wp_enqueue_style($sheets);
}

// Register/Enqueue External (Hosted elsewhere, e.g. googleapis.com) Stylesheets
function ext_styles() {
  global $ext_stylesheets;
  $sheets = array();
  if(count($ext_stylesheets)) {
    foreach($ext_stylesheets as $key => $stylesheet) {
      wp_register_style($key, $stylesheet['url'], $stylesheet['dep'], $stylesheet['ver'], $stylesheet['med']);
      $sheets[] = $key;
    }
  }
  wp_enqueue_style($sheets);
}

// Collection of archives filter
function filter_query_collection($where) {
  global $content;
  $where .= " AND post_title = '$content'";
  return $where;
}

// Current user's notes filter
function filter_query_notes($where) {
  $user_id = get_current_user_id();
  $where .= " AND post_content = '$user_id'";
  return $where;
}

// Disable extraneous template and meta stuff
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'wp_generator');
add_filter('show_admin_bar', '__return_false');

// Run the theme action
add_action('wp_enqueue_scripts', 'theme_styles');
add_action('wp_enqueue_scripts', 'ext_styles');

