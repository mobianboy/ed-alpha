<?php

$sql = "SELECT p.*
        FROM wp_posts AS p, wp_usermeta AS um
        WHERE p.post_type = 'follow'
        AND p.post_content = %d
        AND p.post_author = um.user_id
        AND um.meta_key = 'profile_type'
        AND um.meta_value = 'fan'";
$friends = $wpdb->get_results($wpdb->prepare($sql, $user->ID));

$sql = "SELECT p.*
        FROM wp_posts AS p, wp_usermeta AS um
        WHERE p.post_type = 'follow'
        AND p.post_author = %d
        AND p.post_content = um.user_id
        AND um.meta_key = 'profile_type'
        AND um.meta_value = 'artist'";
$following = $wpdb->get_results($wpdb->prepare($sql, $user->ID));

?>
<div class="explorer fan">
  <? include('explodes/songs.php') ?>
  <? include('explodes/following.php') ?>
  <? include('explodes/friends.php') ?>
  <? include('explodes/photos.php') ?>
</div>

