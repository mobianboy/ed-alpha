<?php
/*
Plugin Name: News Tool
Plugin URI: 
Description: Eardish News System
Version: 2.0
Author: Steven Kornblum
*/

// Include WP MVC Lib
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
include_once($_SERVER['DOCUMENT_ROOT'].'/wp-includes/wp-db.php');

/**
 * @desc article management lib
 * @author SDK (steve@eardish.com)
 * @date 2013-01-21
 */
class news {


  /**
   * @desc Get article
   * @author SDK (steve@eardish.com)
   * @date 2012-08-22
   * @param int $id - Specifc article to query
   * @return obj - Return article object
  */
  public static function get_article($id) {
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
      'post_type' => 'post',
      'p'         => $id,
    );

    // Run wp_query
    $query = new wp_query($args);

    // Grab article object from posts array
    $article = $query->posts[0];

    // Return result
    return $article;
  } // end function get_article
  
  public static function get_ontheradar() {
    global $wpdb;
    
    $sql = "SELECT * FROM `ontheradar`";
    
    $radar = $wpdb->get_row($sql, "ARRAY_N");
    
    $articles = array();
    
    foreach ($radar as $val) {
      $articles[$val] = get_post($val);
      
      $articles[$val]->meta = get_post_meta($val);
    }
    
    return $articles;
  }


  /**
   * @desc Get article(s)
   * @author SDK (steve@eardish.com)
   * @date 2012-08-15
   * @param [OPTIONAL] int $id - Specifc article to query
   * @param [OPTIONAL] int $limit - How many posts per page? 
   * @param [OPTIONAL] int $offset - What page are we on?
   * @param [OPTIONAL] str $orderby - Sort on?
   * @param [OPTIONAL] str $order - Sort on?
   * @param [OPTIONAL] bool $wp - Return full wp_query object?
   * @return arr - Return array of article objects
  */
  public static function get_articles($id=NULL, $limit=15, $offset=0, $wp=FALSE, $orderby='date', $order='desc') {
    global $wpdb;

    // Build args
    $args = array(
      'post_type' => 'post',
    );

    // If passed an id, query against it
    if($id) {
      $args['p'] = $id;
    } else {
      $args['posts_per_page'] = $limit;
      $args['offset']         = $offset;
      $args['orderby']        = $orderby;
      $args['order']          = $order;
    }

    // Run wp_query
    $query = new wp_query($args);

    // Grab articles array
    $articles = $query->posts;

    // Push jordyn mallory to top
    $articles = array();
    $articles[] = get_post(2794);
    $articles[] = get_post(4888);
    $articles[] = get_post(3933);
    $articles[] = get_post(3992);
    $articles[] = get_post(4572);
    //array_unshift($articles, $jordyn);

    // Get metadata for each article
    if(count($articles)) {
      foreach($articles as $key => $article) {
        $article->meta = get_post_meta($article->ID);
        $articles[$key] = $article;
      }
    }

    // Return result (either just articles array or full wp_query object?)
    if($wp) {
      $query->posts = $articles;
      return $query;
    } else {
      return $articles;
    }
  } // end function get_articles


  /**
   * @desc Get news image
   * @author SDK (steve@eardish.com)
   * @date 2012-05-07
   * @param int $id - The post id of the related image to fetch
   * @param int $w - The width of the image
   * @param int $h - The height of the image 
   * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
   * @return str - Return the cloud URL of the news image thumb
  */
  public static function get_news_image($id, $w, $h, $archive=FALSE) {
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
    $res = ed::get_mfp_image('news', $id, $w, $h, $archive);

    // Return result (cloud url)
    return $res;
  } // end function get_news_image


  /**
   * @desc Filter article tags down to only those with the profile prefix
   * @author SDK (steve@eardish.com)
   * @date 2013-03-16
   * @param arr $tags - The array of tags associated with a particular article
   * @return arr - Return the filtered tag list
  */
  public static function filter_tags($tags) {
    global $wpdb;

    // Filter tags out of the result array that don't have the profile prefix
    $res = array();
    if(count($tags)) {
      foreach($tags as $key => $tag) {
        if(strtolower(substr($tag->name, 0, 10)) == '[profile]-') {
          $res[] = substr($tag->name, 10);
        }
      }
    }

    // Return results
    return $res;
  } // end function filter_tags


  /**
   * @desc Search articles (article titles, body, tags) 
   * @author SDK (steve@eardish.com)
   * @date 2013-04-15
   * @param str $search - Search query string
   * @return arr - Array of article ids
  */
  public static function search_news($search) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($search)) {
        throw new Exception('Need to provide search');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query articles that have titles or bodies that match the search
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_type = 'post'
            AND (
              post_title LIKE '%$search%'
              OR post_content LIKE '%$search%'
            )
            ORDER BY p.id ASC";
    $search_articles = $wpdb->get_col($sql);
    
    // Query article tags that match the search
    $sql = "SELECT p.ID
            FROM wp_posts AS p, wp_term_relationships AS tr, wp_term_taxonomy AS tt, wp_terms AS t
            WHERE post_type = 'post'
            AND p.ID = tr.object_id
            AND tr.term_taxonomy_id = tt.term_taxonomy_id
            AND tt.taxonomy = 'post_tag'
            AND tt.term_id = t.term_id
            AND t.name LIKE '%$search%'
            ORDER BY p.ID ASC";
    $search_tags = $wpdb->get_col($sql);

    // Merge, de-dup and sort the 2 results
    $res = array_merge($search_articles, $search_tags);
    $res = array_unique($res);
    sort($res);
  
    // Return the result
    return $res;
  } // end function search_news


  /**
   * @desc Get post id by slug
   * @author SDK (steve@eardish.com)
   * @date 2013-07-17
   * @param str $slug - The slug of the post to fetch
   * @return int - Return the id of the post
  */
  public static function get_post_id_by_slug($slug) {
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
  } // end function get_post_id_by_slug


  /**
   * @desc Get article slug name by id
   * @author SDK (steve@eardish.com)
   * @date 2013-06-05
   * @param int $id - Specifc article to query
   * @return str - Return article slug (post_name)
  */
  public static function get_post_name_by_id($id) {
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
  } // end function get_post_name_by_id


  /**
   * @desc Get article  
   * @author SDK (steve@eardish.com)
   * @date 2013-06-27
   * @param int $id - Specifc article to query
   * @param [OPTIONAL] int $count - Word limit 
   * @return str - Return article excerpt
  */
  function get_excerpt($excerpt, $count=NULL) {

    // Strip all xml tags
    $excerpt = strip_tags($excerpt);

    // If word limit is specified, cut up excerpt
	  if($count) {
	    $e = explode(' ', $excerpt);
      $e = array_splice($e, 0, $count);
		  $excerpt = implode(' ', $e);
	  }

    // Return results
	  return $excerpt;
  } // end function get_excerpt


} // end class news 

