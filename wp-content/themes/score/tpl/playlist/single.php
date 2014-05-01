<?php

// Pull out list of song ID's from playlist post object
$songs = (array) json_decode(strip_tags($post->post_content));

?>
<ul class="playlist" id="_<?= $post->ID ?>" data-sort="myOrder">
<? if(count($songs)): ?>
  <? foreach($songs as $id): ?>
    <? $song = song::get_song($id) ?> 
    <li class="btn song">
      <div id= "<?= $song->ID ?>" data-post-type="playlist" data-template="<?= $post->ID ?>" data-action="play" data-content="<?= $song->ID ?>" data-title="<?= $song->post_title ?>" data-artist="<?= song::get_artist($song->post_author) ?>" data-img="<?= song::get_song_image($song->ID, 188, 188) ?>" data-vote="0">
        <div class="albumThumb"><img src="<?= song::get_song_image($song->ID, 188, 188) ?>" /></div>
        <div class="song"><span class="title"><?= $song->post_title ?></span><span class="artist"><?= song::get_artist($song->post_author) ?></span></div>
        <div class="rating">
          <div class="full"></div>
          <div class="progress" style="width:<?= ((song::get_my_rating($song->ID)/5)*100) ?>%"></div>
        </div>
        <? if($post->ID != playlist::get_library_id()): ?>
        <div class="btn delete"></div>
        <? endif ?>
      </div>
    </li>
  <? endforeach ?>
<? endif ?>
</ul>

