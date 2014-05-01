<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/notification/notification.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action     = (isset($_POST['action']))    ? $_POST['action']     : NULL;
  $id         = (isset($_POST['id']))        ? $_POST['id']         : NULL;
  $type       = (isset($_POST['type']))      ? $_POST['type']       : NULL;
  $initiator  = (isset($_POST['initiator'])) ? $_POST['initiator']  : NULL;
  $recipient  = (isset($_POST['recipient'])) ? $_POST['recipient']  : NULL;
  $title      = (isset($_POST['title']))     ? $_POST['title']      : NULL;
  $post       = (isset($_POST['post']))      ? $_POST['post']       : NULL;
  $parent     = (isset($_POST['parent']))    ? $_POST['parent']     : NULL;
  $status     = (isset($_POST['status']))    ? $_POST['status']     : NULL;
  $song       = (isset($_POST['song']))      ? $_POST['song']       : NULL;

  // Initialize return array
  $notifications = array();

  // Process data
  switch($action) {
    case 'push':
      $notifications['html'] = notification::push_notes($id);
      break;
    case 'get':
      $notifications = notification::get_note($id);
      break;
    case 'send':
      $args = array(
        'type'      => $type,
        'initiator' => $initiator,
        'recipient' => $recipient,
        'title'     => $title,
        'post'      => $post,
        'parent'    => $parent,
        'status'    => 'draft',
        'song'      => $song,
      );
      $notifications = notification::set_note($args);
      break;
    case 'mark':
      $notifications = notification::mark_note($id);
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($notifications);

} // end check for _POST

