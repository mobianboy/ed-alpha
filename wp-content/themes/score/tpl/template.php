<?php

/*
Description: BALLS View / Template Builder
Author: SDK (steve@eardish.com)
Date: 2012-07-13
*/


// Which post type?
switch($post_type) {

  // Post type user
  case 'user':
    
    // Archive?
    if(in_array($template, array('archive', 'archiveLoop'))) {
      
      // Default pagination calculations
      $posts_per_page = 25;
      $offset = ($page - 1) * $posts_per_page;

      // Sort and pagination args
			$args = array(
				'orderby'         => $orderby,
				'order'           => $order,
				//'offset'          => $offset,
				'number'          => $posts_per_page,
      );

      // Process profile type filter
      $tax1 = FALSE;
      if(isset($tax['profileType']) && strlen($tax['profileType'])) {
        $args['meta_query'][] = array(
          'key'     => 'profile_type',
          'value'   => $tax['profileType'],
          'compare' => '=',
        );
        $tax1 = TRUE;
      }

      // Process genre filter
      $tax2 = FALSE;
      if(isset($tax['genre']) && strlen($tax['genre'])) {
        $args['meta_query'][] = array(
          'key'     => 'main_genre',
          'value'   => $tax['genre'],
          'compare' => '=',
        );
        $tax2 = TRUE;
      }

      // Add a meta_query relationship
      if($tax1 && $tax2) {
        $args['meta_query']['relation'] = 'AND';
      }
      

      // Process search query
      if($search) {

        // Prevent sql injection
        $search = mysql_real_escape_string($search);

        // Search users
        $res = user::search_users($search);

        // If search results exist, then conditionalize wp_query
        if(count($res)) {
          $args['include'] = $res;
        }

      } // end search

      // TODO: Process visibility filters


      // Process exclusions
      if(count($exclude)) {
        $args['exclude'] = $exclude;
      }

      // Query users
      $users = get_users($args);

      // Process results
      if(count($users)) {
        foreach($users as $key => $user) {

          // Filter by location
          if(in_array($template, array('archive', 'archiveLoop')) && $loc_zip && $loc_rad) {
            $zips = location::getZipList($loc_zip, $loc_rad);
            $location = user::get_user_location($user->ID);
            if(!in_array($location, $zips)) {
              unset($users[$key]);
              continue;
            }
          }

          // Metadata
          $users[$key]->meta = get_metadata('user', $user->ID);

          // Append to excludes
          $exclude[] = $user->ID;

        }
      } // end loop through get_users results

    // User single template / profile
    } else {

      // Process profile ID
      if(empty($content)) $content = get_current_user_id();
      $field = (is_numeric($content)) ? 'id' : 'slug';

      // Get specific user
      $user = get_user_by($field, $content);

      // Get meta data
      $user->meta = get_metadata('user', $user->ID);
      
      // Force default profile type to fan
      $profile_type = ($user->meta['profile_type'][0]) ? $user->meta['profile_type'][0] : 'fan';
    
    }
    break;
  // End post type user 

  // All custom post types
  default:

    // Initialize the args array for wp_query, needs filters and sorts also per post type conditions
    $args = array();
    $features = array();
    $dish_picks = array();

    // Archive?
    if(in_array($template, array('archive', 'archiveLoop'))) {

      // Status of posts
      $post_status = 'publish';

      // Count of posts for pagination
      switch($post_type) {
        case 'song':
          $posts_per_page = 25;
          break;
        case 'post':
          $posts_per_page = 7;
          break;
        default:
          $posts_per_page = 15;
      }

      // Pagination offset
      $offset = ($page - 1) * $posts_per_page;

      // Default parent
      $parent = NULL;

      // Notification exception args
      if($post_type == 'notification') {
        $post_status =  'any';
        $orderby = 'ID';
        $order = 'DESC';
        $posts_per_page = -1;
      }

      // Message exception args
      if($post_type == 'message') {
        $order = 'ASC';
      }

      // Song exception args
      if($post_type == 'song') {
        $orderby = 'date';
        $order = 'DESC';
      }

      // Comment exception args 
      if($post_type == 'comment') {
        $parent = $content;
        $posts_per_page = -1;
        $offset = NULL;
        $orderby = 'date';
        $order = 'ASC';
      }

      // Base args array
      $args = array(
        'post_status'     => $post_status,
        //'offset'          => $offset,
        'posts_per_page'  => $posts_per_page,
        'order'           => $order,
        'orderby'         => $orderby,
        'post_parent'     => $parent,
      );

      // Process taxonmies
      if($tax) {
        foreach($tax as $tax_key => $tax_val) {
          if(!empty($tax_val)) {
            $args[$tax_key] = $tax_val;
          }
        }
      }

      // Process tags
      if($tag) {
        $args['tag'] = $tag;
      }

      // Process search query
      if($search) {

        // Prevent sql injection
        $search = mysql_real_escape_string($search);

        // Search songs (song title and artist names)
        switch($post_type) {
          case 'song':
            $res = song::search_songs($search);
            break;
          case 'post':
            $res = news::search_news($search);
            break;
          default:
            $res = array();
        }

        // If search results exist, then conditionalize wp_query
        if(count($res)) {
          $args['post__in'] = $res;
        }

      } // end search

      // News article exclusions from featured list
      if($post_type == 'post') {
          $sql = "SELECT 
                    *
                  FROM
                    `featurednews`";
          
          $res = $wpdb->get_row($sql);
          $features = array($res->featured1, $res->featured2, $res->featured3, $res->featured4);
      }

      // Song exclusions from dish_picks list
      if($post_type == 'song') {
          $sql = "SELECT 
                    *
                  FROM
                    `dishpicks`";
          
          $res = $wpdb->get_row($sql);
          
          $dish_picks = array($res->pick1, $res->pick2, $res->pick3, $res->pick4, $res->pick5);
          
        /*$dish_picks = array(
          15171, // Can Somebody by Fatiniza
          15173, // Muscle Cars, Muscle Shirts and Muscle Shoals by Sabastian Roberts
          14770, // Weatherman by Dead Sara
          15180, // Easy Does It by Wildstreet
          15177, // Sunshine by The New Futures
        );*/
      }

      // Process exclusions
      $exclude = array_merge($features, $dish_picks, $exclude);
      if(count($exclude)) {
        $args['post__not_in'] = $exclude;
      }

      // Playlist exception args
      if($post_type == 'playlist') {
        $args['author'] = get_current_user_id();
      }

      // Filter on post title (Search?)
      if($content && $post_type != 'comment') {
        add_filter('posts_where', 'filter_query_collection');
      }

      // Search contents of notifications?
      if($post_type == 'notification') {
        add_filter('posts_where', 'filter_query_notes');
      }

    // Single templates
    } else {
      $content = (is_numeric($content)) ? $content : balls::get_id_by_slug($content); // If slug, get ID
      $args['post__in'] = array($content);
      $args['post_status'] = 'any';
    }

    // Force post type into args
    $args['post_type'] = $post_type;

    // Run wp_query
    if(in_array($template, array('single', 'form', 'archive', 'archiveLoop'))) {
      $posts = new WP_Query($args);
    }

    // Remove filters
    remove_filter('posts_where', 'filter_query_collection');
    remove_filter('posts_where', 'filter_query_notes');

    // Process results
    if(count($posts->posts)) {
      foreach($posts->posts as $key => $post) {
        
        // Clean up shout and comment data
        if(in_array($post_type, array('shout', 'comment'))) {
          $post_content = $post->post_content;
          $post_content = preg_replace("~\<.*?\>~", '', $post_content);
          $post_content = preg_replace("~\<\/.*?\>~", '', $post_content);
          $posts->posts[$key]->post_content = $post_content;
        }

        // Add metadata
        $posts->posts[$key]->meta = get_metadata('post', $post->ID);

        // Add author data
        $posts->posts[$key]->owner = get_user_by('id', $post->post_author);

        // Filter by location
        if(in_array($template, array('archive', 'archiveLoop')) && $loc_zip && $loc_rad) {
          $zips = location::getZipList($loc_zip, $loc_rad);
          if(!in_array(user::get_user_location($post->post_author), $zips)) {
            unset($posts->posts[$key]);
            $posts->post_count -= 1;
          }
        }

        // Append to excludes
        $exclude[] = $post->ID;
      }
    }
    break;
  // End custom post type

} // End post type switch

// Include dynamic post type template
include("$post_type/$template.php");

// Reset wp query
wp_reset_query();

