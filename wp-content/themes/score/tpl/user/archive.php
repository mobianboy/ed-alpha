<?php

// Get genre list
$genres = song::get_genres();

?>
<section class="header">

	<section class="topBar">
		<h2>Artist/Fan Gallery</h2>
		<div class="advSearch">
      <div class="btn">Advanced Search</div>
    </div>
    <div class="search">
      <input class="left" type="input" size="17" maxlength="100" placeholder="Search" />
      <div class="find btn"><span>FIND</span></div>
    </div>
    <select class="selectBox profType" data-placeholder="Profile Type:">
  	  <option value=""></option>
  	  <option value="fan">Fan</option>
  	  <option value="artist">Artist</option>
    </select>
    <select class="selectBox genre" data-placeholder="Select a Genre">
      <option value=""></option>
    <? if(count($genres)): ?>
      <? foreach($genres as $genre): ?>
        <option value="<?= $genre->slug ?>"><?= $genre->genre ?></option>
      <? endforeach ?>
    <? endif ?>
    </select>
    <select class="selectBox location" data-placeholder="Location">
      <option value="">Location</option>
      <option value="<?= user::get_user_location(get_current_user_id()) ?>">*NEAR ME*</option>
  <? if(count(location::$locations)): ?>
    <? foreach(location::$locations as $loc => $zip): ?>
      <option value="<?= $zip ?>"><?= $loc ?></option>
    <? endforeach ?>
  <? endif ?>
    </select>
    <select class="selectBox radius" data-placeholder="Radius">
      <option value="">Radius</option>
      <option value="10">10</option>
      <option value="25">25</option>
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="250">250</option>
    </select>
    <select class="selectBox orderBy">
      <option value="registered">Joined Date</option>
      <option value="nicename">Username</option>
    </select>
    <select class="selectBox order">
      <option value="desc">Desc</option>
      <option value="asc">Asc</option>
    </select>
	</section>
<?/*
	<section class="btmBar">
		<div class="viewing">Viewing: <span>Random Fans<? $_GET['sort'] ?></span></div>
    <div class="toggleView"><span>Arrangement:</span>
			<div class="grid active"></div>
			<div class="list"></div>
		</div>
	</section>
*/?>
</section>

<section class="explorer grid">
  <a name="1" class="paginate"><span></span></a>
<? if(count($users)): ?>
  <ul>
    <? include_once('archiveLoop.php') ?>
  </ul>
  <img class="showMore hidden" src="http://<?= CDN ?>/images/loaderL.gif" />
<? endif ?>
</section>

