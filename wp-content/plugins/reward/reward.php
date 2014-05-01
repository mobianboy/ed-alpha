<?php
/*
Plugin Name: Analytics/Rewards Lib Plugin
Description: Analytics/Rewards Lib for tracking, analyzing and determining leaderboards and winners and other behavior based tracking and actions
Author: SDK
Date: 2012-03-19
*/


// Includes needed for AJAX calls
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');


// Setup actions for calls within WP system
add_action('track_activity', array('reward', 'track_activity'), 10, 5);


/**
 * @desc All rewards tracking and analytics functions are set as methods in the reward class
 * @author SDK (steve@eardish.com)
 * @date 2012-03-10
 */
class reward {


  /**
   * @desc Track user's activity in custom tables with associated cotent id's, action types and optional values/counts, with timestamps
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param int $user_id The user (artist or fan) being tracked
   * @param int $content_id The content item (post or song, etc) being tracked
   * @param int $action_id The action (rate, play, view, post, fan, etc) being tracked
   * @param int $action_value_id [OPTIONAL] The value of the action (rating score) being tracked
   * @param int $id [OPTIONAL] The id of the record being updated (only applicable to certain actions like download)
   * @return int Returns the id of the row affected (insert, replace or update)
  */
  public static function track_activity($user_id, $content_id, $action_id, $action_value_id=NULL, $id=NULL) {
    global $wpdb;

    // Determine the query type and if the counter should be incremented based on action type
    switch($action_id) {
      case 1: // login
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 2: // logout
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 3: // article-view
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      case 4: // profile-view
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      case 5: // follow
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 6: // friend
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 7: // shout
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 8: // comment
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 9: // play
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      case 10: // demo-play
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      case 11: // rate
        $qtype = 'REPLACE INTO';
        $count = FALSE;
        break;
      case 12: // cpe-init
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      case 13: // license
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      case 14: // download
        $qtype = 'UPDATE';
        $count = TRUE;
        break;
      default:
        return FALSE;
    }

    // If $action_id is a string, go get the related ID
    if(!is_numeric($action_id)) {
      $action_id = self::get_action_id_by_name($action_id);
    }

    // Initialize the data array
    $data = array(
      'user_id'     => $user_id,
      'content_id'  => $content_id,
      'action_id'   => $action_id,
    );

    // If qtype is update and no id was provided, then go get it
    if($qtype == 'UPDATE' && !$id) {
      $id = self::get_activity_id($user_id, $content_id, $action_id);
      if(!$id) $qtype = 'INSERT INTO';
    }

    // If a value is specified (e.g. rating score) then append it to the data array
    if($action_value_id) {
      $data['action_value_id'] = $action_value_id;
    }

    // If this action type uses a counter then append a count incrementer to the data array
    if($count) {
      $data['count'] = ($qtype == 'UPDATE') ? 'count + 1': 1;
    }

    // Build query
    $sql = "$qtype users_activity SET ";
    if(count($data)) {
      foreach($data as $key => $val) {
        $sql .= ($key == 'count') ? "$key = $val, " : "$key = '$val', ";
      }
    }
    $sql .= "modified = NOW()";
    if($qtype == 'UPDATE') {
      $sql .= " WHERE id = '$id'";
    }

    // Run query
    $res = $wpdb->query($sql);

    // If rating, send note to owner of parent post id
    $post = get_post($content_id);
    if($res && $action_id == 11 && $post->post_author != get_current_user_id()) {
      $note = notification::set_note(array(
        'type'      => 'rating',
        'initiator' => get_current_user_id(),
        'recipient' => $post->post_author,
        'title'     => 'Rating',
        'status'    => 'draft',
      ));
    }

    // Return "touched" record ID
    return ($qtype == 'UPDATE') ? $id : $wpdb->insert_id;
  } // end function track_activity


