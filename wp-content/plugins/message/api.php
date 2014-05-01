<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/message/message.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action = (isset($_POST['action']))  ? $_POST['action'] : NULL;
  $id     = (isset($_POST['id']))      ? $_POST['id']     : NULL;
  $type   = (isset($_POST['type']))    ? $_POST['type']   : NULL;
  $data   = (isset($_POST['data']))    ? $_POST['data']   : NULL;
  $parent = (isset($_POST['parent']))  ? $_POST['parent'] : NULL;

  // Initialize return array
  $messages = array();

  // Process data
  switch($action) {
    case 'push':
      $messages = message::push_messages();
      break;
    case 'get_relationships':
      $messages = socialnetwork::get_relationships($id);
      break;
    case 'get':
      $messages['html'] = message::get_convo($id);
      break;
    case 'send':
      $messages['html'] = message::set_message($parent, $data);
      break;
    case 'read':
      $messages = message::mark_message('read', $type, $id);
      break;
    case 'hide':
      $messages = message::mark_message('hide', $type, $id);
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($messages);

} // end check for _POST

