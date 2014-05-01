<?php

// Get songs the fan has rated
$songs = song::get_fan_songs($user->ID);

?>
<div class="portlet songs">
	<div class="overlay">
		<div class="caption">
			<h3>Songs</h3>
			<div class="number">(<?= count($songs) ?>)</div>
		</div>
	</div>
  <div class="explode">
    <h2><span>Songs rated by: </span><?= $user->display_name ?></h2>
    <ul>
  <? if(count($songs)): ?>
    <? foreach($songs as $k => $song): ?>
      <? $genre = song::get_genre($song->ID) // Get the genre of the song ?>
      <li class="song" id="<?= $song->ID ?>" data-genre="<?= $genre ?>" data-content="<?= $song->ID ?>" data-artist="<?= $song->owner->display_name ?>" data-title="<?= $song->post_title ?>" data-img="<?= song::get_song_image($song->ID, 112, 112) ?>" data-src="<?= song::get_song_file($song->ID, 'demo') ?>">
        <ul>
    			<li class="name">
            <img src="<?= song::get_song_image($song->ID, 26, 26) ?>" alt="<?= $song->post_title ?>" width="26" height="26" />
            <a href="/song/<?= $song->post_name ?>" class="goTo template"><?= $song->post_title ?></a></li>
    			<?// <li class="length"></li> ?>
    			<li class="genre"><?= song::get_genre($song->ID) ?></li>
    			<li class="year"><?= date('Y', strtotime($song->post_date)) ?></li>
    			<li class="listens"><?= song::get_play_count($song->ID) ?></li>
    			<li class="rating">
	    		  <div class="stars">
	    				<div class="full"></div>
	    				<div class="progress" style="width:<?= (song::has_rated_song($song->ID)) ? ((song::get_average_rating($song->ID)/5)*100) : 0 ?>%"></div>
	    			</div>
    			</li>
    			<li class="options">
	    			<ul>
							<li><a class="<?= song::has_rated_song($song->ID) ? 'queue' : 'demo' ?>"></a></li>
            <? if($song->owner->ID == get_current_user_id()): ?>
              <li><a class="delete"></a></li>
            <? endif ?>
						</ul>
    			</li>
    		</ul>
    	</li>
    <? endforeach ?>
  <? endif ?>
    </ul>
    <div class="portlet-close"></div>
  </div>    
</div>   

