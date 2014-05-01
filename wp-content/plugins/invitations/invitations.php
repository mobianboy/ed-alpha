<?php

/*
Plugin Name: Invitations
Plugin URI: 
Description: Eardish Invitations
Version: 2.0
Author: Steven Kornblum
*/

class uinvite {
    public function __construct() {
        if ( is_admin() ){
            add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
            add_action( 'admin_init', array( $this, 'page_init' ) );
        }
    }
	
    public function add_plugin_page(){
        // This page will be under "Settings"
        
        
        // select * from wp_users inner join wp_usermeta on (wp_users.ID = wp_usermeta.user_id) where wp_usermeta.meta_key = 'cpe_demo' order by wp_users.display_name ASC
        
        add_options_page( 'Settings Admin', 'Invitations Panel', 'manage_options', 'invite-setting-admin', array( $this, 'create_admin_page' ) );
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
		    settings_fields( 'invite_option_group' );	
		    do_settings_sections( 'invite-setting-admin' );
		?>
	    </form>
	</div>
	<?php
    }
	
    public function page_init() {		
        register_setting( 'invite_option_group', 'uinvite_key', array( $this, 'savecpe' ) );
            
        add_settings_section(
            'uinvite_section_id',
            'User CPE Panel',
            array( $this, 'print_section_info' ),
            'invite-setting-admin'
        );	
            
        add_settings_field(
            'invite-item-1', 
            'Users', 
            array( $this, 'create_users' ), 
            'invite-setting-admin',
            'uinvite_section_id'			
        );	
    }
	
    public function savecpe( $input ) {
        global $wpdb, $edhost;
        
        foreach ($input as $key => $val) {
          $sql = "select * from prereg where id = ".$key."";
        
          $invite = $wpdb->get_row($sql);
          
          $link = "http://".$edhost."/?token=".$invite->token;
          
            ed::send_email('email3', $invite->email, "You have been invited to join EarDish.com!", false, $invite->token, false, $link, false, true);
          
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
        
        $sql = "select * from prereg where expired IS NULL order by email ASC";
        
        $invite = $wpdb->get_results($sql);
        
        $curFL = "";
        $letters = array();
        $alpha = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
        
        $table = "<table width=\"100%\" border=\"0\">";
        foreach ($invite as $user) {
            $FL = strtoupper(substr($user->email, 0, 1));
            
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
                $table .= "<tr><td colspan=\"2\"><h2><a name=\"".$FL."\">".$FLname."</a></h2></td></tr>";
            }
            
            $table .= "<tr>";
            $table .= "<td>".$user->email." ";
            if ($user->sent) {
              $table .= "<span style=\"color:#009900; font-style: italic\">Sent</span>";
            }
            $table .= "</td>";
            
            $table .= "<td><input type=\"submit\" class=\"button button-primary\" name=\"uinvite_key[".$user->id."]\" ";
            if ($user->sent) {
                $table.= "value=\"Resend\" /></td>";
            } else {
                $table .= "value=\"Send\" /></td>";
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

$uinvite = new uinvite();
