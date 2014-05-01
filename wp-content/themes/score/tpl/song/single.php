<?php

// Does the 1st person and 3rd person match?
$is_song_owner = ($post->post_author == get_current_user_id()) ? TRUE : FALSE;

// Other songs by the owner of this current song
$songs = song::get_artist_songs($post->post_author, $post->ID);

// List of genres
$genres = song::get_genres();
$genreList = array();
if(count($genres)) {
  foreach($genres as $genre) {
    $genreList[] = "{$genre->genre}:{$genre->ID}";
  }
}
sort($genreList);
$genres = implode(',', $genreList);

?>
<section id="/song/rr/single" class="rail right song">
  <? include(get_template_directory().'/tpl/global/180px/topSongs.php') ?>
  <? include(get_template_directory().'/tpl/global/180px/topArtists.php') ?>
  <? include(get_template_directory().'/tpl/global/180px/mostPopularArtists.php') ?>
  <?// include(get_template_directory().'/tpl/global/180px/topRaters.php') ?>
  <?// include(get_template_directory().'/tpl/global/180px/mostPopularFans.php') ?>
  <?// include(get_template_directory().'/tpl/global/180px/mostActive.php') ?>
</section>
<section class="header">
  <header>
    <h1><?= $post->post_title ?></h1>
    <h2><a class="template" href="/profile/<?= $post->owner->user_login ?>"><?= $post->owner->display_name ?></a></h2>
  <? if($is_song_owner): ?>
    <button class="deleteSong">Delete Song</button>
  <? endif ?>
    <span class="genre"<?= $is_song_owner ? 'data-eip="select" data-field="genre" data-select="'.$genres.'" data-content="'.$post->ID.'"' : '' ?>><?= song::get_genre($post->ID) ?></span>
  </header>
  <div class="song">
    <div class="art">
      <img src="<?= song::get_song_image($post->ID, 200, 197) ?>" alt="Cover Art" />
      <div class="info">
        <img src="<?= '' // silver gold platinum ?>" class="singleLevel hidden" />
        <div>Played: <span class="playCount"><?= song::get_play_count($post->ID) ?></span></div>
        <div>Rated: <span class="rateCount"><?= song::get_rate_count($post->ID) ?></span></div>
      </div>
    </div>
  <? if($is_song_owner || song::has_rated_song($post->ID)): ?>
    <div class="waveform full">
      <div class="waveImage">
        <div class="buttonArea" data-audio="<?= song::get_song_file($post->ID, 'song') ?>"><span class="play" data-audio="<?= song::get_song_file($post->ID, 'song') ?>">Full</span></div>
        <img src="<?= song::get_song_waveform($post->ID, 'song_waveform') ?>" />
        <div class="length">0:00</div>
        <?/* <div class="bpm"><?= '120 bpm' ?></div> */?>
        <div class="progress">
          <span class="progressTime">0:00</span>
        </div>
      </div>
    </div>
  <? endif ?>
  <? if($is_song_owner || !song::has_rated_song($post->ID)): ?>
    <div class="waveform demo">
      <div class="waveImage">
        <div class="buttonArea" data-audio="<?= song::get_song_file($post->ID, 'demo') ?>"><span class="play" data-audio="<?= song::get_song_file($post->ID, 'demo') ?>">Demo</span></div>
        <img src="<?= song::get_song_waveform($post->ID, 'demo_waveform') ?>" />
        <div class="length">0:00</div>
        <?/* <div class="bpm"><?= '120 bpm' ?></div> */?>
        <div class="progress">
          <span class="progressTime">0:00</span>
        </div>
      </div>
    </div>
  <? endif ?>
  <? if(!$is_song_owner): ?>
    <div class="buttons">
    <? if(song::has_rated_song($post->ID)): ?>
      <div class="split">
        <button class="atp"><span> </span>Add to Playlist</button>
      <? if(user::is_user_cpe()): ?>
        <button class="download"><span> </span>Download</button>
      <? endif ?>
      </div>
    <? endif ?>
      <div class="split right">
      <? if(!$is_song_owner): ?>
        <button class="<?= socialnetwork::get_follow($post->owner->ID) ? 'unfollow' : 'follow' ?>" data-content="<?= $post->owner->user_login ?>" data-profile="artist"></button>
      <? endif ?>
        <button class="dish"><span> </span>Dish</button>
      <? if(!$is_song_owner): ?>
        <? include(get_template_directory().'/tpl/dig/single.php') ?>
      <? endif ?>
      </div>
      <? if(song::has_rated_song($post->ID)): ?>
      <div class="rating">
        My Rating
        <div class="stars">
          <div class="full"></div>
          <div class="progress" style="width:<?= ((song::get_my_rating($post->ID) / 5) * 100) ?>%" ></div>
        </div>
      </div>
      <div class="communityRating">
        Average Rating
        <div class="stars">
          <div class="full"></div>
          <div class="progress" style="width:<?= ((song::get_average_rating($post->ID) / 5) * 100) ?>%" ></div>
        </div>
      </div>
      <? else: ?>
      <div class="rate">
        Rate Demo
        <div class="stars">
          <div class="1"></div>
          <div class="2"></div>
          <div class="3"></div>
          <div class="4"></div>
          <div class="5"></div>
        </div>
      </div>
      <? endif ?>
    </div>
  <? endif ?>
  </div>
</section>

<section class="comments" data-content="<?= $post->ID ?>">
  <h3>Comments</h3>
  <?= balls::get_balls_template(array(
    'template'  => 'archive',
    'post_type' => 'comment',
    'content'   => $post->ID,
  )) ?>
</section>

<section class="otherSongs">
  <h3>Other Songs by <?= $post->owner->display_name ?></h3>
  <ul>
<? if(count($songs)): ?>
  <? foreach($songs as $song): ?>
    <li>
      <img src="<?= song::get_song_image($song->ID, 25, 25) ?>" class="songArt" alt="<?= $song->post_title ?>" />
      <span class="artist"><?= $song->owner->display_name ?></span>
      <pre> - </pre>
      <a class="template" href="/song/<?= song::get_song_slug($song->ID) ?>"><span class="title"><?= $song->post_title ?></span></a>
      <?// <span class="length">0:00</span> ?>
      <span class="length"><?= song::get_average_rating($song->ID) ?></span>
      <span class="<?= (song::has_rated_song($song->ID)) ? 'atp' : 'play' ?>"></span>
    </li>
  <? endforeach ?>
<? endif ?>
  </ul>
</section>

