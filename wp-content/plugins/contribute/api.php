<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/contribute/contribute.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action   = (isset($_POST['action']))         ? $_POST['action']        : NULL;
  $id       = (isset($_POST['mfpResourceId']))  ? $_POST['mfpResourceId'] : NULL;
  $typeid   = (isset($_POST['typeid']))         ? $_POST['typeid']        : NULL;
  $wfid     = (isset($_POST['wfid']))           ? $_POST['wfid']          : NULL;
  $title    = (isset($_POST['title']))          ? $_POST['title']         : NULL;
  $genre    = (isset($_POST['genre']))          ? $_POST['genre']         : NULL;
  $start    = (isset($_POST['demoStart']))      ? $_POST['demoStart']     : NULL;
  $length   = (isset($_POST['demoLength']))     ? $_POST['demoLength']    : NULL;
  $status   = (isset($_POST['status']))         ? $_POST['status']        : NULL;
  $caption  = (isset($_POST['caption']))        ? $_POST['caption']       : NULL;

  // Clear out result set
  unset($contribute);

  // Process data
  switch($action) {
    case 'setphoto':
      $contribute = contribute::set_photo($id, $caption);
      break;
    case 'deletephoto':
      $contribute = contribute::delete_photo($id);
      break;
    case 'set':
      $contribute = contribute::set_song($id, $wfid, $title, $genre, $start, $length);
      break;
    case 'confirm':
      $contribute = contribute::confirm_song($typeid, $status);
      break;
    default:
      $contribute = FALSE;
  }

  // Return encoded result
  echo json_encode($contribute);

} // end check for _POST

