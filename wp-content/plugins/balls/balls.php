<?php
/*
Plugin Name: BALLS
Plugin URI: 
Description: Base Asynchronous Link Logic System
Version: 2.0
Author: Steven Kornblum
*/


// Add wp actions for admin tools and menus and balls positions register
add_action('widgets_init', array('balls', 'init'));
add_action('admin_menu', array('balls', 'balls_menu'));


/**
 * @desc balls framework class/lib
 * @author SDK (steve@eardish.com)
 * @date 2012-06-11
 */
class balls {


  /**
   * @desc Initialize/register the positions 
   * @author SDK (steve@eardish.com)
   * @date 2012-09-11
   * @return none
  */
  public static function init() {
    $error = FALSE; // TODO: Work on better error logic/handling
    $positions = self::get_positions();
    if(count($positions)) {
      foreach($positions as $position) {
        if($position->active) {
          register_sidebar(array(
			      'id'            => $position->name,
			      'name'          => $position->name,
			      'description'   => $position->description,
			      'before_widget' => '',
			      'after_widget'  => '',
			      'before_title'  => '',
			      'after_title'   => '',
			    ));
        }
      }
    }
    flush_rewrite_rules(FALSE);
  } // end function init


  /**
   * @desc Adds to admin menu
   * @author SDK (steve@eardish.com)
   * @date 2012-08-27
   * @return none
  */
  public static function balls_menu() {

    // Register & Enqueue Stylesheets
    wp_register_style('style', plugins_url('css/balls.css', __FILE__));
    wp_enqueue_style('style');

    // Register & Enqueue Javascript
    wp_register_script('balls', get_option('siteurl')."/wp-content/plugins/balls/js/balls.js", array('jquery'));
    wp_enqueue_script(array('balls'));

    // Setup menu/submenu
    add_menu_page('B.A.L.L.S.', 'B.A.L.L.S.', 'manage_options', 'balls_admin', array('balls', 'admin_widgets'));
    add_submenu_page('balls_admin', 'Widget Options', 'Widgets', 'manage_options', 'balls_admin', array('balls', 'admin_widgets'));
    add_submenu_page('balls_admin', 'Positions', 'Positions', 'manage_options', 'admin_positions', array('balls', 'admin_positions'));
    add_submenu_page('balls_admin', 'Position Map', 'Position Map', 'manage_options', 'admin_position_map', array('balls', 'admin_position_map'));
    add_submenu_page('balls_admin', 'Post Types', 'Post Types', 'manage_options', 'admin_post_types', array('post_type', 'post_type_options_page'));

	} // end function balls_menu


  /**
   * @desc Build out admin page template for position map
   * @author SDK (steve@eardish.com)
   * @date 2012-10-23
   * @return none
  */
  public static function admin_position_map() {

    // Process request vars
    $action       = (isset($_REQUEST['action']))      ? $_REQUEST['action']       : NULL;
    $template_id  = (isset($_REQUEST['template_id'])) ? $_REQUEST['template_id']  : NULL;
    $delete       = (isset($_REQUEST['delete']))      ? $_REQUEST['delete']       : NULL;
    $set          = (isset($_REQUEST['set']))         ? $_REQUEST['set']          : NULL;

    // Process delete
    if($delete) {
      $res = self::delete_position_by_template($template_id, $delete);
    }

    // Process add
    if($set) {
      $res = self::set_position_by_template($template_id, $set);
    }

    // Get post type list
	  $post_types = post_type::get_post_types(TRUE, FALSE, array(), 'name');

    // Include template
    include('tpl/balls.map.list.php');

	} // end function admin_position_map


  /**
   * @desc Build out admin page template for positions
   * @author SDK (steve@eardish.com)
   * @date 2012-10-23
   * @return none
  */
  public static function admin_positions() {

    // Process request vars
    $action       = (isset($_REQUEST['action']))      ? $_REQUEST['action']       : NULL;
    $delete       = (isset($_REQUEST['delete']))      ? $_REQUEST['delete']       : NULL;
    $set          = (isset($_REQUEST['set']))         ? $_REQUEST['set']          : NULL;
    $name         = (isset($_REQUEST['name']))        ? $_REQUEST['name']         : NULL;
    $desc         = (isset($_REQUEST['desc']))        ? $_REQUEST['desc']         : NULL;
    $ttl          = (isset($_REQUEST['ttl']))         ? $_REQUEST['ttl']          : NULL;
    $id           = (isset($_REQUEST['id']))          ? $_REQUEST['id']           : NULL;

    // Process delete
    if($delete) {
      $res = self::delete_position($delete);
    }

    // Process add
    if($set) {
      $res = self::set_position($name, $desc, $ttl, $id);
    }
	  
    // Get positions list
    $positions = self::get_positions();
    
    // Include template
    include('tpl/balls.positions.php');

	} // end function admin_positions


