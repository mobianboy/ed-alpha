<?php

$top_songs = new WP_Query(array(
  'post_type'   => 'song',
  'post_status' => 'publish',
));
reward::get_top_scores($top_songs, 5);

?>
<div class="topSongs widget">
	<h3><span>Highest Rated</span> Songs</h3>
	<div class="wrapper">
		<ul class="songs">
	<? if(count($top_songs->posts)): ?>
		<? foreach($top_songs->posts as $song): ?>
      <? $song->meta = get_post_meta($song->ID) ?>
      <? $song->owner = get_user_by('id', $song->post_author) ?> 
			<li>
        <a class="template" href="/song/<?= $song->post_name ?>">
					<img class="thumb" src="<?= song::get_song_image($song->ID, 18, 18) ?>" alt="<?= $song->post_title ?>" width="18" height="18" />
					<span class="title"><?= $song->post_title ?></span>
				  <span class="artist"><?= $song->owner->display_name ?></span>
        </a>
			</li>
			<? endforeach ?>
		<? endif ?>
		</ul>
	</div>
</div>

