<?php

// Get user object from content
$content = (is_numeric($content)) ? $content : user::get_user_id_by_slug($content);
$user = get_user_by('id', $content);

// Get list of videos for profile
$videos = user::get_videos($user->ID);

?>
<section class="videoGallery">
  <h1>Videos</h1>
  <button class="close"></button>
  <div class="videoPane">
    <iframe id="yt-player" type="text/html" width="640px" height="360px" src="" allowfullscreen></iframe>
  </div>
  <div class="infoPane">
    <header>
      <h2></h2>
      <p></p>
    <? if($user->ID == get_current_user_id()): // is owner ?>
      <button class="delete">Delete Video</button>
    <? endif ?>
    </header>
  <? if(count($videos)): ?>
    <ul>
    <? foreach($videos as $video): ?>
      <li data-ytid="<?= $video['id'] ?>">
        <img src="<?= $video['thumb'] ?>" />
      <? if($user->ID == get_current_user_id()): // is owner ?>
        <span class="delete"></span>
      <? endif ?>
        <div class="length"><?= $video['length'] ?></div>
        <div class="description hidden"><?= $video['desc'] ?></div>
        <div class="overlay">
          <span class="title"><?= $video['title'] ?></span>
        </div>
      </li>
    <? endforeach ?>
    </ul>
  <? else: ?>
    <ul></ul>
  <? endif ?>
  </div>
</section>

