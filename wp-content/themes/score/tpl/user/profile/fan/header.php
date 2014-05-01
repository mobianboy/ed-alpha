<?php

// Last song listened to
$last_listen = user::last_song_listened_to($user->ID);

// Last profile viewed
$last_profile = user::last_profile_viewed($user->ID);

// Last song added to playlist
$last_playlist = user::last_song_added_to_playlist($user->ID);

// Last artist followed
$last_followed = user::last_artist_followed($user->ID);

// List of genres
$genres = song::get_genres();
$genreList = array();
if(count($genres)) {
  foreach($genres as $genre) {
    $genreList[] = $genre->genre;
  }
}
sort($genreList);
$genres = implode(',', $genreList);

// List of states
$stateList = array('AL', 'AK', 'AZ', 'AR', 'CA', 'CO', 'CT', 'DE', 'DC', 'FL', 'GA', 'HI', 'ID', 'IL', 'IN', 'IA', 'KS', 'KY', 'LA', 'ME', 'MD', 'MA', 'MI', 'MN', 'MS', 'MO', 'MT', 'NE', 'NV', 'NH', 'NJ', 'NM', 'NY', 'NC', 'ND', 'OH', 'OK', 'OR', 'PA', 'RI', 'SC', 'SD', 'TN', 'TX', 'UT', 'VT', 'VA', 'WA', 'WV', 'WI', 'WY');
$states = implode(',', $stateList);

// Force oldest user registration date to Mar 15, 2013
if(strtotime($user->user_registered) < strtotime('2013-04-04 00:00:00')) {
  $user->user_registered = '2013-04-04 00:00:01';
}

// Profile type of logged in session
$current_profile_type = get_user_meta(get_current_user_id(), 'profile_type' , TRUE);

?>
<? if($user->ID == get_current_user_id()): ?>
<div class="profileImageUploadWashout">
  <div class="profileImageUpload">
    <div class="wrapper">
      <div class="header">
        <span class="text">Edit Profile Picture</span>
        <div class="close">
          <span>close</span>
          <div class="closeX"></div>
        </div>
      </div>
      <div class="split left">
        <div class="caption">Browse and upload a photo from your computer</div>
        <div class="browseArea"><div class="fakeBrowse">browse</div><input type="file" name="browse" /></div>
      </div>
      <div class="split right">
        <div class="caption">Drop a photo in the field below to upload</div>
        <div class="dropArea"><input type="file" name="browse2" /></div>
      </div>
      <div class="or">OR</div>
      <div class="confirm">
        <div class="imgPreview">
          <img src="" />
        </div>
        <div class="msg">This is the image you want?</div>
        <div class="btn">Confirm</div>
      </div>
    </div>
  </div>
</div>
<? endif ?>
<header class="fan">
	<div class="topBar">
		<h2>Fan | <span class="userName" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="display_name" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->display_name)) ? $user->display_name : '(name)' ?></span></h2>
		<ul>
    <? if($user->ID == get_current_user_id()): ?>
      <li class="eipToggle"><label for="eipToggleCB"><input id="eipToggleCB" type="checkbox" />Edit Profile</label></li>
    <? endif ?>
 			<li class="sex"><span <?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="Gender" data-select="Male,Female" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['Gender'][0])) ? $user->meta['Gender'][0] : '(gender)' ?></span></li>
      <li class="fanGenre"><span <?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="main_genre" data-select="'.$genres.'" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['main_genre'][0])) ? $user->meta['main_genre'][0] : '(genre)' ?></span></li>
      <li class="location">
        <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="City" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['City'][0])) ? $user->meta['City'][0] : '(city)' ?>,</span>
        <span class="text" <?= ($user->ID == get_current_user_id()) ? 'data-eip="select" data-field="State" data-select="'.$states.'" data-content="'.$user->ID.'"' : '' ?>><?= (!empty($user->meta['State'][0])) ? $user->meta['State'][0] : '(state)' ?></span>
      </li>
			<?/* <li class="location"><span <?= ($user->ID == get_current_user_id()) ? 'data-eip="text" data-field="postal_code" data-content="'.$user->ID.'"' : '' ?>><?= $user->meta['postal_code'][0] ?></span></li> */?>
		</ul>
	</div>
	<div class="main">
		<div class="mainImg">
			<img data-eip="image" src="<?= user::get_user_image(374, 250, $user->ID) ?>" alt="" width="374" height="250" />
    <? if($user->ID == get_current_user_id()): ?>
      <span class="editPictureOverlay">Edit Picture&nbsp;&nbsp;</span>
    <? endif ?>
		</div>
		<div class="explorer mini">
			<? include('explodes/rewards.php') ?>
			<? include('explodes/bio.php') ?>
			<? include('explodes/playlists.php') ?>	
			<div class="recentActivity">
				<dl>
					<dt>Recently Listened:</dt>
					<dd>
          <? if(count($last_listen)): ?>
            <a class="template" href="/song/<?= $last_listen['song']->post_name ?>"><?= $last_listen['song']->post_title ?></a>
             by 
            <a class="template" href="/profile/<?= $last_listen['artist']->data->user_login ?>"><?= $last_listen['artist']->data->display_name ?></a>
          <? endif ?>
          </dd>
					<dt>Last Profile Viewed:</dt>
					<dd>
            <a class="template" href="/profile/<?= user::get_user_login_by_id($last_profile->ID) ?>"><?= $last_profile->display_name ?></a>
          </dd>
					<dt>Last Artist Followed:</dt>
					<dd>
            <a class="template" href="/profile/<?= user::get_user_login_by_id($last_followed->ID) ?>"><?= $last_followed->display_name ?></a>
          </dd>
				</dl>
			</div>
		</div>
		<div class="more">
    <? if($user->ID != get_current_user_id()): ?>
			<div class="buttons">
				<? if($current_profile_type == 'artist'): ?>
          <div class="<?= socialnetwork::get_follow($user->ID) ? 'unfollow' : 'follow' ?>" data-profile="fan"></div>
        <? else: ?>
          <div class="<?= socialnetwork::get_follow($user->ID) ? 'unfriend': (socialnetwork::get_follow($user->ID, TRUE) ? 'requested' : 'friend') ?>" data-profile="fan"></div>
        <? endif ?>
        <div class="shout">Send a Shout</div>
			</div>
    <? endif ?>
			<div class="stats">
				<dl>
					<dt>Joined EarDish:</dt>
					<dd><?= date('M j, Y', strtotime($user->user_registered)) ?></dd>
					<dt>Songs Demoed/Rated:</dt>
					<dd><?= user::number_of_songs_rated($user->ID) ?></dd>
					<dt>Songs Rated Today:</dt>
					<dd><?= user::number_of_songs_rated_today($user->ID) ?></dd>
				</dl>
			</div>
		</div>
	</div>
</header>

