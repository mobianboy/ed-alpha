<?php
/*
Plugin Name: Song Management
Plugin URI: 
Description: Eardish Song Management System
Version: 2.0
Author: Steven Kornblum
*/

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');

/**
 * @desc song management lib
 * @author SDK (steve@eardish.com)
 * @date 2012-08-13
 */
class song {


  /**
   * @desc Has the current user rated a specific song?
   * @author SDK (steve@eardish.com)
   * @date 2012-11-24
   * @param int $id - Specifc song to query
   * @return bool - Return true or false if user has rated this song
  */
  public static function has_rated_song($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide song id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    $res = reward::get_activity_id(get_current_user_id(), $id, 11);

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function has_rated_song


  /**
   * @desc Get song
   * @author SDK (steve@eardish.com)
   * @date 2012-08-22
   * @param int $id - Specifc song to query
   * @return obj - Return song object
  */
  public static function get_song($id) {
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
      'post_type' => 'song',
      'p'         => $id,
    );

    // Run wp_query
    $query = new wp_query($args);

    // Grab song object from posts array
    $song = $query->posts[0];

    // Return result
    return $song;
  } // end function get_song


  /**
   * @desc Get song(s)
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param [OPTIONAL] int $id - Specifc song to query
   * @param [OPTIONAL] int $limit - How many posts per page? 
   * @param [OPTIONAL] int $offset - What page are we on?
   * @param [OPTIONAL] bool $wp - Return full wp_query object?
   * @return arr - Return array of song objects
  */
  public static function get_songs($id=NULL, $limit=15, $offset=0, $wp=FALSE) {
    global $wpdb;

    // Build args
    $args = array(
      'post_type' => 'song',
    );

    // If passed an id, query against it
    if($id) {
      $args['post__in'] = array($id);
    } else {
      $args['posts_per_page'] = $limit;
      $args['offset'] = $offset;
    }

    // Run wp_query
    $query = new wp_query($args);

    // Grab songs array
    $songs = $query->posts;

    // Get metadata for each song
    if(count($songs)) {
      foreach($songs as $key => $song) {
        $song->meta = get_post_meta($song->ID);
        $song->meta['artist'] = get_user_by('id', $song->post_author);
        $song->meta['owned'] = (bool) playlist::check_library($song->ID);
        $waveform_type = ($song->meta['owned']) ? 'song_waveform' : 'demo_waveform';
        $song->meta['song_thumbnail'][0] = self::get_song_image($song->ID, 38, 38);
        $song->meta['song_waveform'][0] = self::get_song_waveform($song->ID, $waveform_type);
        $song->meta['song_demo'][0] = self::get_song_file($song->ID, 'demo');
        $song->meta['song_file'][0] = (self::get_song_file($song->ID, 'song')) ? self::get_song_file($song->ID, 'song') : self::get_song_file($song->ID, 'demo');
        $songs[$key] = $song;
      }
    }

    // Return result (either just songs array or full wp_query object?)
    if($wp) {
      $query->posts = $songs;
      return $query;
    } else {
      return $songs;
    }
  } // end function get_songs


