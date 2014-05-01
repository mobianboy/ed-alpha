<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/reward/reward.php');

// If ajax post is made, process data and return result 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action   = (isset($_POST['action']))   ? $_POST['action']  : NULL;
  $value    = (isset($_POST['value']))    ? $_POST['value']   : NULL;
  $content  = (isset($_POST['content']))  ? $_POST['content'] : NULL;
  $id       = (isset($_POST['id']))       ? $_POST['id']      : NULL;

  // Clear out result set
  unset($res);
  
  // Set current user
  $user = get_current_user_id();

  // Run the reward method to track the activity
  $res['activity_id'] = reward::track_activity($user, $content, $action, $value, $id);

  // Get back activity counts for this user on this action
  $res['count'] = reward::get_user_activity_stats($user, 'user_id', NULL, NULL, $action);

  // If rating a song, return the full song cloud url
  if($action == 11) {
    $res['full_song'] = song::get_song_file($content, 'song');
  }

  // Serialize and return data
  echo json_encode($res);

} // end check for _POST

