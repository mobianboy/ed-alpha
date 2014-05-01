<?php

// Get featured articles
$featured = array();
if(count($features)) {
  foreach($features as $feature) {
    $featured[] = get_post($feature);
  }
}

?>
<section class="news-head">
	<h2>News</h2>
	<hr>
  <div class="featured">
    <section class="position_news-head">
      <div class="col1">
        <?// image 551x306 ?>
        <a class="featureArticle tile template" href="/article/<?= news::get_post_name_by_id($featured[0]->ID) ?>">
          <img src="<?= news::get_news_image($featured[0]->ID, 551, 306) ?>" alt="<?= $featured[0]->post_title ?>" />
          <div class="caption">
          	<h3><?= $featured[0]->post_title ?></h3>
          	<p><?= $featured[0]->post_excerpt ?></p>
          </div>
        </a>
      </div>
      <a class="featured tile full template" href="/article/<?= news::get_post_name_by_id($featured[1]->ID) ?>">
        <img src="<?= news::get_news_image($featured[1]->ID, 414, 166) ?>" alt="<?= $featured[1]->post_title ?>" />
        <div class="caption">
        	<h3><?= $featured[1]->post_title ?></h3>
        	<p><?= $featured[1]->post_excerpt ?></p>
        </div>
      </a>
      <div class="col2">
        <a class="featured tile template" href="/article/<?= news::get_post_name_by_id($featured[2]->ID) ?>">
          <img src="<?= news::get_news_image($featured[2]->ID, 200, 166) ?>" alt="<?= $featured[2]->post_title ?>" />
          <div class="caption">
      	    <h3><?= $featured[2]->post_title ?></h3>
      	    <p><?= $featured[2]->post_excerpt ?></p>
          </div>
        </a>
      </div>
      <div class="col3">
        <a class="featured tile template" href="/article/<?= news::get_post_name_by_id($featured[3]->ID) ?>">
          <img src="<?= news::get_news_image($featured[3]->ID, 200, 166) ?>" alt="<?= $featured[3]->post_title ?>">
          <div class="caption">
      	    <h3><?= $featured[3]->post_title ?></h3>
      	    <p><?= $featured[3]->post_excerpt ?></p>
          </div>
        </a>
      </div>
    </section>
  </div>
  <a name="1" class="paginate"></a>
</section>
<section class="header">
    <section class="topBar">
      <select class="selectBox orderBy">
        <option value="date">Date</option>
        <option value="rand">Random</option>
      </select>
      <select class="selectBox order">
        <option value="desc">Desc</option>
        <option value="asc">Asc</option>
      </select>
      <div class="search">
        <input class="left" type="input" size="17" maxlength="100" placeholder="Search" />
        <div class="find btn"><span>FIND</span></div>
      </div>
    </section>
    <section class="btmBar">
      <?// <div class="viewing">Most Recent Articles</div> ?>
    </section>
</section>
<section class="explorer list">
  <a name="1" class="paginate"><span></span></a>
  <ul>
    <? include_once('archiveLoop.php') ?>
  </ul>
  <img class="showMore hidden" src="http://<?= CDN ?>/images/loaderL.gif" />
</section>

<section class="rail right user">
  <? include('rr-archive.php') ?>
</section>

