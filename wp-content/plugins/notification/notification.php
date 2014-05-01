<?php
/*
Plugin Name: Eardish Notifications
Plugin URI: 
Description: Eardish Notifications System 
Version: 2.0
Author: Steven Kornblum
*/

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');

/**
 * @desc Notification management lib
 * @author SDK (steve@eardish.com)
 * @date 2012-11-13
 */
class notification {


  /**
   * @desc Get unread notifications count for current user
   * @author SDK (steve@eardish.com)
   * @date 2012-01-24
   * @return int - Return count of unread notifications
  */
  public static function get_unread() {
    global $wpdb;

    // Current user
    $user_id = get_current_user_id();

    // Build query
    $sql = "SELECT COUNT(ID) AS unread
            FROM wp_posts
            WHERE post_type = 'notification'
            AND post_content = %d
            AND post_status = 'draft'";

    // Get # of unread notes for this user
    $unread = $wpdb->get_var($wpdb->prepare($sql, $user_id));

    // Return result
    return $unread;
  } // end function get_unread


  /**
   * @desc Get notification
   * @author SDK (steve@eardish.com)
   * @date 2012-11-15
   * @param int $id - Specifc note to query
   * @return obj - Return note object
  */
  public static function get_note($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build args
    $args = array(
      'post_type' => 'note',
      'p'         => $id,
    );

    // Run wp_query
    $query = new wp_query($args);

    // Grab note object from posts array
    $note = $query->posts[0];

    // Get note metadata
    $note->meta = get_post_meta($note->ID);

    // Return result
    return $note;
  } // end function get_note


  /**
   * @desc Push newest note(s)
   * @author SDK (steve@eardish.com)
   * @date 2012-12-15
   * @param int [OPTIONAL] $id - Which ID to compare as last pushed
   * @return arr - Return array of note single templates
  */
  public static function push_notes($id=0) {
    global $wpdb;

    // Build query
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_type = 'notification'
            AND post_content = %d
            AND id > %d
            ORDER BY id DESC";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, get_current_user_id(), $id));

    // Call BALLS API for Note single on each new id
    $notes = array();
    if(count($res)) {
      foreach($res as $key => $val) {
        $notes[] = balls::get_balls_template(array(
          'post_type' => 'notification',
          'template'  => 'single',
          'content'   => $val->ID,
        ));
      }
    }

    // Return results
    return $notes;

  } // end function push_notes


  /**
   * @desc Set a CPE note (insert new or update existing)
   * @author SDK (steve@eardish.com)
   * @date 2013-04-02
   * @param arr $args - All the metadata properties of note 
   * @param [OPTIONAL] int $id - Specifc note to query/update
   * @return mixed|bool - Return success (affected ID) or failure of operation
  */
  public static function set_note_cpe($args=array(), $id=NULL) {
    global $wpdb;

    // Delete any existing CPE notes for this User
    $sql = "DELETE FROM wp_posts
            WHERE post_type = 'notification'
            AND post_content = %d
            AND post_title = 'Download/License'";
    $wpdb->query($wpdb->prepare($sql, get_current_user_id()));

    // Call regular set note method
    $res = self::set_note($args, $id, FALSE);

    // Return result
    return $res;
  } // end function set_note_cpe


  /**
   * @desc Set note (insert new or update existing)
   * @author SDK (steve@eardish.com)
   * @date 2012-08-20
   * @param arr $args - All the metadata properties of note 
   * @param [OPTIONAL] int $id - Specifc note to query/update
   * @param [OPTIONAL] bool $email - Should an email be sent with this note? (defaults to true)
   * @return mixed|bool - Return success (affected ID) or failure of operation
  */
  public static function set_note($args=array(), $id=NULL, $email=TRUE) {
    global $wpdb;

    // common: note type, initiator id, recipient id, title, action post id, parent post id
    $type       = (isset($args['type']))      ? $args['type']       : NULL;
    $initiator  = (isset($args['initiator'])) ? $args['initiator']  : NULL;
    $recipient  = (isset($args['recipient'])) ? $args['recipient']  : NULL;
    $title      = (isset($args['title']))     ? $args['title']      : NULL;
    $post       = (isset($args['post']))      ? $args['post']       : NULL;
    $parent     = (isset($args['parent']))    ? $args['parent']     : NULL;
    $status     = (isset($args['status']))    ? $args['status']     : 'draft';
    $song       = (isset($args['song']))      ? $args['song']       : NULL;

    // Set meta array
    $meta = array(
      'type'    => $type,
      'post'    => $post,
      'parent'  => $parent,
      'song'    => $song,
    );

    // Create post object
    $note = array(
      'post_type'     => 'notification',
      'post_status'   => $status,
      'post_author'   => $initiator,
      'post_title'    => wp_strip_all_tags($title),
      'post_content'  => $recipient,
    );

    // Insert the post
    if($id) {
      $note['ID'] = $id;
      $res = wp_update_post($note);
    } else {
      $res = wp_insert_post($note, TRUE);
    }

    // If meta data is passed, go set it
    if(count($meta)) {
      foreach($meta as $key => $val) {
        self::set_note_meta($res, $key, $val);
      }
    }
    
    // Process email alert for notification
	if($email) {
	    $email = ed::send_email('email1', $recipient, wp_strip_all_tags($title), '', 'You have been dished!', 'Read More', WP_HOME, user::get_user_image(108, 108, $initiator));
	}

    // Return result
    return $res;
  } // end function set_note


  /**
   * @desc Create/Edit a note meta key/value pair 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-02
   * @param int $post_id The id of the note being referenced in the meta data 
   * @param str $meta_key The meta key 
   * @param str $meta_value The meta value 
   * @return int Returns the id of the row affected (insert, replace or update) 
  */
  public static function set_note_meta($post_id, $meta_key, $meta_value) {
    global $wpdb;

    // Convert boolean values binary bit
    if(is_bool($meta_value)) {
      $meta_value = ($meta_value) ? 1 : 0;
    }
 
    // Build query
    $res = update_post_meta($post_id, $meta_key, $meta_value); 

    // Return result
    return $res;
  } // end function set_note_meta


  /**
   * @desc Mark note as read
   * @author SDK (steve@eardish.com)
   * @date 2013-04-02
   * @param int $id - Last note seen
   * @return bool - Return success or failure of operation
  */
  public static function mark_note($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Update all notifications to current user from provided id and backwards to be marked as read
    $sql = "UPDATE wp_posts SET
            post_status = 'publish'
            WHERE post_content = %d
            AND id <= %d";
    $res = $wpdb->query($wpdb->prepare($sql, get_current_user_id(), $id));

    // Return result
    return $res;
  } // end function mark_note


  /**
   * @desc Delete note
   * @author SDK (steve@eardish.com)
   * @date 2012-09-02
   * @param int $id - Specifc note to delete 
   * @return bool - Return success or failure of operation
  */
  public static function delete_note($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Delete note
    $res = wp_delete_post($id);

    // Return result
    return $res;
  } // end function delete_note


} // end class notification

