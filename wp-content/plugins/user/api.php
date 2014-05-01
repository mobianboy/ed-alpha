<?php

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-content/plugins/user/user.php');

// If ajax post is made, process data and return JSON data 
if(isset($_POST)) {

  // Valid session?
  if(!in_array($_POST['action'], array('login', 'prereg', 'register', 'validatereg', 'checkusername', 'forgotpass', 'resetpass')) && (!is_user_logged_in() || !get_user_meta(get_current_user_id(), 'initialized', TRUE))) {
    die('Not a valid or initialized user session.');
  }

  // Check for post vars
  $action   = (isset($_POST['action']))   ? $_POST['action']    : NULL;
  $username = (isset($_POST['username'])) ? $_POST['username']  : NULL;
  $password = (isset($_POST['password'])) ? $_POST['password']  : NULL;
  $oldpass  = (isset($_POST['oldpass']))  ? $_POST['oldpass']   : NULL;
  $confpass = (isset($_POST['confpass'])) ? $_POST['confpass']  : NULL;
  $remember = (isset($_POST['remember'])) ? $_POST['remember']  : NULL;
  $email    = (isset($_POST['email']))    ? $_POST['email']     : NULL;
  $token    = (isset($_POST['token']))    ? $_POST['token']     : NULL;
  $profile  = (isset($_POST['profile']))  ? $_POST['profile']   : NULL;
  $setting  = (isset($_POST['setting']))  ? $_POST['setting']   : NULL;
  $data     = (isset($_POST['data']))     ? $_POST['data']      : NULL;
  $id       = (isset($_POST['id']))       ? $_POST['id']        : NULL;

  // Initialize return array
  $user = array();

  // Process data - run actions
  switch($action) {
    case 'login':
      $user['data'] = user::login($email, $password, $remember);
      break;
    case 'logout':
      $user['data'] = user::logout();
      break;
    case 'prereg':
      $user['data'] = user::prereg($email);
      $user['html'] = balls::get_balls_template(array(
        'post_type' => 'user',
        'template'  => ($user['data']) ? 'confirm' : 'form',
      ));
      break;
    case 'resetpass':
      $user['data'] = user::reset_password($oldpass, $password, $confpass, $email);
      break;
    case 'forgotpass':
      $user['data'] = user::forgot_password($email);
      break;
    case 'register':
      $user['data'] = user::register($email, $profile, $username, $password, $confpass);
      break;
    case 'validatereg':
      $user['data'] = user::validate_token($email, $token);
      break;
    case 'checkusername':
      $user['data'] = user::check_username($username);
      break;
    case 'update':
      $user['data'] = user::update_setting($setting, $data);
      break;
    case 'get_videos':
      $user['data'] = user::get_videos($id);
      break;
    case 'set_videos':
      $user['data'] = user::set_videos($data, $id);
      $user['html'] = balls::get_balls_template(array(
        'post_type' => 'user',
        'template'  => 'explodes-videos',
        'content'   => $id,
      ));
      break;
    default:
      exit;    
  }

  // Serialize the return data
  echo json_encode($user);

} // end check for _POST

