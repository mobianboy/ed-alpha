<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/edit-in-place/eip.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action = (isset($_POST['action']))  ? $_POST['action'] : NULL;
  $field  = (isset($_POST['field']))   ? $_POST['field']  : NULL;
  $value  = (isset($_POST['value']))   ? $_POST['value']  : NULL;
  $id     = (isset($_POST['id']))      ? $_POST['id']     : NULL;

  // Clear out result set
  unset($eip);
 
  // Process data
  switch($action) {
    case 'song':
      $eip = eip::update_song($id, $field, $value);
      break;
    case 'post':
      $eip = eip::update_postmeta($id, $field, $value);
      break;
    case 'user':
      $eip = eip::update_usermeta($id, $field, $value);
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($eip);

} // end check for _POST

