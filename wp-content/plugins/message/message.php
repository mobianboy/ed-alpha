<?php
/*
Plugin Name: Eardish messages
Plugin URI: 
Description: Eardish messages System 
Version: 2.0
Author: Steven Kornblum
*/

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');

 

/**
 * @desc message management lib
 * @author SDK (steve@eardish.com)
 * @date 2012-12-13
 */
class message {


  /**
   * @desc Get unread messages count for current user
   * @author SDK (steve@eardish.com)
   * @date 2012-01-24
   * @return int - Return count of unread messages
  */
  public static function get_unread() {
    global $wpdb;

    // Current user
    $user_id = get_current_user_id();

    // Build query
    $sql = "SELECT COUNT(p.ID) AS unread
            FROM wp_postmeta AS pm, wp_posts AS p
            WHERE pm.post_id = p.ID
            AND p.post_type = 'message'
            AND (
              p.post_title LIKE '$user_id'
              OR p.post_title LIKE '$user_id,%'
              OR p.post_title LIKE '%,$user_id,%'
              OR p.post_title LIKE '%,$user_id'
            )
            AND pm.meta_key = 'read'
            AND pm.meta_value NOT LIKE '$user_id'
            AND pm.meta_value NOT LIKE '$user_id,%'
            AND pm.meta_value NOT LIKE '%,$user_id,%'
            AND pm.meta_value NOT LIKE '%,$user_id'";

    // Get # of unread notes for this user
    $unread = $wpdb->get_var($sql);

    // Return result
    return $unread;
  } // end function get_unread


  /**
   * @desc Get a conversation by its orignal msg ID
   * @author SDK (steve@eardish.com)
   * @date 2012-01-15
   * @param int $id - The id of the orignal message in the new conversation
   * @return str - Return html of conversation <li>
  */
  public static function get_convo($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }
    
    // Sort parent list
    $users = explode(',', $id);
    $users = array_unique($users);
    sort($users, SORT_NUMERIC);
    $id = implode(',', $users);
  
    // Initialize data array
    $thread = array();

    // Get latest message in conversation
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'message'
            AND post_title = %s
            ORDER BY ID ASC
            LIMIT 1";
    $thread['last'] = $wpdb->get_row($wpdb->prepare($sql, $id));
      
    // Append meta data for latest message
    $thread['last']->meta = get_post_meta($thread['last']->ID);

    // Get participants list
    $participants = array();
    $users = explode(',', $id);
    if(count($users)) {
      foreach($users as $user) {
        if($user != get_current_user_id()) {
          $participant = get_user_by('id', $user);
          $participants[] = $participant->display_name;
        }
      }
    }
    $thread['participants'] = implode(',', $participants);

    // Get # of unread messages in conversation
    $thread['unread'] = 0;

    // Start output buffer
    ob_start();

    // Template source include and grab from output buffer into $data
    $source = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/score/tpl/message/convo.php';
    include($source);
    $data .= ob_get_contents();

    // Flush output buffer
    ob_end_clean();

