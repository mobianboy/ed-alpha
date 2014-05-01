<?php

// Get all user object to top level
$user = $user->data;

// Track profile view
if($user->ID != get_current_user_id()) {
  reward::track_activity(get_current_user_id(), $user->ID, 4);
}

?>
<section class="header">
  <? include_once("profile/$profile_type/header.php") ?>
</section>

<section class="main">
  <? include_once("profile/$profile_type/explorer.php") ?>
  <? include_once('profile/wall.php') ?>
</section>

<section id="/user/rr/single" class="ttl rail right user">
<? if($profile_type == 'artist'): ?>
	<? include(get_template_directory().'/tpl/global/180px/topSongs.php') ?>
	<? include(get_template_directory().'/tpl/global/180px/topArtists.php') ?>
	<? include(get_template_directory().'/tpl/global/180px/mostPopularArtists.php') ?>
<? else: ?>
	<? include(get_template_directory().'/tpl/global/180px/topRaters.php') ?>
	<? include(get_template_directory().'/tpl/global/180px/mostPopularFans.php') ?>
	<? include(get_template_directory().'/tpl/global/180px/mostActive.php') ?>
<? endif ?>
</section>
