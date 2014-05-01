<?php

// Move library to the begining of the playlist array
$lists = array();
$lib = playlist::get_library_id();
if(count($posts->posts)) {
  $playlists = $posts->posts;
  if(count($playlists)) {
    foreach($playlists as $key => $playlist) {
      if($playlist->ID == $lib) {
        $library = $playlist;
        unset($playlists[$key]);
      } else {
        $lists[] = $playlist;
      }
    }
  }
  array_unshift($lists, $library);
  $posts->posts = $lists;
}

?>
<ul>
  <li><a class='playlist_new playlist'><span class="playlist">Add playlist</span></a></li>
<? while($posts->have_posts()): ?>
  <? $posts->the_post() ?>
  <li>
    <a class="playlist <?= (get_the_ID() == $lib) ? 'library' : '' ?> <?= (playlist::is_active(get_the_ID())) ? 'active' : '' ?>" id="<?= get_the_ID() ?>">
      <?= get_the_title() ?>
    </a>
    <? if(get_the_ID() != $lib):?>
      <div class="edit"></div><div class="delete">X</div>
    <? endif; ?>
  </li>
<? endwhile ?>
</ul>

