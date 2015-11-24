<?php
/*
Plugin Name: Post Type
Plugin URI: 
Description: Post Type Management System
Version: 2.0
Author: Steven Kornblum
*/


// Debug mode
//error_reporting(E_ALL);
//ini_set('display_errors', 1);

// Add wp actions
add_action('init', array('post_type', 'init'));


/**
 * @desc post type framework class/lib
 * @author SDK (steve@eardish.com)
 * @date 2012-04-20
 */
class post_type {


  /**
   * @desc List of meta keys that have boolean type values 
   * @author SDK (steve@eardish.com)
   * @date 2012-07-05
  */
  protected static $bools = array(
    'public'              => 1,
    'exclude_from_search' => 1,
    'publicly_queryable'  => 1,
    'show_ui'             => 1,
    'show_in_nav_menus'   => 1,
    'show_in_menu'        => 1,
    'show_in_admin_bar'   => 1,
    'map_meta_cap'        => 1,
    'hierarchical'        => 1,
    'query_var'           => 1,
    'has_archive'         => 1,
    'can_export'          => 1,
    'post_tag'            => 1,
	  'positions'           => 1,
  );

 
  /**
   * @desc List of meta keys that have array type values 
   * @author SDK (steve@eardish.com)
   * @date 2012-07-10
  */
  protected static $arrs = array(
    'supports'      => array(),
    'capabilities'  => array(),
  );


  /**
   * @desc Initialize/register the post types
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @return none
  */
  public static function init() {
    $error = FALSE; // TODO: Work on better error logic/handling
    $post_types = self::get_post_types(TRUE);
    if(count($post_types)) {
      foreach($post_types as $post_type) {
        if($post_type->active) {
          self::register_pt($post_type);
        }
      }
    }
    flush_rewrite_rules(FALSE);
  } // end function init


  /**
   * @desc Build out admin page template 
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @return none
  */
  public static function post_type_options_page() {

    // Setup scripts
    wp_register_script('post_type', plugins_url("js/post_type.js", __FILE__), array('jquery'));
    wp_enqueue_script(array('post_type'));
	
	  // Setup styles
	  wp_register_style('post_type_styles', plugins_url("css/admin.style.css", __FILE__));
	  wp_enqueue_style('post_type_styles');
	
    // Process request vars
    $action = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : '';
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : 0;
    switch($action) {
      case 'edit':
        if(isset($_REQUEST['Save'])) {
          $id = self::set_post_type($_REQUEST['name'], $_REQUEST['description'], $_REQUEST['active'], $_REQUEST['templates'], $_REQUEST['metadata'], $_REQUEST['id']);
        }
        if($id) {
          $post_type = self::get_post_type($id, TRUE);
        }
        $template_types = self::get_template_types();
        include('tpl/post_type.form.php');
        break;
      case 'delete':
        self::delete_post_type($_REQUEST['id']);
        $post_types = self::get_post_types(TRUE);
        include('tpl/post_type.list.php');
        break;
      default:
        $post_types = self::get_post_types(TRUE);
        include('tpl/post_type.list.php');
    }
	} // end function post_type_options_page


