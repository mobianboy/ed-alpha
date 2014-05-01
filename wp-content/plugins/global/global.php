<?php
/*
Plugin Name: Global Lib Plugin
Description: Global Lib for eardish custom functionality 
Author: SDK
Date: 2012-03-19
*/


// Include all global libs
require_once('inflect.php');
require_once('date.php');
require_once('location.php');
require_once('mail.php');


/**
 * @desc global lib
 * @author SDK (steve@eardish.com)
 * @date 2013-07-29
 */
class ed {


	/**
	 * @desc Get cloud URL of audio file from MFP
	 * @author SDK (steve@eardish.com)
	 * @date 2013-05-09
	 * @param int $id - The post ID
	 * @param str $type - The type (demo or song)
	 * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
	 * @return str - The cloud url of the audio file
	 */
	public static function get_mfp_audio($id, $type, $archive=FALSE) {
	  global $wpdb, $instance;
	
	  // If not provided necessary args, throw an error
	  try {
	    if(!isset($id) || !isset($type)) {
	      throw new Exception('Need to provide id and type');
	    }
	  } catch(Exception $e) {
	    return $e->getMessage();
	  }
	
	  // Query DB for audio format
	  $sql = "SELECT f.s3_key
	          FROM mfp_audio AS a, mfp_audio_formats AS f
	          WHERE a.type = %s
	          AND a.type_id = %d
	          AND a.id = f.audio_id
	          AND f.format = %s
            ORDER BY f.date_modified DESC
            LIMIT 0,1";
	  $res = $wpdb->get_var($wpdb->prepare($sql, $type, $id, 'mp3'));

    if($res) {
      // Setup CDN URL
      $res = "http://".CDNA."/{$res}";
      // Strip extension
      $res = preg_replace("~\.[^.]*$~i", '', $res);
    }

	  // MFP API Call
    if(!$res && !$archive) {

      // MFP API URL
      $url = 'http://'.MFP_HOST.'/get';
     
      // Data array to be passed into API call for IMG Proc
      $data = array(
        'type'    => $type,
        'id'      => $id,
        'format'  => 'audio',
      );
      $json['data'] = json_encode($data, TRUE);
    
      // CURL The API
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, count($json));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      //curl_setopt($ch, CURLOPT_VERBOSE, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $mfp = json_decode(curl_exec($ch), TRUE);
      curl_close($ch);
    
      // Get CLOUD URL
      if($mfp['status']['code'] == 20) {
        $res = $mfp['data']['resource']['source'];
      } else {
        $res = FALSE;
      }

    } // end MFP API Call
	
	  // Strip out double slash after the domain
	  $res = preg_replace("~([^:])\/\/~", "$1/", $res);

    // If in dev environment and no resource, force a sample audio track
    /*if($instance != 'prod' && empty($res)) {
      $res = ($type == 'demo') ? 'http://'.CDNA.'/demo/15591/702' : 'http://'.CDNA.'/song/339/685';
    }*/