  /**
   * @desc Get user's activity in custom tables, filter by optional action types, with timestamps
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param int $user_id The user (artist or fan) being tracked
   * @param str $user_type [OPTIONAL] Which field to tally counts against (user_id or content_id, default is user_id)
   * @param str $start [OPTIONAL] The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end [OPTIONAL] The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param int $action_id [OPTIONAL] The action (rate, play, view, post, fan, etc) being tracked
   * @return int returns the count of activity
  */
  public static function get_user_activity_stats($user_id, $user_type='user_id', $start=NULL, $end=NULL, $action_id=NULL) {
    global $wpdb;

    // Depending on action type, determine which field and operation to run calculations on
    switch($action_id) {
      case 3:
      case 4:
      case 9:
      case 10:
      case 12:
      case 13:
      case 14:
        $calc = 'SUM(count)';
        break;
      case 1:
      case 2:
      case 5:
      case 6:
      case 7:
      case 8:
      case 11:
      default:
        $calc = 'COUNT(id)';
    }

    // Build query
    $sql = "SELECT $calc AS total_activity
            FROM users_activity
            WHERE $user_type = '$user_id' ";

    // If action type is specified, filter against it
    if($action_id) {
      $sql .= "AND action_id = '$action_id' ";
    }

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    // Run query
    $activity_count = $wpdb->get_var($sql);

    // Return user's activity count
    return $activity_count;
  } // end function get_user_activity_stats


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the top rated original song and get the associated artist
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning artist's info for notification and fulfillment
  */
  public static function highest_rated_original_song($start, $end) {
    global $wpdb;

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

    // Final args for WP Query
  	$args = array(
    	'post_type'       => 'song',
      'is_cover'        => 1,
    	'post_status'     => 'publish',
      'posts_per_page'  => -1,
      'orderby'         => 'none',
  	);
  	$query = new WP_Query($args);

    // Re-Sort the posts by top rating (no assignment needed, because there's no return, because the query object is passed by reference now :-P clever huh?)
    self::get_top_scores($query, -1, $start, $end);

    // Grab winning song off the top of the sorted posts array
    $song = array_shift($query->posts);

    // Get associated artist for winning song
    $artist = get_users(array(
      'include' => $song->post_author,
    ));

    // Return winning artist
    return $artist;
  } // end function highest_rated_original_song


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the top played song and get the associated artist
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning artist's info for notification and fulfillment
  */
  public static function most_sampled_song($start, $end) {
    global $wpdb;

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

    // Final args for WP Query
  	$args = array(
    	'post_type'       => 'song',
    	'post_status'     => 'publish',
      'posts_per_page'  => -1,
      'orderby'         => 'none',
  	);
  	$query = new WP_Query($args);

    // Re-Sort the posts by most plays (no assignment needed, because there's no return, because the query object is passed by reference now :-P clever huh?)
    self::get_top_plays($query, -1, $start, $end);

    // Grab winning song off the top of the sorted posts array
    $song = array_shift($query->posts);

    // Get associated artist for winning song
    $artist = get_users(array(
      'include' => $song->post_author,
    ));

    // Return winning artist
    return $artist;
  } // end function most_sampled_song


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the top fanned artist
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning artist's info for notification and fulfillment
  */
  public static function most_new_fans($start, $end) {
    global $wpdb;

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

    // Get all users with song (artists)
  	$artists = user::get_artists();

    // Re-Sort the artists by most fans (no assignment needed, because there's no return, because the query object is passed by reference now :-P clever huh?)
    self::get_most_fans($artists, -1, $start, $end);

    // Grab winning artist off the top of the sorted artists array
    $artist = array_shift($artists);

    // Return winning artist
    return $artist;
  } // end function most_new_fans


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the top viewed artist profile
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning artist's info for notification and fulfillment
  */
  public static function most_viewed_profile_page($start, $end) {
    global $wpdb;

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

    // Get all users with song (artists)
  	$artists = user::get_artists();

    // Re-Sort the artists by most profile views (no assignment needed, because there's no return, because the query object is passed by reference now :-P clever huh?)
    self::get_most_profile_views($artists, -1, $start, $end);

    // Grab winning artist off the top of the sorted posts array
    $artist = array_shift($artists);

    // Return winning artist
    return $artist;
  } // end function most_viewed_profile_page