  /**
   * @desc Set song (insert new or update existing)
   * @author SDK (steve@eardish.com)
   * @date 2012-08-20
   * @param arr $args - All the metadata properties of a song
   * @param [OPTIONAL] int $id - Specifc song to query/update
   * @return mixed|bool - Return success (affected ID) or failure of operation
  */
  public static function set_song($args=array(), $id=NULL) {
    global $wpdb;

    // Process args
    $name   = (isset($args['name']))    ? $args['name']   : NULL;
    $status = (isset($args['status']))  ? $args['status'] : NULL;
    $genre  = (isset($args['genre']))   ? $args['genre']  : NULL;
    $meta   = (isset($args['meta']))    ? $args['meta']   : NULL;

    // Setup Slug
    $artist = self::get_artist(get_current_user_id());
    $slug = sanitize_title("$artist $name");

    // Create post object
    $song = array(
      'post_type'     => 'song',
      'post_author'   => get_current_user_id(),
      'post_title'    => wp_strip_all_tags($name),
      'post_name'     => $slug,
    );

    // Post status?
    if($status) {
      $song['post_status'] = $status;
    }

    // Insert the post
    if($id) {
      $song['ID'] = $id;
      $res = wp_update_post($song);
    } else {
      $res = wp_insert_post($song);
    }

    // If post was successfully inserted
    if($res) {

      // Setup genre
      $sql = "SELECT term_taxonomy_id
              FROM wp_term_taxonomy
              WHERE term_id = %d
              AND taxonomy = 'Genre'";
      $term_taxonomy_id = $wpdb->get_var($wpdb->prepare($sql, $genre));

      // If updating, delete the original term relationship for genre to song
      if($id) {
        $sql = "DELETE FROM wp_term_relationships
                WHERE object_id = %d";
        $wpdb->query($wpdb->prepare($sql, $res));
      }

      // Insert genre to song in term relationships
      $sql = "INSERT INTO wp_term_relationships SET
              object_id = %d,
              term_taxonomy_id = %d";
      $wpdb->query($wpdb->prepare($sql, $res, $term_taxonomy_id));

      // If meta data is passed, go set it
      if(count($meta)) {
        foreach($meta as $key => $val) {
          self::set_song_meta($res, $key, $val);
        }
      }
    }

    // Return result
    return $res;
  } // end function set_song


  /**
   * @desc Create/Edit a song meta key/value pair 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-02
   * @param int $post_id The id of the song being referenced in the meta data 
   * @param str $meta_key The meta key 
   * @param str $meta_value The meta value 
   * @return int Returns the id of the row affected (insert, replace or update) 
  */
  public static function set_song_meta($post_id, $meta_key, $meta_value) {
    global $wpdb;

    // Convert boolean values binary bit
    if(is_bool($meta_value)) {
      $meta_value = ($meta_value) ? 1 : 0;
    }
 
    // Build query
    $res = update_post_meta($post_id, $meta_key, $meta_value); 

    // Return result
    return $res;
  } // end function set_song_meta


  /**
   * @desc Delete song
   * @author SDK (steve@eardish.com)
   * @date 2013-06-26
   * @param int $id - Specifc song to delete 
   * @return bool - Return success or failure of operation
  */
  public static function delete_song($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide song id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Default return state
    $res = FALSE;

    // Delete song post
    $sql = "DELETE FROM wp_posts
            WHERE ID = %d";
    $res = $wpdb->query($wpdb->prepare($sql, $id));

    // Delete meta data
    $sql = "DELETE FROM wp_postmeta
            WHERE post_id = %d";
    $res = $wpdb->query($wpdb->prepare($sql, $id));

    // Return result
    return ($res) ? TRUE: FALSE;
  } // end function delete_song


  /**
   * @desc Check if a song is owned by the current user
   * @author SDK (steve@eardish.com)
   * @date 2013-07-02
   * @param int $id - Specifc song to check
   * @return bool - True or false value of ownership status
  */
  public static function is_owner($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide song id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    $sql = "SELECT post_author
            FROM wp_posts
            WHERE ID = %d";
    $owner = $wpdb->get_var($wpdb->prepare($sql, $id));

    // Check if this song ID is in the user's library playlist
    $res = ($owner == get_current_user_id()) ? TRUE : FALSE;

    // Return result
    return $res;
  } // end function is_owner


  /**
   * @desc Get user information for song artist
   * @author SDK (steve@eardish.com)
   * @date 2012-09-06
   * @param int $id - Artist's user ID
   * @return str - Display name of artist
  */
  public static function get_artist($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide artist user id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get the user object of the artist
    $artist = get_user_by('id', $id);

    // Return the display name of the artist
    return $artist->display_name;
  } // end function get_artist


