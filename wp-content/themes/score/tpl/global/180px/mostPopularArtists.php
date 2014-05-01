<?php

// Get most popular artists (new friends)
$users = get_users(array(
  'meta_key'    => 'profile_type',
  'meta_value'  => 'artist',
));
reward::get_most_fans($users, 5);

?>
<div class="topArtists widget">
	<h3><span>Most New</span> Fans</h3>
	<div class="wrapper">
		<ul>
	<? if(count($users)): ?>
		<? foreach($users as $user): ?>
      <? $user->meta = get_user_meta($user->ID) ?>
			<li>
        <a class="template" href="/profile/<?= $user->user_login ?>">
        <div class="thumb">
						<img src="<?= user::get_user_image(18, 18, $user->ID)  ?>" alt="<?= $user->display_name ?>" width="18" height="18" />
					</div>

				  <span class="artist"><?= $user->display_name ?></span>
				  <span class="location"><?= $user->meta['City'][0] ?>, <?= $user->meta['State'][0] ?></span>
			  </a>
      </li>
    <? endforeach ?>
  <? endif ?>
		</ul>
	</div>
</div>