  /**
   * @desc Choose a random artist
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @return object The winning artist's info for notification and fulfillment
  */
  public static function random_artist_profile() {
    global $wpdb;

    // Get all users with song (artists)
  	$artists = user::get_artists();

    // Grab winning artist randomly from the array
    $winner = array_rand($artists);
    $artist = $artists[$winner];

    // Return winning artist
    return $artist;
  } // end function random_artist_profile


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the user that rated the most cover songs
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning fan's info for notification and fulfillment
  */
  public static function most_rated_covers($start, $end) {
    global $wpdb;

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

    // Get all users with song (fans)
  	$fans = get_users();

    // Re-Sort the fans array by most rating activity for cover songs (no assignment needed, because there's no return, because the array is passed by reference now :-P clever huh?)
    self::get_top_raters($fans, -1, $start, $end, 1);

    // Grab winning user off the top of the sorted fans array
    $fan = array_shift($fans);

    // Return winning fan
    return $fan;
  } // end function most_rated_covers


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the user that rated the most original songs
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning fan's info for notification and fulfillment
  */
  public static function most_rated_originals($start, $end) {
    global $wpdb;

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

    // Get all users with song (fans)
  	$fans = get_users();

    // Re-Sort the fans array by most rating activity for original songs (no assignment needed, because there's no return, because the array is passed by reference now :-P clever huh?)
    self::get_top_raters($fans, -1, $start, $end, 0);

    // Grab winning user off the top of the sorted fans array
    $fan = array_shift($fans);

    // Return winning fan
    return $fan;
  } // end function most_rated_originals


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the user that made the most wall posts
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning fan's info for notification and fulfillment
  */
  public static function most_activity_wall_postings($start, $end) {
     global $wpdb;

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

    // Get all users
  	$fans = get_users();

    // Re-Sort the users by most wall posts (no assignment needed, because there's no return, because the fans array is passed by reference now :-P clever huh?)
    self::get_most_wall_posts($fans, -1, $start, $end);

    // Grab winning fan off the top of the sorted posts array
    $fan = array_shift($fans);

    // Return winning fan
    return $fan;
  } // end function most_activity_wall_postings


  /**
   * @desc Based on specificied time windows (e.g. rolling weeks), determine the top friended fan
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return obj The winning fan's info for notification and fulfillment
  */
  public static function most_new_friends($start, $end) {
    global $wpdb;

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

    // Get all users
  	$fans = get_users();

    // Re-Sort the fans by most friends (no assignment needed, because there's no return, because the fans array is passed by reference now :-P clever huh?)
    self::get_most_friends($fans, -1, $start, $end);

    // Grab winning fan off the top of the sorted fans array
    $fan = array_shift($fans);

    // Return winning fan
    return $fan;
  } // end function most_new_friends


  /**
   * @desc Choose a random fan
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @return object The winning fan's info for notification and fulfillment
  */
  public static function random_fan_profile() {
    global $wpdb;

    // Get all users with song (fans)
  	$fans = get_users();

    // Grab winning fan randomly from the array
    $winner = array_rand($fans);
    $fan = $fans[$winner];

    // Return winning fan
    return $fan;
  } // end function random_fan_profile


