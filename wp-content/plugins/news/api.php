<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/news/news.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action = (isset($_POST['action']))  ? $_POST['action'] : NULL;
  $id     = (isset($_POST['id']))      ? $_POST['id']     : NULL;

  // Initialize return array
  $news = array();

  // Process data
  switch($action) {
    case 'related':
      news::get_related();
      break;
    case 'get':
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($news);

} // end check for _POST