	  // Return results
	  return $res;
	} // end function get_mfp_audio
	
	
	/**
	 * @desc Get cloud URL of image from MFP
	 * @author SDK (steve@eardish.com)
	 * @date 2013-05-06
	 * @param str $type - The post type
	 * @param int $id - The post ID
	 * @param int $w -  The width of the thumb
	 * @param int $h -  The height of the thumb
	 * @param [OPT] bool $archive - If this is an archive page request (forces skip on api calls for performance reasons, default=FALSE)
	 * @return str - The cloud url of the image/thumb
	 */
	public static function get_mfp_image($type, $id, $w, $h, $archive=FALSE) {
	  global $wpdb, $instance;
	
	  // If not provided necessary args, throw an error
	  try {
	    if(!isset($type) || !isset($id) || !isset($w) || !isset($h)) {
	      throw new Exception('Need to provide id, type, w, and h');
	    }
	  } catch(Exception $e) {
	    return $e->getMessage();
	  }
	
	  // Query DB for image format
	  $format = 'thumb'.$w.'x'.$h;
	  $sql = "SELECT f.s3_key
	          FROM mfp_images AS i, mfp_image_formats AS f
	          WHERE i.type = %s
	          AND i.type_id = %d
	          AND i.id = f.image_id
	          AND f.format = %s
            ORDER BY f.date_modified DESC
            LIMIT 0,1";
	  $res = $wpdb->get_var($wpdb->prepare($sql, $type, $id, $format));

    // Setup CDN URL
    if($res) {
      $res = "http://".CDNI."/{$res}";
    }

	  // MFP API Call
    if(!$res && !$archive) {

      // MFP API URL
      $url = 'http://'.MFP_HOST.'/get';
     
      // Data array to be passed into API call for IMG Proc
      $data = array(
        'w'       => $w,
        'h'       => $h,
        'type'    => $type,
        'id'      => $id,
        'format'  => 'image',
      );
      $json['data'] = json_encode($data, TRUE);
    
      // CURL The API
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_POST, count($json));
      curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
      //curl_setopt($ch, CURLOPT_VERBOSE, 1);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
      $mfp = json_decode(curl_exec($ch), TRUE);
      curl_close($ch);
    
      // Get Cloud URL
      if(in_array($mfp['status']['code'], array(20, 21, 52))) {
        $res = $mfp['data']['resource']['source'];
      } else {
        $res = FALSE;
      }

    } // end MFP API Call

	  // Default replacement image
	  if(!$res) {
      $res = "http://".CDNI."/{$type}_default_thumb.svg";
	  }

	  // Default replacement image for waveform in dev environments
	  /*if($instance != 'prod' && preg_match("~waveform~i", $type)) {
      $res = 'http://'.CDN.'/images/waveform_default_thumb.svg';
	  }*/

	  // Strip out double slash after the domain
	  $res = preg_replace("~([^:])\/\/~", "$1/", $res);
	
	  // Return results
	  return $res;
	} // end function get_mfp_image
	
	
	/**
	 * @desc Clean screen output of complex data types for testing/debugging
	 * @author SDK (steve@eardish.com)
	 * @date 2012-06-13
	 * @param arr/obj $data The serialized/complex data object or array
	 * @param str [OPTIONAL] $name The label of the output
	 * @param bool [OPTIONAL] $exit Trigger exit of all scripts/processes
	 * @param bool [OPTIONAL] $advanced If true, it uses var_dump instead of pre_print
	 */
	public static function pre_print($data, $name=NULL, $exit=FALSE, $advanced=FALSE) {
	  echo "<br/>";
	  if($name) echo "<h1>$name</h1>";
	  echo "<pre>";
	  if($advanced) {
	    var_dump($data);
	  } else {
	    print_r($data);
	  }
	  echo "</pre>";
	  if($exit) exit;
	} // end function pre_print
	
	
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
	* @desc Process email alert content and send to mail queue
	* @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
	* @date 2013-07-15
	* @param str $tpl - The email template to use
	* @param int $id - The id of the recipient user
	* @param str $subject - The message subject/title/heading
	* @param str $lead - The lead of the main message (e.g. name referenced)
	* @param str $content - The body of the message
	* @param str $button - The button text
	* @param str $link - The button link URL
	* @param str $leftimage - The cloud URL of the left aligned image
	* @param [OPT] str $rightimage - The cloud URL of the right aligned image
	* @return bool - Return success or failure of operation
	*/
	public static function send_email($tpl, $id, $subject, $lead, $content, $button, $link, $leftimage, $rightimage=NULL) {
    global $wpdb;

    // User info
    if (is_numeric($id)) {
      $user = get_user_by('id', $id);
      $address = $user->user_email;
    } else {
      $address = $id;
    }

    // Setup tokens for template injection
    $args = array(
      'HEADING'     => $subject,
      'LEAD'        => $lead,
      'CONTENT'     => $content,
      'BUTTON'      => $button,
      'LINK'        => $link,
      'LEFTIMAGE'   => $leftimage,
      'RIGHTIMAGE'  => $rightimage, // used in email2 tpl only
    );

    // To send HTML mail, the Content-type header must be set
    $headers = array(
      'MIME-Version'  => '1.0',
      'Content-type'  => 'text/html; charset=iso-8859-1',
    );

    // Mail object setup
	  $sendmail = new SendMailSystem();
	  $sendmail->sendFrom('no-reply@eardish.com');
	  $mail = new MailNotification();
	  $mail->setMailer($sendmail);
    $mail->addMessage($address, $subject, $args, $tpl, $headers);
	  $res = $mail->processMessages();

	  // Return results
	  return (empty($res)) ? TRUE : FALSE;
	} // end function send_email

	
	/**
	* @desc Get video data from Youtube data API
	* @author Jordan LeDoux (jordan@eardish.com), SDK (steve@eardish.com)
	* @date 2013-09-10
	* @param int $id - The youtube id of the video
	* @return arr - Return parsed data array from youtube API
	*/
  public static function get_youtube_data($id) {
	  global $wpdb;
	
	  // If not provided necessary args, throw an error
	  try {
	    if(!isset($id)) {
	      throw new Exception('Need to provide id');
	    }
	  } catch(Exception $e) {
	    return $e->getMessage();
	  }

    // Setup API URL
    $url = "https://gdata.youtube.com/feeds/api/videos/{$id}?v=2&prettyprint=true&alt=jsonc";

    // Call API for data
    $json = file_get_contents($url);

    // Decode return data
    $info = json_decode($json, true);

    // Duration conversion calculations
    $t = $info['data']['duration'];
    $duration = sprintf('%02d:%02d:%02d', ($t / 3600), ($t / 60 % 60), $t % 60);

    // Setup data array
    $res = array(
      'id'      => $id,
      'title'   => $info['data']['title'],
      'desc'    => $info['data']['description'],
      'thumb'   => $info['data']['thumbnail']['hqDefault'],
      'length'  => $duration,
    );

    // Return results
    return $res;
  } // end function get_youtube_data


} // end class ed