  /**
   * @desc Register a post type in wordpress
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param obj $post_type The new custom post type and all its metadata
   * @return bool Returns the success or failure of the process TODO: Need to work this logic out
  */
	public static function register_pt($post_type) {

    // Process supports data 
    $supports = array();
    if(isset($post_type->metadata['supports'])) {
      $sups = unserialize($post_type->metadata['supports']);
      if(count($sups)) {
        foreach($sups as $key => $val) {
          $supports[] = $key;
        }
      }
    }
    $post_type->metadata['supports'] = $supports;

    // Process capabilities data
    $capabilities = array();
    if(isset($post_type->metadata['capabilities'])) {
      $caps = unserialize($post_type->metadata['capabilities']);
      if(count($caps)) {
        foreach($caps as $key => $val) {
          $capabilities[] = $key;
        }
      }
    }
    $post_type->metadata['capabilities'] = $capabilities;

    // Process rewrite data
    $post_type->metadata['rewrite'] = unserialize($post_type->metadata['rewrite']);

    // Process taxonomies data
    $taxonomies = array();
    $taxonomies[] = $post_type->metadata['taxonomies'];
    if($post_type->metadata['post_tag']) $taxonomies[] = 'post_tag';

    // Setup register args
    $args = array(
      'label' => inflect::pluralize($post_type->name),
		  'labels' => array(
		    'name'                => ucfirst(inflect::pluralize($post_type->name)),
		    'singular_name'       => ucfirst($post_type->name),
		    'add_new'             => 'Add New '.ucfirst($post_type->name),
        'all_items'           => ucfirst(inflect::pluralize($post_type->name)),
		    'add_new_item'        => 'Add New '.ucfirst($post_type->name),
		    'edit_item'           => 'Edit '.ucfirst($post_type->name),
		    'new_item'            => 'New '.ucfirst($post_type->name),
		    'view_item'           => 'View '.ucfirst($post_type->name),
		    'search_items'        => 'Search '.ucfirst($post_type->name),
	      'not_found'           => 'No '.ucfirst(inflect::pluralize($post_type->name)).' found',
		    'not_found_in_trash'  => 'No '.ucfirst(inflect::pluralize($post_type->name)).' found in trash',
        'parent_item_colon'   => 'Parent '.ucfirst($post_type->name),
        'menu_name'           => ucfirst(inflect::pluralize($post_type->name)),
		  ),
      'description' => $post_type->description,
		  'public' => (bool)$post_type->metadata['public'],
      'exclude_from_search' => (bool)$post_type->metadata['exclude_from_search'],
      'publicly_queryable' => (bool)$post_type->metadata['publicly_queryable'],
      'show_ui' => (bool)$post_type->metadata['show_ui'],
      'show_in_nav_menus' => (bool)$post_type->metadata['show_in_nav_menus'],
      'show_in_menu' => (bool)$post_type->metadata['show_in_menu'],
      'show_in_admin_bar' => (bool)$post_type->metadata['show_in_admin_bar'],
	  
	    // TODO: does not work. Value has no affect on position -- needs fixing
      'menu_position' => $post_type->metadata['menu_position'], // 5 = below posts
      
	    //'menu_icon' => NULL, // need to figure this out
		  //'capability_type' => 'post',
      /*'capabilities' => array(
        'edit_post'           => 'edit_'.$post_type->name,
        'edit_posts'          => 'edit_'.inflect::pluralize($post_type->name),
        'edit_others_posts'   => 'edit_others_'.inflect::pluralize($post_type->name),
        'publish_posts'       => 'publish_'.inflect::pluralize($post_type->name),
        'read_post'           => 'read_'.$post_type->name,
        'read_private_posts'  => 'read_private_'.inflect::pluralize($post_type->name),
        'delete_post'         => 'delete_'.$post_type->name,
      ),
      'capabilities' => array(
        'edit_post'           => $post_type->metadata['capabilities']['edit_post'],
        'edit_posts'          => $post_type->metadata['capabilities']['edit_posts'],
        'edit_others_posts'   => $post_type->metadata['capabilities']['edit_other_posts'],
        'publish_posts'       => $post_type->metadata['capabilities']['publish_posts'],
        'read_post'           => $post_type->metadata['capabilities']['read_post'],
        'read_private_posts'  => $post_type->metadata['capabilities']['read_private_posts'],
        'delete_post'         => $post_type->metadata['capabilities']['delete_post'],
      ),*/
      //'map_meta_cap' => TRUE, //(bool)$post_type->metadata['map_meta_cap'],
      'hierarchical' => (bool)$post_type->metadata['hierarchical'],
		  'supports' => $post_type->metadata['supports'],
      //'register_meta_box_cb' => '', // need to look into this for custom meta box setup
      'has_archive' => (bool)$post_type->metadata['has_archive'],
      'permalink_epmask' => 'EP_PERMALINK',
		  'rewrite' => array(
		    'slug' => ($post_type->metadata['rewrite']['slug']) ? $post_type->metadata['rewrite']['slug'] : inflect::pluralize($post_type->name),
        'with_front' => FALSE, //$post_type->metadata['rewrite']['with_front'],
        'feeds' => ucfirst(inflect::pluralize($post_type->name)),
        'pages' => $post_type->metadata['rewrite']['pages'],
        'ep_mask' => $post_type->metadata['rewrite']['ep_mask'],
		  ),
      'query_var' => (bool)$post_type->metadata['query_var'],
      'can_export' => (bool)$post_type->metadata['can_export'],
		);

    // If count of tax array, then set up arg
    if(count($taxonomies)) {
      $args['taxonomies'] = $taxonomies;
    }

    // Register the post type in the wp system
		$pt = register_post_type($post_type->name, $args);

    // Register taxonomy for post type
    $tax = register_taxonomy($taxonomies[0], $post_type->name, array(
      'label'                 => inflect::pluralize($taxonomies[0]),
      'hierarchical'          => TRUE,
    ));

    // Return status
    //return ($pt) ? TRUE : FALSE; // TODO: expand
	} // end function register_pt


