<?php

// Get user object if loaded through BALLS
if(isset($content)) {
  $content = (is_numeric($content)) ? $content : user::get_user_id_by_slug($content);
  $user = get_user_by('id', $content);
}

// Get list of videos for profile
$videos = user::get_videos($user->ID);

?>
<div class="portlet videos">
	<div class="overlay">
		<div class="caption">
			<h3>Videos</h3>
			<div class="number">(<?= count($videos) ?>)</div>
		</div>
	</div>
  <div class="explode">
    <h2>Videos</h2>
  <? if($user->ID == get_current_user_id()): // if is owner ?>
    <button class="add">Add Video</button>
    <div class="addURL hidden">
      <input type="text" class="ytURL" placeholder="Add a YouTube video by pasting the URL or share link here!" />
      <button class="submit">ADD</button>
    </div>
  <? endif ?>
    <div class="wrapper">
    <? if(count($videos)): ?>
    	<ul>
    	<? foreach($videos as $video): ?>
    		<li class="video" data-ytid="<?= $video['id'] ?>">
    			<img src="<?= $video['thumb'] ?>" alt="" width="235" height="150" />
    			<div class="overlay">
    				<div class="title"><?= $video['title'] ?></div>
    			</div>
          <div class="length"><?= $video['length'] ?></div>
        <? if($user->ID == get_current_user_id()): // if is owner ?>
          <div class="delete"></div>
        <? endif ?>
    		</li>
    	<? endforeach ?>
    	</ul>
    <? else: ?>
      <ul></ul>
    <? endif ?>
    </div>
    <div class="portlet-close"></div>
  </div>
</div>

