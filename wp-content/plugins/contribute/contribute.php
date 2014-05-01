<?php
/*
Plugin Name: Contribute
Plugin URI: 
Description: Eardish Contribute System
Version: 2.0
Author: Steven Kornblum
*/


// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');


/**
 * @desc contribute lib
 * @author SDK (steve@eardish.com)
 * @date 2012-10-28
 */
class contribute {


  /**
   * @desc Create song post type record and send demo slice info to MFP
   * @author SDK (steve@eardish.com)
   * @date 2012-05-15
   * @param int id - ID of the original MFP resource for the song
   * @param int wfid - ID of the original MFP resource for the waveform
   * @param str title - The song title
   * @param int genre - The song's primary genre ID
   * @param int start - The second count for the demo slice to start from
   * @param int length - The duration in seconds for the demo slice
   * @return int - ID of the new song post in WP
  */
  public static function set_song($id, $wfid, $title, $genre, $start, $length) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($wfid) || !isset($title) || !isset($genre) || !isset($start) || !isset($length)) {
        throw new Exception('Need to provide id, wfid, title, genre, start, length');
      }
      if($length != 60 && $length != 90) {
        throw new Exception('Length must be 60 or 90 seconds');
      }

    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Insert post and meta to WP
    $typeid = song::set_song(array(
      'name'    => $title,
      'status'  => 'draft',
      'genre'   => $genre,
    ));

    // Get genre name for response object
    $sql = "SELECT name
            FROM wp_terms
            WHERE term_id = %d";
    $genre_name = $wpdb->get_var($wpdb->prepare($sql, $genre));

    // Default return value
    $res = FALSE;

    // If inserting song post was successful, then process MFP demo slice
    if($typeid) {
    
      // MFP API URL
      $url = 'http://'.MFP_HOST.'/demoslice';
 
      // Data array to be passed into API call for IMG Proc
      $data = array(
        'id'      => $id,
        'wfid'    => $wfid,
        'typeid'  => $typeid,
        'start'   => $start,
        'length'  => $length,
      );
      $json['data'] = json_encode($data, TRUE);

      // CURL The API
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, count($json));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      //curl_setopt($ch, CURLOPT_VERBOSE, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $mfp = json_decode(curl_exec($ch), TRUE);
      curl_close($ch);

      // Get CLOUD URL
      if($mfp['status']['code'] == 20) {
        $res = array(
          'id'    => $typeid,
          'genre' => $genre_name,
          'mfp'   => $mfp,
        );
      } else {
        $res = $mfp;
      }

    } // end demo slice

    // Return result
    return $res;
  } // end function set_song


  /**
   * @desc Publish or delete song?
   * @author SDK (steve@eardish.com)
   * @date 2013-05-15
   * @param int $id - The post ID of the song
   * @param str $status - Confirm or delete?
   * @return bool - Return success or failure of operation
  */
  public static function confirm_song($id, $status) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($status)) {
        throw new Exception('Need to provide id and status');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Change status of song to publish
    switch($status) {
      case 'publish':
        $sql = "UPDATE wp_posts SET
                post_status = 'publish'
                WHERE ID = %d";
        break;
      case 'delete':
        $sql = "DELETE FROM wp_posts
              WHERE ID = %d";
        break;
      default:
        return FALSE;
    }

    // Run query
    $res = $wpdb->query($wpdb->prepare($sql, $id));

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function confirm_song


  /**
   * @desc Create photo post type record
   * @author SDK (steve@eardish.com)
   * @date 2013-10-01
   * @param int id - ID of the original MFP resource for the photo
   * @param [OPT] str caption - The caption of the photo
   * @return mixed(str|bool) - CF URL of photo on success and FALSE on failure
  */
  public static function set_photo($id, $caption=NULL) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Insert post and meta to WP
    $photo = wp_insert_post(array(
      'post_content'  => $id,
      'post_title'    => $caption,
      'post_author'   => get_current_user_id(),
    ));

    // If inserting was successful, then query MFP for CF URL of image
    if($photo) {
      $res = ed::get_mfp_image('photo', $id, 142, 142);
    } else {
      $res = FALSE;
    }

    // Return result
    return $res;
  } // end function set_photo


  /**
   * @desc Delete photo post type record
   * @author SDK (steve@eardish.com)
   * @date 2013-10-10
   * @param int id - ID of the original MFP resource for the photo
   * @return bool - True on success or False on failure of operation
  */
  public static function delete_photo($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Delete photo post
    $res = wp_delete_post($id, TRUE);
    
    // Return result
    return $res;
  } // end function delete_photo


} // end class contribute