  /**
   * @desc Create/Edit a post type 
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param str $name The name of the snew post type 
   * @param str $description The description 
   * @param bool $active The enable/disable option
   * @param arr $templates The template types being supported for this post type
   * @param arr $meta The metadata options 
   * @param int $id [OPTIONAL] The id of the post type being edited, otherwise assumes create a new record
   * @return int Returns the id of the row affected (insert, replace or update) 
  */
  public static function set_post_type($name, $description, $active, $templates=array(), $meta=array(), $id=NULL) {
    global $wpdb;

    // If $id is passed in, update the matching record, otherwise insert a new record
    $qtype = ($id > 0) ? 'UPDATE' : 'INSERT INTO';

    // Initialize the data array
    $data = array(
      'name'        => $name,
      'description' => $description,
      'active'      => ($active) ? 1 : 0,
    );
 
    // Build query
    $sql = "$qtype post_types SET ";
    if(count($data)) {
      foreach($data as $k => $v) {
        $sql .= "$k = '$v', ";
      }
    }
    $sql .= "modified = NOW()";
    if($qtype == 'UPDATE') {
      $sql .= " WHERE id = '$id'";
    }

    // Run query
    $wpdb->query($sql);

    // Get affected record ID
    $id = ($qtype == 'UPDATE') ? $id : $wpdb->insert_id;

    // Set templates
    $template_types = self::get_template_types();
    if(count($template_types)) {
      foreach($template_types as $template_type) {
        if(isset($templates[$template_type->id])) {
          self::set_template($id, $template_type->id);
        } else {
          self::delete_template($id, $template_type->id);
        }
      }
    }

    // Set meta data
    if(count($meta)) {
      foreach($meta as $key => $val) {
        if($key == 'supports' || $key == 'capabilities') {
          $val = serialize($val);
        }
        self::set_post_type_meta($id, $key, $val);
        unset(self::$bools[$key]);
        unset(self::$arrs[$key]);
      }
    }

    // Set false bool vals
    if(count(self::$bools)) {
      foreach(self::$bools as $key => $val) {
        self::set_post_type_meta($id, $key, FALSE);
      }
    }

    // Set empty arr vals
    if(count(self::$arrs)) {
      foreach(self::$arrs as $key => $val) {
        self::set_post_type_meta($id, $key, serialize($val));
      }
    }

    // Return "touched" record ID
    return $id;
  } // end function set_post_type 


  /**
   * @desc Create/Edit a post type meta key/value pair 
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param int $post_type_id - The id of the post type being referenced in the meta data 
   * @param str $meta_key - The meta key 
   * @param str $meta_value - The meta value 
   * @return int - Returns the id of the row affected (insert, replace or update) 
  */
  public static function set_post_type_meta($post_type_id, $meta_key, $meta_value) {
    global $wpdb;

    // Boolean values
    if(is_bool($meta_value)) {
      $meta_value = ($meta_value) ? 1: 0;
    }

    // Initialize the data array
    $data = array(
      'post_type_id'  => $post_type_id,
      'meta_key'      => $meta_key,
      'meta_value'    => $meta_value,
    );
 
    // Build query
    $sql = "REPLACE INTO post_type_meta SET ";
    if(count($data)) {
      foreach($data as $k => $v) {
        $sql .= "$k = '$v', ";
      }
    }
    $sql .= "modified = NOW()";

    // Run query
    $wpdb->query($sql);

    // Return "touched" record ID
    return $wpdb->insert_id;
  } // end function set_post_type_meta


