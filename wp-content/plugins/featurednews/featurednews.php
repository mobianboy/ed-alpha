<?php

/*
Plugin Name: Featured News Tool
Plugin URI: 
Description: Eardish Featured Articles Admin Tool
Version: 1.0
Author: Steven Kornblum
*/

class featurednews {
    public function __construct() {
        if ( is_admin() ){
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
        }
    }
	
    public function add_plugin_page(){
        // This page will be under "Settings"
        add_options_page( 'Settings Admin', 'Featured News', 'manage_options', 'fnews-setting-admin', array( $this, 'create_admin_page' ) );
    }

    public function create_admin_page() {
        ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>News Settings</h2>			
	    <form method="post" action="options.php">
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields( 'fnews_option_group' );	
		    do_settings_sections( 'fnews-setting-admin' );
		    do_settings_sections( 'onradar-setting-admin' );
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
	<?php
    }
	
    public function page_init() {		
        register_setting( 'fnews_option_group', 'array_key2', array( $this, 'savenews' ) );
            
        add_settings_section(
            'featurednews_section_id',
            'Featured News Articles',
            array( $this, 'print_section_info' ),
            'fnews-setting-admin'
        );	
            
        add_settings_field(
            'news-item-1', 
            'Primary Featured Story', 
            array( $this, 'create_an_id_field1' ), 
            'fnews-setting-admin',
            'featurednews_section_id'			
        );	
            
        add_settings_field(
            'news-item-2', 
            'Featured Story', 
            array( $this, 'create_an_id_field2' ), 
            'fnews-setting-admin',
            'featurednews_section_id'			
        );	
            
        add_settings_field(
            'news-item-3', 
            'Featured Story', 
            array( $this, 'create_an_id_field3' ), 
            'fnews-setting-admin',
            'featurednews_section_id'			
        );	
            
        add_settings_field(
            'news-item-4', 
            'Featured Story', 
            array( $this, 'create_an_id_field4' ), 
            'fnews-setting-admin',
            'featurednews_section_id'			
        );	
        
        add_settings_section(
            'onradar_section_id',
            'On The Radar Articles',
            array( $this, 'print_section_infoR' ),
            'onradar-setting-admin'
        );
        
        add_settings_field(
            'radar-item-1', 
            'On The Radar Story 1', 
            array( $this, 'ontheradar1' ), 
            'onradar-setting-admin',
            'onradar_section_id'			
        );	
        
        add_settings_field(
            'radar-item-2', 
            'On The Radar Story 2', 
            array( $this, 'ontheradar2' ), 
            'onradar-setting-admin',
            'onradar_section_id'			
        );	
        
        add_settings_field(
            'radar-item-3', 
            'On The Radar Story 3', 
            array( $this, 'ontheradar3' ), 
            'onradar-setting-admin',
            'onradar_section_id'			
        );	
        
        
        add_settings_field(
            'radar-item-4', 
            'On The Radar Story 4', 
            array( $this, 'ontheradar4' ), 
            'onradar-setting-admin',
            'onradar_section_id'			
        );	
        
        add_settings_field(
            'radar-item-5', 
            'On The Radar Story 5', 
            array( $this, 'ontheradar5' ), 
            'onradar-setting-admin',
            'onradar_section_id'			
        );	
    }
	
    public function savenews( $input ) {
        global $wpdb;
        
        $sql = "UPDATE
                    `featurednews`
                SET
                    `featured1` = ".$input['featured1'].",
                    `featured2` = ".$input['featured2'].",
                    `featured3` = ".$input['featured3'].",
                    `featured4` = ".$input['featured4']."";
        
        $res = $wpdb->query($sql);
        
        $sql2 = "UPDATE
                  `ontheradar`
                SET
                  `radar1` = ".$input['radar1'].",
                  `radar2` = ".$input['radar2'].",
                  `radar3` = ".$input['radar3'].",
                  `radar4` = ".$input['radar4'].",
                  `radar5` = ".$input['radar5']."";
        
        $res2 = $wpdb->query($sql2);
        
        return ($res && $res2) ? true : false;
    }
	
    public function print_section_info(){
        print 'Select the Featured News articles below:';
    }
    
    public function print_section_infoR(){
        print 'Select the On The Radar articles below:';
    }
	
    public function create_an_id_field1(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `featurednews`.`featured1`
                FROM
                    `featurednews`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key2[featured1]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field2(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `featurednews`.`featured2`
                FROM
                    `featurednews`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key2[featured2]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field3(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `featurednews`.`featured3`
                FROM
                    `featurednews`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key2[featured3]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function create_an_id_field4(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `featurednews`.`featured4`
                FROM
                    `featurednews`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);
        
        $news = $query->posts;
        
        $output = "<select name=\"array_key2[featured4]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function ontheradar1(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `ontheradar`.`radar1`
                FROM
                    `ontheradar`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key2[radar1]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function ontheradar2(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `ontheradar`.`radar2`
                FROM
                    `ontheradar`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key2[radar2]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function ontheradar3(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `ontheradar`.`radar3`
                FROM
                    `ontheradar`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key2[radar3]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function ontheradar4(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `ontheradar`.`radar4`
                FROM
                    `ontheradar`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key2[radar4]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
    
    public function ontheradar5(){
        global $wpdb;
        
        $args = array(
            "post_type" => "post",
            "orderby" => "date",
            "order" => "desc",
            "post_status" => "publish",
            "posts_per_page" => -1,
        );
        
        $sql = "SELECT
                    `ontheradar`.`radar5`
                FROM
                    `ontheradar`";
        
        $fnewsid = $wpdb->get_var($sql);
        
        $query = new wp_query($args);

        $news = $query->posts;
        
        $output = "<select name=\"array_key2[radar5]\">";
        
        foreach ($news as $post) {
            $output .= "<option value=\"".$post->ID."\" ";
            if ($post->ID == $fnewsid) {
                $output .= "selected";
            }
            $output .= ">".$post->post_title."</option>";
        }
        
        $output .= "</select>";
        
        echo $output;
    }
}

$featurednews = new featurednews();
