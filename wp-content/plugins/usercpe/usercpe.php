<?php

/*
Plugin Name: User CPE
Plugin URI: 
Description: Eardish User CPE Panel
Version: 2.0
Author: Steven Kornblum
*/

class usercpe {
    public function __construct() {
        if ( is_admin() ){
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
        }
    }
	
    public function add_plugin_page(){
        // This page will be under "Settings"
        
        
        // select * from wp_users inner join wp_usermeta on (wp_users.ID = wp_usermeta.user_id) where wp_usermeta.meta_key = 'cpe_demo' order by wp_users.display_name ASC
        
        add_options_page( 'Settings Admin', 'User CPE Panel', 'manage_options', 'ucpe-setting-admin', array( $this, 'create_admin_page' ) );
    }

    public function create_admin_page() {
        ?>
	<div class="wrap">
	    <?php screen_icon(); ?>
	    <h2>User CPE Settings</h2>			
	    <form method="post" action="options.php">
                 <?php submit_button(); ?>
	        <?php
                    // This prints out all hidden setting fields
		    settings_fields( 'ucpe_option_group' );	
		    do_settings_sections( 'ucpe-setting-admin' );
		?>
	        <?php submit_button(); ?>
	    </form>
	</div>
	<?php
    }
	
    public function page_init() {		
        register_setting( 'ucpe_option_group', 'usercpe_key', array( $this, 'savecpe' ) );
            
        add_settings_section(
            'usercpe_section_id',
            'User CPE Panel',
            array( $this, 'print_section_info' ),
            'ucpe-setting-admin'
        );	
            
        add_settings_field(
            'ucpe-item-1', 
            'Users', 
            array( $this, 'create_users' ), 
            'ucpe-setting-admin',
            'usercpe_section_id'			
        );	
    }
	
    public function savecpe( $input ) {
        global $wpdb;
        
        foreach ($input as $key => $val) {
            if ($val == "On") {
                $val = "TRUE";
            } elseif ($val == "Off") {
                $val = "FALSE";
            } else {
                continue;
            }
            $sql = "UPDATE `wp_usermeta` SET `meta_value` = '".$val."' WHERE `meta_key` = 'cpe_demo' AND `user_id` = ".$key."";
            
            $wpdb->query($sql);
        }
        
        return true;
    }
	
    public function print_section_info(){
        print 'Change the setting for the CPE Demo for each user:';
    }
	
    public function create_users(){
        global $wpdb;
        
        $sql = "select * from wp_users inner join wp_usermeta on (wp_users.ID = wp_usermeta.user_id) where wp_usermeta.meta_key = 'cpe_demo' order by wp_users.display_name ASC";
        
        $ucpe = $wpdb->get_results($sql);
        
        $curFL = "";
        $letters = array();
        $alpha = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        
        $table = "<table width=\"100%\" border=\"0\">";
        foreach ($ucpe as $user) {
            $FL = strtoupper(substr($user->display_name, 0, 1));
            
            if (!in_array($FL, $alpha)) {
                $FL = "#";
            }
            
            if ($curFL != $FL) {
                $curFL = $FL;
                $letters[] = $FL;
                if ($FL == "#") {
                    $FL = "char";
                    $FLname = "#";
                } else {
                    $FLname = $FL;
                }
                $table .= "<tr><td colspan=\"3\"><h2><a name=\"".$FL."\">".$FLname."</a></h2></td></tr>";
            }
            
            $table .= "<tr>";
            $table .= "<td>".$user->display_name." <i>(".$user->ID.")</i></td>";
            
            $table .= "<td><input type=\"radio\" name=\"usercpe_key[".$user->ID."]\" ";
            if ($user->meta_value == "TRUE") {
                $table.= "checked value=\"Onn\" />On</td>";
            } else {
                $table .= " value=\"On\" />On</td>";
            }
            
            $table .= "<td><input type=\"radio\" name=\"usercpe_key[".$user->ID."]\" ";
            if ($user->meta_value == "FALSE") {
                $table.= "checked value=\"Offf\" />Off</td>";
            } else {
                $table .= " value=\"Off\" />Off</td>";
            }
                
            $table .= "</tr>";
        }
        $table .= "</table>";
        
        $header = "<div>";
        if (in_array("#", $letters)) {
            $header .= "<a href=\"#char\">#</a> ";
        } else {
            $header .= "# ";
        }
        foreach ($alpha as $letter) {
            $header .= "| ";
            if (in_array($letter, $letters)) {
                $header .= "<a href=\"#".$letter."\">".$letter."</a> ";
            } else {
                $header .= $letter." ";
            }
        }
        $header .= "</div>";
        
        $output = $header."<br /><br />".$table;
        
        echo $output;
    }
}

$usercpe = new usercpe();