  /**
   * @desc Delete a post type and assoc metadata
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param int $id The id of the post type
   * @return bool
  */
  public static function delete_post_type($id) {
    global $wpdb;

    // Delete post type
    $sql = "DELETE FROM post_types
            WHERE id = %d";
    $delete = $wpdb->query($wpdb->prepare($sql, $id));

    // Delete metadata
    $sql = "DELETE FROM post_type_meta
            WHERE post_type_id = %d";
    $delete = $wpdb->query($wpdb->prepare($sql, $id));

    // Return success/failure status
    return ($delete) ? TRUE : FALSE;
  } // end function delete_post_type
 

  /**
   * @desc Delete a post type meta key/val pair
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param int $meta_id The id of the post type
   * @return bool
  */
  public static function delete_post_type_meta($meta_id) {
    global $wpdb;

    // Build query
    $sql = "DELETE FROM post_type_meta WHERE meta_id = %d";
    
    // Run query
    $delete = $wpdb->query($wpdb->prepare($sql, $meta_id));

    // Return success/failure status
    return ($delete) ? TRUE : FALSE;
  } // end function delete_post_type_meta


  /**
   * @desc Get post type ID by name
   * @author SDK (steve@eardish.com)
   * @date 2012-10-16
   * @param str $post type - post type name to query
   * @return int - the ID of the post type
  */
  public static function get_post_type_id_by_name($post_type) {
    global $wpdb;

    // Build query
    $sql = "SELECT id
            FROM post_types
            WHERE name = %s";

    // Run query
    $res = $wpdb->get_var($wpdb->prepare($sql, $post_type));

    // Return result
    return $res;
  } // end function get_post_type_id_by_name


  /**
   * @desc Get post type name by ID
   * @author SDK (steve@eardish.com)
   * @date 2012-11-10
   * @param str $post type_id - post type ID to query
   * @return str - the name of the post type
  */
  public static function get_post_type_name_by_id($post_type_id) {
    global $wpdb;

    // Build query
    $sql = "SELECT name
            FROM post_types
            WHERE id = %d";

    // Run query
    $res = $wpdb->get_var($wpdb->prepare($sql, $post_type_id));

    // Return result
    return $res;
  } // end function get_post_type_name_by_id


  /**
   * @desc Get template name by ID
   * @author SDK (steve@eardish.com)
   * @date 2012-11-10
   * @param str $template type_id - template type ID to query
   * @return str - the name of the template type
  */
  public static function get_template_type_name_by_id($template_type_id) {
    global $wpdb;

    // Build query
    $sql = "SELECT name
            FROM balls_template_types
            WHERE id = %d";

    // Run query
    $res = $wpdb->get_var($wpdb->prepare($sql, $template_type_id));

    // Return result
    return $res;
  } // end function get_template_type_name_by_id


  /**
   * @desc Get single post type 
   * @author SDK (steve@eardish.com)
   * @date 2012-06-06
   * @param int $id post type id to filter 
   * @param bool [OPTIONAL] $meta Include metadata? 
   * @return obj
  */
  public static function get_post_type($id, $meta=FALSE) {
    global $wpdb;

    // Build query
    $sql = "SELECT *
            FROM post_types
            WHERE id = $id";

    // Run query
    $res = $wpdb->get_row($sql);

    // Get templates data
    $res->templates = array();
    $templates = balls::get_templates_by_post_type($id);
    if(count($templates)) {
      foreach($templates as $template) {
        $res->templates[$template->tt_id] = $template->template_type;
      }
    }

    // If metadata is requested, append it to the array
    if($meta) {
      $res->metadata = array();
      $metadata = self::get_post_type_meta(array($id));
      if(count($metadata)) {
        foreach($metadata as $meta) {
          $res->metadata[$meta->meta_key] = $meta->meta_value;
        }
      }
      // Unserialize into arrays 
      $res->metadata['supports'] = unserialize($res->metadata['supports']);
      $res->metadata['capabilities'] = unserialize($res->metadata['capabilities']);
      $res->metadata['rewrite'] = unserialize($res->metadata['rewrite']);
    }

    // Return result
    return $res;
  } // end function get_post_type


