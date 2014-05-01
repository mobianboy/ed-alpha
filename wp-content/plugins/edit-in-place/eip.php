<?php
/*
Plugin Name: Edit-In-Place
Plugin URI: 
Description: Edit in place allows granular profile field updates from within the presentation layer through BALLS API
Version: 2.0
Author: Steven Kornblum
*/


// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');


/**
 * @desc eip lib
 * @author SDK (steve@eardish.com)
 * @date 2012-12-09
 */
class eip {


  /**
   * @desc Update song data
   * @author SDK (steve@eardish.com)
   * @date 2013-09-13
   * @param int $id - ID of post
   * @param int $key - Field being updated
   * @param int $val - New value
   * @return str - Return new value
  */
  public static function update_song($id, $key, $val) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($key) || !isset($val)) {
        throw new Exception('Need to provide post id, key and val');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    switch($key) {
      case 'genre':
        $sql = "DELETE FROM wp_term_relationships
                WHERE object_id = %d";
        $wpdb->query($wpdb->prepare($sql, $id));
        $sql = "SELECT term_taxonomy_id
                FROM wp_term_taxonomy
                WHERE term_id = %d
                AND taxonomy = 'Genre'";
        $term_taxonomy_id = $wpdb->get_var($wpdb->prepare($sql, $val));
        $sql = "INSERT INTO wp_term_relationships SET
                object_id = %d,
                term_taxonomy_id = %d";
        $res = $wpdb->query($wpdb->prepare($sql, $id, $term_taxonomy_id));
        if($res) {
          $sql = "SELECT name
                  FROM wp_terms
                  WHERE term_id = %d";
          $val = $wpdb->get_var($wpdb->prepare($sql, $term_taxonomy_id));
        }
        break;
    }

    // Need to clear object cache
    wp_cache_flush();

    // Return result
    return ($res) ? $val : $res;
  } // end function update_song


  /**
   * @desc Update a meta value associated with meta key and post id
   * @author SDK (steve@eardish.com)
   * @date 2012-12-09
   * @param int $id - ID of post
   * @param int $key - Meta key being updated
   * @param int $val - New value
   * @return str - Return new value
  */
  public static function update_postmeta($id, $key, $val) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($key) || !isset($val)) {
        throw new Exception('Need to provide post id, meta key and meta value');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Update post meta
    $res = update_post_meta($id, $key, $val);

    // Need to clear object cache
    wp_cache_flush();

    // Return result
    return ($res) ? $val : $res;
  } // end function update_postmeta


  /**
   * @desc Update a meta value associated with meta key and user id
   * @author SDK (steve@eardish.com)
   * @date 2012-12-09
   * @param int $id - ID of user
   * @param int $key - Meta key being updated
   * @param int $val - New value
   * @return str - Return new value
  */
  public static function update_usermeta($id, $key, $val) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($key) || !isset($val)) {
        throw new Exception('Need to provide user id, meta key and meta value');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Display name is not a meta key, otherwise update user meta
    if($key == 'display_name') {
      $sql = "UPDATE wp_users
              SET display_name = %s
              WHERE ID = %d";
      $res = $wpdb->query($wpdb->prepare($sql, $val, $id));
    } else {
      $res = update_user_meta($id, $key, $val);
    }

    // Need to clear object cache
    wp_cache_flush();

    // Return result
    return ($res) ? $val : $res;
  } // end function update_usermeta


} // end class eip

