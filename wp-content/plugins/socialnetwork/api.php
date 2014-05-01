<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/socialnetwork/socialnetwork.php');

// If ajax post is made, process data and return result 
if(isset($_POST)) {

  // Valid session?
  if(!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE)) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $post_type  = (isset($_POST['post-type'])) ? $_POST['post-type']  : NULL;
  $action     = (isset($_POST['action']))    ? $_POST['action']     : NULL;
  $parent     = (isset($_POST['parent']))    ? $_POST['parent']     : NULL;
  $comment    = (isset($_POST['comment']))   ? $_POST['comment']    : NULL;
  $id         = (isset($_POST['id']))        ? $_POST['id']         : NULL;
  $name       = (isset($_POST['name']))      ? $_POST['name']       : NULL;
  $data       = (isset($_POST['data']))      ? $_POST['data']       : NULL;
  $type       = (isset($_POST['type']))      ? $_POST['type']       : NULL;

  // Clear out result set
  unset($res);

  // Which post_type?
  switch($post_type) {

    // Comment
    case 'comment':
      switch($action) {
        case 'push':
          $res['html'] = socialnetwork::push_comments($parent, $id);
          break;
        case 'get':
          $res = socialnetwork::get_comments(array(
            'parent' => $parent,
          ));
          break;
        case 'set':
          $res = socialnetwork::set_comment($parent, $comment);
          break;
        case 'delete':
          $res = socialnetwork::delete_comment($id);
          break;
      }
      break;
    // End comment actions

    // Follow
    case 'follow':
      switch($action) {
        case 'get':
          $res = socialnetwork::get_follow($id);
          break;
        case 'get_relationships':
          $res = socialnetwork::get_relationships($id);
          break;
        case 'set':
          $res = socialnetwork::set_follow($type, $id);
          break;
        case 'delete':
          $res = socialnetwork::delete_follow($id);
          break;
      }
      break;
    // End follow actions

    // Shout 
    case 'shout':
      switch($action) {
        case 'push':
          $res['html'] = socialnetwork::push_shouts($parent, $id);
          break;
        case 'push_3p':
          $res['html'] = socialnetwork::push_following_shouts($parent, $id);
          break;
        case 'get':
          $res = socialnetwork::get_shouts($id);
          break;
        case 'get_3p':
          $res = socialnetwork::get_following_shouts($id);
          break;
        case 'set':
          $res['html'] = socialnetwork::set_shout($data, $id);
          break;
        case 'delete':
          $res = socialnetwork::delete_shout($id);
          break;
      }
      break;
    // End shout actions

    // Dig 
    case 'dig':
      switch($action) {
        case 'count':
          $res = socialnetwork::count_digs($id);
          break;
        case 'is':
          $res = socialnetwork::is_dug($id);
          break;
        case 'get':
          $res = socialnetwork::get_dig($id);
          break;
        case 'set':
          $res = socialnetwork::set_dig($id);
          break;
        case 'delete':
          $res = socialnetwork::delete_dig($id);
          break;
      }
      break;
    // End dig actions

    // Flag
    case 'flag':
      switch($action) {
        case 'get':
          $res = socialnetwork::get_flag($id);
          break;
        case 'set':
          $res = socialnetwork::set_flag($id);
          break;
        case 'delete':
          $res = socialnetwork::set_flag($id, TRUE);
          break;
      }
      break;
    // End flag actions

  } // end switch on $post_type

  // Return result 
  echo json_encode($res);

} // end check for _POST