    // Return result
    return $data;
  } // end function get_convo


  /**
   * @desc Get list of conversations
   * @author SDK (steve@eardish.com)
   * @date 2012-01-15
   * @return arr - Return list of conversations (latest message in each?)
  */
  public static function get_conversations() {
    global $wpdb;

    // Get viewing user
    $user_id = get_current_user_id();

    // Get unique conversation threads that involve this user
    $threads = self::get_user_convos();

    // Initialize return data
    $convos = array();

    // Loop through conversation threads
    if(count($threads)) {
      foreach($threads as $key => $thread) {

        // Initialize data array
        $convo = array();

        // Get # of not hidden
        $sql = "SELECT COUNT(p.ID) AS unhidden 
                FROM wp_postmeta AS pm, wp_posts AS p
                WHERE pm.post_id = p.ID
                AND p.post_type = 'message'
                AND p.post_title = '$thread'
                AND pm.meta_key = 'hide'
                AND pm.meta_value NOT LIKE '$user_id'
                AND pm.meta_value NOT LIKE '$user_id,%'
                AND pm.meta_value NOT LIKE '%,$user_id,%'
                AND pm.meta_value NOT LIKE '%,$user_id'";
        $not_hidden = $wpdb->get_var($sql);

        // Skip this convo if no messages are visible
        if(!$not_hidden) {
          continue;
        }

        // Get # of unread messages in conversation
        $sql = "SELECT COUNT(p.ID) AS unread 
                FROM wp_postmeta AS pm, wp_posts AS p
                WHERE pm.post_id = p.ID
                AND p.post_type = 'message'
                AND p.post_title = '$thread'
                AND pm.meta_key = 'read'
                AND pm.meta_value NOT LIKE '$user_id'
                AND pm.meta_value NOT LIKE '$user_id,%'
                AND pm.meta_value NOT LIKE '%,$user_id,%'
                AND pm.meta_value NOT LIKE '%,$user_id'";
        $convo['unread'] = $wpdb->get_var($sql);

        // Get latest message in conversation
        $sql = "SELECT *
                FROM wp_posts
                WHERE post_type = 'message'
                AND post_title = %s
                ORDER BY ID DESC
                LIMIT 1";
        $convo['last'] = $wpdb->get_row($wpdb->prepare($sql, $thread));
        
        // Append meta data for latest message
        $convo['last']->meta = get_post_meta($convo->last->ID);

        // Get participants list
        $participants = array();
        $users = explode(',', $thread);
        if(count($users)) {
          foreach($users as $user) {
            if($user != get_current_user_id()) {
              $participant = get_user_by('id', $user);
              $participants[] = $participant->display_name;
            }
          }
        }
        $convo['participants'] = implode(',', $participants);

        // Assign modified iteration back to master array
        $convos[$thread] = $convo;

      }
    } // end loop through conversation threads

    // Return result
    return $convos;
  } // end function get_conversations


  /**
   * @desc Push messages that involve current user and are unread by current user
   * @author SDK (steve@eardish.com)
   * @date 2012-12-15
   * @return arr - Return array of message single templates
  */
  public static function push_messages() {
    global $wpdb;

    // Initialize return data
    $convos = array();

    // Get viewing user
    $user_id = get_current_user_id();

    // Get unique conversation threads that involve this user
    $threads = self::get_user_convos();

    // Loop through conversation threads
    if(count($threads)) {
      foreach($threads as $key => $thread) {

        // Get messages in conversation
        $sql = "SELECT *
                FROM wp_posts
                WHERE post_type = 'message'
                AND post_title = %s
                ORDER BY ID DESC";
        $messages = $wpdb->get_results($wpdb->prepare($sql, $thread));
        
        // Loop through each message in thread and if message is not read yet by current user, then add to convos array
        if(count($messages)) {
          foreach($messages as $message) {
            if(!self::is_marked($message->ID, 'read')) {
              $msg = balls::get_balls_template(array(
                'post_type' => 'message',
                'template'  => 'single',
                'content'   => $message->ID,
              ));
              $convos[$message->post_title] .= $msg;
            }
          }
        } // end loop through each message in thread
        
      }
    } // end loop through conversation threads

    // Return results
    return $convos;
  } // end function push_messages


  /**
   * @desc Set message (insert new or update existing)
   * @author SDK (steve@eardish.com)
   * @date 2012-12-20
   * @param str $parent - A comma-seperated list of User ID's (the users involved in the thread)
   * @param str $data - The content of the message
   * @return str - Return message single template of new post
  */
  public static function set_message($parent, $data) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($parent) || !isset($data)) {
        throw new Exception('Need to provide parent and data');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Sort parent list
    $users = explode(',', $parent);
    $users = array_unique($users);
    sort($users, SORT_NUMERIC);
    $parent = implode(',', $users);

    // Create post object
    $post = array(
      'post_type'     => 'message',
      'post_author'   => get_current_user_id(),
      'post_content'  => $data,
      'post_title'   => $parent,
      'post_status'   => 'publish',
    );

    // Insert the post
    $res = wp_insert_post($post, TRUE);

    // Mark as read
    $read = self::mark_message('read', 'message', $res);

    // Make hide metakey with empty value
    $hide = update_post_meta($res, 'hide', '');

    if(is_numeric($res)) {

      // Get message single template for new post
      $message = balls::get_balls_template(array(
        'post_type' => 'message',
        'template'  => 'single',
        'content'   => $res,
      ));

      // Process email alert for notification
      $author = get_user_by('id', get_current_user_id());
      $subject = $author->display_name." sent a message";
      if(count($users)) {
        foreach($users as $user) {
          if($user != get_current_user_id()) {
            $email = ed::send_email('email1', $user, $subject, $author->display_name, 'You have been dished!', 'Read More', WP_HOME, user::get_user_image(108, 108, get_current_user_id()));
          }
        }
      }

    } else {
      $message = $res;
    }

    // Return result
    return $message;
  } // end function set_message


  /**
   * @desc Get list of convos involving the current user
   * @author SDK (steve@eardish.com)
   * @date 2012-01-19
   * @return arr - Return array of convo threads
  */
  public static function get_user_convos() {
    global $wpdb;

    // Set current user id from wp session
    $user_id = get_current_user_id();

    // Get unique conversation threads that involve this user
    $sql = "SELECT DISTINCT post_title
            FROM wp_posts
            WHERE post_type = 'message'
            AND (
              post_title LIKE '$user_id'
              OR post_title LIKE '$user_id,%'
              OR post_title LIKE '%,$user_id,%'
              OR post_title LIKE '%,$user_id'
            )";
    $threads = $wpdb->get_col($sql);

    // Return result
    return $threads;
  } // end function get_user_convos


  /**
   * @desc Is the message marked as read or hidden by the viewing user
   * @author SDK (steve@eardish.com)
   * @date 2012-01-16
   * @param int $id - Specifc message to mark
   * @param str $key - Meta key (read or hide)
   * @return bool - Return success or failure of operation
  */
  public static function is_marked($id, $key) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($key)) {
        throw new Exception('Need to provide id and key');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Retrieve list of users that are marked for this key on this post id
    $res = get_post_meta($id, $key, TRUE);
    $users = explode(',', $res);

    // Loop through and set to true if current user is found in marked list or default to false
    $marked = FALSE;
    if(count($users)) {
      foreach($users as $user) {
        if($user == get_current_user_id()) {
          $marked = TRUE;
          break;
        }
      }
    }

    // Return result
    return $marked;
  } // end function is_marked


  /**
   * @desc Mark message as read or hidden by a user
   * @author SDK (steve@eardish.com)
   * @date 2012-01-16
   * @param str $key - Meta key (read or hide)
   * @param str $type - message or thread
   * @param int $id - message id or thread parent id
   * @return mixed - bool or ID on success OR false on failur
  */
  public static function mark_message($key, $type, $id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($key) || !isset($type) || !isset($id)) {
        throw new Exception('Need to provide key, type and id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }
 
    // Sort parent list
    $users = explode(',', $id);
    $users = array_unique($users);
    sort($users, SORT_NUMERIC);
    $id = implode(',', $users);

    // Build query
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'message' ";

    // Thread or message?
    switch($type) {
      case 'message':
        $sql .= "AND ID = %d ";
        break;
      case 'thread':
        $sql .= "AND post_title = %s ";
        break;
    }

    // ORDER BY date
    $sql .= "ORDER BY post_date ASC";

    // Get messages matching post id or thread parent id depending on type
    $messages = $wpdb->get_results($wpdb->prepare($sql, $id));

    // Loop through each message in convo
    if(count($messages)) {
      foreach($messages as $message) {

        // Retrieve list of users that are marked for this key on this post id
        $meta = get_post_meta($message->ID, $key, TRUE);

        // Explode comman seperated list into an array
        $users = explode(',', $meta);

        // Append current user to list of read or hidden
        $users[] = get_current_user_id();

        // Trim out empty array elements
        if(count($users)) {
          foreach($users as $k => $v) {
            if(empty($v)) {
              unset($users[$k]);
            }
          }
        }

        // De-dup new list
        $users = array_unique($users);

        // Sort new list
        sort($users, SORT_NUMERIC);

        // Convert back to comma-seperated string
        $value = implode(',', $users);

        // Update the meta data
        $res = update_post_meta($message->ID, $key, $value);

      }
    } // end loop of each message in convo

    // Return results
    return $res;
  } // end function mark_message


} // end class message

