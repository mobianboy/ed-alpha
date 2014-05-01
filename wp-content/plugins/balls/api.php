<?php

// Include WP/Balls MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/balls/balls.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $href         = (isset($_POST['href']))      ? $_POST['href']       : NULL;
  $post_type    = (isset($_POST['post-type'])) ? $_POST['post-type']  : NULL;
  $template     = (isset($_POST['template']))  ? $_POST['template']   : NULL;
  $content      = (isset($_POST['content']))   ? $_POST['content']    : NULL;
  $parent       = (isset($_POST['parent']))    ? $_POST['parent']     : NULL;
  $page         = (isset($_POST['page']))      ? $_POST['page']       : 1;
  $orderby      = (isset($_POST['orderby']))   ? $_POST['orderby']    : NULL;
  $order        = (isset($_POST['order']))     ? $_POST['order']      : NULL;
  $tax          = (isset($_POST['tax']))       ? $_POST['tax']        : NULL;
  $tag          = (isset($_POST['tag']))       ? $_POST['tag']        : NULL;
  $loc_zip      = (isset($_POST['loc-zip']))   ? $_POST['loc-zip']    : NULL;
  $loc_rad      = (isset($_POST['loc-rad']))   ? $_POST['loc-rad']    : NULL;
  $search       = (isset($_POST['search']))    ? $_POST['search']     : NULL;
  $exclude      = (isset($_POST['exclude']))   ? $_POST['exclude']    : NULL;
  $resource_id  = (isset($_POST['id']))        ? $_POST['id']         : NULL;
  $duration     = (isset($_POST['duration']))  ? $_POST['duration']   : NULL;

  // Initialize return array
  $balls = array();

  // Process href for new standard
  if($href) {
    balls::balls_permalink($href, $template);
  }

  // Process template request from BALLS lib
  list($balls['html'], $balls['exclude']) = balls::get_balls_template(array(
    'post_type'   => $post_type,
    'template'    => $template,
    'content'     => $content,
    'page'        => $page,
    'orderby'     => $orderby,
    'order'       => $order,
    'tax'         => $tax,
    'tag'         => $tag,
    'loc_zip'     => $loc_zip,
    'loc_rad'     => $loc_rad,
    'search'      => $search,
    'exclude'     => $exclude,
    'resource_id' => $resource_id,
    'duration'    => $duration,
  ), TRUE);

  // Minify HTML
  $find = array(
    '/\>[^\S ]+/s', // strip whitespaces after tags, except space
    '/[^\S ]+\</s', // strip whitespaces before tags, except space
    '/(\s)+/s',     // shorten multiple whitespace sequences
  );
  $replace = array(
    '>',
    '<',
    '\\1',
  );
  $balls['html'] = preg_replace($find, $replace, $balls['html']);

  // Serialize the return data
  echo json_encode($balls);

} // end check for _POST

