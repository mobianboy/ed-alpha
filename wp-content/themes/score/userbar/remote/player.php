<?php

// Force new playlist if doesn't exist for this user
$lib = playlist::get_library_id();

?>
<section id="jp_container_N">
  <div id="the_player" class="bottom">
    <div class="jp-gui">
      <div class="jp-video-play">
        <div  class="jp-video-play-icon" tabindex="1"></div>
      </div>
      <div class="jp-interface">
        <!-- controls -->
        <div class="jp-controls-holder">
          <div class="jp-controls">
            <div class="jp-previous" title="back">back</div>
            <div class="jp-play"     title="play">play</div>
            <div class="jp-pause"    title="pause">pause</div>
            <div class="jp-next"     title="next">next</div>
          </div>
          <div class="jp-controls">
          	<div class="jp-volume">
          		<div class="jp-volume-cont">
          			<div class="jp-volume-bar"></div>
                <div class="jp-volume-bar-value"></div>
          		</div>
          		<div class="jp-mute"       title="mute">mute</div>
          		<div class="jp-unmute"     title="unmute">unmute</div>
          	</div>     
            <div class="jp-volume-max" title="max volume">maxVol</div>
          </div>
          <div class="jp-now-playing">
	      	  <img src="" alt="" width="38" height="38"/>
            <div class="nowPlaying">
              <p class="nowPlayingTitle">Song</p>
              <p class="nowPlayingSeparator"> | </p>
              <p class="nowPlayingArtist">Artist</p>
            </div>
	          <div class="jp-progress">
	            <div class="jp-seek-bar"></div>
	            <div class="jp-play-bar"></div>
	          </div>
	          <div class="jp-time-c">
	            <div class="jp-current-time"></div>
	            <div class="jp-time-sep"> | </div>
	            <div class="jp-duration"></div>
	          </div>	        
          </div>
        </div>
      </div>
      <div id="the_playlists">
    	  <h2>
          <span>Playlists</span>
          <span class="title">Title</span>
          <span class="artist">Artist</span>
          <span class="rating">Rating</span>
          <input type="text" name="searchActiveList" id="searchActiveList" class="searchActiveList" />
          <div class="showSearch"></div>
        </h2>
        <div class="playlist_list">
          <?= balls::get_balls_template(array(
            'post_type' => 'playlist',
            'template'  => 'archive',
          )) ?>
        </div>
        <div class="active_playlist">
          <div class="fluffer hidden"><img src="http://<?= CDN ?>/images/loaderL.gif" /></div>
        </div>
      </div>
      <div class="btn" id="btn_playlists">
	      <span>Playlists</span>
      </div>
      <div id="jquery_jplayer_N" class="jp-jplayer"></div>
      <div class="jp-no-solution"></div>
    </div>
  </div>
</section>

