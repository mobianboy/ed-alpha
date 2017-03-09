<?php

/*
Plugin Name: Dish Picks
Plugin URI: 
Description: Eardish Featured Music Selections Admin Tool
Version: 1.0
Author: Steven Kornblum
*/

class dishpicks {
    public function __construct() {
        if ( is_admin() ){
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
        }
    }
	
    public function add_plugin_page(){
        // This page will be under "Settings"
        add_options_page( 'Settings Admin', 'Dish Picks', 'manage_options', 'pick-setting-admin', array( $this, 'create_admin_page' ) );
    }

    public function create_admin_page() {
        ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>Dish Picks</h2>			
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields( 'pick_option_group' );	
		    do_settings_sections( 'pick-setting-admin' );
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
	<?php
    }
	
    public function page_init() {		
        register_setting( 'pick_option_group', 'array_key', array( $this, 'savepick' ) );
            
        add_settings_section(
            'pick_section_id',
            'Picks',
            array( $this, 'print_section_info' ),
            'pick-setting-admin'
        );	
            
        add_settings_field(
            'pick-item-1', 
            'Pick 1', 
            array( $this, 'create_an_id_field1' ), 
            'pick-setting-admin',
            'pick_section_id'			
        );	
            
        add_settings_field(
            'pick-item-2', 
            'Pick 2', 
            array( $this, 'create_an_id_field2' ), 
            'pick-setting-admin',
            'pick_section_id'			
        );	
            
        add_settings_field(
            'pick-item-3', 
            'Pick 3', 
            array( $this, 'create_an_id_field3' ), 
            'pick-setting-admin',
            'pick_section_id'			
        );	
            
        add_settings_field(
            'pick-item-4', 
            'Pick 4', 
            array( $this, 'create_an_id_field4' ), 
            'pick-setting-admin',
            'pick_section_id'			
        );	
           
        add_settings_field(
            'pick-item-5', 
            'Pick 5', 
            array( $this, 'create_an_id_field5' ), 
            'pick-setting-admin',
            'pick_section_id'			
        );
    }
	
    public function savepick( $input ) {
        global $wpdb;
        
        $sql = "UPDATE
                    `dishpicks`
                SET
                    `pick1` = ".$input['featured1'].",
                    `pick2` = ".$input['featured2'].",
                    `pick3` = ".$input['featured3'].",
                    `pick4` = ".$input['featured4'].",
                    `pick5` = ".$input['featured5']."";
        
        $res = $wpdb->query($sql);
        
        return ($res) ? true : false;
    }
	
    public function print_section_info(){
        print 'Select the songs below:';
    }
	
    public function create_an_id_field1(){
        global $wpdb;
        
        $args = array(
            "post_type" => "song",
            "orderby" => "title",
            "order" => "asc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `dishpicks`.`pick1`
                FROM
                    `dishpicks`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key[featured1]\">";
        
        foreach ($news as $post) {
            $artist = song::get_artist($post->post_author);
            
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title." by ".$artist."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field2(){
        global $wpdb;
        
        $args = array(
            "post_type" => "song",
            "orderby" => "title",
            "order" => "asc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `dishpicks`.`pick2`
                FROM
                    `dishpicks`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key[featured2]\">";
        
        foreach ($news as $post) {
            $artist = song::get_artist($post->post_author);
            
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title." by ".$artist."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field3(){
        global $wpdb;
        
        $args = array(
            "post_type" => "song",
            "orderby" => "title",
            "order" => "asc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `dishpicks`.`pick3`
                FROM
                    `dishpicks`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key[featured3]\">";
        
        foreach ($news as $post) {
            $artist = song::get_artist($post->post_author);
            
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title." by ".$artist."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field4(){
        global $wpdb;
        
        $args = array(
            "post_type" => "song",
            "orderby" => "title",
            "order" => "asc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `dishpicks`.`pick4`
                FROM
                    `dishpicks`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key[featured4]\">";
        
        foreach ($news as $post) {
            $artist = song::get_artist($post->post_author);
            
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title." by ".$artist."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field5(){
        global $wpdb;
        
        $args = array(
            "post_type" => "song",
            "orderby" => "title",
            "order" => "asc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `dishpicks`.`pick5`
                FROM
                    `dishpicks`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key[featured5]\">";
        
        foreach ($news as $post) {
            $artist = song::get_artist($post->post_author);
            
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title." by ".$artist."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
}

$dishpicks = new dishpicks();
