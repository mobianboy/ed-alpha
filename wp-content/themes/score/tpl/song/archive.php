<?php

// Get genre list
$genres = song::get_genres();

?>
<section class="header">
  <section class="dishPicks grid">
    <section class="topBar">
      <h2>Dish Picks</h2>
      <div class="tabs"><?// Add class, two, three, four, five for the select number of tabs ?>
        <div class="tab selected">
          <div class="userName">
            <a class="template" href="/profile/davidkersh">David Kershenbaum</a>
          </div>
          <div class="userPic">
            <a class="template" href="/profile/davidkersh"><img src="<?= user::get_user_image(40, 40, 23) ?>" alt="David Kershenbaum" /></a>
          </div>
        </div>
      </div>
      <span>by:&nbsp;</span>
    </section>
    <ul class="showSet-1">
      <? include_once('dishPicksLoop.php') ?>
    </ul>
  </section>
	<section class="topBar">
		<h2>Song Gallery</h2>
		<div class="advSearch">
      <div class="btn">Advanced Search</div>
    </div>
    <div class="search">
      <input type="input" size="17" maxlength="100" placeholder="Search" />
      <div class="find btn"><span>FIND</span></div>
    </div>
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
    <select class="selectBox orderBy" data-placeholder="Order By:">
      <option value="date">Date</option>
      <option value="title">Title</option>
      <option value="rand">Random</option>
    </select>
    <select class="selectBox order" data-placeholder="Ascending">
      <option value="desc">Desc</option>
      <option value="asc">Asc</option>
    </select>
  </section>
</section>

<section class="explorer grid">
  <a class="paginate" name="1"><span></span></a>
  <ul>
    <? include_once('archiveLoop.php') ?>
  </ul>
  <img class="showMore hidden" src="http://<?= CDN ?>/images/loaderL.gif" />
</section>

