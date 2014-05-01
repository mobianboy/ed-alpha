<?php

$picks = new wp_query(array(
  'post_type' => 'song',
  'post__in'  => $dish_picks,
  'orderby'   => 'date',
  'order'     => 'DESC',
));

?>
<? while($picks->have_posts()): ?>
  <? $picks->the_post() ?>
  <? $genre = song::get_genre(get_the_ID()) // Get the genre of the song ?>
  <? $artist = user::get_user(get_the_author_meta('ID')) // Get artist of the song ?>
  <li class="track" id="<?= get_the_ID() ?>" data-genre="<?= $genre ?>" data-owned="<?= (song::has_rated_song(get_the_ID())) ? 'true' : 'false' ?>" data-title="<?= get_the_title() ?>" data-artist="<?= song::get_artist(get_the_author_meta('ID')) ?>" data-src="<?= song::get_song_file(get_the_ID(), 'demo', TRUE) ?>" data-img="<?= song::get_song_image(get_the_ID(), 188, 188, TRUE) ?>" data-post-type="<?= get_post_type() ?>" data-template="single" href="/song/<?= song::get_song_slug(get_the_ID()) ?>">
    <img src="<?= song::get_song_image(get_the_ID(), 188, 188, TRUE) ?>" width="188" height="188" />
    <div class="actions hidden">
      <div class="btn">
      <? if(!song::has_rated_song(get_the_ID())): ?>
        Play Demo:
        <div class="play"></div>
      <? else: ?>
        +Playlist:
        <div class="addToPlaylist"></div>
      <? endif ?>
      </div>
      <div class="btn">
        Go To Track:
        <a href="/song/<?= song::get_song_slug(get_the_ID()) ?>" class="goTo template"></a>
      </div>
    </div>
    <div class="metaBox">
      <div class="meta">
        <? if(user::is_user_cpe()): ?>
        <div class="cpePopUp" id="<?= get_the_ID() ?>" data-src="<?= song::get_song_file(get_the_ID(), 'song', TRUE) ?>" data-post-type="song" data-title="<?= get_the_title() ?>" data-artist="<?= $artist->display_name ?>" data-length="<?= get_post_meta(get_the_ID(), 'duration', TRUE) ?>" data-genre="<?= $genre ?>" data-img="<?= song::get_song_image(get_the_ID(), 68, 68, TRUE) ?>"></div>
        <? endif ?>
        <a class="title template" href="/song/<?= song::get_song_slug(get_the_ID()) ?>">
          <?= get_the_title() ?>
        </a>
        <a class="artist template" href="/profile/<?= user::get_user_login_by_id(get_the_author_meta('ID')) ?>">
          <?= song::get_artist(get_the_author_meta('ID')) ?>
        </a>
      </div>
    </div>
  </li>
<? endwhile ?>