  /**
   * @desc Sort the posts array in the query object by highest average rating from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param obj (passed by reference) &$query The query object to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since query object is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_top_scores(&$query, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $pids = array();
    if(count($query->posts)) {
      foreach($query->posts as $post) {
        $pids[]	=	$post->ID;
      }
    }
    $pidsStr = implode(',', $pids);

    $sql = "SELECT activity.content_id AS song_id, AVG(action_values.value) + COUNT(activity.id) AS average_rating
            FROM users_activity AS activity, user_action_values AS action_values
            WHERE activity.action_id = 11
            AND activity.content_id IN({$pidsStr}) ";

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

    $sql .= "AND activity.action_value_id = action_values.id ";

    $sql .= "GROUP BY song_id
             ORDER BY average_rating DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    if($nposts == -1) {
      $nposts = $query->post_count;
    }

    $foundPids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $foundPids[] = $avg['song_id'];
      }
    }

    $foundPids = array_flip($foundPids);

    if(count($pids)) {
      foreach($pids as $pid) {
        if(count($foundPids) < $nposts && !isset($foundPids[$pid])) {
          $foundPids[$pid] = count($foundPids);
        }
      }
    }

    $foundPids = array_flip($foundPids);

    $resPosts = array();
    if(count($foundPids)) {
      foreach($foundPids as $key => $val) {
        if(count($query->posts)) {
          foreach($query->posts as $post) {
            if($val == $post->ID) {
              $resPosts[$key] = $post;
            }
          }
        }
      }
    }

    $query->post_count = ($nposts < count($query->posts)) ? $nposts : count($query->posts);
    $query->posts = $resPosts;

  } // end function get_top_scores


  /**
   * @desc Sort the posts array in the query object by total play count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param obj (passed by reference) &$query The query object to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since query object is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_top_plays(&$query, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $pids = array();
    if(count($query->posts)) {
      foreach($query->posts as $post) {
        $pids[]	=	$post->ID;
      }
    }
    $pidsStr = implode(',', $pids);

    $sql = "SELECT content_id AS song_id, SUM(count) AS total_plays
            FROM users_activity
            WHERE action_id = 9
            AND content_id IN({$pidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY song_id
             ORDER BY total_plays DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    if($nposts == -1) {
      $nposts = $query->post_count;
    }

    $foundPids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $foundPids[] = $avg['song_id'];
      }
    }

    $foundPids = array_flip($foundPids);

    if(count($pids)) {
      foreach($pids as $pid) {
        if(count($foundPids) < $nposts && !isset($foundPids[$pid])) {
          $foundPids[$pid] = count($foundPids);
        }
      }
    }

    $foundPids = array_flip($foundPids);

    $resPosts = array();
    if(count($foundPids)) {
      foreach($foundPids as $key => $val) {
        if(count($query->posts)) {
          foreach($query->posts as $post) {
            if($val == $post->ID) {
              $resPosts[$key] = $post;
            }
          }
        }
      }
    }

    $query->post_count = ($nposts < count($query->posts)) ? $nposts : count($query->posts);
    $query->posts = $resPosts;

  } // end function get_top_plays


  /**
   * @desc Sort the artists array by profile view count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param arr (passed by reference) &$artists The artists array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since artists array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_most_profile_views(&$artists, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $uids = array();
    if(count($artists)) {
      foreach($artists as $artist) {
        $uids[]	=	$artist->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    $sql = "SELECT content_id AS artist_id, SUM(count) AS total_profile_views
            FROM users_activity
            WHERE action_id = 4
            AND content_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY artist_id
             ORDER BY total_profile_views DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['artist_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resArtists = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($artists)) {
          foreach($artists as $artist) {
            if($val == $artist->ID) {
              $resArtists[$key] = $artist;
            }
          }
        }
      }
    }

    $artists = $resArtists;
  } // end function get_most_profile_views


  /**
   * @desc Sort the artists that a particular fan is following by sum of profile view count and song sample count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-05-09
   * @param int $user_id The id of the fan
   * @param arr (passed by reference) &$artists The artists array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since artists array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_fav_artists($user_id, &$artists, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $uids = array();
    if(count($artists)) {
      foreach($artists as $artist) {
        $uids[]	=	$artist->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    $sql = "SELECT content_id AS artist_id, SUM(count) AS score
            FROM users_activity
            WHERE action_id = 5
            AND user_id = $user_id
            AND content_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY artist_id
             ORDER BY score DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['artist_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resArtists = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($artists)) {
          foreach($artists as $artist) {
            if($val == $artist->ID) {
              $resArtists[$key] = $artist;
            }
          }
        }
      }
    }

    $artists = $resArtists;
  } // end function get_fav_artists


  /**
   * @desc Sort the fans of a particular artist array by profile view count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-05-08
   * @param int $user_id The id of the artist
   * @param arr (passed by reference) &$fans The fans array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since fans array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_top_fans($user_id, &$fans, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $uids = array();
    if(count($fans)) {
      foreach($fans as $fan) {
        $uids[]	=	$fan->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    $sql = "SELECT user_id AS fan_id, SUM(count) AS total_profile_views
            FROM users_activity
            WHERE action_id = 5
            AND content_id = $user_id
            AND user_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY fan_id
             ORDER BY total_profile_views DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['fan_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resFans = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($fans)) {
          foreach($fans as $fan) {
            if($val == $fan->ID) {
              $resFans[$key] = $fan;
            }
          }
        }
      }
    }

    $fans = $resFans;
  } // end function get_top_fans


  /**
   * @desc Sort the artists array by total fan count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param arr (passed by reference) &$artists The artists array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since artists array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_most_fans(&$artists, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $uids = array();
    if(count($artists)) {
      foreach($artists as $artist) {
        $uids[]	=	$artist->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    $sql = "SELECT content_id AS artist_id, SUM(id) AS total_fans
            FROM users_activity
            WHERE action_id = 5
            AND content_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY artist_id
             ORDER BY total_fans DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['artist_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resArtists = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($artists)) {
          foreach($artists as $artist) {
            if($val == $artist->ID) {
              $resArtists[$key] = $artist;
            }
          }
        }
      }
    }

    $artists = $resArtists;
  } // end function get_most_fans


  /**
   * @desc Sort the fans array by total friend count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param arr (passed by reference) &$fans The fans array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since fans array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_most_friends(&$fans, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $uids = array();
    if(count($fans)) {
      foreach($fans as $fan) {
        $uids[]	=	$fan->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    $sql = "SELECT user_id AS fan_id, COUNT(id) AS total_friends
            FROM users_activity
            WHERE action_id = 6
            AND user_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY fan_id
             ORDER BY total_friends DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['fan_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resfans = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($fans)) {
          foreach($fans as $fan) {
            if($val == $fan->ID) {
              $resfans[$key] = $fan;
            }
          }
        }
      }
    }

    $fans = $resfans;
  } // end function get_most_friends


  /**
   * @desc Sort the fans array by wall post count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param arr (passed by reference) &$fans The fans array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since fans array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_most_wall_posts(&$fans, $nposts=-1, $start=NULL, $end=NULL) {
    global $wpdb;

    $uids = array();
    if(count($fans)) {
      foreach($fans as $fan) {
        $uids[]	=	$fan->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    $sql = "SELECT user_id AS fan_id, COUNT(id) AS total_wall_posts
            FROM users_activity
            WHERE action_id = 7
            AND user_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    $sql .= "GROUP BY fan_id
             ORDER BY total_wall_posts DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['fan_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resfans = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($fans)) {
          foreach($fans as $fan) {
            if($val == $fan->ID) {
              $resfans[$key] = $fan;
            }
          }
        }
      }
    }

    $fans = $resfans;
  } // end function get_most_wall_posts


  /**
   * @desc Sort the fans array by most rated activity count from the activity table (optionally within a specified date range)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param arr (passed by reference) &$fans The fans array to be sorted
   * @param int [OPTIONAL] $nposts The number of artists to limit the results to
   * @param str [OPTIONAL] $start The start of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @param str [OPTIONAL] $end The end of the time window in datetime format (e.g. 2012-03-10 00:00:01)
   * @return none (since fans array is passed by reference, all processing is done with the original container in memory and no return value is necessary)
  */
  public static function get_top_raters(&$fans, $nposts=-1, $start=NULL, $end=NULL, $is_cover=-1) {
    global $wpdb;

    $uids = array();
    if(count($fans)) {
      foreach($fans as $fan) {
        $uids[]	=	$fan->ID;
      }
    }
    $uidsStr = implode(',', $uids);

    // If filtering by cover or original song type, then get list of song ids that match
    $sids = array();
    if($is_cover > -1) {
  	  $args = array(
    	  'post_type'       => 'song',
        'is_cover'        => $is_cover,
    	  'post_status'     => 'publish',
        'posts_per_page'  => -1,
        'orderby'         => 'none',
  	  );
  	  $query = new WP_Query($args);
      if(count($query->posts)) {
        foreach($query->posts as $song) {
          $sids[] = $song->ID;
        }
      }
    }

    $sql = "SELECT user_id AS fan_id, COUNT(id) AS total_rates
            FROM users_activity
            WHERE action_id = 11
            AND user_id IN({$uidsStr}) ";

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
      $sql .= "AND modified BETWEEN '$start' AND '$end' ";
    }

