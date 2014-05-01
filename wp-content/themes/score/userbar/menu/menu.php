<?php

// Load user info from WP session
get_currentuserinfo();

// Is this an archive template?
$tt = (in_array($template, array('archive', 'archiveLoop'))) ? TRUE : FALSE;

// Current user's own profile
$my_profile = ($post_type == 'user' && $template == 'single' && $content == get_current_user_id()) ? TRUE : FALSE;

?>
<div class="headCont">
	<a class="template button" href="/news/"><h1>EarDish</h1></a>
<? if(is_user_logged_in()): ?>
	<ul class="user">
	  <li class="userPic <?= ($my_profile) ? 'active' : '' ?>">
      <a class="template" href="/profile/<?= $current_user->user_login ?>"><img src="<?= user::get_user_image(20, 20) ?>" alt="<?= $current_user->display_name ?>" /><?= $current_user->display_name ?></a>
    </li>
	  <li class="more"><span><div class="arrowDown"></div></span></li>
	</ul>
	<ul class="dropdown">
		<li><a class="template" href="/account/settings/">Account Settings</a></li>
		<li><a class="pp">Privacy Policy</a></li>
		<li><a class="tos">Terms of Service</a></li>
		<li class="logout"><a>Logout</a></li>
	</ul>
	<ul class="global">
    <li class="<?= ($tt && $post_type == 'song') ? 'open' : '' ?>"><a class="template button" href="/music/">Music</a></li>
    <li class="<?= ($tt && $post_type == 'post') ? 'open' : '' ?>"><a class="template button" href="/news/">News</a></li>
	  <li class="<?= ($tt && $post_type == 'user') ? 'open' : '' ?>"><a class="template button" href="/members/">Members</a></li>
  </ul>
<? endif ?>
</div>

