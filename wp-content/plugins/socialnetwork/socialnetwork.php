<?php
/*
Plugin Name: Eardish Social Network Lib 
Plugin URI:
Description: Eardish Social Network Activity Library
Version: 2.0
Author: Steven Kornblum
*/


// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');


/**
 * @desc Eeadish Social Network lib
 * @author SDK (steve@eardish.com)
 * @date 2012-08-01
 */
class socialnetwork {


  /**
   * @desc Get comments based on given criteria
   * @author SDK (steve@eardish.com)
   * @date 2012-08-09
   * @param arr $args - Any criteria to filter results
   * @return arr - Return an array of comments that meet the criteria given 
  */
  public static function get_comments($args=array()) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($args['parent'])) {
        throw new Exception('Need to provide parent');
      }
      $parent = $args['parent'];
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query published comments that belong to the specified parent post ID and order by date desc
    $query = wp_query(array(
      'post_type'   => 'comment',
      'post_parent' => $parent,
      'post_status' => 'publish',
      'orderby'     => 'date',
      'order'       => 'ASC',
    ));

    // Get posts array for comments
    $comments = $query->posts;

    // Loop through and parse comment data
    if(count($comments)) {
      foreach($comments as $key => $comment) {

        // Clean up comment data
        $data = trim($comment->post_content);
        $data = preg_replace("~\<.*?\>~", '', $data);
        $data = preg_replace("~\<\/.*?\>~", '', $data);
        $comments[$key]->post_content = $data;

        // Get metadata and owner data for each comment
        $comments[$key]->meta = get_metadata('comment', $comment->comment_ID);
        $comments[$key]->owner = get_user_by('id', $comment->user_id);
      }
    }

    // Return results
    return $comments;
  } // end function get_comments


  /**
   * @desc Push newest comments related to parent post ID
   * @author SDK (steve@eardish.com)
   * @date 2013-04-25
   * @param int $parent - Which post ID for the thread
   * @param int [OPTIONAL] $id - Which ID to compare as last pushed
   * @return arr - Return array of comment single templates
  */
  public static function push_comments($parent, $id=0) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($parent)) {
        throw new Exception('Need to provide parent');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process parent slug or ID
    if($parent == '/profile/') {
      $parent = get_current_user_id();
    } else {
      $slug = preg_replace("~\/profile\/~", '', $parent);
      $parent = (is_numeric($slug)) ? $slug : ed::get_post_id_by_slug($slug);
    }

    // Build query
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_type = 'comment'
            AND post_parent = %d
            AND id > %d
            ORDER BY post_date ASC";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, $parent, $id));

    // Call BALLS API for comment single on each new id
    $comments = array();
    if(count($res)) {
      foreach($res as $key => $val) {
        $comments[] = balls::get_balls_template(array(
          'post_type' => 'comment',
          'template'  => 'single',
          'content'   => $val->ID,
          'hide'      => TRUE,
        ));
      }
    }

    // Return results
    return $comments;

  } // end function push_comments


  /**
   * @desc Post a comment
   * @author SDK (steve@eardish.com)
   * @date 2012-08-09
   * @param int $parent - The post type ID that the comment belongs to 
   * @param str $comment - The comment text
   * @return int - Return the new comment ID
  */
  public static function set_comment($parent, $comment) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($parent) || !isset($comment)) {
        throw new Exception('Need to provide parent and comment');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }
    // User ID from session
    $id = get_current_user_id();

    // Clean up shout data
    $comment = trim($comment);
    $comment = preg_replace("~\<.*?\>~", '', $comment);
    $comment = preg_replace("~\<\/.*?\>~", '', $comment);

    // Create post object
    $post = array(
      'post_type'     => 'comment',
      'post_status'   => 'publish',
      'post_parent'   => $parent,
      'post_author'   => $id,
      'post_content'  => $comment,
    );

    // Insert the post
    $res = wp_insert_post($post, TRUE);

    // Track activity on a successful shout
    if($res) {
      reward::track_activity($id, $res, 8);
    }

    // Send note if successful
    if($res) {
      $post = get_post($parent);
      if($post->post_author != get_current_user_id()) {
        $note = notification::set_note(array(
          'type'      => 'comment',
          'initiator' => get_current_user_id(),
          'recipient' => $post->post_author,
          'title'     => 'Comment',
          'status'    => 'draft',
        ));
      }
    }

    // Return result
    return $res;
  } // end function set_comment


  /**
   * @desc Delete a comment
   * @author SDK (steve@eardish.com)
   * @date 2012-08-09
   * @param int $id - The comment ID to delete 
   * @return bool - Return the success or faliure of the operation 
  */
  public static function delete_comment($id) {
    global $wpdb;

    // Get comment post
    $comment = get_post($id);

    // Check for perms
    if(!is_super_admin() && $comment->user_id != get_current_user_id()) {
      return FALSE;
    }

    // Delete comment post
    $res = wp_delete_post($id);

    // Return result
    return $res;
  } // end function delete_comment


  /**
   * @desc Push newest following (3p) shout(s)
   * @author SDK (steve@eardish.com)
   * @date 2013-04-23
   * @param int $parent - Which user ID for the wall
   * @param int [OPTIONAL] $id - Which ID to compare as last pushed
   * @return arr - Return array of shout single templates
  */
  public static function push_following_shouts($parent, $id=0) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($parent)) {
        throw new Exception('Need to provide parent');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process parent slug or ID
    if($parent == '/profile/') {
      $parent = get_current_user_id();
    } else {
      $slug = preg_replace("~\/profile\/~", '', $parent);
      $parent = (is_numeric($slug)) ? $slug : user::get_user_id_by_slug($slug);
    }

    // Get list of user IDs that the specified user is following
    $network = self::get_followees($parent);

    // Build query
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_type = 'shout'
            AND post_parent IN(%s)
            AND id > %d
            ORDER BY ID DESC";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, $network, $id));

    // Call BALLS API for shout single on each new id
    $shouts = array();
    if(count($res)) {
      foreach($res as $key => $val) {
        $shouts[] = balls::get_balls_template(array(
          'post_type' => 'shout',
          'template'  => 'single',
          'content'   => $val->ID,
          'hide'      => TRUE,
        ));
      }
    }

    // Return results
    return $shouts;

  } // end function push_following_shouts


  /**
   * @desc Push newest shout(s)
   * @author SDK (steve@eardish.com)
   * @date 2012-01-15
   * @param int $parent - Which user ID for the wall
   * @param int [OPTIONAL] $id - Which ID to compare as last pushed
   * @return arr - Return array of shout single templates
  */
  public static function push_shouts($parent, $id=0) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($parent)) {
        throw new Exception('Need to provide parent');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process parent slug or ID
    if($parent == '/profile/') {
      $parent = get_current_user_id();
    } else {
      $slug = preg_replace("~\/profile\/~", '', $parent);
      $parent = (is_numeric($slug)) ? $slug : user::get_user_id_by_slug($slug);
    }

    // Build query
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_type = 'shout'
            AND post_parent = %d
            AND id > %d
            ORDER BY ID DESC";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, $parent, $id));

    // Call BALLS API for shout single on each new id
    $shouts = array();
    if(count($res)) {
      foreach($res as $key => $val) {
        $shouts[] = balls::get_balls_template(array(
          'post_type' => 'shout',
          'template'  => 'single',
          'content'   => $val->ID,
          'hide'      => TRUE,
        ));
      }
    }

    // Return results
    return $shouts;

  } // end function push_shouts


  /**
   * @desc Get shouts based on filter criteria provided
   * @author SDK (steve@eardish.com)
   * @date 2012-08-13
   * @param int $id - The user ID for the shouts
   * @return arr - Return an array of shouts that meet the criteria given 
  */
  public static function get_shouts($id=NULL) {
    global $wpdb;

    // User ID of shout recipient
    $id = ($id) ? $id : get_current_user_id();

    // Set up filters
    $args = array(
      'post_type'       => 'shout',
      'post_parent'     => $id,
      'orderby'         => 'date',
      'order'           => 'DESC',
      'posts_per_page'  => 10,
    );
    
    // Run query
    $query = new WP_Query($args);

    // Pull result list of shouts
    $shouts = $query->posts;

    // Clean up shout data
    if(count($shouts)) {
      foreach($shouts as $key => $shout) {
        $data = trim($shout->post_content);
        $data = preg_replace("~\<.*?\>~", '', $data);
        $data = preg_replace("~\<\/.*?\>~", '', $data);
        $shouts[$key]->post_content = $data;
      }
    }

    // Return results
    return $shouts;
  } // end function get_shouts


  /**
   * @desc Get followees of specified user
   * @author SDK (steve@eardish.com)
   * @date 2013-04-23
   * @param int $id - The user ID for the shouts
   * @return str - Return comman seperated list of distinct user ids
  */
  public static function get_followees($id=NULL) {
    global $wpdb;

    // User ID of shout recipient
    $id = ($id) ? $id : get_current_user_id();

    // Get list of user ID's of those the specified user is following
    $sql = "SELECT DISTINCT post_content
            FROM wp_posts
            WHERE post_type = 'follow'
            AND post_author = %d
            ORDER BY post_content";
    $followees = $wpdb->get_col($wpdb->prepare($sql, $id));
    $network = "'".implode("','", $followees)."'";

    // Return results
    return $network;
  } // end function get_followees


  /**
   * @desc Get network shouts based on following (3p) 
   * @author SDK (steve@eardish.com)
   * @date 2013-04-23
   * @param int $id - The user ID for the shouts
   * @return arr - Return an array of shouts that meet the criteria given 
  */
  public static function get_following_shouts($id=NULL) {
    global $wpdb;

    // User ID of shout recipient
    $id = ($id) ? $id : get_current_user_id();

    // Get list of user ID's of those the specified user is following
    $network = self::get_followees($id);

    // Get list of shout IDs that were written by those the specified user is following
    $sql = "SELECT DISTINCT ID
            FROM wp_posts
            WHERE post_type = 'shout'
            AND post_author IN($network)
            ORDER BY post_date DESC
            LIMIT 10";
    $res = $wpdb->get_results($sql);

    // Return results
    return $res;
  } // end function get_following_shouts


  /**
   * @desc Set shout
   * @author SDK (steve@eardish.com)
   * @date 2012-09-01
   * @param str $data - The content of the shout
   * @param [OPTIONAL] int $id - The id of the intended recipient
   * @return bool - Success or failure of operation
  */
  public static function set_shout($data, $id=NULL) {
    global $wpdb;

    // Is ID current?
    $id = ($id) ? $id : get_current_user_id();

    // Process parent slug or ID
    if($id == '/profile/') {
      $id = get_current_user_id();
    } else {
      $slug = preg_replace("~\/profile\/~", '', $id);
      $id = (is_numeric($slug)) ? $slug : user::get_user_id_by_slug($slug);
    }

    // Clean up shout data
    $data = trim($data);
    $data = preg_replace("~\<.*?\>~", '', $data);
    $data = preg_replace("~\<\/.*?\>~", '', $data);

    // Create post object
    $shout = array(
      'post_type'     => 'shout',
      'post_status'   => 'publish',
      'post_parent'   => $id,
      'post_author'   => get_current_user_id(),
      'post_content'  => $data,
    );

    // Insert the post
    $res = wp_insert_post($shout, TRUE);

    // Track activity on a successful shout
    if($res) {
      reward::track_activity($id, $res, 7);
      if($id != get_current_user_id()) {
        $note = notification::set_note(array(
          'type'      => 'shout',
          'initiator' => get_current_user_id(),
          'recipient' => $id,
          'parent'    => $res,
          'title'     => 'Shout',
          'status'    => 'draft',
        ));
      }
      $res = balls::get_balls_template(array(
        'post_type' => 'shout',
        'template'  => 'single',
        'content'   => $res,
      ));
    }

    // Return result
    return $res;
  } // end function set_shout


  /**
   * @desc Delete shout
   * @author SDK (steve@eardish.com)
   * @date 2012-09-01
   * @param int $id - The id of the shout to delete
   * @return bool - Success or failure of operation
  */
  public static function delete_shout($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Delete shout
    $res = wp_delete_post($id);
    
    // Return result
    return $res;
  } // end function delete_shout


  /**
   * @desc Get dig count of any post of any type
   * @author SDK (steve@eardish.com)
   * @date 2012-12-18
   * @param int $id - The id of the post to query
   * @return int - Number of digs for post id
  */
  public static function count_digs($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT COUNT(*) 
            FROM wp_posts
            WHERE post_type = 'dig'
            AND post_content = %d";

    // Check for current dig count
    $digs = $wpdb->get_var($wpdb->prepare($sql, $id));

    // Return result
    return $digs;
  } // end function count_digs


  /**
   * @desc Get dig count of any post of any type
   * @author SDK (steve@eardish.com)
   * @date 2013-02-13
   * @param int $id - The id of the post to query
   * @return arr - Array of digs on the post ID 
  */
  public static function get_digs($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT * 
            FROM wp_posts
            WHERE post_type = 'dig'
            AND post_content = %d";

    // Check for current dig count
    $digs = $wpdb->get_results($wpdb->prepare($sql, $id));

    // Loop through digs and populate user and post info
    if(count($digs)) {
      foreach($digs as $key => $dig) {
        $digs[$key]->owner = get_user_by('id', $dig->author_id);
        $digs[$key]->parent = get_post($dig->post_content);
      }
    }

    // Return result
    return $digs;
  } // end function get_digs


  /**
   * @desc Get a dig object (current user to post id)
   * @author SDK (steve@eardish.com)
   * @date 2012-12-18
   * @param int $id - The id of the post to query
   * @return obj - The dig object
  */
  public static function get_dig($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'dig'
            AND post_content = %d
            AND post_author = %d";

    // Get dig by the current user for the post ID
    $dig = $wpdb->get_row($wpdb->prepare($sql, $id, get_current_user_id()));

    // Get user info
    $dig->owner = get_user_by('id', $dig->author_id);

    // Get post info
    $dig->parent = get_post($dig->post_content);

    // Return result
    return $dig;
  } // end function get_dig

  /**
   * @desc Has the current user dug the post?
   * @author SDK (steve@eardish.com)
   * @date 2012-12-18
   * @param int $id - The id of the post to query
   * @return bool - Did they dig it yet?
  */
  public static function is_dug($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // If id is a slug, translate it
    if(preg_match("~[a-zA-Z]~", $id)) {
      $id = ed::get_post_id_by_slug($id);
    }

    // Build query
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'dig'
            AND post_content = %d
            AND post_author = %d";

    // Check for current dig count by the current user for the post ID
    $digs = $wpdb->get_col($wpdb->prepare($sql, $id, get_current_user_id()));

    // Calculate
    $dug = (count($digs)) ? TRUE : FALSE;

    // Return result
    return $dug;
  } // end function is_dug


  /**
   * @desc Add a dig by current user to the specified post ID 
   * @author SDK (steve@eardish.com)
   * @date 2012-12-18
   * @param int $id - The id of the post to dig
   * @return mixed(arr|bool) - Dig count/array or Success or failure of operation
  */
  public static function set_dig($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // If id is a slug, translate it
    if(preg_match("~[a-zA-Z]~", $id)) {
      $id = ed::get_post_id_by_slug($id);
    }

    // Setup args
    $args = array(
      'post_type'     => 'dig',
      'post_author'   => get_current_user_id(),
      'post_content'  => $id,
    );

    // Insert the dig
    $res = wp_insert_post($args);

    // Get post object of referenced ID
    $post = get_post($id);

    // If successful, send a note to the parent post owner
    $note = notification::set_note(array(
      'type'      => 'dig',
      'initiator' => get_current_user_id(),
      'recipient' => $post->post_author,
      'title'     => 'Dig',
      'post'      => $res,
      'parent'    => $id,
      'status'    => 'draft',
    ));

    // Get new dig count/array
    $digs = self::count_digs($id);

    // Return result
    return ($res) ? $digs : $res;
  } // end function set_dig


  /**
   * @desc Remove a dig by current user to the specified post ID 
   * @author SDK (steve@eardish.com)
   * @date 2012-12-18
   * @param int $id - The id of the post to dig
   * @return mixed(arr|bool) - Dig count/array or Success or failure of operation
  */
  public static function delete_dig($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // If id is a slug, translate it
    if(preg_match("~[a-zA-Z]~", $id)) {
      $id = ed::get_post_id_by_slug($id);
    }

    // Get dig ID
    $dig = self::get_dig($id);

    // Delete dig
    $res = wp_delete_post($dig->ID, TRUE);

    // Delete any and all metadata for this dig
    $sql = "DELETE FROM wp_postmeta
            WHERE post_id = %d";
    $wpdb->query($wpdb->prepare($sql, $dig->ID));

    // Get new dig count/array
    $digs = self::count_digs($id);

    // Return result
    return ($res) ? $digs : $res;
  } // end function delete_dig


  /**
   * @desc Get flag count of any post of any type
   * @author SDK (steve@eardish.com)
   * @date 2012-09-01
   * @param int $id - The id of the post to query
   * @return int - Count of flags
  */
  public static function get_flag($id) {
    global $wpdb;

    // Initialize meta key
    $key = 'flag';

    // Check for current flag count
    $flags = get_post_meta($id, $key, TRUE);

    // Return result
    return $flags;
  } // end function get_flag


  /**
   * @desc Add a flag count to any post of any post type
   * @author SDK (steve@eardish.com)
   * @date 2012-09-01
   * @param int $id - The id of the post to flag
   * @param bool $remove - The id of the post to flag
   * @return mixed(int|bool) - Flag count or Success or failure of operation
  */
  public static function set_flag($id, $remove=FALSE) {
    global $wpdb;

    // Initialize meta key
    $key = 'flag';

    // Check for current flag count
    $flags = get_flags($id);

    // Increment for current action
    $flags = $flags + 1;

    // If count exists, then increment, otherwise create flag meta key
    if($flags > 1) {
      $res = update_post_meta($id, $key, $flags);
    } else {
      $res = add_post_meta($id, $key, 1, TRUE);
    }

    // Return result
    return ($res) ? $flags : $res;
  } // end function set_flag


  /**
   * @desc Get a list of all a user's follow relationships
   * @author SDK (steve@eardish.com)
   * @date 2012-01-14
   * @param [OPTIONAL] int $id - The id of the related user
   * @return arr - A list of follow objects
  */
  public static function get_relationships($id=NULL) {
    global $wpdb;

    // Process related user ID arg
    $id = ($id) ? $id : get_current_user_id();

    // Build query
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'follow'
            AND (
              post_content = %s
              OR post_author = %d
            )";

    // Get list of follow relationships
    $follows = $wpdb->get_results($wpdb->prepare($sql, $id, $id));

    // Loop through follow records and build unique array of relationships
    $relationships = array();
    if(count($follows)) {
      foreach($follows as $follow) {
        $user_id = ($follow->post_author == $id) ? $follow->post_content : $follow->post_author;
        $follow->owner = get_user_by('id', $user_id);
        $relationships[$user_id] = $follow->owner->display_name;
      }
    }

    // Sort by username
    asort($relationships);

    // Return result
    return $relationships;
  } // end function get_relationships


  /**
   * @desc Get a follow object (current user to parent user id)
   * @author SDK (steve@eardish.com)
   * @date 2012-01-14
   * @param int $id - The id of the related user
   * @param [OPT] bool $pending - Whether or not to include pending friendships? (default false, exclude pending)
   * @return bool - Is there a relationship? 
  */
  public static function get_follow($id, $pending=FALSE) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // If id is a slug, translate it
    if(preg_match("~[a-zA-Z]~", $id)) {
      $id = user::get_user_id_by_slug($id);
    }

    // Build query
    $sql = "SELECT COUNT(ID) AS follow
            FROM wp_posts
            WHERE post_type = 'follow'
            AND post_content = %d
            AND post_author = %d ";

    // If pending is excluded, force post_status condition to publish
    if(!$pending) {
      $sql .= "AND post_status = 'publish'";
    }

    // Get follow by the current user for the post ID
    $follow = $wpdb->get_var($wpdb->prepare($sql, $id, get_current_user_id()));

    // Clean up duplicate follow records (caused by legacy buggy code)
    if($follow > 1) {
      $diff = $follow - 1;
      $sql = "DELETE FROM wp_posts
              WHERE post_type = 'follow'
              AND post_content = %d
              AND post_author = %d
              AND post_status = 'publish'
              ORDER BY post_date DESC
              LIMIT $diff";
      $wpdb->query($wpdb->prepare($sql, $id, get_current_user_id()));
    }

    // Return result
    return ($follow) ? TRUE : FALSE;
  } // end function get_follow


  /**
   * @desc Request/Respond to a follow
   * @author SDK (steve@eardish.com)
   * @date 2012-12-21
   * @param str $type - The type of follow action (request, accept, reject)
   * @param int $id - The id of the user to send a following request 
   * @return bool - The success or failure of the operation
  */
  public static function set_follow($type, $id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($type)) {
        throw new Exception('Need to provide type');
      }
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // If id is a slug, translate it
    if(preg_match("~[a-zA-Z]~", $id)) {
      $id = user::get_user_id_by_slug($id);
    }

    // Is this a request or a reponse or a removal?
    switch($type) {
      case 'request':
        if($id == get_current_user_id() || self::get_follow($id, TRUE)) {
          return FALSE;
        }
        // are both parties fans?
        $profile1 = get_user_meta(get_current_user_id(), 'profile_type', TRUE);
        $profile2 = get_user_meta($id, 'profile_type', TRUE);
        if($profile1 != 'fan' || $profile2 != 'fan') {
          $status = 'publish';
          $note_type = 'follow';
          $note_title = 'Follow';
          $action_id = 5;
        } else {
          $status = 'draft';
          $note_type = 'follow-request';
          $note_title = 'Friend Request';
          $action_id = 6;
        }
        $res = wp_insert_post(array(
          'post_type'     => 'follow',
          'post_author'   => get_current_user_id(),
          'post_content'  => $id,
          'post_status'   => $status,
        ));
        if($res) {
          $note = notification::set_note(array(
            'type'      => $note_type,
            'initiator' => get_current_user_id(),
            'recipient' => $id,
            'title'     => $note_title,
            'status'    => 'draft',
          ));
          reward::track_activity(get_current_user_id(), $id, $action_id);
        }
        $user = get_user_by('id', $id);
        $res = $user->display_name;
        break;
      case 'accept':
        $sql = "UPDATE wp_posts SET
                post_status = 'publish'
                WHERE post_type = 'follow'
                AND post_content = %d
                AND post_author = %d";
        $wpdb->query($wpdb->prepare($sql, get_current_user_id(), $id));
        wp_insert_post(array(
          'post_type'     => 'follow',
          'post_content'  => $id,
          'post_author'   => get_current_user_id(),
          'post_status'   => 'publish',
        ));
        $sql = "DELETE FROM wp_posts
                WHERE post_type = 'notification'
                AND post_title = 'Friend Request'
                AND post_author = %d
                AND post_content = %d";
        $wpdb->query($wpdb->prepare($sql, $id, get_current_user_id()));
        $sql = "DELETE FROM wp_posts
                WHERE post_type = 'notification'
                AND post_title = 'Friend Request'
                AND post_author = %d
                AND post_content = %d";
        $wpdb->query($wpdb->prepare($sql, get_current_user_id(), $id));
        notification::set_note(array(
          'type'      => 'friend-accept',
          'initiator' => get_current_user_id(),
          'recipient' => $id,
          'title'     => 'Friend Accept',
          'status'    => 'publish',
        ));
        notification::set_note(array(
          'type'      => 'friend-accept',
          'initiator' => $id,
          'recipient' => get_current_user_id(),
          'title'     => 'Friend Accept',
          'status'    => 'publish',
        ));
        reward::track_activity(get_current_user_id(), $id, 6);
        break;
      case 'reject':
      case 'remove':
        $sql = "DELETE FROM wp_posts
                WHERE post_type = 'follow'
                AND post_content = %d
                AND post_author = %d";
        $res = $wpdb->query($wpdb->prepare($sql, $id, get_current_user_id()));
        $res = ($res) ? TRUE : FALSE;
        $sql = "DELETE FROM wp_posts
                WHERE post_type = 'notification'
                AND post_title = 'Friend Request'
                AND post_author = %d
                AND post_content = %d";
        $delete = $wpdb->query($wpdb->prepare($sql, $id, get_current_user_id()));
        $sql = "DELETE FROM wp_posts
                WHERE post_type = 'follow'
                AND post_content = %d
                AND post_author = %d";
        $res = $wpdb->query($wpdb->prepare($sql, get_current_user_id(), $id));
        $res = ($res) ? TRUE : FALSE;
        $sql = "DELETE FROM wp_posts
                WHERE post_type = 'notification'
                AND post_title = 'Friend Request'
                AND post_author = %d
                AND post_content = %d";
        $delete = $wpdb->query($wpdb->prepare($sql, get_current_user_id(), $id));
        break;
    }

    // Return result
    return $res;
  } // end function set_follow


  /**
   * @desc Delete a follow (reject or remove)
   * @author SDK (steve@eardish.com)
   * @date 2012-12-21
   * @param int $id - The id of the follow post to delete
   * @return bool - The success or failure of the operation
  */
  public static function delete_follow($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Delete follow record
    $res = wp_delete_post($id);

    // Return result
    return $res;
  } // end function delete_follow


} // end class socialnetwork 

