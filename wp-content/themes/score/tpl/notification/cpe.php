<?php

// Get song object from meta key of song ID
$songs = song::get_songs($post->meta['song'][0]);
$song = $songs[0];

// Get the genre of the song
$genre = song::get_genre($song->ID);

// Get artist of the song
$artist = user::get_user($song->post_author);

?>
<img class="user" src="http://<?= CDN ?>/images/icon-eardish-note.png" alt="" width="45px" height="45px" />
<div class="content">
  <a class="template button" href="/music/">
    <div class="title"><?= $post->post_title ?></div>
    You've been selected to download a song in our <i>Music Gallery</i>.
    <span class="time"><?= relative_date($post->post_date) ?></span>
  </a>
</div>

