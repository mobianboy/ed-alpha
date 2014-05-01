<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/song/song.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action   = (isset($_POST['action']))  ? $_POST['action']   : NULL;
  $content  = (isset($_POST['content'])) ? $_POST['content']  : NULL;
  $id       = (isset($_POST['id']))      ? $_POST['id']       : NULL;
  $name     = (isset($_POST['name']))    ? $_POST['name']     : NULL;
  $genre    = (isset($_POST['genre']))   ? $_POST['genre']    : NULL;

  // Initialize return array
  $songs = array();

  // Process data
  switch($action) {
    case 'set':
      $args = array(
        'name'  => $name,
        'genre' => $genre,
      );
      $songs = song::set_song($args, $id);
      break;
    case 'has_rated':
      $songs = song::has_rated_song($id);
      break;
    case 'average_rating':
      $songs = song::get_average_rating($id);
      break;
    case 'my_rating':
      $songs = song::get_my_rating($id);
      break;
    case 'play_count':
      $songs = song::get_play_count($id);
      break;
    case 'rate_count':
      $songs = song::get_rate_count($id);
      break;
    case 'download':
      $songs = song::download_song($id);
      break;
    case 'delete':
      $songs = song::delete_song($id);
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($songs);

} // end check for _POST