  /**
   * @desc Build out admin page template for widgets
   * @author SDK (steve@eardish.com)
   * @date 2012-08-27
   * @return none
  */
  public static function admin_widgets() {

    // Process request vars
    $action       = (isset($_REQUEST['action']))      ? $_REQUEST['action']       : NULL;
    $template_id  = (isset($_REQUEST['template_id'])) ? $_REQUEST['template_id']  : NULL;

    // Determine action and includes
    switch($action) {
      case 'edit':
        include('tpl/balls.form.php');
        break;
      default:
	      $post_types = post_type::get_post_types(TRUE, FALSE, array(), 'name');
        include('tpl/balls.list.php');
    }

	} // end function admin_widgets
 

  /**
   * @desc Get balls template 
   * @author SDK (steve@eardish.com)
   * @date 2012-06-06
   * @param arr $args = array(
     * @param int $id - balls template id to request 
     * @param int $post_type - post type to request
     * @param int $template - template type to request
   * ) // end args array 
   * @param bool $api - Is this request from the API?
   * @return obj
  */
  public static function get_balls_template($args=array(), $api=FALSE) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($args['post_type']) && !isset($args['template'])) {
        throw new Exception('Need to provide post_type and template');
      }
      $post_type = $args['post_type'];
      $template = $args['template'];
      if($template == 'single' && !isset($args['content'])) {
        throw new Exception('Need to provide content id for single templates');
      }
      $content      = (isset($args['content']))     ? $args['content']      : NULL;
      $page         = (isset($args['page']))        ? $args['page']         : 1;
      $orderby      = (isset($args['orderby']))     ? $args['orderby']      : NULL;
      $order        = (isset($args['order']))       ? $args['order']        : NULL;
      $tax          = (isset($args['tax']))         ? $args['tax']          : '';
      $tag          = (isset($args['tag']))         ? $args['tag']          : '';
      $loc_zip      = (isset($args['loc_zip']))     ? $args['loc_zip']      : '';
      $loc_rad      = (isset($args['loc_rad']))     ? $args['loc_rad']      : '';
      $search       = (isset($args['search']))      ? $args['search']       : '';
      $exclude      = (isset($args['exclude']))     ? $args['exclude']      : NULL;
      $hide         = (isset($args['hide']))        ? $args['hide']         : FALSE;
      $resource_id  = (isset($args['resource_id'])) ? $args['resource_id']  : NULL;
      $duration     = (isset($args['duration']))    ? $args['duration']     : NULL;
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Parse orderby param
    if(!$orderby) {
      switch($post_type) {
        case 'song':
          $orderby = 'date';
          break;
        case 'post':
          $orderby = 'date';
          break;
        case 'user':
          $orderby = 'registered';
          break;
        default:
          $orderby = 'date';
      }
    }

    // Parse order param
    if(!$order) {
      $order = 'DESC';
    }

    // Parse excludes
    if(isset($exclude)) {
      $exclude = explode(',', $exclude);
    } else {
      $exclude = array();
    }

    // Randomize ttl value for now
    $ttl = self::get_balls_ttl('template');

    // Initialize return data
    $data = '';

    // Start output buffer
    ob_start();

    // If template is not standard, then bypass to a direct include (it's kinda like a widget)
    if($template == 'explodes-videos') {
      $source = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/score/tpl/user/profile/artist/explodes/videos.php';
    } elseif(in_array($template, array('single', 'form', 'archive', 'archiveLoop'))) {
      $source = $_SERVER['DOCUMENT_ROOT'].'/wp-content/themes/score/tpl/template.php';
    } else {
      $source = $_SERVER['DOCUMENT_ROOT']."/wp-content/themes/score/tpl/$post_type/$template.php";
    }

    // Template source include and grab from output buffer into $data
    include($source);
    $data .= ob_get_contents();

    // Flush output buffer
    ob_end_clean();

    // Return result
    if($api) {
      $exclude = implode(',', $exclude);
      return array($data, $exclude);
    } else {
      return $data;
    }
  } // end function get_balls_template


  /**
   * @desc Get template by ID
   * @author SDK (steve@eardish.com)
   * @date 2012-09-10
   * @param int $id The template ID to grab
   * @return str - The template name
   */
  public static function get_template_name($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT *
            FROM balls_templates
            WHERE id = %d";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, $id));

    // Grab template object
    $template = $res[0];

    // Split seperate words into an array
    $words = explode(' ', $template->name);

    // Loop through each and capitalize first letter
    if(count($words)) {
      foreach($words as $k => $word) {
        $words[$k] = ucfirst($word);
      }
    }

    // Re-join the words
    $name = implode(' ', $words);

    // Return template name
    return $name;
  } // end function get_template_name


  /**
   * @desc Get list of templates (Types) available for each post type
   * @author SDK (steve@eardish.com)
   * @date 2012-08-27
   * @param int $post_type_id The post type to search against 
   * @return arr - A list of template objects that match the post type 
   */
  public static function get_templates_by_post_type($post_type_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($post_type_id)) {
        throw new Exception('Need to provide post_type_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT t.id AS id, t.name, t.description, t.ttl, t.modified, tt.id as tt_id, tt.name as template_type
            FROM balls_templates AS t, balls_template_types AS tt
            WHERE t.post_type_id = %d
            AND t.active = 1
            AND t.template_type_id = tt.id";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, $post_type_id));

    // Return result
    return $res;
  } // end function get_templates_by_post_type


  /**
   * @desc Get all positions
   * @author SDK (steve@eardish.com)
   * @date 2012-09-11
   * @return arr - A list of position objects
   */
  public static function get_positions() {
    global $wpdb;

    // Build query
    $sql = "SELECT *
            FROM balls_positions
            ORDER BY name ASC";

    // Run query
    $res = $wpdb->get_results($sql);

    // Return result
    return $res;
  } // end function get_positions


  /**
   * @desc Create or update a position
   * @author SDK (steve@eardish.com)
   * @date 2012-10-22
   * @param str $name - The position name
   * @param str $desc - The position description
   * @param int $ttl - The position ttl value
   * @param int [OPTIONAL] $id - The position id for update
   * @return int - The record ID if success, error if false
   */
  public static function set_position($name, $desc, $ttl, $id=NULL) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($name) || !isset($desc) || !isset($ttl)) {
        throw new Exception('Need to provide name, desc and ttl');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // If $id is passed in, update the matching record, otherwise insert a new record
    $qtype = ($id > 0) ? 'UPDATE' : 'INSERT INTO';
 
    // Build query
    $sql = "$qtype balls_positions SET
            name = %s,
            description = %s,
            ttl = %d,
            modified = NOW()";
    if($qtype == 'UPDATE') {
      $sql .= " WHERE id = '$id'";
    }

    // Run query
    $wpdb->query($wpdb->prepare($sql, $name, $desc, $ttl));

    // Get affected record ID
    $id = ($qtype == 'UPDATE') ? $id : $wpdb->insert_id;

    // Return "touched" record ID
    return $id;
  } // end function set_position


  /**
   * @desc Set a position a for a post-type template
   * @author SDK (steve@eardish.com)
   * @date 2012-10-22
   * @param int $template_id - The post type template record
   * @param int $position_id - The position record
   * @return int - The relationship record ID if true, error if false
   */
  public static function set_position_by_template($template_id, $position_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($template_id) || !isset($position_id)) {
        throw new Exception('Need to provide template_id and position_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "INSERT INTO balls_templates_positions SET
            balls_template_id = %d,
            balls_position_id = %d";

    // Run query
    $wpdb->query($wpdb->prepare($sql, $template_id, $position_id));

    // Get affected record ID
    $res = $wpdb->insert_id;

    // Return result
    return $res;
  } // end function set_position_by_template


  /**
   * @desc Delete a position
   * @author SDK (steve@eardish.com)
   * @date 2012-10-22
   * @param int $id - The id of the position
   * @return bool - Success or failure of operation
  */
  public static function delete_position($id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($id)) {
        throw new Exception('Need to provide id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query 
    $sql = "DELETE FROM balls_positions
            WHERE id = %d";

    // Run query
    $delete = $wpdb->query($wpdb->prepare($sql, $id));

    // Return success/failure status
    return ($delete) ? TRUE : FALSE;
  } // end function delete_position


  /**
   * @desc Delete a position a for a post-type template
   * @author SDK (steve@eardish.com)
   * @date 2012-10-22
   * @param int $template_id - The post type template record
   * @param int $position_id - The position record
   * @return bool - True if successful operation, error if false
   */
  public static function delete_position_by_template($template_id, $position_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($template_id) || !isset($position_id)) {
        throw new Exception('Need to provide template_id and position_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "DELETE FROM balls_templates_positions
            WHERE balls_template_id = %d
            AND balls_position_id = %d";

    // Run query
    $res = $wpdb->query($wpdb->prepare($sql, $template_id, $position_id));

    // Return result
    return ($res) ? TRUE : FALSE;
  } // end function delete_position_by_template


  /**
   * @desc Get list of positions available for each post-type template
   * @author SDK (steve@eardish.com)
   * @date 2012-09-10
   * @param int $template_id The template to search against 
   * @return arr - A list of position objects
   */
  public static function get_positions_by_template($template_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($template_id)) {
        throw new Exception('Need to provide template_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT p.*
            FROM balls_templates_positions AS tp, balls_positions AS p
            WHERE tp.balls_template_id = %d
            AND tp.balls_position_id = p.id";

    // Run query
    $res = $wpdb->get_results($wpdb->prepare($sql, $template_id));

    // Return result
    return $res;
  } // end function get_positions_by_template


  /**
   * @desc Get list of widgets available for each position
   * @author SDK (steve@eardish.com)
   * @date 2012-10-16
   * @param int $position_id The position to search against 
   * @return arr - A list of widget objects
   */
  public static function get_widgets_by_position($position_id) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($position_id)) {
        throw new Exception('Need to provide position_id');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $option_id = 102; // For now, it's always the same ID until we break this out further
    $sql = "SELECT *
            FROM wp_options
            WHERE option_id = %d";

    // Run query
    $res = $wpdb->get_row($wpdb->prepare($sql, $option_id));

    // Unserialize position data
    $positions = unserialize($res->option_value);

    // Pull specific position ID
    $widgets = $positions[$position_id];

    // Return result
    return $widgets;
  } // end function get_widgets_by_position


  /**
   * @desc Generate ttl value 
   * @author SDK (steve@eardish.com)
   * @date 2012-07-02
   * @param str $type The level of container
   * @return int - The ttl value
   */
  public static function get_balls_ttl($type='widget') {
    global $wpdb;

    // Possible ttl values
    $speeds = array(
      'template'  => 'long',
      'position'  => 'medium',
      'widget'    => 'fast',
    );

    $ttl = $speeds[$type];

    // Return result
    return $ttl;
  } // end function get_balls_ttl


  /**
  * @desc Get post ID by slug (post_name)
  * @author SDK (steve@eardish.com)
  * @date 2013-07-11
  * @param str $slug - The post slug
  * @return int - The id of the post
  */
  function get_id_by_slug($slug) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($slug)) {
        throw new Exception('Need to provide slug');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Query for post ID by slug
    $sql = "SELECT ID
            FROM wp_posts
            WHERE post_name = %s";
    $id = $wpdb->get_var($wpdb->prepare($sql, $slug));

    // Return result
    return $id;
  } // end function get_id_by_slug


  /**
   * @desc Get template object by post_type / template type combo
   * @author SDK (steve@eardish.com)
   * @date 2012-10-16
   * @param str $post_type The post type
   * @param str $template The template type (single, archive, etc)
   * @return obj - The template object 
   */
  public static function get_template($post_type, $template) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($post_type) || !isset($template)) {
        throw new Exception('Need to provide post_type and template');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT *
            FROM balls_templates
            WHERE post_type_id = %d
            AND template_type_id = %d";

    // Run query
    $res = $wpdb->get_row($wpdb->prepare($sql, $post_type, $template));

    // Return result
    return $res;
  } // end function get_template


  /**
   * @desc Get template type ID by template name
   * @author SDK (steve@eardish.com)
   * @date 2012-10-16
   * @param str $template The template name
   * @return int - The template type ID
   */
  public static function get_template_type($template) {
    global $wpdb;

    // If not provided necessary args, throw an error
    try {
      if(!isset($template)) {
        throw new Exception('Need to provide template');
      }
    } catch(Exception $e) {
      return $e->getMessage();
    }

    // Build query
    $sql = "SELECT id
            FROM balls_template_types
            WHERE name = %s";

    // Run query
    $res = $wpdb->get_var($wpdb->prepare($sql, $template));

    // Return result
    return $res;
  } // end function get_template_type


  /**
   * @desc Process permalink and provide the correct routing vars back to the theme
   * @author SDK (steve@eardish.com)
   * @date 2012-10-20
   * @param [OPT] str $href - Provide href request from API (otherwise defaults to REQUEST_URI from server)
   * @param [OPT] str $tt - Legacy template types (e.g. archiveLoop)
   * @return none - Using global vars instead
   */
  public static function balls_permalink($href=NULL, $tt=NULL) {

    // Global vars
    global $post_type, $template, $content, $sort, $tax, $tag, $permalink, $token, $forgot_token;

    // If href is passed, parse that, otherwise parse the request_uri on initial load
    $request = ($href) ? $href : $_SERVER['REQUEST_URI'];

    // Put permalink into global container for template usage
    $permalink = $request;

    // If at top level domain, force to news archive
    if($permalink == '/') {
      $permalink = '/news/';
    }

    // Parse out token if there is one in the query string for regsitration
    if(preg_match("~\?token=([^ ]*)$~", $request, $matches)) {
      $token = $matches[1];
    }

    // Parse out forgot token if there is one in the query string for forgot password procedure
    if(preg_match("~\?forgot_token=([^ ]*)$~", $request, $matches)) {
      $forgot_token = $matches[1];
    }

    // Remove query string
    $request = preg_replace('~\?.*$~', '', $request);

    // Get URL pattern between leading and trailing slashes
    $request = preg_replace('~\/(.*)\/?$~', '$1', $request);

    // Split pattern on internal slashes
    $request = explode('/', $request);

    // Process custon URL paths for archive templates
    $ptype = $request[0];

    switch($ptype) {
      case 'music':
        $ptype = 'songs';
        break;
      case 'members':
      case 'profiles':
        $ptype = 'users';
        break;
      case 'member':
      case 'profile':
        $ptype = 'user';
        break;
      case 'news':
      case 'articles':
        $ptype = 'posts';
        break;
      case 'article':
        $ptype = 'post';
        break;
    }

    // Set post type
    $post_type = (strlen($ptype)) ? inflect::singularize($ptype) : 'post';

    // Parse special template types for special post types
    if($post_type == 'account') {
      $tt = $request[1];
    }

    // Set template type
    if($tt) {
      $template = $tt;
    } else {
      $template = ($post_type == $ptype) ? 'single' : 'archive';
    }

    // Single Templates
    if($template == 'single') {
      switch(count($request)) {
        case 2:
          $content = $request[1];
          break;
        case 3:
          $cat = $request[1];
          $content = $request[2];
          break;
        default:
      }
    }

    // Default Profile
    if($post_type == 'user' && $template == 'single' && !$content) {
      $content = get_current_user_id();
    }

    // If template is single with no content id, then force to archive
    if($template == 'single' && !$content) {
      $template = 'archive';
    }

    // Archive Templates
    if($template == 'archive') {
      switch(count($request)) {
        case 2:
          $sort = $request[1];
          break;
        case 3:
        case 4:
          if($request[1] == 'tag') {
            $tag = $request[2];
          } else {
            $tax[$request[1]] = $request[2];
          }
          $sort = (isset($request[3])) ? $request[3] : NULL;
          break;
        default:
      }
    }

    // Process slugs
    if($template == 'single' && $content && !is_numeric($content)) {
      if($post_type == 'user') {
        $user = get_user_by('login', $content);
        $content = $user->ID;
      } else {
        $content = self::get_id_by_slug($content);
      }
    }

  } // end function balls_permalink


} // end class balls

