<?php

// Get related articles
$articles = news::get_ontheradar();

?>
<div class="widget related news">
  <h3>On The <span>Radar</span></h3>
  <ul>
<? if(count($articles)): ?>
  <? foreach($articles as $article): ?>
    <li>
	    <div class="image">
  	    <a href="/article/<?= $article->post_name ?>" class="template button">
          <img src="<?= news::get_news_image($article->ID, 100, 44) ?>" />
          <div class="date"><?= date('N d, Y', $article->post_date) ?></div>
		    </a>
	    </div>
	    <div class="title">
  	    <a href="/article/<?= $article->post_name ?>" class="template button"><?= $article->post_title ?></a>
        <div class="excerpt">
          <?= $article->post_excerpt ?>
        </div>
	    </div>
    </li>
  <? endforeach ?>
<? endif ?>
</div>