    // If there are song ids to filter by, append the condition to the query
    if(count($sids)) {
      $sidsStr = implode(',', $sids);
      $sql .= "AND content_id IN({$sidsStr})";
    }

    $sql .= "GROUP BY fan_id
             ORDER BY total_rates DESC";

    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    $res = $wpdb->get_results($sql);

    $founduids = array();
    if(count($res)) {
      foreach($res as $avg) {
        $avg = get_object_vars($avg);
        $founduids[] = $avg['fan_id'];
      }
    }

    $founduids = array_flip($founduids);

    if(count($uids)) {
      foreach($uids as $uid) {
        if(count($founduids) < $nposts && !isset($founduids[$uid])) {
          $founduids[$uid] = count($founduids);
        }
      }
    }

    $founduids = array_flip($founduids);

    $resfans = array();
    if(count($founduids)) {
      foreach($founduids as $key => $val) {
        if(count($fans)) {
          foreach($fans as $fan) {
            if($val == $fan->ID) {
              $resfans[$key] = $fan;
            }
          }
        }
      }
    }

    /* Force Paul Leighton to the top! :-P
    $paul = get_users(array(
      'include' => 25,
    ));
    array_unshift($resfans, $paul[0]);
  	$resfans = array_slice($resfans, 0, 5);
*/
    $fans = $resfans;
  } // end function get_top_raters


  /**
   * @desc Get action id based on action name
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param str $action The action type name
   * @return int Returns the id of the action type
  */
  public static function get_action_id_by_name($action) {
    global $wpdb;
    $sql = "SELECT id
            FROM user_actions
            WHERE action = '$action'";
    $id = $wpdb->get_var($sql);
    return $id;
  } // end function get_action_id_by_name


  /**
   * @desc Get action value id based on action id and value (score)
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param int $action_id The action id associated with the value
   * @param int $value The value (score) for the action
   * @return int Returns the id of the action value type
  */
  public static function get_action_value_id($action_id, $value) {
    global $wpdb;
    $sql = "SELECT id
            FROM user_action_values
            WHERE action_id = '$action_id'
            AND value = '$value'";
    $id = $wpdb->get_var($sql);
    return $id;
  } // end function get_action_value_id


  /**
   * @desc Get unique activity id based on compounded key match of user, content and action
   * @author SDK (steve@eardish.com)
   * @date 2012-03-10
   * @param int $user_id The user (artist or fan) being tracked
   * @param int $content_id The content item (post or song, etc) being tracked
   * @param int $action_id The action (rate, play, view, post, fan, etc) being tracked
   * @return int Returns the id of the unique activity record found
  */
  public static function get_activity_id($user_id, $content_id, $action_id) {
    global $wpdb;
    $sql = "SELECT id
            FROM users_activity
            WHERE user_id = '$user_id'
            AND content_id = '$content_id'
            AND action_id = '$action_id'";
    $id = $wpdb->get_var($sql);
    return $id;
  } // end function get_activity_id


} // end reward class

