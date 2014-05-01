<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/playlist/playlist.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action = (isset($_POST['action']))  ? $_POST['action'] : NULL;
  $name   = (isset($_POST['name']))    ? $_POST['name']   : NULL;
  $data   = (isset($_POST['data']))    ? $_POST['data']   : NULL;
  $id     = (isset($_POST['id']))      ? $_POST['id']     : NULL;
 
  // Initialize return array
  $playlists = array();
 
  // Process data
  switch($action) {
    case 'all':
      $playlists = playlist::get_library();
      break;
    case 'set_lib':
      $playlists = playlist::set_library($data);
      break;
    case 'get':
      $playlists = playlist::get_playlists($id);
      break;
    case 'set':
      $playlists = playlist::set_playlist($name, $data);
      break;
    case 'update':
      $playlists = playlist::update_playlist($id, $data, $name);
      break;
    case 'append':
      $playlists = playlist::append_playlist($data, $id);
      break;
    case 'delete':
      $playlists = playlist::delete_playlist($id);
      break;
    case 'get_state':
      $playlists = playlist::get_player_state();
      break;
    case 'set_state':
      $playlists = playlist::set_player_state($data);
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($playlists);

} // end check for _POST