  /**
   * @desc Begin dowload of full song process 
   * @author SDK (steve@eardish.com)
   * @date 2012-11-26
   * @param int $id - Song ID
   * @return str - URL of full song in cloudfront
  */
  public static function download_song($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide song id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get full song URL from song meta data
    $res = self::get_song_file($id, 'full');

    // Return the result
    return $res;
  } // end function download_song


  /**
   * @desc Get song audio file
   * @author SDK (steve@eardish.com)
   * @date 2013-05-09
   * @param int $id - The post id of the related audio file to fetch
   * @param str $type - The type of the song file: demo or song (for full)
	 * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
   * @return str - Return the cloud URL of the song audio file
  */
  public static function get_song_file($id, $type, $archive=FALSE) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($type)) {
        throw new Exception('Need to provide id and type');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process MFP call
    $res = ed::get_mfp_audio($id, $type, $archive);

    // Return result (cloud url)
    return $res;
  } // end function get_song_file


  /**
   * @desc Get song cover image
   * @author SDK (steve@eardish.com)
   * @date 2012-05-07
   * @param int $id - The post id of the related image to fetch
   * @param int $w - The width of the image
   * @param int $h - The height of the image 
	 * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
   * @return str - Return the cloud URL of the song image thumb
  */
  public static function get_song_image($id, $w, $h, $archive=FALSE) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($w) || !isset($h)) {
        throw new Exception('Need to provide id, w and h');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process MFP call
    $res = ed::get_mfp_image('song', $id, $w, $h, $archive);

    // Return result (cloud url)
    return $res;
  } // end function get_song_image


  /**
   * @desc Get song waveform image
   * @author SDK (steve@eardish.com)
   * @date 2012-05-07
   * @param int $id - The post id of the related image to fetch
   * @param str $type - The full or demo waveform image
	 * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
   * @return str - Return the cloud URL of the song image thumb
  */
  public static function get_song_waveform($id, $type, $archive=FALSE) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($type)) {
        throw new Exception('Need to provide id and type');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process MFP call
    $res = ed::get_mfp_image($type, $id, 1, 1, $archive);

    // Return result (cloud url)
    return $res;
  } // end function get_song_waveform


  /**
   * @desc Create song wave form image
   * @author SDK (steve@eardish.com)
   * @date 2012-10-28
   * @param int $id - the post id
   * @param str $url - the cloud url of the orignal song file
   * @return str - cloud url of the waveform image
  */
  public static function set_waveform($id, $url) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id) || !isset($url)) {
        throw new Exception('Need to provide id and url');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Process song file into waveform image
    $waveform = getSongWaveFormImage($url);

    // Update post/meta with waveform cloud url
    $res = update_post_meta($id, 'waveform', $waveform); 

    // Return result
    return $waveform;
  } // end function set_waveform


  /**
   * @desc Get song slug name by id
   * @author SDK (steve@eardish.com)
   * @date 2013-07-12
   * @param int $id - Specifc song to query
   * @return str - Return article slug (post_name)
  */
  public static function get_song_slug($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get post object by ID
    $post = get_post($id);

    // Return result
    return $post->post_name;
  } // end function get_song_slug


  /**
   * @desc Get song id by slug
   * @author SDK (steve@eardish.com)
   * @date 2013-07-17
   * @param str $slug - The slug of the song to fetch
   * @return int - Return the id of the song
  */
  public static function get_song_id_by_slug($slug) {
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
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_name = %s";
    $id = $wpdb->get_var($wpdb->prepare($sql, $slug));

    // Return result
    return $id;
  } // end function get_song_id_by_slug


  /**
   * @desc Get genre list for songs
   * @author SDK (steve@eardish.com)
   * @date 2013-02-13
   * @return arr - Genre list
  */
  public static function get_genres() {
    global $wpdb;

    // Build query
    $sql = "SELECT b.term_id AS ID, b.slug AS slug, b.name AS genre
            FROM wp_term_taxonomy AS a, wp_terms AS b
            WHERE a.taxonomy = 'Genre'
            AND a.term_id = b.term_id
            ORDER BY genre ASC";

    // Run query
    $genres = $wpdb->get_results($sql);

    // Return the result
    return $genres;
  } // end function get_genres


  /**
   * @desc Get genre for song
   * @author SDK (steve@eardish.com)
   * @date 2012-12-16
   * @param int $id - Song ID
   * @return str - Genre name of song
  */
  public static function get_genre($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide song id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT c.name AS genre, COUNT(b.term_taxonomy_id) AS total
            FROM wp_term_relationships AS a, wp_term_taxonomy AS b, wp_terms AS c
            WHERE a.object_id = %d
            AND a.term_taxonomy_id = b.term_taxonomy_id
            AND b.taxonomy = 'Genre'
            AND b.term_id = c.term_id
            GROUP BY genre
            ORDER BY total DESC
            LIMIT 1";
    
    // Run query
    $tax = $wpdb->get_row($wpdb->prepare($sql, $id));

    // Get genre name from object
    $res = $tax->genre;
  
    // Return the result
    return $res;
  } // end function get_genre


  /**
   * @desc Search songs (song titles and artist names) 
   * @author SDK (steve@eardish.com)
   * @date 2013-04-15
   * @param str $search - Search query string
   * @return arr - Array of song ids
  */
  public static function search_songs($search) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($search)) {
        throw new Exception('Need to provide search');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query songs from artists whose names match the search
    $sql = "SELECT p.ID
            FROM wp_posts AS p, wp_users AS u
            WHERE p.post_type = 'song'
            AND p.post_author = u.ID
            AND (
              u.user_nicename LIKE '%$search%'
              OR u.display_name LIKE '%$search%'
              OR u.user_login LIKE '%$search%'
            )
            ORDER BY p.ID ASC";
    $search_artists = $wpdb->get_col($sql);
    
    // Query song titles that match the search
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_type = 'song'
            AND post_title LIKE '%$search%'
            ORDER BY ID ASC";
    $search_songs = $wpdb->get_col($sql);

    // Merge, de-dup and sort the 2 results
    $res = array_merge($search_artists, $search_songs);
    $res = array_unique($res);
    sort($res);
  
    // Return the result
    return $res;
  } // end function search_songs


  /**
   * @desc Get the average rating of a song
   * @author SDK (steve@eardish.com)
   * @date 2013-06-25
   * @param int $id The id of the song
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return float - The average rating of the song
  */
  public static function get_average_rating($id, $start=NULL, $end=NULL) {
    global $wpdb;

    // If missing args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id.');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query for average rating of a song
    $sql = "SELECT AVG(action_values.value)
            FROM users_activity AS activity, user_action_values AS action_values
            WHERE activity.action_id = 11
            AND activity.content_id = %d ";

    // Filter with time windows when provided
    if(isset($start) && isset($end)) {

      // Convert datetime strings into unix timestamp format
      $open = strtotime($start);
      $close = strtotime($end);

      // If window doesn't make chronological sense, throw an error
      try {
        if($close < $open) {
          throw new Exception('Start and End dates are out of order');
        }
      } catch(Exception $e) {
        return $e->getMessage();
      }

      // Append date window condition to sql
      $sql .= "AND activity.modified BETWEEN '$start' AND '$end' ";
    }

    // Join to action_values table
    $sql .= "AND activity.action_value_id = action_values.id ";

    // Process query
    $res = $wpdb->get_var($wpdb->prepare($sql, $id));

    // Round average rating to nearest half integer
    $avg = round(($res * 2)) / 2;

    // Return result
    return $avg;
  } // end function get_average_rating


  /**
   * @desc Get the rating of a song by the current active user session
   * @author SDK (steve@eardish.com)
   * @date 2013-06-25
   * @param int $id The id of the song
   * @return int - The current user's rating of the song
  */
  public static function get_my_rating($id) {
    global $wpdb;

    // If missing args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id.');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Set user
    $user = get_current_user_id();

    // Build query for average rating of a song
    $sql = "SELECT action_values.value
            FROM users_activity AS activity, user_action_values AS action_values
            WHERE activity.action_id = 11
            AND activity.user_id = %d
            AND activity.content_id = %d
            AND activity.action_value_id = action_values.id";

    // Process query
    $res = $wpdb->get_var($wpdb->prepare($sql, $user, $id));

    // Return result
    return $res;
  } // end function get_my_rating


  /**
   * @desc Get the total play count for a song
   * @author SDK (steve@eardish.com)
   * @date 2013-06-25
   * @param int $id The id of the song
   * @return int - The total number of plays of a song
  */
  public static function get_play_count($id) {
    global $wpdb;

    // If missing args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id.');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Set user
    $user = get_current_user_id();

    // Build query for average rating of a song
    $sql = "SELECT COUNT(id) AS play_count
            FROM users_activity
            WHERE action_id = 9
            AND content_id = %d";

    // Process query
    $res = $wpdb->get_var($wpdb->prepare($sql, $id));

    // Return result
    return $res;
  } // end function get_play_count


  /**
   * @desc Get the total rate count for a song
   * @author SDK (steve@eardish.com)
   * @date 2013-06-25
   * @param int $id The id of the song
   * @return int - The total number of rartings of a song
  */
  public static function get_rate_count($id) {
    global $wpdb;

    // If missing args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id.');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Set user
    $user = get_current_user_id();

    // Build query for average rating of a song
    $sql = "SELECT COUNT(id) AS play_count
            FROM users_activity
            WHERE action_id = 11
            AND content_id = %d";

    // Process query
    $res = $wpdb->get_var($wpdb->prepare($sql, $id));

    // Return result
    return $res;
  } // end function get_rate_count


  /**
   * @desc Get songs by a specific artist
   * @author SDK (steve@eardish.com)
   * @date 2013-06-25
   * @param int $id The id of the user (artist)
   * @param [OPTIONAL] int $exclude - ID of any song to exclude
   * @return arr - List of songs by the specified artist
  */
  public static function get_artist_songs($id, $exclude=NULL) {
    global $wpdb;

    // If missing args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id.');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query for songs by artist
    $sql = "SELECT *
            FROM wp_posts
            WHERE post_type = 'song'
            AND post_status = 'publish'
            AND post_author = %d ";

    // Exclude song ID if provided
    if($exclude) {
      $sql .= "AND ID != $exclude ";
    }
    
    // Sort query
    $sql .= "ORDER BY post_date DESC";

    // Run query
    $songs = $wpdb->get_results($wpdb->prepare($sql, $id));

    // Loop through results and attach metadata and owner data
    if(count($songs)) {
      foreach($songs as $k => $song) {
        $song->meta = get_post_meta($song->ID);
        $song->owner = get_user_by('id', $song->post_author);
        $songs[$k] = $song;
      }
    }

    // Return results
    return $songs;
  } // end function get_artist_songs


  /**
   * @desc Get songs in user's lib (been demo'd/rated)
   * @author SDK (steve@eardish.com)
   * @date 2013-09-06
   * @param int $id The id of the user (fan)
   * @return arr - List of songs owned by the specified fan
  */
  public static function get_fan_songs($id) {
    global $wpdb;

    // If missing args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id.');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get library of user
    $sql = "SELECT post_content
            FROM wp_posts
            WHERE post_type = 'playlist'
            AND post_title = 'Library'
            AND post_author = %d
            AND post_status = 'publish'";
    $lib = $wpdb->get_var($wpdb->prepare($sql, $id));
    $posts = json_decode($lib);

    // Loop through results and attach metadata and owner data
    $songs = array();
    if(count($posts)) {
      foreach($posts as $k => $post) {
        $song = self::get_song($post);
        $song->meta = get_post_meta($song->ID);
        $song->owner = get_user_by('id', $song->post_author);
        $songs[$k] = $song;
      }
    }

    // Return results
    return $songs;
  } // end function get_fan_songs


} // end class song

