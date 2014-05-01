<?php
/*
Plugin Name: User Management System
Plugin URI:
Description: Eardish User and session management
Version: 2.0
Author: Steven Kornblum
*/


// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');


// Hook a login action to track time
add_action('wp_login', array('user', 'set_last_login'));


/**
 * @desc user/session lib
 * @author SDK (steve@eardish.com)
 * @date 2012-08-10
 */
class user {


  /**
   * @desc Get prereg users
   * @author SDK (steve@eardish.com)
   * @date 2013-08-20
   * @return arr - Return an array of user objects for artists
  */
  public static function get_pre_users() {
    global $wpdb;

    // Get all prereg user records
    $sql = "SELECT *
            FROM prereg";
    $users = $wpdb->get_results($sql);

    // Return result
    return $users;
  } // end function get_pre_users


  /**
   * @desc Pre-registration
   * @author SDK (steve@eardish.com)
   * @date 2012-11-24
   * @param str $email - The pre-reg email address for invite
   * @return bool - Return success or failure of operation
  */
  public static function prereg($email) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email)) {
        //throw new Exception('Need to provide email');
        throw new Exception(FALSE);
      }
      $email = trim($email);
      if(strlen($email) < 6 || !preg_match("~^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$~i", $email)) {
        //throw new Exception('Need to provide valid email address');
        throw new Exception(FALSE);
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    $exists = FALSE;

    // Check if email is in prereg
    $sql = "SELECT COUNT(id) AS count
            FROM prereg
            WHERE email = %s";
    $exists = $wpdb->get_var($wpdb->prepare($sql, $email));

    // Check if email is in wp_users
    $sql = "SELECT COUNT(ID) AS count
            FROM wp_users
            WHERE user_email = %s";
    $exists = $wpdb->get_var($wpdb->prepare($sql, $email));

    // If this email hasn't yet been registered or pre-registered
    if(!$exists) {

      // Build query
      $sql = "INSERT INTO prereg SET
              email = %s";

      // Insert prereg into DB
      $res = $wpdb->query($wpdb->prepare($sql, $email));

      // Generate invite token
      if($res) {
        $token = self::encode_token($wpdb->insert_id);
        $sql = "UPDATE prereg SET
                token = %s
                WHERE id = %d";
        $wpdb->query($wpdb->prepare($sql, $token, $wpdb->insert_id));
      }

    // Otherwise report duplicate error msg
    } else {
      return 'exists';
    }

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function prereg


  /**
   * @desc Login
   * @author SDK (steve@eardish.com)
   * @date 2012-08-10
   * @param str $email - The login email
   * @param str $password - The login password
   * @param [OPT] bool $remember - Set the remember cookie (default false)
   * @param [OPT] str $token - Forgot password token to override password
   * @return mixed(obj|str) - Return user object if successful, or error string
  */
  public static function login($email, $password, $remember=FALSE, $token=NULL) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email) || !isset($password)) {
        throw new Exception('Need to provide email and password');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Force change of password to token during forgot password process
    if($token) {
      $token_pass = wp_hash_password($token); // hash token into password format
      $sql = "UPDATE wp_users
              SET user_pass = %s
              WHERE user_email = %s";
      $wpdb->query($wpdb->prepare($sql, $token_pass, $email));
      $password = $token;
    }

    // Get user_login that matches email address
    $sql = "SELECT user_login
            FROM wp_users
            WHERE user_email = %s";
    $user_login = $wpdb->get_var($wpdb->prepare($sql, $email));

    // If username wasn't matched from email, throw and exception
    try {
      if(!$user_login) {
        throw new Exception('Invalid email or password');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Setup creds array
    $creds = array(
      'user_login'    => $user_login,
      'user_password' => $password,
      'remember'      => $remember,
    );

    // Attempt login
    $user = wp_signon($creds, FALSE);

    // Return result (error OR user object)
    if(is_wp_error($user)) {
      return $user->get_error_message();
    } else {
      
      // Track login in analytics
      reward::track_activity($user->ID, $user->ID, 1);

      // Return user object
      return $user;
    }

  } // end function login


  /**
   * @desc Logout
   * @author SDK (steve@eardish.com)
   * @date 2012-08-10
   * @return bool - Return success or failure
  */
  public static function logout() {
    global $wpdb;

    // Track logout in analytics
    reward::track_activity(get_current_user_id(), get_current_user_id(), 2);

    // Attempt logout
    $res = wp_logout();

    // Return result
    return $res;
  } // end function logout


  /**
   * @desc Get user by forgot token
   * @author SDK (steve@eardish.com)
   * @date 2013-09-30
   * @param str $token - The temp forgot password token string
   * @return obj - Return user object
  */
  public static function get_user_by_forgot_token($token) {
    global $wpdb;

    // Query for user id from user meta matching forgot token
    $sql = "SELECT user_id
            FROM wp_usermeta
            WHERE meta_key = 'forgotpass_token'
            AND meta_value = %s";
    $id = $wpdb->get_var($wpdb->prepare($sql, $token));
  
    // Get user by id
    if($id) {
      $user = get_user_by('id', $id);
      $user->meta = get_user_meta($user->ID);
    } else {
      $user = NULL;
    }

    // Return result
    return $user;
  } // end function get_user_by_forgot_token


  /**
   * @desc Validate forgot password token
   * @author SDK (steve@eardish.com)
   * @date 2013-09-30
   * @param str $token - The temp forgot password token string
   * @param [OPT] bool $expire - Should the token be expired now?
   * @return bool - Return valid or not
  */
  public static function validate_forgot_token($token, $expire=FALSE) {
    global $wpdb;

    // Get user by token
    $user = self::get_user_by_forgot_token($token);

    // Check for currently valid timestamp (2 hours)
    if($user) {
      $res = ((time() - $user->data->meta['forgotpass_timestamp'][0]) < (2*60*60)) ? TRUE : FALSE;
      if($expire) {
        delete_user_meta($user->data->ID, 'forgotpass_token');
        delete_user_meta($user->data->ID, 'forgotpass_timestamp');
      }
    } else {
      $res = FALSE;
    }


    // Return result
    return $res;
  } // end function validate_forgot_token


  /**
   * @desc Check username is unique
   * @author SDK (steve@eardish.com)
   * @date 2013-07-22
   * @return bool - Is username unique?
  */
  public static function check_username($username) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($username)) {
        throw new Exception('Need to provide username');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Check if username exists
    $sql = "SELECT COUNT(*) AS count
            FROM wp_users
            WHERE user_login = %s";
    $res = $wpdb->get_var($wpdb->prepare($sql, $username));

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function check_username


  /**
   * @desc Validate registration token
   * @author SDK (steve@eardish.com)
   * @date 2013-07-22
   * @return bool - Is valid?
  */
  public static function validate_token($email, $token) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email) || !isset($token)) {
        throw new Exception('Need to provide email and token');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Check for valid token
    $res = ($token === self::get_token($email)) ? TRUE : FALSE;

    // Return result
    return $res;
  } // end function validate_token


  /**
   * @desc Register a new user
   * @author SDK (steve@eardish.com)
   * @date 2012-08-10
   * @return str - Return balls api call to new user's profile on success or error message on failure
  */
  public static function register($email, $profile, $username, $password, $confpass) {
    global $wpdb;

    // If not provided necessary args or expected values, throw an error
    try {
      if(!isset($email) || !isset($profile) || !isset($username) || !isset($password) || !isset($confpass)) {
        throw new Exception('Need to provide email, profile, username, password and confpass');
      }
      if($password != $confpass) { // Check for matched passwords
        throw new Exception('Passwords did not match');
      }
      if(strlen($password) < 6 || !preg_match("~[0-9]~", $password) || !preg_match("~[A-Z]~", $password) || !preg_match("~[^A-Za-z0-9]~", $password)) { // Check for valid password patterns
        throw new Exception('Password is not valid');
      }
      if(self::check_username($username)) { // Check for unique user_login
        throw new Exception('Username is already in use');
      }
      if(!preg_match("~^[a-zA-Z0-9\s\.\_\-]{5,}$~i", $username)) { // Check that the username meets all the rules for validation
        throw new Exception('Username is not valid');
      }
      if(!in_array($profile, array('fan', 'artist'))) { // Check for allowed profile type
        throw new Exception('Profile must be a fan or an artist');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Hash password through wordpress
    $password = wp_hash_password($password);

    // Create user
    $id = wp_create_user($username, $password, $email);

    // Insert Metadata for profile type
    $meta = add_user_meta($id, 'profile_type', $profile);

    // Expire token
    $expire = self::expire_token($email);

    // Force login
    $user = self::login($email, $password, TRUE);

    // Return result
    return $user;
  } // end function register


  /**
   * @desc Update profile settings
   * @author SDK (steve@eardish.com)
   * @date 2013-07-23
   * @param str $setting - The type of setting to update
   * @param str $data - The value(s) of the setting
   * @return bool - Return success or failure
  */
  public static function update_setting($setting, $data) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($setting) || !isset($data)) {
        throw new Exception('Need to provide setting and data');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    /*** Supported setting types:
      - profile_visibility (public, private, network, extended)
      - block_list (comma delimited list of user ids)
      - comments_song (true or false)
      - comments_video (true or false)
      - comments_image (true or false)
      - email_note (true or false)
      - email_msg (true or false)
    ***/

    // Initialize return array
    $res = array();

    // Update the user meta
    $res['result'] = update_user_meta(get_current_user_id(), $setting, $data);

    // Get the current stored value
    $res['value'] = get_user_meta(get_current_user_id(), $setting, TRUE);

    // Return result
    return $res;
  } // end function update_setting


  /**
   * @desc Get artists
   * @author SDK (steve@eardish.com)
   * @date 2013-06-28
   * @return arr - Return an array of user objects for artists
  */
  public static function get_artists() {
    global $wpdb;

    // Get users that are artists
    $artist = get_users(array(
      'meta_key'     => 'profile_type',
      'meta_value'   => 'artist',
      'orderby'      => 'ID',
      'order'        => 'ASC',
      'fields'       => 'all',
    ));

    // Return result
    return $artists;
  } // end function get_artists


  /**
   * @desc Get user object
   * @author SDK (steve@eardish.com)
   * @date 2012-11-07
   * @param int $id - The id of the user to fetch
   * @return obj - Return the user object
  */
  public static function get_user($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the user object by user id
    $user = get_user_by('id', $id);

    // Return result
    return $user;
  } // end function get_user


  /**
   * @desc Get profile image
   * @author SDK (steve@eardish.com)
   * @date 2012-05-07
   * @param int $w - The width of the image
   * @param int $h - The height of the image
   * @param [OPT] int $id - The user id of the profile image (defaults to current session)
	 * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
   * @return str - Return the cloud URL of the profile image thumb
  */
  public static function get_user_image($w, $h, $id=NULL, $archive=FALSE) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($w) || !isset($h)) {
        throw new Exception('Need to provide w and h');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get user ID from current session if not specified
    $id = ($id) ? $id : get_current_user_id();

    // Process MFP call
    $res = ed::get_mfp_image('profile', $id, $w, $h, $archive);

    // Return result (cloud url)
    return $res;
  } // end function get_user_image


  /**
   * @desc Get formatted user city, state
   * @author SDK (steve@eardish.com)
   * @date 2013-07-31
   * @param int $id - The id of the user to fetch
   * @return str - Return the formatted location (city, state) of the user
  */
  public static function get_formatted_location($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the City
    $city = get_metadata('user', $id, 'City', TRUE);

    // Get the State
    $state = get_metadata('user', $id, 'State', TRUE);

    // Format based on available data
    if(empty($city) && empty($state)) {
      $location = "";
    } elseif(empty($city)) {
      $location = "$state";
    } elseif(empty($state)) {
      $location = "$city";
    } else {
      $location = "$city, $state";
    }

    // Return result
    return $location;
  } // end function get_formatted_location


  /**
   * @desc Get user location
   * @author SDK (steve@eardish.com)
   * @date 2012-11-19
   * @param int $user_id - The id of the user to fetch
   * @return str - Return the location metadata value of the specified user
  */
  public static function get_user_location($user_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($user_id)) {
        throw new Exception('Need to provide user_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the metadata for user location
    $location = get_metadata('user', $user_id, 'postal_code', TRUE);

    // Return result
    return $location;
  } // end function get_user_location


  /**
   * @desc Get user id by email
   * @author SDK (steve@eardish.com)
   * @date 2013-10-09
   * @param str $email - The email of the user to fetch
   * @return int - Return the id of the user
  */
  public static function get_user_id_by_email($email) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email)) {
        throw new Exception('Need to provide email');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the user object by email 
    $user = get_user_by('email', $email);

    // Return result
    return $user->ID;
  } // end function get_user_id_by_email


  /**
   * @desc Get user id by slug
   * @author SDK (steve@eardish.com)
   * @date 2013-07-17
   * @param str $slug - The slug of the user to fetch
   * @return int - Return the id of the user
  */
  public static function get_user_id_by_slug($slug) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($slug)) {
        throw new Exception('Need to provide slug');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the user object by Slug
    $user = get_user_by('login', $slug);

    // Return result
    return $user->ID;
  } // end function get_user_id_by_slug


  /**
   * @desc Get user login name by id
   * @author SDK (steve@eardish.com)
   * @date 2013-06-05
   * @param int $id - The id of the user to fetch
   * @return str - Return the user_login value from the wp users db
  */
  public static function get_user_login_by_id($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the user object by ID
    $user = get_user_by('id', $id);

    // Return result
    return $user->user_login;
  } // end function get_user_login_by_id


  /**
   * @desc Set user password
   * @author SDK (steve@eardish.com)
   * @date 2013-06-03
   * @param str $oldpass - The old password for the user on a reset
   * @param str $password - The new password for the user
   * @param str $confpass - The new password confirmed
   * @param [OPT] str $email - The email address of the account
   * @return bool - Success or failure of operation
  */
  public static function reset_password($oldpass, $password, $confpass, $email=NULL) {
    global $wpdb;

    // Get user object
    if($email) {
      $user = get_user_by('email', $email);
    } else {
      $user = get_user_by('id', get_current_user_id());
    }

    // Error checking
    try {

      // If not provided necessary args, throw an error
      if(!isset($oldpass) || !isset($password) || !isset($confpass)) {
        throw new Exception('Need to provide oldpass, password and confpass');
      }

      // Forgot password token valid? 
      if($email && !self::validate_forgot_token($oldpass, TRUE)) {
        throw new Exception('Your account has been locked, please email our <a href="mailto:help@eardish.com">support desk for further assistance.');
      }
      
      // Old/current password is correct?
      if(!$email && $wp_check_password($oldpass, $user->data->user_pass, $user->ID)) {
        throw new Exception('Your current password does not match');
      }

      //  New password and confirmation match?
      if($password != $confpass) {
        throw new Exception('Password confirmation did not match new password');
      }

      // Check for valid password patterns
      if(strlen($password) < 6 || !preg_match("~[0-9]~", $password) || !preg_match("~[A-Z]~", $password) || !preg_match("~[^A-Za-z0-9]~", $password)) {
        throw new Exception('Password is not valid');
      }

    // Catch block for error exception handling
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Hash password through wordpress
    $password = wp_hash_password($password);

    // Update password
    $sql = "UPDATE wp_users
            SET user_pass = %s
            WHERE ID = %d";
    $res = $wpdb->query($wpdb->prepare($sql, $password, $user->ID));

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function reset_password


  /**
   * @desc Help a user with a forgotten password
   * @author SDK (steve@eardish.com)
   * @date 2013-07-24
   * @param str $email - The email address of the user that forgot his password
   * @return bool - Success or failure of operation
  */
  public static function forgot_password($email) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email)) {
        throw new Exception('Need to provide email');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get user object
    $user = get_user_by('email', $email);

    // Setup temp token for user
    $token = md5(uniqid($user->user_login, TRUE));

    // Set token and timestamp in user meta
    $res = update_user_meta($user->ID, 'forgotpass_token', $token);
    $res = update_user_meta($user->ID, 'forgotpass_timestamp', time());

    // Hash password through wordpress
    $password = wp_hash_password($token);

    // Update password
    $sql = "UPDATE wp_users
            SET user_pass = %s
            WHERE ID = %d";
    //$res = $wpdb->query($wpdb->prepare($sql, $password, $user->ID));

    // Process email alert for notification
    $res = ed::send_email('forgotPass', $user->ID, 'Eardish Password Reset', $token, 'It seems you forgot your password. Copy and paste the password reset token below into the forgot password form on our home page or click the link below to take the easy way there. If you did not request this password change, just ignore this email and nothing will happen. Or email help@eardish.com if you need further assistance.', 'Reset Password', WP_HOME.'/?forgot_token='.$token, user::get_user_image(108, 108, $user->ID));

    // Return result
    return $res;
  } // end function forgot_password


  /**
   * @desc Set user login time
   * @author SDK (steve@eardish.com)
   * @date 2012-12-19
   * @param $user - The user_login
   * @return bool - Success or failure of operation
  */
  public static function set_last_login($user) {
    global $wpdb;

    // Get user login info
    $user = get_userdatabylogin($user);

    // Update user meta data
    $res = update_user_meta($user->ID, 'last_login', time());

    // Return result
    return $res;
  } // end function set_last_login


  /**
   * @desc Get user login time
   * @author SDK (steve@eardish.com)
   * @date 2012-12-19
   * @return str - Return the last login time of user
  */
  public static function get_last_login() {
    global $wpdb;

    // Get last_login from user meta
    $res = get_user_meta(get_current_user_id(), 'last_login', TRUE);

    // Return result
    return $res;
  } // end function get_last_login


  /**
   * @desc Has user logged in recently?
   * @author SDK (steve@eardish.com)
   * @date 2012-12-19
   * @return bool - Did user log in recently?
  */
  public static function is_recent_login() {
    global $wpdb;

    // Get last login time of current user
    $login = self::get_last_login();

    // Subtract login time from current time
    $diff = time() - $login;

    // Compare time diff
    $res = ($diff <= 60) ? TRUE : FALSE;

    // Return result
    return $res;
  } // end function is_recent_login


  /**
   * @desc Search users (user names)
   * @author SDK (steve@eardish.com)
   * @date 2013-04-15
   * @param str $search - Search query string
   * @return arr - Array of user ids
  */
  public static function search_users($search) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($search)) {
        throw new Exception('Need to provide search');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query usernames that match the search
    $sql = "SELECT ID
            FROM wp_users
            WHERE user_nicename LIKE '%$search%'
            OR user_login LIKE '%$search%'
            OR display_name LIKE '%$search%'
            ORDER BY ID ASC";
    $search_users = $wpdb->get_col($sql);

    // Merge, de-dup and sort the 2 results
    //$res = array_merge($search_artists, $search_songs);
    $res = $search_users;
    $res = array_unique($res);
    sort($res);

    // Return the result
    return $res;
  } // end function search_users


  /**
   * @desc Get token hash from prereg to registration
   * @author SDK (steve@eardish.com)
   * @date 2013-06-12
   * @param str $email - Email address of prereg user
   * @return str - Token hash for registration
  */
  public static function get_token($email) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email)) {
        throw new Exception('Need to provide email');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query token for email address
    $sql = "SELECT token
            FROM prereg
            WHERE email = %s
            AND expired IS NULL";
    $token = $wpdb->get_var($wpdb->prepare($sql, urldecode($email)));

    // Return the result
    return $token;
  } // end function get_token


  /**
   * @desc Expire token hash from prereg to registration
   * @author SDK (steve@eardish.com)
   * @date 2013-06-12
   * @param str $email - Email address of prereg user
   * @return bool - Success or failure of operation
  */
  public static function expire_token($email) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($email)) {
        throw new Exception('Need to provide email');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query usernames that match the search
    $sql = "UPDATE prereg SET
            expired = NOW()
            WHERE email = %s";
    $res = $wpdb->query($wpdb->prepare($sql, $email));

    // Return the result
    return ($res) ? TRUE : FALSE;
  } // end function expire_token


  /**
   * @desc Does the specified User (or current session user) have access to the CPE/Download
   * @author SDK (steve@eardish.com)
   * @date 2013-05-08
   * @param [OPT] int $id - The user ID to be checked
   * @return bool - The boolean value to authenticate user's access to CPE/Download
  */
  public static function is_user_cpe($id=NULL) {
    global $wpdb;

    // Fallback on Current User Session ID if no ID provided
    $id = ($id) ? $id : get_current_user_id();

    // If super admin then force access as true return value
    if(is_super_admin($id)) {
      $res = 'TRUE';
    } else {
      $res = get_user_meta($id, 'cpe_demo', TRUE); // Is user approved?
    }

    // Return Result
    return ($res == 'TRUE') ? TRUE : FALSE;
  } // end function is_user_cpe


	/**
	 * @desc Returns the last artist followed by the fan whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2012-01-11
	 * @param int $user_id The user to query
	 * @return obj user object
	 */
	public static function last_artist_followed($user_id) {
	  global $wpdb;
	
	  // Query artist follows from activity table
	  $sql = "SELECT content_id
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id = 5
	          ORDER BY modified DESC
	          LIMIT 1";
	  $artist_id = $wpdb->get_var($wpdb->prepare($sql, $user_id));
	
	  // Query user for artist id
	  $args = array(
	    'include'  => $artist_id,
	  );
	  $artists = get_users($args);
	
	  return $artists[0];
	} // end function last_artist_followed
	
	
	/**
	 * @desc Returns the last song rated by the user whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2013-01-20
	 * @param int $user_id The user to query
	 * @return obj Song post type object
	 */
	public static function last_song_rated($user_id) {
	  global $wpdb;
	
	  // Query song samples from activity table
	  $sql = "SELECT content_id
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id = 11
	          ORDER BY modified DESC
	          LIMIT 1";
	  $song_id = $wpdb->get_var($wpdb->prepare($sql, $user_id));
	
	  // No data?
	  if(!$song_id) {
	    return FALSE;
	  }
	  // Query post type for song id
	  $song = get_post($song_id);

	  // Get the song meta data
	  $song->meta = get_post_meta($song->ID);
	
	  // Get the artist that owns this song
	  $song->owner = get_user_by('id', $song->post_author);
	
	  // Return result
	  return $song;
	} // end function last_song_rated
	
	
	/**
	 * @desc Returns the last song added to a playlist by the user whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2012-01-11
	 * @param int $user_id The user to query
	 * @return obj Song post type object
	 */
	public static function last_song_added_to_playlist($user_id) {
	  global $wpdb;
	
	  // Query song samples from activity table
	  $sql = "SELECT content_id
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id IN(9,10)";
	  $song_id = $wpdb->get_var($wpdb->prepare($sql, $user_id));
	  //TODO: FIX QUERY TO FIT FUNCTION
	
	  // Query post type for song id
	  $args = array(
	    'post_type' => 'song',
	    'post__in'  => array($song_id),
	  );
	  $query = new WP_Query($args);

	  // Get the artist that owns this song
	  $args = array(
	    'include'  => $query->posts[0]->post_author,
	  );
	  $artists = get_users($args);
	  $artist = $artists[0];
	
	  // Push title onto user object
	  $artist->song_title = $query->posts[0]->post_title;
	
	  // Return result
	  return $artist;
	} // end function last_song_added_to_playlist
	
	
	/**
	 * @desc Returns the last profile viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2013-03-28
	 * @param int $user_id The user to query
	 * @return obj The user object
	 */
	public static function last_profile_viewed($user_id) {
	  global $wpdb;
	
	  // Query song samples from activity table
	  $sql = "SELECT content_id
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id = 4
	          ORDER BY modified DESC
	          LIMIT 1";
	  $uid = $wpdb->get_var($wpdb->prepare($sql, $user_id));

	  // Query post type for song id
	  $user = get_user_by('id', $uid);
	
	  // Return result
	  return $user;
	} // end function last_profile_viewed
	
	
	/**
	 * @desc Returns the last song listened to by the fan whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2012-05-09
	 * @param int $user_id The user to query
	 * @return obj Song post type object
	 */
	public static function last_song_listened_to($user_id) {
	  global $wpdb;
	
	  // Initialize return array
	  $res = array();
	
	  // Query song samples from activity table
	  $sql = "SELECT content_id
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id IN(9,10)
	          ORDER BY modified DESC
	          LIMIT 1";
	  $song_id = $wpdb->get_var($wpdb->prepare($sql, $user_id));
	
	  // Check for song
	  if($song_id) {
	
	    // Get song
	    $song = get_post($song_id);
	
	    // Get artist
	    $artist = get_user_by('id', $song->post_author);
	
	    // Compile artist and song data
	    $res = array(
	      'song'    => $song,
	      'artist'  => $artist,
	    );
	
	  } // end check for song
	
	  // Return result
	  return $res;
	} // end function last_song_listened_to


	/**
	 * @desc Returns the count of songs rated by the fan whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2012-05-09
	 * @param int $user_id The user to query
	 * @return int Count of songs rated by a fan
	 */
	public static function number_of_songs_rated($user_id) {
	  global $wpdb;
	
	  // Query rating activity for specified user
	  $sql = "SELECT COUNT(id) AS songs_rated
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id = 11 ";

	  // Run query
	  $res = $wpdb->get_var($wpdb->prepare($sql, $user_id));

	  // Return result
	  return $res;
	} // end function number_of_songs_rated
	
	
	/**
	 * @desc Returns the count of songs rated TODAY by the fan whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2013-03-25
	 * @param int $user_id The user to query
	 * @return int Count of songs rated by a fan today
	 */
	public static function number_of_songs_rated_today($user_id) {
	  global $wpdb;
	
	  // Reset wp_query
	  wp_reset_query();
	
	  // Today's date
	  $date = date('Y-m-d');
	
	  // Query rating activity for specified user
	  $sql = "SELECT COUNT(id) AS songs_rated
	          FROM users_activity
	          WHERE user_id = %d
	          AND action_id = 11
	          AND modified >= '$date 00:00:00'";
	
	  // Run query
	  $res = $wpdb->get_var($wpdb->prepare($sql, $user_id));
	
	  // Return result
	  return $res;
	} // end function number_of_songs_rated_today
	
	
	/**
	 * @desc Returns the count of songs rated owned by the artist whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2013-03-25
	 * @param int $user_id The user to query
	 * @return int Count of songs rated for an artist
	 */
	public static function number_of_songs_rated_owned($user_id) {
	  global $wpdb;
	
	  // Query rating activity for specified user
	  $sql = "SELECT COUNT(u.id) AS songs_rated
	          FROM users_activity AS u, wp_posts AS p
	          WHERE u.action_id = 11
	          AND u.content_id = p.ID
	          AND p.post_type = 'song'
	          AND p.post_author = %d";

	  // Run query
	  $res = $wpdb->get_var($wpdb->prepare($sql, $user_id));

	  // Return result
	  return $res;
	} // end function number_of_songs_rated_owned
	
	
	/**
	 * @desc Returns the count of songs rated TODAY owned by the artist whose profile is being viewed
	 * @author SDK (steve@eardish.com)
	 * @date 2013-03-25
	 * @param int $user_id The user to query
	 * @return int Count of songs rated for an artist
	 */
	public static function number_of_songs_rated_today_owned($user_id) {
	  global $wpdb;
	
	  // Today's date
	  $date = date('Y-m-d 00:00:00');
	
	  // Query rating activity for specified user
	  $sql = "SELECT COUNT(u.id) AS songs_rated
	          FROM users_activity AS u, wp_posts AS p
	          WHERE u.action_id = 11
	          AND u.modified >= %s
	          AND u.content_id = p.ID
	          AND p.post_type = 'song'
	          AND p.post_author = %d";
	
	  // Run query
	  $res = $wpdb->get_var($wpdb->prepare($sql, $date, $user_id));

	  // Return result
	  return $res;
	} // end function number_of_songs_rated_today_owned
	
	
	/**
	 * @desc Generate an invite token
	 * @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
	 * @date 2013-07-31
	 * @param int $id  - The id of the user
	 * @return str - The generated invite token
	 */
  public static function encode_token($id) {
    global $wpdb;

    // Convert user ID to hexadecimal value
    $hexval = dechex($id);

    // Reverse the string and convert to upper case
    $revhex = strtoupper(strrev($hexval));

    // Pad the string with 0's to force 7 digits
    $numzeros = 7 - strlen($revhex);
    $token = $revhex;
    for($i=0; $i<$numzeros; $i++) {
      $token .= "0";
    }

    // Return result
    return $token;
  } // end function encode_token


	/**
	 * @desc Validate an invite token
	 * @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
	 * @date 2013-07-31
	 * @param str $token - The invite token
	 * @return id - The user ID
	 */
  public static function decode_token($token) {
    global $wpdb;

    // Reverse string and convert from hex to dec
    $token = hexdec(strrev($token));

    // Return result
    return $token;
  } // end function decode_token


  /**
   * @desc Get youtube video ids for a specific user profile
   * @author SDK (steve@eardish.com)
   * @date 2013-09-10
   * @param [OPT] $id - ID of user (optional, defaults to current session)
   * @return arr - Array of youtube video ID's
  */
  public static function get_videos($id=NULL) {
    global $wpdb;

    // If id is provided use it, otherwise default to current session
    $id = ($id) ? $id : get_current_user_id();

    // Get videos (Serialized youtube ID list) from user meta
    $res = get_user_meta($id, 'videos', TRUE);

    // Decode serialized list into an array
    if(preg_match("~[a-zA-Z0-9]~i", $res)) {
      $videos = (preg_match("~[,]~i", $res)) ? explode(',', $res) : array($res);
    } else {
      $videos = array();
    }

    // Process youtube api data
    if(count($videos)) {
      foreach($videos as $key => $video) {
        $videos[$key] = ed::get_youtube_data($video);
      }
    }

    // Return result
    return $videos;
  } // end function get_videos


  /**
   * @desc Set youtube video ids for a specific user profile
   * @author SDK (steve@eardish.com)
   * @date 2013-09-10
   * @param $data - A serialized list (comma-seperated) of youtube video ID's
   * @param [OPT] $id - ID of user (optional, defaults to current session)
   * @return bool - Success or failure of operation
  */
  public static function set_videos($data, $id=NULL) {
    global $wpdb;

    // If id is provided use it, otherwise default to current session
    $id = ($id) ? $id : get_current_user_id();

    // Get videos (Serialized youtube ID list) from user meta
    $res = update_user_meta($id, 'videos', $data);

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function set_videos


} // end class user

