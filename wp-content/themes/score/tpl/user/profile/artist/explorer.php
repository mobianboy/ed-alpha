<?php

$sql = "SELECT *
        FROM wp_posts
        WHERE post_type = 'follow'
        AND post_content = '{$user->ID}'";
$fans = $wpdb->get_results($sql);

$sql = "SELECT *
        FROM wp_posts
        WHERE post_type = 'follow'
        AND post_author = '{$user->ID}'";
$following = $wpdb->get_results($sql);

?>
<div class="explorer artist">
  <? include('explodes/bio.php') ?>
  <? include('explodes/songs.php') ?>
  <? include('explodes/videos.php') ?>
  <? include('explodes/photos.php') ?>
  <? include('explodes/fans.php') ?>
  <? include('explodes/following.php') ?>
  <? include('explodes/rewards.php') ?>
  <? include('explodes/playlists.php') ?>
</div>

