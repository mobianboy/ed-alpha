<?php
/*
Plugin Name: Playlist
Plugin URI: 
Description: Eardish Playlists
Version: 2.0
Author: Steven Kornblum
*/


// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');


/**
 * @desc playlist lib
 * @author SDK (steve@eardish.com)
 * @date 2012-07-19
 */
class playlist {


  /**
   * @desc Check if a song (by ID) is in this user's lib
   * @author SDK (steve@eardish.com)
   * @date 2012-08-22
   * @param int $data - Id of song to check
   * @return bool - Return true or false if user already owns song in lib
  */
  public static function check_library($data) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($data)) {
        throw new Exception('Need to provide data');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get songs owned by the current user
    $songs = (array) json_decode(get_user_meta(get_current_user_id(), 'songs_owned', TRUE));

    // Is song owned by user?
    $owned = (in_array($data, $songs)) ? TRUE : FALSE;

    // Return result
    return $owned;
  } // end function check_library


  /**
   * @desc Get count of songs this user has in lib
   * @author SDK (steve@eardish.com)
   * @date 2013-07-27
   * @return int - Return count of songs in lib
  */
  public static function count_library() {
    global $wpdb;

    // Get library for current user
    $lib = self::get_library();

    // Count songs in lib
    $res = count($lib[0]->songs);

    // Return result
    return $res;
  } // end function count_library


  /**
   * @desc Get all songs this user has in lib
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param [OPTIONAL] int $id - The id of the user owning the library
   * @return arr - Return array of playlist/song objects
  */
  public static function get_library($id=NULL) {
    global $wpdb;

    // If specific user id is provided, otherwise default to current session
    $id = ($id) ? $id : get_current_user_id();

    // Get library playlist ID
    $lib = self::get_library_id($id);

    // Get playlist object for lib
    $res = self::get_playlists($lib, TRUE);

    // Return result
    return $res;
  } // end function get_library


  /**
   * @desc - Add a song to a user's lib
   * @author SDK (steve@eardish.com)
   * @date 2012-08-22
   * @param int $data - Id of song to append
   * @return bool - Success or failure
  */
  public static function set_library($data) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($data)) {
        throw new Exception('Need to provide data');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get library playlist ID
    $id = self::get_library_id();

    // Append the song
    $res = self::append_playlist($data, $id);

    // Return result
    return $res;
  } // end function set_library


  /**
   * @desc Is the list active in the user's saved player state?
   * @author SDK (steve@eardish.com)
   * @date 2012-09-06
   * @param int $id - Id of list to check
   * @return bool - Return true or false if the list is active
  */
  public static function is_active($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get last player state
    $state = self::get_player_state();

    // Set active list flag based on last saved player state
    $active = ($id == $state['activeList']) ? TRUE : FALSE;

    // Return result
    return $active;
  } // end function is_active


  /**
   * @desc Get the active playlist
   * @author SDK (steve@eardish.com)
   * @date 2012-09-06
   * @return int - Return active playlist ID
  */
  public static function get_active() {
    global $wpdb;

    // Retrieve player state meta data
    $state = self::get_player_state();

    // Unserialize data
    $state = json_decode($state);

    // Get active list ID
    $id = $state->activeList;

    // Return result
    return $id;
  } // end function get_active


  /**
   * @desc Get the last state of the player
   * @author SDK (steve@eardish.com)
   * @date 2012-08-20
   * @return obj - Return last saved player state
  */
  public static function get_player_state() {
    global $wpdb;

    // Retrieve meta data
    $res = get_user_meta(get_current_user_id(), 'player_state', TRUE);

    // Return result
    return $res;
  } // end function get_player_state


  /**
   * @desc Save the state of the player
   * @author SDK (steve@eardish.com)
   * @date 2012-08-20
   * @param str $data - json encoded array of state info (active playlist, sort order, last played song, shuffle, repeat, etc) 
   * @return bool - Success or failure
  */
  public static function set_player_state($data) {
    global $wpdb;

    if(!$data || empty($data)) {
      $data = '{"activeList":null,"lastPlayedSong":null,"sortOrder":null,"shuffle":null,"repeat":null,"index":null}';
    }


    // Update meta data
    $res = update_user_meta(get_current_user_id(), 'player_state', $data);

    // Return result
    return ($res) ? 'true' : 'false';
  } // end function set_player_state


  /**
   * @desc Append a song to a playlist
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param int $data - Id of song to append
   * @param int [OPTIONAL] $playlist_id - List to append to
   * @return bool - Success or failure
  */
  public static function append_playlist($data, $playlist_id=NULL) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($data)) {
        throw new Exception('Need to provide data');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the library playlist ID
    $id = self::get_library_id();

    // Make an array of playlist ID's for loop
    if($playlist_id) {
      $plids = array($id, $playlist_id);
    } else {
      $plids = array($id);
    }

    // Loop through playlist ID's
    if(count($plids)) {
      foreach($plids as $plid) {

        // Get the playlists array
        $playlists = self::get_playlists($plid);

        // Get the playlist object
        $playlist = $playlists[0];

        // Check that this song doesn't already belong to this list
        if(!in_array($data, $playlist->post_content) && (song::has_rated_song($data) || song::is_owner($data))) {
        
          // Append the new song id to the end of the list array
          $playlist->post_content[] = $data;

          // Reserialize the list data
          $playlist->post_content = json_encode($playlist->post_content);

          // Set the new data
          $append = self::update_playlist($plid, $playlist->post_content);

        }

        // Clear our containers for loop
        unset($playlists);
        unset($playlist);

      }
    } // end foreach loop of playlist ID's

    // Return result
    return $append;
  } // end function append_playlist


  /**
   * @desc Get playlist(s)
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param [OPTIONAL] int $id - Specifc playlist to query
   * @param [OPTIONAL] bool $lib - Reduce functionality for just getting the full lib
   * @return arr - Return array of playlist objects
  */
  public static function get_playlists($id=NULL, $lib=FALSE) {
    global $wpdb;

    // Build args
    $args = array(
      'post_type' => 'playlist',
      'status'    => 'publish',
      'author'    => get_current_user_id(),
    );

    // If passed an id, query against it
    if($id) {
      $args['p'] = $id;
    }

    // Run wp_query
    $query = new wp_query($args);

    // Grab playlists array
    $playlists = $query->posts;

    // Get last player state
    $state = self::get_player_state();

    // Loop through each playlist object
    if(count($playlists)) {
      foreach($playlists as $key => $playlist) {

        // Strip any markup that WP applies to content
        $playlist->post_content = strip_tags($playlist->post_content);

        // If content exists, unserialize, else initialize an empty array
        if(!empty($playlist->post_content)) {
          $playlist->post_content = json_decode($playlist->post_content);
        } else {
          $playlist->post_content = array();
        }

        // Initialize the song object array for this playlist
        $playlist->songs = array();

        // Loop through post_content array (song ids)
        if(count($playlist->post_content)) {
          foreach($playlist->post_content as $k => $v) {

            // If lib flag is true, get simple song object, else get complex object (with metadata, ownership, etc)
            if($lib) {
              $song = song::get_song($v);
            } else {
              $songs = song::get_songs($v);
              $song = $songs[0];
            }

            // Assign song object into songs array
            $playlist->songs[] = $song;

          }
        } // End loop through post_content (song ids)

        // Set active list flag based on last saved player state
        $playlist->active = ($playlist->ID == $state['activeList']) ? TRUE : FALSE;

        // Reassign modified playlist object to playlists array
        $playlists[$key] = $playlist;

      }
    } // End loop through playlist objects

    // Return result
    return $playlists;
  } // end function get_playlists


  /**
   * @desc Get library playlist id
   * @author SDK (steve@eardish.com)
   * @date 2012-08-22
   * @param [OPTIONAL] int $id - The id of the user owning the library
   * @return int - Return id of user's library playlist
  */
  public static function get_library_id($id=NULL) {
    global $wpdb;

    // If specific user id is provided, otherwise default to current session
    $id = ($id) ? $id : get_current_user_id();

    // Build query for this user's library playlist post object
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'playlist'
            AND post_status = 'publish'
            AND post_title = 'Library'
            AND post_author = %d";

    // Prepare query for data
    $sql = $wpdb->prepare($sql, $id);

    // Run query
    $res = $wpdb->get_results($sql);

    // If exists, get playlist ID, otherwise create it
    if(count($res) != 1) {
      if($id == get_current_user_id()) {
        $lib = self::set_playlist('Library', '');
      }
    } else {
      $lib = $res[0]->ID;
    }

    // Return result
    return $lib;
  } // end function get_library_id


  /**
   * @desc Set playlist
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param str $name - The name of the playlist
   * @param [OPTIONAL] str $data - The playlist data (song ids serialized in sort order)
   * @return bool - Success or failure of operation
  */
  public static function set_playlist($name, $data=NULL) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($name)) {
        throw new Exception('Need to provide name');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Create post object
    $playlist = array(
      'post_type'     => 'playlist',
      'post_status'   => 'publish',
      'post_author'   => get_current_user_id(),
      'post_title'    => wp_strip_all_tags($name),
    );

    // If song data is passed, assign it
    if($data) {
      $playlist['post_content'] = $data;
    }

    // Insert the post
    $res = wp_insert_post($playlist, TRUE);

    // Return result
    return $res;
  } // end function set_playlist


  /**
   * @desc Update playlist
   * @author SDK (steve@eardish.com)
   * @date 2012-08-30
   * @param int $id - ID of playlist to update 
   * @param [OPTIONAL] str $data - The playlist data (song ids serialized in sort order)
   * @param [OPTIONAL] str $name - The name of the playlist
   * @return bool - Success or failure of operation
  */
  public static function update_playlist($id, $data=NULL, $name=NULL) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
      if(strtolower($name) == 'library') {
        throw new Exception('Invalid playlist name');
      }

    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Initialize playlist array
    $playlist = array();

    // If data is provided, add to object
    if($data) {
      $playlist['post_content'] = $data;
    }

    // If name is provided, add to object (but can't rename the main library)
    if($name && self::get_library_id() != $id) {
      $playlist['post_title'] = wp_strip_all_tags($name);
    }

    // Set ID of playlist object to update
    $playlist['ID'] = $id;

    // Update the post
    $res = wp_update_post($playlist);

    // Return result
    if($name) {
      return $name;
    } else {
      return $res;
    }
  } // end function update_playlist


  /**
   * @desc Delete playlist
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param int $id - The id of the playlist
   * @return bool - Success or failure of operation
  */
  public static function delete_playlist($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Check that the main library is not being deleted
    try {
      if(self::get_library_id() == $id) {
        throw new Exception('Can not delete the main library');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Delete post
    $res = wp_delete_post($id);
    
    // Return result
    return $res;
  } // end function delete_playlist


} // end class playlist