  /**
   * @desc Get post type list (or just single post type) 
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param bool [OPTIONAL] $meta Include metadata? 
   * @param bool [OPTIONAL] $active Filter on active
   * @param arr [OPTIONAL] $ids An array of post type ids to filter 
   * @param str [OPTIONAL] $orderby The sort param 
   * @param int [OPTIONAL] $nposts The number of post types to limit 
   * @return arr 
  */
  public static function get_post_types($meta=FALSE, $active=FALSE, $ids=array(), $orderby=NULL, $nposts=-1) {
    global $wpdb;

    // Build query
    $sql = "SELECT *
            FROM post_types
            WHERE 1=1 ";

    // If ids are passed in, filter the query
    if(count($ids)) {
      $pids = implode("', '", $ids);
      $sql .= "AND id IN($pids) ";
    }

    // If active is passed in, filter the query
    if($active) {
      $sql .= "AND active = 1 ";
    }

    // If sorting is passed in, append order by clause
    if($orderby) {
      $sql .= "ORDER BY $orderby";
    }

    // If number of posts is specified, append limit clause
    if($nposts > 0) {
      $sql .= " LIMIT $nposts";
    }

    // Run query
    $res = $wpdb->get_results($sql);

    // If metadata is requested, append it to the array
    if($meta) {
      if(count($res)) {
        foreach($res as $k => $v) {
          $metadata = self::get_post_type_meta(array($v->id));
          if(count($metadata)) {
            foreach($metadata as $meta) {
              $res[$k]->metadata[$meta->meta_key] = $meta->meta_value;
            }
          }
        }
      }
    }

    // Return results
    return $res;
  } // end function get_post_types
  

  /**
   * @desc Get post type metadata
   * @author SDK (steve@eardish.com)
   * @date 2012-04-20
   * @param arr [OPTIONAL] $ids An array of post type ids to filter 
   * @return bool
  */
  public static function get_post_type_meta($ids=array()) {
    global $wpdb;

    // Build query
    $sql = "SELECT *
            FROM post_type_meta ";

    // If ids are passed in, filter the query
    if(count($ids)) {
      $pids = implode("', '", $ids);
      $sql .= "WHERE post_type_id IN($pids) ";
    }

    // Run query
    $res = $wpdb->get_results($sql);

    // Return results
    return $res;
  } // end function get_post_type_meta


  /**
   * @desc Get template types
   * @author SDK (steve@eardish.com)
   * @date 2012-11-10
   * @return arr - List of template type objects
  */
  public static function get_template_types() {
    global $wpdb;

    // Build query
    $sql = "SELECT *
            FROM balls_template_types ";

    // Run query
    $res = $wpdb->get_results($sql);

    // Return results
    return $res;
  } // end function get_template_types
 

  /**
   * @desc Delete a post type/template type relationship
   * @author SDK (steve@eardish.com)
   * @date 2012-11-10
   * @param int $post_type_id - The id of the post type being referenced in the relationship
   * @param int $template_type_id - The id of the template type being referenced in the relationship
   * @return bool
  */
  public static function delete_template($post_type_id, $template_type_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($post_type_id) || !isset($template_type_id)) {
        throw new Exception('Need to provide post_type_id and template_type_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "DELETE FROM balls_templates WHERE 
            post_type_id = %d
            AND template_type_id = %d";

    // Run query
    $delete = $wpdb->query($wpdb->prepare($sql, $post_type_id, $template_type_id));

    // Return success/failure status
    return ($delete) ? TRUE : FALSE;
  } // end function delete_template


  /**
   * @desc Create/Edit a post type/template type relationship
   * @author SDK (steve@eardish.com)
   * @date 2012-11-10
   * @param int $post_type_id - The id of the post type being referenced in the relationship
   * @param int $template_type_id - The id of the template type being referenced in the relationship
   * @return int - Returns the id of the row affected (insert, replace or update) 
  */
  public static function set_template($post_type_id, $template_type_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($post_type_id) || !isset($template_type_id)) {
        throw new Exception('Need to provide post_type_id and template_type_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Get post type name
    $post_type = self::get_post_type_name_by_id($post_type_id);

    // Get template type name
    $template_type = self::get_template_type_name_by_id($template_type_id);

    // Make relationship name
    $name = "$post_type $template_type";

    // Build query
    $sql = "REPLACE INTO balls_templates SET
            post_type_id = %d,
            template_type_id = %d,
            name = %s";

    // Run query
    $wpdb->query($wpdb->prepare($sql, $post_type_id, $template_type_id, $name, $name));

    // Return "touched" record ID
    return $wpdb->insert_id;
  } // end function set_template
 

} // end class post_type

