<?php

// Pre load genre list
$genres = song::get_genres();

?>
<div class="songConfirm" data-content="<?= $resource_id ?>">
  <div class="theClip">
    <div class="timeSelect">
      <span>Demo Length:</span>
      <input type="radio" class="sixtySec" name="demoLength" value="60" id="demo-length-60" checked="checked" /><div class="radio"></div><label for="demo-length-60">60 sec.</label>
      <input type="radio" class="nintySec" name="demoLength" value="90" id="demo-length-90" /><div class="radio"></div><label for="demo-length-90">90 sec.</label>
    </div>
    <div class="previewUpload">
      <input type="button" class="playDemo" name="playClip" value="Demo" data-audio="" />
      <?// <input type="button" class="playFull" name="playFull" value="Full" data-audio="" /> ?>
      <input type="button" class="stop hidden" name="stopSong" value="Stop" />
    </div>
    <div class="slideContainer">
      <img src="" width="100%" height="50px"/>
      <div class="selector" data-length="<?= $duration ?>"></div>
    </div>
  </div>
  <div class="meta">
    <span>Song Title:</span>
    <input name="name" type="text" size="25" value="" placeholder="Song Title" />
    <span>Genre:</span>
    <select class="genre" data-placeholder="Select Genre">
      <option value=""></option>
    <? if(count($genres)): ?>
      <? foreach($genres as $genre): ?>
        <option value="<?= $genre->ID ?>"><?= $genre->genre ?></option>
      <? endforeach ?>
    <? endif ?>
    </select>
  </div>
  <div class="commit">
    <input type="button" name="cancel" value="Cancel"/>
    <input class="red" type="button" name="save" value="Continue &#62;" data-title="" data-resource="" data-start="" data-length="" data-genre=""/>
  </div>
  <div class="albumArt hidden">
    <h3>Cover Art</h3>
    <div class="dropArea">
      <div>Drop Image [JPG/PNG/GIF] here.</div>
    </div>
    <input type="button" name="skip" value="Skip" />
  </div>
  <div class="complete hidden">
  <img src="" width="80px" height="80px" />
    <div class="meta">
      <div class="title"></div>
      <div class="artist"></div>
      <hr />
      <div class="genre"><span>Genre: </span> </div>
      <div class="length"><span>Length: </span></div>
    </div>
    <div class="full">
      <div class="playFull" data-audio="">
        <span></span>
        Full
      </div> 
      <div class="stop hidden"><span></span>Stop</div>
      <img src="" />
      <div class="progress"></div>
    </div><?// full song wave and playing capabilities ?>
    <div class="demo">
      <div class="playDemo" data-audio="">
        <span></span>
        Demo
      </div>
      <div class="stop hidden"><span></span>Stop</div>
      <img src=""/>
      <div class="progress"></div>
    </div><?// demo song wave form playing capabilities ?>
    <div class="button publish">Publish</div>
    <?// <div class="button publishPlus">Publish + 1</div> ?>
    <div class="button delete">Remove</div>
    <?// <div class="button startOver">StartOver</div> ?>
  </div>
</div>
